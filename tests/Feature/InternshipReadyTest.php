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
function makeReadySubmission(): array
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

function setupFakeReadyTemplate(): string
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

describe('ready for internship authorization', function () {

    it('redirects guest to login for all ready routes', function () {
        ['submission' => $submission] = makeReadySubmission();

        $this->get(route('review.ready.index'))->assertRedirect(route('login'));
        $this->post(route('review.submissions.mark-applying', $submission->id))->assertRedirect(route('login'));
        $this->post(route('review.submissions.company-decision', $submission->id))->assertRedirect(route('login'));
    });

    it('prevents students from accessing ready routes', function () {
        $student = User::factory()->create(['role' => 'student']);
        ['submission' => $submission] = makeReadySubmission();

        $this->actingAs($student)->get(route('review.ready.index'))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.mark-applying', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('review.submissions.company-decision', $submission->id))->assertForbidden();
    });

    it('allows operators to access ready routes', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeReadySubmission();

        $this->actingAs($operator)->get(route('review.ready.index'))->assertOk();
    });

    it('allows administrators to access ready routes', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        ['submission' => $submission] = makeReadySubmission();

        $this->actingAs($admin)->get(route('review.ready.index'))->assertOk();
    });

});

describe('ready to print / siap magang workflow', function () {

    it('displays the correct ready to print and applying groups in readyIndex', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        ['submission' => $sub1] = makeReadySubmission();
        $sub1->update(['status' => 'letter_published']);
        $sub1->group->update(['status' => 'letter_published']);

        ['submission' => $sub2] = makeReadySubmission();
        $sub2->update(['status' => 'applying']);
        $sub2->group->update(['status' => 'applying']);

        ['submission' => $sub3] = makeReadySubmission();
        $sub3->update(['status' => 'applying', 'company_response_path' => 'responses/file.pdf']);
        $sub3->group->update(['status' => 'applying']);

        $this->actingAs($operator)
            ->get(route('review.ready.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/Ready')
                ->has('readyToPrint', 1)
                ->where('readyToPrint.0.id', $sub1->id)
                ->has('waitingResponse', 2)
                ->has('receivedResponse', 1)
                ->where('receivedResponse.0.id', $sub3->id)
            );
    });

    it('successfully transitions letter_published submission to applying', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeReadySubmission();

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
        ['submission' => $submission] = makeReadySubmission();

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
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeReadySubmission();

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

        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('accepted');
        expect($submission->submissionMemberships()->where('user_id', $member->id)->first()->status)->toBe('accepted');

        expect($group->memberships()->count())->toBe(2);
    });

    it('processes company outcome decision: all rejected', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeReadySubmission();

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

        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('rejected');
        expect($submission->submissionMemberships()->where('user_id', $member->id)->first()->status)->toBe('rejected');

        expect($group->memberships()->count())->toBe(0);
    });

    it('processes company outcome decision: partially accepted (leader accepted, member rejected)', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeReadySubmission();

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

        expect($submission->submissionMemberships()->where('user_id', $leader->id)->first()->status)->toBe('accepted');
        $rejectedMem = $submission->submissionMemberships()->where('user_id', $member->id)->first();
        expect($rejectedMem->status)->toBe('rejected');
        expect($rejectedMem->rejection_note)->toBe('Kuota divisi penuh');

        expect($group->memberships()->where('user_id', $member->id)->exists())->toBeFalse();
        expect($group->memberships()->where('user_id', $leader->id)->exists())->toBeTrue();
        expect($group->fresh()->leader_id)->toBe($leader->id);
    });

    it('processes company outcome decision: partially accepted (leader rejected, member accepted, requires new leader)', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group, 'leader' => $leader, 'member' => $member] = makeReadySubmission();

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

        expect($group->memberships()->where('user_id', $leader->id)->exists())->toBeFalse();
        expect($group->memberships()->where('user_id', $member->id)->exists())->toBeTrue();
    });

});
