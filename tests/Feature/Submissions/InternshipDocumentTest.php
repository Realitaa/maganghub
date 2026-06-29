<?php

use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\SubmissionMembership;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

// ────────────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────────────

function setupFakeTemplate(): string
{
    $tempFile = tempnam(sys_get_temp_dir(), 'test_docx_');
    $zip = new ZipArchive;
    $zip->open($tempFile, ZipArchive::CREATE);
    $zip->addFromString('word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>{{name}} {{nim}} {{semester}} {{phone}} {{email}} {{number}} {{today}} {{company_name}} {{start_date}} {{end_date}} {{calculateDuration}} {{field_of_interest}} {{division ? Division : field_of_interest}}</w:t></w:r></w:p><w:sectPr><w:pgSz w:w="12240" w:h="15840"/></w:sectPr></w:body></w:document>');
    $zip->close();
    $dummyDocxContent = file_get_contents($tempFile);
    unlink($tempFile);

    Storage::put('templates/letter_template.docx', $dummyDocxContent);

    return $dummyDocxContent;
}

function makeSubmittedSubmissionForLetter(): array
{
    $leader = User::factory()->create(['role' => 'student']);
    $member = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'submitted']);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

    $submission = InternshipSubmission::factory()->submitted()->create([
        'group_id' => $group->id,
        'company_name' => 'PT Solusi Bersama',
        'start_date' => '2026-07-01',
        'end_date' => '2026-09-30',
    ]);

    SubmissionMembership::factory()->create(['submission_id' => $submission->id, 'user_id' => $leader->id]);
    SubmissionMembership::factory()->create(['submission_id' => $submission->id, 'user_id' => $member->id]);

    return compact('group', 'leader', 'member', 'submission');
}

// ────────────────────────────────────────────────────────────────────────────
// Tests
// ────────────────────────────────────────────────────────────────────────────

describe('Template Management Upload', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('allows operators to view template index page', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        $this->actingAs($operator)
            ->get(route('review.templates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/templates/Index')
                ->has('template')
                ->where('template.exists', false)
            );
    });

    it('prevents students from viewing template index page', function () {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('review.templates.index'))
            ->assertForbidden();
    });

    it('allows operator to upload a new docx template', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        $file = UploadedFile::fake()->create('letter_template.docx', 500);

        $this->actingAs($operator)
            ->post(route('review.templates.store'), [
                'file' => $file,
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Template surat permohonan magang berhasil diperbarui.',
            ]);

        Storage::assertExists('templates/letter_template.docx');
    });

    it('rejects template upload with wrong file extension', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        $file = UploadedFile::fake()->create('letter_template.txt', 100);

        $this->actingAs($operator)
            ->post(route('review.templates.store'), [
                'file' => $file,
            ])
            ->assertSessionHasErrors(['file']);
    });
});

describe('Generate Letter', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('generates document when submission is approved', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        expect($submission->fresh()->status)->toBe('letter_published');
        $memberships = $submission->fresh()->submissionMemberships;
        expect($memberships)->not->toBeEmpty();
        foreach ($memberships as $membership) {
            expect($membership->letter_path)->not->toBeNull();
        }
    });

    it('stores generated document', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        $memberships = $submission->fresh()->submissionMemberships;
        foreach ($memberships as $membership) {
            Storage::assertExists($membership->letter_path);
        }
    });

    it('uses submission memberships snapshot instead of current group memberships', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmissionForLetter();

        // Alter current group membership (remove the member), but snapshot remains
        GroupMembership::where('group_id', $group->id)->where('user_id', '!=', $group->leader_id)->delete();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        // Read XML from generated DOCX for the leader membership
        $leaderMembership = $submission->fresh()->submissionMemberships()->where('user_id', $group->leader_id)->first();
        $path = $leaderMembership->letter_path;
        $tempFile = tempnam(sys_get_temp_dir(), 'out_docx_');
        file_put_contents($tempFile, Storage::get($path));

        $zip = new ZipArchive;
        $zip->open($tempFile);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($tempFile);

        // Leader name from snapshot should be inside
        expect(html_entity_decode($xml))->toContain($group->leader->name);
    });

    it('generates document only once and does not regenerate on re-approval attempts', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        // 1. Approve
        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        $leaderMembership = $submission->fresh()->submissionMemberships()->where('user_id', $submission->group->leader_id)->first();
        $firstPath = $leaderMembership->letter_path;
        expect($firstPath)->not->toBeNull();

        // Temporarily reset status to test calling approve on it
        $submission->update(['status' => 'submitted']);

        // 2. Approve again
        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        $secondPath = $submission->fresh()->submissionMemberships()->where('user_id', $submission->group->leader_id)->first()->letter_path;
        expect($secondPath)->toBe($firstPath);
    });
});

