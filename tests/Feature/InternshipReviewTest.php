<?php

use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\SubmissionMembership;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake();
});

// ────────────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────────────

/**
 * Create a group with a leader and an extra member, then submit a proposal.
 *
 * @return array{group: InternshipGroup, leader: User, member: User, submission: InternshipSubmission}
 */
function makeSubmittedSubmission(): array
{
    $leader = User::factory()->create(['role' => 'student']);
    $member = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'submitted']);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

    $submission = InternshipSubmission::factory()->submitted()->create([
        'group_id' => $group->id,
        'company_name' => 'PT Solusi Bersama',
    ]);

    SubmissionMembership::factory()->create(['submission_id' => $submission->id, 'user_id' => $leader->id]);
    SubmissionMembership::factory()->create(['submission_id' => $submission->id, 'user_id' => $member->id]);

    return compact('group', 'leader', 'member', 'submission');
}

function setupFakeReviewTemplate(): string
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

// ────────────────────────────────────────────────────────────────────────────
// Tests
// ────────────────────────────────────────────────────────────────────────────

describe('review submission authorization', function () {

    it('redirects guest to login for all review routes', function () {
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->get(route('review.submissions.index'))->assertRedirect(route('login'));
        $this->get(route('review.submissions.show', $submission->id))->assertRedirect(route('login'));
        $this->post(route('review.submissions.approve', $submission->id))->assertRedirect(route('login'));
        $this->post(route('review.submissions.reject', $submission->id))->assertRedirect(route('login'));
        $this->get(route('review.ready.index'))->assertRedirect(route('login'));
        $this->post(route('review.submissions.mark-applying', $submission->id))->assertRedirect(route('login'));
        $this->post(route('review.submissions.company-decision', $submission->id))->assertRedirect(route('login'));
    });

    it('prevents students from accessing review routes', function () {
        $student = User::factory()->create(['role' => 'student']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($student)->get(route('review.submissions.index'))->assertForbidden();
        $this->actingAs($student)->get(route('review.submissions.show', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.approve', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.reject', $submission->id))->assertForbidden();
        $this->actingAs($student)->get(route('review.ready.index'))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.mark-applying', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.company-decision', $submission->id))->assertForbidden();
    });

    it('allows operators to access review routes', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($operator)->get(route('review.submissions.index'))->assertOk();
        $this->actingAs($operator)->get(route('review.submissions.show', $submission->id))->assertOk();
        $this->actingAs($operator)->get(route('review.ready.index'))->assertOk();
    });

    it('allows administrators to access review routes', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($admin)->get(route('review.submissions.index'))->assertOk();
        $this->actingAs($admin)->get(route('review.submissions.show', $submission->id))->assertOk();
        $this->actingAs($admin)->get(route('review.ready.index'))->assertOk();
    });

});

describe('review submission index list', function () {

    it('only lists submitted (active) submissions', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        // Active submitted submission
        ['submission' => $activeSub] = makeSubmittedSubmission();

        // Draft submission (inactive)
        $groupDraft = InternshipGroup::factory()->create(['status' => 'forming']);
        $draftSub = InternshipSubmission::factory()->create([
            'group_id' => $groupDraft->id,
            'status' => 'draft',
        ]);

        // Rejected submission (inactive)
        $groupRejected = InternshipGroup::factory()->create(['status' => 'forming']);
        $rejectedSub = InternshipSubmission::factory()->create([
            'group_id' => $groupRejected->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($operator)
            ->get(route('review.submissions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/submissions/Index')
                ->has('submissions', 1)
                ->where('submissions.0.id', $activeSub->id)
                ->where('submissions.0.company_name', 'PT Solusi Bersama')
            );
    });

});

describe('review submission detail', function () {

    it('returns detail JSON of active submission with memberships snapshot', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmission();

        $response = $this->actingAs($operator)
            ->get(route('review.submissions.show', $submission->id))
            ->assertOk();

        $data = $response->json('data');
        expect($data['id'])->toBe($submission->id);
        expect($data['company_name'])->toBe('PT Solusi Bersama');
        expect($data['group']['leader']['id'])->toBe($leader->id);
        expect(count($data['submission_memberships']))->toBe(2);
        expect($data['submission_memberships'][0]['user']['id'])->toBe($leader->id);
        expect($data['submission_memberships'][1]['user']['id'])->toBe($member->id);
    });

});

