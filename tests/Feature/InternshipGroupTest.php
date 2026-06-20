<?php

use App\Models\GroupJoinRequest;
use App\Models\GroupMembership;
use App\Models\InternshipGroup;
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
function makeGroupWithMember(string $status = 'forming'): array
{
    $leader = User::factory()->create(['role' => 'student']);
    $member = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => $status]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

    return compact('group', 'leader', 'member');
}

/**
 * Create a forming group with only the leader as member, and return a student
 * who has a pending join request to it.
 *
 * @return array{group: InternshipGroup, leader: User, student: User, request: GroupJoinRequest}
 */
function makeGroupWithPendingRequest(): array
{
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);
    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
    $request = GroupJoinRequest::factory()->create(['group_id' => $group->id, 'user_id' => $student->id]);

    return compact('group', 'leader', 'student', 'request');
}

// ────────────────────────────────────────────────────────────────────────────
// 1. Dashboard
// ────────────────────────────────────────────────────────────────────────────

describe('dashboard', function () {

    it('redirects guest to login', function () {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    });

    it('renders student/Index with null group when student has no group', function () {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->where('group', null)
                ->where('pendingJoinRequests', [])
            );
    });

    it('renders student/Index with pending join requests when student has requests', function () {
        ['student' => $student] = makeGroupWithPendingRequest();

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->where('group', null)
                ->has('pendingJoinRequests', 1)
            );
    });

    it('renders student/Index with group data when student is in a group', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupWithMember();

        $this->actingAs($leader)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('student/Index')
                ->has('group')
                ->where('group.id', $group->id)
            );
    });

    it('exposes pending join requests only to the group leader', function () {
        ['group' => $group, 'leader' => $leader, 'member' => $member] = makeGroupWithMember();
        $requester = User::factory()->create(['role' => 'student']);
        GroupJoinRequest::factory()->create(['group_id' => $group->id, 'user_id' => $requester->id]);

        // Leader sees requests
        $this->actingAs($leader)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('group.join_requests', 1)
            );

        // Member does NOT see requests
        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('group.join_requests', 0)
            );
    });

    it('exposes only actionable pending join requests to the group leader', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupWithMember();

        $pendingStudent = User::factory()->create(['role' => 'student']);
        $approvedStudent = User::factory()->create(['role' => 'student']);
        $rejectedStudent = User::factory()->create(['role' => 'student']);
        $cancelledStudent = User::factory()->create(['role' => 'student']);
        $staleStudent = User::factory()->create(['role' => 'student']);

        $pendingRequest = GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $pendingStudent->id,
            'status' => 'pending',
        ]);

        GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $approvedStudent->id,
            'status' => 'approved',
        ]);

        GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $rejectedStudent->id,
            'status' => 'rejected',
        ]);

        GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $cancelledStudent->id,
            'status' => 'cancelled',
        ]);

        GroupJoinRequest::factory()->create([
            'group_id' => $group->id,
            'user_id' => $staleStudent->id,
            'status' => 'pending',
        ]);

        $otherGroup = InternshipGroup::factory()->create();
        GroupMembership::factory()->create(['group_id' => $otherGroup->id, 'user_id' => $otherGroup->leader_id]);
        GroupMembership::factory()->create(['group_id' => $otherGroup->id, 'user_id' => $staleStudent->id]);

        $this->actingAs($leader)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('group.join_requests', 1)
                ->where('group.join_requests.0.id', $pendingRequest->id)
                ->where('group.join_requests.0.status', 'pending')
            );
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 2. Creating a Group
// ────────────────────────────────────────────────────────────────────────────