describe('Download Letter', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('administrator can download generated letter as zip', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        $admin = User::factory()->create(['role' => 'administrator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $response = $this->actingAs($admin)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertOk();

        $filename = 'surat_permohonan_magang_'.($submission->group->code ?? $submission->id).'.zip';
        $response->assertHeader('Content-Disposition', 'attachment; filename='.$filename);
    });

    it('operator can download generated letter as zip', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $response = $this->actingAs($operator)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertOk();

        $filename = 'surat_permohonan_magang_'.($submission->group->code ?? $submission->id).'.zip';
        $response->assertHeader('Content-Disposition', 'attachment; filename='.$filename);
    });

    it('operator can download individual student letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $response = $this->actingAs($operator)
            ->get(route('groups.submissions.download-letter', [
                'submission' => $submission->id,
                'user_id' => $leader->id,
            ]))
            ->assertOk();

        $safeName = str_replace([' ', '/', '\\'], '_', $leader->name);
        $filename = 'surat_permohonan_magang_'.$safeName.'_'.($submission->group->code ?? $submission->id).'.docx';
        $response->assertHeader('Content-Disposition', 'attachment; filename='.$filename);
    });

    it('leader cannot download generated letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $this->actingAs($leader)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertForbidden();
    });

    it('member cannot download generated letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'member' => $member] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $this->actingAs($member)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertForbidden();
    });

    it('non member cannot download generated letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $outsider = User::factory()->create(['role' => 'student']);
        $this->actingAs($outsider)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertForbidden();
    });

    it('guest cannot download generated letter', function () {
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();
        $this->get(route('groups.submissions.download-letter', $submission->id))
            ->assertRedirect(route('login'));
    });

    it('letter cannot be downloaded before approval', function () {
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($leader)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertForbidden();
    });
});

describe('Student Dashboard', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('document card is hidden before approval', function () {
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($leader)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->has('group')
                ->where('group.active_submission.status', 'submitted')
            );
    });

    it('document card is shown after approval', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $this->actingAs($leader)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->has('group')
                ->where('group.active_submission.status', 'letter_published')
            );
    });

    it('upload company response button is visible only to leader', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        // Check leader view
        $this->actingAs($leader)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->where('auth.user.id', $leader->id)
                ->where('group.leader_id', $leader->id)
            );

        // Check member view
        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->where('auth.user.id', $member->id)
                ->where('group.leader_id', $leader->id)
            );
    });
});