describe('reject submission', function () {

    it('successfully rejects submission, changes group to forming, and stores notes', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader] = makeSubmittedSubmission();

        $this->actingAs($operator)
            ->post(route('review.submissions.reject', $submission->id), [
                'notes' => 'Alasan penolakan: Berkas kurang lengkap.',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil ditolak.',
            ]);

        expect($submission->fresh()->status)->toBe('rejected');
        expect($submission->fresh()->rejection_note)->toBe('Alasan penolakan: Berkas kurang lengkap.');
        expect($group->fresh()->status)->toBe('forming');

        // Leader can now edit again
        $this->actingAs($leader)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'PT Baru Sukses',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Draf pengajuan magang berhasil disimpan.',
            ]);
    });

    it('requires reject notes', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($operator)
            ->post(route('review.submissions.reject', $submission->id), [
                'notes' => '',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Catatan penolakan wajib diisi.',
            ]);
    });

});

describe('approve submission', function () {

    it('successfully approves submission and changes status to letter_published', function () {
        setupFakeReviewTemplate();
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmission();

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil disetujui.',
            ]);

        expect($submission->fresh()->status)->toBe('letter_published');
        expect($group->fresh()->status)->toBe('letter_published');
    });

});

describe('edge cases and race conditions', function () {

    it('cannot reject an already rejected submission', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $submission->update(['status' => 'rejected']);

        $this->actingAs($operator)
            ->post(route('review.submissions.reject', $submission->id), [
                'notes' => 'Tolak lagi',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Pengajuan ini sudah tidak dapat diproses.',
            ]);
    });

    it('cannot approve an already approved submission', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $submission->update(['status' => 'letter_published']);

        $this->actingAs($operator)
            ->post(route('review.submissions.approve', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Pengajuan ini sudah tidak dapat diproses.',
            ]);
    });

});