describe('creating a group', function () {

    it('creates a group and makes the student leader and member', function () {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('groups.store'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Berhasil membuat kelompok magang.');

        $group = InternshipGroup::where('leader_id', $student->id)->first();
        expect($group)->not->toBeNull();
        expect($group->status)->toBe('forming');
        expect($group->code)->toHaveLength(10);
        expect($group->code)->toMatch('/^[A-Z0-9]+$/');
        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $student->id)->exists())->toBeTrue();
    });

    it('prevents a student already in a group from creating another', function () {
        ['leader' => $leader] = makeGroupWithMember();

        $before = InternshipGroup::count();

        $this->actingAs($leader)
            ->post(route('groups.store'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(InternshipGroup::count())->toBe($before);
    });

    it('cancels all pending join requests when a student creates a group', function () {
        $student = User::factory()->create(['role' => 'student']);

        // Student has pending requests to two different groups
        $groupA = InternshipGroup::factory()->create();
        $groupB = InternshipGroup::factory()->create();
        $requestA = GroupJoinRequest::factory()->create(['group_id' => $groupA->id, 'user_id' => $student->id]);
        $requestB = GroupJoinRequest::factory()->create(['group_id' => $groupB->id, 'user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('groups.store'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect($requestA->fresh()->status)->toBe('cancelled');
        expect($requestB->fresh()->status)->toBe('cancelled');
        expect(GroupMembership::where('user_id', $student->id)->exists())->toBeTrue();
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 3. Requesting to Join
// ────────────────────────────────────────────────────────────────────────────

describe('requesting to join a group', function () {

    it('sends a join request successfully', function () {
        $student = User::factory()->create(['role' => 'student']);
        $group = InternshipGroup::factory()->create();
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $group->leader_id]);

        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Permintaan bergabung telah dikirim. Tunggu persetujuan ketua kelompok.');

        expect(GroupJoinRequest::where('group_id', $group->id)->where('user_id', $student->id)->where('status', 'pending')->exists())->toBeTrue();
    });

    it('returns validation error for an invalid code', function () {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => 'DOESNOTEXIST'])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');
    });

    it('prevents a student already in a group from sending a join request', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember();
        $otherGroup = InternshipGroup::factory()->create();

        $this->actingAs($member)
            ->post(route('groups.join'), ['code' => $otherGroup->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupJoinRequest::where('user_id', $member->id)->exists())->toBeFalse();
    });

    it('prevents sending a duplicate request to the same group', function () {
        ['group' => $group, 'student' => $student, 'request' => $existing] = makeGroupWithPendingRequest();

        $before = GroupJoinRequest::where('user_id', $student->id)->count();

        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupJoinRequest::where('user_id', $student->id)->count())->toBe($before);
    });

    it('prevents joining a group that is not in forming status', function () {
        $student = User::factory()->create(['role' => 'student']);
        $group = InternshipGroup::factory()->submitted()->create();
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $group->leader_id]);

        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupJoinRequest::where('user_id', $student->id)->exists())->toBeFalse();
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 4. Canceling a Join Request
// ────────────────────────────────────────────────────────────────────────────

describe('canceling a join request', function () {

    it('allows a student to cancel their own pending request', function () {
        ['request' => $request, 'student' => $student] = makeGroupWithPendingRequest();

        $this->actingAs($student)
            ->delete(route('groups.join-requests.cancel', $request))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Permintaan bergabung dibatalkan.');

        expect(GroupJoinRequest::find($request->id))->toBeNull();
    });

    it('prevents a student from canceling someone else\'s request', function () {
        ['request' => $request] = makeGroupWithPendingRequest();
        $other = User::factory()->create(['role' => 'student']);

        $this->actingAs($other)
            ->delete(route('groups.join-requests.cancel', $request))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupJoinRequest::find($request->id))->not->toBeNull();
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 5. Approving a Join Request
// ────────────────────────────────────────────────────────────────────────────

describe('approving a join request', function () {

    it('approves a join request and cancels all other pending requests from that student', function () {
        $student = User::factory()->create(['role' => 'student']);

        $leaderA = User::factory()->create(['role' => 'student']);
        $groupA = InternshipGroup::factory()->create(['leader_id' => $leaderA->id]);
        GroupMembership::factory()->create(['group_id' => $groupA->id, 'user_id' => $leaderA->id]);
        $requestA = GroupJoinRequest::factory()->create(['group_id' => $groupA->id, 'user_id' => $student->id]);

        $leaderB = User::factory()->create(['role' => 'student']);
        $groupB = InternshipGroup::factory()->create(['leader_id' => $leaderB->id]);
        GroupMembership::factory()->create(['group_id' => $groupB->id, 'user_id' => $leaderB->id]);
        $requestB = GroupJoinRequest::factory()->create(['group_id' => $groupB->id, 'user_id' => $student->id]);

        $leaderC = User::factory()->create(['role' => 'student']);
        $groupC = InternshipGroup::factory()->create(['leader_id' => $leaderC->id]);
        GroupMembership::factory()->create(['group_id' => $groupC->id, 'user_id' => $leaderC->id]);
        $requestC = GroupJoinRequest::factory()->create(['group_id' => $groupC->id, 'user_id' => $student->id]);

        // Leader B approves
        $this->actingAs($leaderB)
            ->post(route('groups.join-requests.approve', $requestB))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Permintaan bergabung diterima.');

        expect(GroupMembership::where('group_id', $groupB->id)->where('user_id', $student->id)->exists())->toBeTrue();
        expect($requestB->fresh()->status)->toBe('approved');
        expect($requestA->fresh()->status)->toBe('cancelled');
        expect($requestC->fresh()->status)->toBe('cancelled');
    });

    it('prevents a non-leader from approving a join request', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember();
        $requester = User::factory()->create(['role' => 'student']);
        $request = GroupJoinRequest::factory()->create(['group_id' => $group->id, 'user_id' => $requester->id]);

        $this->actingAs($member)
            ->post(route('groups.join-requests.approve', $request))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupMembership::where('user_id', $requester->id)->exists())->toBeFalse();
    });

    it('handles race condition where student is already in a group when leader approves', function () {
        $student = User::factory()->create(['role' => 'student']);

        // Group A already accepted the student
        $groupA = InternshipGroup::factory()->create();
        GroupMembership::factory()->create(['group_id' => $groupA->id, 'user_id' => $groupA->leader_id]);
        GroupMembership::factory()->create(['group_id' => $groupA->id, 'user_id' => $student->id]);

        // Group B leader has a stale pending request
        $leaderB = User::factory()->create(['role' => 'student']);
        $groupB = InternshipGroup::factory()->create(['leader_id' => $leaderB->id]);
        GroupMembership::factory()->create(['group_id' => $groupB->id, 'user_id' => $leaderB->id]);
        $requestB = GroupJoinRequest::factory()->create(['group_id' => $groupB->id, 'user_id' => $student->id]);

        $this->actingAs($leaderB)
            ->post(route('groups.join-requests.approve', $requestB))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupMembership::where('group_id', $groupB->id)->where('user_id', $student->id)->exists())->toBeFalse();
        expect(GroupMembership::where('group_id', $groupA->id)->where('user_id', $student->id)->count())->toBe(1);
        expect($requestB->fresh()->status)->toBe('cancelled');
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 6. Rejecting a Join Request
// ────────────────────────────────────────────────────────────────────────────

describe('rejecting a join request', function () {

    it('allows a leader to reject a join request', function () {
        ['group' => $group, 'leader' => $leader, 'request' => $request] = makeGroupWithPendingRequest();

        // Re-fetch $leader and $request from the helper output. Helper returns leader with its own group.
        // Create a fresh scenario for clarity:
        ['group' => $group2, 'leader' => $leader2, 'student' => $student2] = makeGroupWithPendingRequest();

        $this->actingAs($leader2)
            ->post(route('groups.join-requests.reject', $request = GroupJoinRequest::where('group_id', $group2->id)->first()))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Permintaan bergabung ditolak.');

        expect($request->fresh()->status)->toBe('rejected');
        expect(GroupMembership::where('user_id', $student2->id)->exists())->toBeFalse();
    });

    it('prevents a non-leader from rejecting a join request', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember();
        $requester = User::factory()->create(['role' => 'student']);
        $request = GroupJoinRequest::factory()->create(['group_id' => $group->id, 'user_id' => $requester->id]);

        $this->actingAs($member)
            ->post(route('groups.join-requests.reject', $request))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect($request->fresh()->status)->toBe('pending');
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 7. Leaving a Group
// ────────────────────────────────────────────────────────────────────────────

describe('leaving a group', function () {

    it('allows a member to leave a group in forming status', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember('forming');

        $this->actingAs($member)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Berhasil keluar dari kelompok magang.');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $member->id)->exists())->toBeFalse();
    });

    it('allows a member to leave a group in company_rejected status', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember('company_rejected');

        $this->actingAs($member)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $member->id)->exists())->toBeFalse();
    });

    it('prevents the leader from leaving the group', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupWithMember('forming');

        $this->actingAs($leader)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $leader->id)->exists())->toBeTrue();
    });

    it('prevents a member from leaving when group status is submitted', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember('submitted');

        $this->actingAs($member)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $member->id)->exists())->toBeTrue();
    });

    it('prevents a member from leaving when group status is under_review', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember('under_review');

        $this->actingAs($member)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $member->id)->exists())->toBeTrue();
    });

});

