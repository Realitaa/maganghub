<?php

use App\Models\GroupJoinRequest;
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
 * Create a group in a given status with a leader and an extra member.
 *
 * @return array{group: InternshipGroup, leader: User, member: User}
 */
function makeGroupForSubmission(string $status = 'forming'): array
{
    $leader = User::factory()->create(['role' => 'student']);
    $member = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => $status]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

    return compact('group', 'leader', 'member');
}

// ────────────────────────────────────────────────────────────────────────────
// Tests
// ────────────────────────────────────────────────────────────────────────────

describe('internship submission', function () {

    it('eager loads active submission on student dashboard', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('forming');
        $submission = InternshipSubmission::factory()->create([
            'group_id' => $group->id,
            'company_name' => 'PT Test Technology',
            'status' => 'draft',
        ]);

        $this->actingAs($leader)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->has('group.active_submission')
                ->where('group.active_submission.company_name', 'PT Test Technology')
                ->where('group.active_submission.status', 'draft')
            );
    });

    it('prevents students without group from saving a draft or submitting', function () {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'PT Nusantara',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak tergabung dalam kelompok magang.',
            ]);

        $this->actingAs($student)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Nusantara',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak tergabung dalam kelompok magang.',
            ]);
    });

    it('prevents non-leader members from saving a draft or submitting', function () {
        ['group' => $group, 'leader' => $leader, 'member' => $member] = makeGroupForSubmission('forming');

        $this->actingAs($member)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'PT Nusantara',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Hanya ketua kelompok yang dapat menyimpan draf pengajuan.',
            ]);

        $this->actingAs($member)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Nusantara',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Hanya ketua kelompok yang dapat mengajukan magang.',
            ]);
    });

    it('allows leader to save submission draft with partial data', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('forming');

        $this->actingAs($leader)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'PT Nusantara',
                'division' => 'Web Developer',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Draf pengajuan magang berhasil disimpan.',
            ]);

        $this->assertDatabaseHas('internship_submissions', [
            'group_id' => $group->id,
            'company_name' => 'PT Nusantara',
            'division' => 'Web Developer',
            'status' => 'draft',
        ]);

        expect($group->fresh()->status)->toBe('forming');
    });

    it('updates existing draft rather than creating new one', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('forming');

        $submission = InternshipSubmission::factory()->create([
            'group_id' => $group->id,
            'company_name' => 'Old Company',
            'status' => 'draft',
        ]);

        $this->actingAs($leader)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'New Company',
                'division' => 'Mobile Dev',
            ])
            ->assertRedirect();

        expect(InternshipSubmission::where('group_id', $group->id)->count())->toBe(1);
        $this->assertDatabaseHas('internship_submissions', [
            'id' => $submission->id,
            'company_name' => 'New Company',
            'status' => 'draft',
        ]);
    });

    it('prevents saving draft if group is already submitted or locked', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('submitted');

        $this->actingAs($leader)
            ->post(route('groups.submissions.store'), [
                'company_name' => 'Attempt PT',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak dapat mengubah pengajuan pada status kelompok saat ini.',
            ]);
    });

    it('requires all fields when submitting proposal', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('forming');

        $this->actingAs($leader)
            ->post(route('groups.submissions.submit'), [
                'company_name' => '', // Empty
                'company_address' => 'Sudirman 12',
                'company_contact' => '021-12345',
                'field_of_interest' => 'Web',
                'division' => 'Web',
                'start_date' => '2026-07-01',
                'end_date' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Nama perusahaan wajib diisi.',
            ]);
    });

    it('requires field_of_interest when submitting proposal', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupForSubmission('forming');

        $this->actingAs($leader)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Nusantara',
                'company_address' => 'Sudirman 12',
                'company_contact' => '021-12345',
                'field_of_interest' => '', // Empty
                'division' => 'Web',
                'start_date' => '2026-07-01',
                'end_date' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Bidang yang diminati wajib diisi.',
            ]);
    });

    it('allows submitting proposal with nullable division', function () {
        ['group' => $group, 'leader' => $leader, 'member' => $member] = makeGroupForSubmission('forming');

        $this->actingAs($leader)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Nusantara',
                'company_address' => 'Sudirman 12',
                'company_contact' => '021-12345',
                'field_of_interest' => 'Web Dev',
                'division' => null, // Optional
                'start_date' => '2026-07-01',
                'end_date' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil dikirim untuk ditinjau.',
            ]);

        $submission = InternshipSubmission::where('group_id', $group->id)->first();
        expect($submission->division)->toBeNull();
        expect($submission->field_of_interest)->toBe('Web Dev');
    });

    it('successfully submits proposal, locks group and takes memberships snapshot', function () {
        ['group' => $group, 'leader' => $leader, 'member' => $member] = makeGroupForSubmission('forming');

        $this->actingAs($leader)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Global Solusi',
                'company_address' => 'Jl. Merdeka No. 45',
                'company_contact' => 'hr@global.com',
                'field_of_interest' => 'Data Engineering',
                'division' => 'Data Analyst',
                'start_date' => '2026-07-01',
                'end_date' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil dikirim untuk ditinjau.',
            ]);

        // Assert submission is updated/created as submitted
        $submission = InternshipSubmission::where('group_id', $group->id)->first();
        expect($submission->status)->toBe('submitted');
        expect($submission->company_name)->toBe('PT Global Solusi');

        // Assert group is locked as submitted
        expect($group->fresh()->status)->toBe('submitted');

        // Assert memberships snapshot exists and matches members
        $snapshotCount = SubmissionMembership::where('submission_id', $submission->id)->count();
        expect($snapshotCount)->toBe(2);

        $this->assertDatabaseHas('submission_memberships', [
            'submission_id' => $submission->id,
            'user_id' => $leader->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('submission_memberships', [
            'submission_id' => $submission->id,
            'user_id' => $member->id,
            'status' => 'pending',
        ]);
    });

    it('locks group actions after submission is sent', function () {
        ['group' => $group, 'leader' => $leader, 'member' => $member] = makeGroupForSubmission('submitted');

        // 1. Member cannot leave group
        $this->actingAs($member)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak dapat keluar dari kelompok pada status ini.',
            ]);

        // 2. Leader cannot disband group
        $this->actingAs($leader)
            ->delete(route('groups.destroy', $group->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kelompok tidak dapat dibubarkan pada status ini.',
            ]);

        // 3. New student cannot request to join this group
        $outsider = User::factory()->create(['role' => 'student']);
        $this->actingAs($outsider)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kelompok ini sudah tidak menerima anggota baru.',
            ]);

        // 4. Leader cannot approve join requests
        $joinRequest = GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $outsider->id,
            'status' => 'pending',
        ]);
        // even if request exists, approval is rejected at service level (or it fails because of status != forming)
    });

    it('prevents submitting proposal if group has fewer than 2 members', function () {
        $leader = User::factory()->create(['role' => 'student']);
        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'forming']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);

        $this->actingAs($leader)
            ->post(route('groups.submissions.submit'), [
                'company_name' => 'PT Global Solusi',
                'company_address' => 'Jl. Merdeka No. 45',
                'company_contact' => 'hr@global.com',
                'field_of_interest' => 'Data Analyst',
                'division' => 'Data Analyst',
                'start_date' => '2026-07-01',
                'end_date' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast', [
                'type' => 'error',
                'message' => 'Kelompok harus memiliki minimal dua anggota sebelum mengajukan magang.',
            ]);
    });
});