describe('ready to print / siap magang workflow', function () {

    it('displays the correct ready to print and applying groups in readyIndex', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        ['submission' => $sub1] = makeSubmittedSubmission();
        $sub1->update(['status' => 'letter_published']);
        $sub1->group->update(['status' => 'letter_published']);

        ['submission' => $sub2] = makeSubmittedSubmission();
        $sub2->update(['status' => 'applying']);
        $sub2->group->update(['status' => 'applying']);

        ['submission' => $sub3] = makeSubmittedSubmission();
        $sub3->update(['status' => 'applying', 'company_response_path' => 'responses/file.pdf']);
        $sub3->group->update(['status' => 'applying']);

        $this->actingAs($operator)
            ->get(route('review.ready.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/Ready')
                ->has('readyToPrint', 1)
                ->where('readyToPrint.0.id', $sub1->id)
                ->has('waitingResponse', 2) // both sub2 and sub3 will be in waitingResponse (the client component filters sub3 using company_response_path)
                ->has('receivedResponse', 1)
                ->where('receivedResponse.0.id', $sub3->id)
            );
    });

    it('successfully transitions letter_published submission to applying', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmission();

        $submission->update(['status' => 'letter_published']);
        $group->update(['status' => 'letter_published']);

        $this->actingAs($operator)
            ->post(route('review.submissions.mark-applying', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Status kelompok berhasil diubah menjadi sedang mengajukan.',
            ]);

        expect($submission->fresh()->status)->toBe('applying');
        expect($group->fresh()->status)->toBe('applying');
    });

    it('cannot mark as applying if status is not letter_published', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($operator)
            ->post(route('review.submissions.mark-applying', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kelompok ini belum disetujui atau sudah mengajukan.',
            ]);
    });

    it('processes company outcome decision: all accepted', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmission();

        $submission->update(['status' => 'applying']);
        $group->update(['status' => 'applying']);

        $this->actingAs($operator)
            ->post(route('review.submissions.company-decision', $submission->id), [
                'decision' => 'all_accepted',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Keputusan penempatan perusahaan berhasil diproses.',
            ]);

        expect($submission->fresh()->status)->toBe('accepted');
        expect($group->fresh()->status)->toBe('accepted');

        // Members status updated in snapshot
        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('accepted');
        expect($submission->submissionMemberships()->where('user_id', $member->id)->first()->status)->toBe('accepted');

        // Active group memberships remain
        expect($group->memberships()->count())->toBe(2);
    });

    it('processes company outcome decision: all rejected', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmission();

        $submission->update(['status' => 'applying']);
        $group->update(['status' => 'applying']);

        $this->actingAs($operator)
            ->post(route('review.submissions.company-decision', $submission->id), [
                'decision' => 'all_rejected',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Keputusan penempatan perusahaan berhasil diproses.',
            ]);

        expect($submission->fresh()->status)->toBe('rejected');
        expect($group->fresh()->status)->toBe('rejected');

        // Members status updated in snapshot
        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('rejected');
        expect($submission->submissionMemberships()->where('user_id', $member->id)->first()->status)->toBe('rejected');

        // Active group memberships are DELETED to free them
        expect($group->memberships()->count())->toBe(0);
    });

    it('processes company outcome decision: partially accepted (leader accepted, member rejected)', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmission();

        $submission->update(['status' => 'applying']);
        $group->update(['status' => 'applying']);

        $this->actingAs($operator)
            ->post(route('review.submissions.company-decision', $submission->id), [
                'decision' => 'partially_accepted',
                'member_decisions' => [
                    ['user_id' => $leader->id, 'status' => 'accepted'],
                    ['user_id' => $member->id, 'status' => 'rejected', 'rejection_note' => 'Kuota divisi penuh'],
                ],
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Keputusan penempatan perusahaan berhasil diproses.',
            ]);

        expect($submission->fresh()->status)->toBe('partially_accepted');
        expect($group->fresh()->status)->toBe('partially_accepted');

        // Member rejection note and status updated in snapshot
        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('accepted');
        $rejectedMem = $submission->submissionMemberships()->where('user_id', $member->id)->first();
        expect($rejectedMem->status)->toBe('rejected');
        expect($rejectedMem->rejection_note)->toBe('Kuota divisi penuh');

        // Active memberships updated: rejected member is removed, leader remains
        expect($group->memberships()->where('user_id', $member->id)->exists())->toBeFalse();
        expect($group->memberships()->where('user_id', $leader->id)->exists())->toBeTrue();
        expect($group->fresh()->leader_id)->toBe($leader->id);
    });

    it('processes company outcome decision: partially accepted (leader rejected, member accepted, requires new leader)', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeSubmittedSubmission();

        $submission->update(['status' => 'applying']);
        $group->update(['status' => 'applying']);

        // Attempt without specifying new leader - should fail validation
        $this->actingAs($operator)
            ->post(route('review.submissions.company-decision', $submission->id), [
                'decision' => 'partially_accepted',
                'member_decisions' => [
                    ['user_id' => $leader->id, 'status' => 'rejected', 'rejection_note' => 'Posisi tidak sesuai'],
                    ['user_id' => $member->id, 'status' => 'accepted'],
                ],
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Ketua kelompok ditolak, Anda wajib menunjuk Ketua baru dari anggota yang diterima.',
            ]);

        // Submit with valid new leader selection
        $this->actingAs($operator)
            ->post(route('review.submissions.company-decision', $submission->id), [
                'decision' => 'partially_accepted',
                'member_decisions' => [
                    ['user_id' => $leader->id, 'status' => 'rejected', 'rejection_note' => 'Posisi tidak sesuai'],
                    ['user_id' => $member->id, 'status' => 'accepted'],
                ],
                'new_leader_id' => $member->id,
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Keputusan penempatan perusahaan berhasil diproses.',
            ]);

        $group = $group->fresh();
        expect($group->status)->toBe('partially_accepted');
        expect($group->leader_id)->toBe($member->id);

        // Old leader's active group membership is removed
        expect($group->memberships()->where('user_id', $leader->id)->exists())->toBeFalse();
        // New leader's active group membership remains
        expect($group->memberships()->where('user_id', $member->id)->exists())->toBeTrue();
    });

});