// ────────────────────────────────────────────────────────────────────────────
// 8. Disbanding a Group
// ────────────────────────────────────────────────────────────────────────────

describe('disbanding a group', function () {

    it('allows the leader to disband a group in forming status', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupWithMember('forming');

        $this->actingAs($leader)
            ->delete(route('groups.destroy', $group))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', 'Berhasil membubarkan kelompok magang.');

        expect(InternshipGroup::find($group->id))->toBeNull();
        expect(GroupMembership::where('group_id', $group->id)->exists())->toBeFalse();
    });

    it('allows the leader to disband a group in company_rejected status', function () {
        ['group' => $group, 'leader' => $leader] = makeGroupWithMember('company_rejected');

        $this->actingAs($leader)
            ->delete(route('groups.destroy', $group))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect(InternshipGroup::find($group->id))->toBeNull();
    });

    it('prevents a non-leader from disbanding the group', function () {
        ['group' => $group, 'member' => $member] = makeGroupWithMember('forming');

        $this->actingAs($member)
            ->delete(route('groups.destroy', $group))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect(InternshipGroup::find($group->id))->not->toBeNull();
    });

    it('prevents the leader from disbanding a locked group', function () {
        foreach (['submitted', 'under_review', 'letter_published'] as $status) {
            ['group' => $group, 'leader' => $leader] = makeGroupWithMember($status);

            $this->actingAs($leader)
                ->delete(route('groups.destroy', $group))
                ->assertRedirect()
                ->assertInertiaFlash('toast.type', 'error');

            expect(InternshipGroup::find($group->id))->not->toBeNull();
        }
    });

});