describe('Upload Company Response', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('leader can upload response letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $file = UploadedFile::fake()->create('balasan.pdf', 500);

        $this->actingAs($leader)
            ->post(route('groups.submissions.upload-response', $submission->id), [
                'file' => $file,
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Surat balasan perusahaan berhasil diunggah.',
            ]);

        expect($submission->fresh()->company_response_path)->not->toBeNull();
        expect($submission->fresh()->status)->toBe('loa_review');
        expect($submission->fresh()->group->status)->toBe('loa_review');
        Storage::assertExists($submission->fresh()->company_response_path);
    });

    it('member cannot upload response letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'member' => $member] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $file = UploadedFile::fake()->create('balasan.pdf', 500);

        $this->actingAs($member)
            ->post(route('groups.submissions.upload-response', $submission->id), [
                'file' => $file,
            ])
            ->assertForbidden();
    });

    it('guest cannot upload response letter', function () {
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();
        $file = UploadedFile::fake()->create('balasan.pdf', 500);

        $this->post(route('groups.submissions.upload-response', $submission->id), [
            'file' => $file,
        ])->assertRedirect(route('login'));
    });

    it('upload validates file type', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $file = UploadedFile::fake()->create('balasan.txt', 500);

        $this->actingAs($leader)
            ->post(route('groups.submissions.upload-response', $submission->id), [
                'file' => $file,
            ])
            ->assertSessionHasErrors(['file']);
    });

    it('upload validates file size', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        $file = UploadedFile::fake()->create('balasan.pdf', 3000); // 3 MB (limit is 2 MB)

        $this->actingAs($leader)
            ->post(route('groups.submissions.upload-response', $submission->id), [
                'file' => $file,
            ])
            ->assertSessionHasErrors(['file']);
    });

    it('reupload replaces previous response letter', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        // 1st upload
        $file1 = UploadedFile::fake()->create('balasan1.pdf', 100);
        $this->actingAs($leader)
            ->post(route('groups.submissions.upload-response', $submission->id), ['file' => $file1])
            ->assertRedirect();

        $firstPath = $submission->fresh()->company_response_path;
        Storage::assertExists($firstPath);

        // 2nd upload
        $file2 = UploadedFile::fake()->create('balasan2.pdf', 200);
        $this->actingAs($leader)
            ->post(route('groups.submissions.upload-response', $submission->id), ['file' => $file2])
            ->assertRedirect();

        $secondPath = $submission->fresh()->company_response_path;
        Storage::assertExists($secondPath);
        Storage::assertMissing($firstPath); // Replaced and deleted
    });
});

describe('Edge Cases', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('approving submission after memberships changed still uses snapshot members', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmissionForLetter();

        // Add a member to group memberships after submission
        $newMember = User::factory()->create(['role' => 'student']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $newMember->id]);

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        // The generated XML should NOT contain the new member, only the snapshot members
        $leaderMembership = $submission->fresh()->submissionMemberships()->where('user_id', $group->leader_id)->first();
        $path = $leaderMembership->letter_path;
        $tempFile = tempnam(sys_get_temp_dir(), 'out_docx_');
        file_put_contents($tempFile, Storage::get($path));

        $zip = new ZipArchive;
        $zip->open($tempFile);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($tempFile);

        expect($xml)->not->toContain($newMember->name);
    });

    it('document generation survives if current group memberships no longer match snapshot', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmissionForLetter();

        // Remove all current group memberships completely to simulate mismatched states
        GroupMembership::where('group_id', $group->id)->delete();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect();

        $leaderMembership = $submission->fresh()->submissionMemberships()->where('user_id', $group->leader_id)->first();
        expect($leaderMembership->letter_path)->not->toBeNull();
    });

    it('document generation fails safely when template is missing', function () {
        // Do NOT call setupFakeTemplate()
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Gagal membuat dokumen permohonan magang: Template surat permohonan magang belum diunggah oleh administrator.',
            ]);

        expect($submission->fresh()->status)->toBe('submitted'); // Rolled back / not changed
        foreach ($submission->fresh()->submissionMemberships as $membership) {
            expect($membership->letter_path)->toBeNull();
        }
    });

    it('download fails gracefully if document file is missing', function () {
        setupFakeTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader] = makeSubmittedSubmissionForLetter();

        $this->actingAs($operator)->post(route('review.submissions.approve', $submission->id))->assertRedirect();

        // Delete generated files from Storage
        foreach ($submission->fresh()->submissionMemberships as $membership) {
            Storage::delete($membership->letter_path);
        }

        $this->actingAs($operator)
            ->get(route('groups.submissions.download-letter', $submission->id))
            ->assertNotFound();
    });
});
