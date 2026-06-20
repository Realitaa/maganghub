<?php

use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\SubmissionMembership;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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

// ────────────────────────────────────────────────────────────────────────────
// Tests
// ────────────────────────────────────────────────────────────────────────────

describe('review submission authorization', function () {

    it('redirects guest to login for all review routes', function () {
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->get(route('operator.submissions.index'))->assertRedirect(route('login'));
        $this->get(route('operator.submissions.show', $submission->id))->assertRedirect(route('login'));
        $this->post(route('operator.submissions.approve', $submission->id))->assertRedirect(route('login'));
        $this->post(route('operator.submissions.reject', $submission->id))->assertRedirect(route('login'));
    });

    it('prevents students from accessing review routes', function () {
        $student = User::factory()->create(['role' => 'student']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($student)->get(route('operator.submissions.index'))->assertForbidden();
        $this->actingAs($student)->get(route('operator.submissions.show', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('operator.submissions.approve', $submission->id))->assertForbidden();
        $this->actingAs($student)->post(route('operator.submissions.reject', $submission->id))->assertForbidden();
    });

    it('allows operators to access review routes', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($operator)->get(route('operator.submissions.index'))->assertOk();
        $this->actingAs($operator)->get(route('operator.submissions.show', $submission->id))->assertOk();
    });

    it('allows administrators to access review routes', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        ['submission' => $submission] = makeSubmittedSubmission();

        $this->actingAs($admin)->get(route('operator.submissions.index'))->assertOk();
        $this->actingAs($admin)->get(route('operator.submissions.show', $submission->id))->assertOk();
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
            ->get(route('operator.submissions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('submissions/Index')
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
            ->get(route('operator.submissions.show', $submission->id))
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
            ->post(route('operator.submissions.reject', $submission->id), [
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
            ->post(route('operator.submissions.reject', $submission->id), [
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
        $operator = User::factory()->create(['role' => 'operator']);
        ['submission' => $submission, 'group' => $group] = makeSubmittedSubmission();

        $this->actingAs($operator)
            ->post(route('operator.submissions.approve', $submission->id))
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
            ->post(route('operator.submissions.reject', $submission->id), [
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
            ->post(route('operator.submissions.approve', $submission->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Pengajuan ini sudah tidak dapat diproses.',
            ]);
    });

});