describe('group membership lifecycle', function () {
    it('allows a student to join, leave, request to join again, and be approved again', function () {
        $leader = User::factory()->create(['role' => 'student']);
        $student = User::factory()->create(['role' => 'student']);

        // 1. kelompok magang dibuat oleh seorang ketua
        $this->actingAs($leader)
            ->post(route('groups.store'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        $group = InternshipGroup::where('leader_id', $leader->id)->first();
        expect($group)->not->toBeNull();

        // 2. seorang mahasiswa bergabung ke kelompok magang
        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        $joinRequest = GroupJoinRequest::where('group_id', $group->id)
            ->where('user_id', $student->id)
            ->where('status', 'pending')
            ->first();
        expect($joinRequest)->not->toBeNull();

        // 3. mahasiswa tersebut diterima bergabung
        $this->actingAs($leader)
            ->post(route('groups.join-requests.approve', $joinRequest->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $student->id)->exists())->toBeTrue();

        // 4. mahasiswa tersebut keluar dari kelompok
        $this->actingAs($student)
            ->post(route('groups.leave'))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $student->id)->exists())->toBeFalse();

        // 5. mencoba bergabung kembali
        $this->actingAs($student)
            ->post(route('groups.join'), ['code' => $group->code])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        $newJoinRequest = GroupJoinRequest::where('group_id', $group->id)
            ->where('user_id', $student->id)
            ->where('status', 'pending')
            ->first();
        expect($newJoinRequest)->not->toBeNull();

        // 6. dapat diterima bergabung
        $this->actingAs($leader)
            ->post(route('groups.join-requests.approve', $newJoinRequest->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        // 7. menjadi anggota dari kelompok itu lagi
        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $student->id)->exists())->toBeTrue();
    });
});
