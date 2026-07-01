<?php

namespace App\Services;

use App\Models\GroupJoinRequest;
use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\User;
use App\Notifications\JoinRequestAcceptedNotification;
use App\Notifications\JoinRequestRejectedNotification;
use App\Notifications\KickedFromGroupNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InternshipGroupService
{
    /**
     * Create a new internship group. The user becomes the leader and first member.
     *
     * @throws ValidationException
     */
    public function createGroup(User $user): InternshipGroup
    {
        if (! $user->hasChangedPassword() || ! $user->isProfileComplete()) {
            throw ValidationException::withMessages([
                'error' => 'Anda harus mengubah password default dan melengkapi biodata terlebih dahulu.',
            ]);
        }

        if ($user->ledGroups()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Anda sudah menjadi ketua di kelompok magang lain.',
            ]);
        }

        // Cancel all pending join requests before creating a new group
        GroupJoinRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $group = InternshipGroup::create([
            'leader_id' => $user->id,
            'code' => $this->generateUniqueCode(),
            'status' => 'forming',
        ]);

        GroupMembership::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        return $group;
    }

    /**
     * Send a join request to an internship group.
     *
     * @throws ValidationException
     */
    public function requestToJoin(User $user, string $code): GroupJoinRequest
    {
        if (! $user->hasChangedPassword() || ! $user->isProfileComplete()) {
            throw ValidationException::withMessages([
                'error' => 'Anda harus mengubah password default dan melengkapi biodata terlebih dahulu.',
            ]);
        }

        $group = InternshipGroup::where('code', $code)->first();

        if (! $group) {
            throw ValidationException::withMessages([
                'code' => 'Kode kelompok tidak ditemukan.',
            ]);
        }

        if ($group->memberships()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Kamu sudah tergabung dalam kelompok ini.',
            ]);
        }

        if ($group->status !== 'forming') {
            throw ValidationException::withMessages([
                'code' => 'Kelompok ini sudah tidak menerima anggota baru.',
            ]);
        }

        $alreadyRequested = GroupJoinRequest::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyRequested) {
            throw ValidationException::withMessages([
                'code' => 'Kamu sudah mengirim permintaan bergabung ke kelompok ini.',
            ]);
        }

        return GroupJoinRequest::updateOrCreate(
            [
                'group_id' => $group->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );
    }

    /**
     * Cancel a pending join request.
     *
     * @throws ValidationException
     */
    public function cancelJoinRequest(User $user, GroupJoinRequest $request): void
    {
        if ($request->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak memiliki izin untuk membatalkan permintaan ini.',
            ]);
        }

        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => 'Permintaan ini tidak dapat dibatalkan.',
            ]);
        }

        $request->delete();
    }

    /**
     * Approve a join request. The student becomes a group member.
     * All other pending requests from the student are cancelled.
     *
     * @throws ValidationException
     */
    public function approveJoinRequest(User $leader, GroupJoinRequest $request): GroupMembership
    {
        $group = $request->group;

        if ($group->leader_id !== $leader->id) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak memiliki izin untuk menyetujui permintaan ini.',
            ]);
        }

        if ($group->status !== 'forming') {
            throw ValidationException::withMessages([
                'error' => 'Kelompok ini sudah tidak menerima anggota baru.',
            ]);
        }

        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => 'Permintaan ini sudah tidak dapat diproses.',
            ]);
        }

        $student = $request->user;

        // Race condition: student is already in THIS group
        if ($group->memberships()->where('user_id', $student->id)->exists()) {
            $request->update(['status' => 'cancelled']);

            throw ValidationException::withMessages([
                'error' => 'Mahasiswa ini sudah tergabung ke kelompok ini.',
            ]);
        }

        // Accept the student
        $membership = GroupMembership::create([
            'group_id' => $group->id,
            'user_id' => $student->id,
        ]);

        $request->update(['status' => 'approved']);

        // Send notification
        $groupName = $group->activeSubmission?->company_name ?: $group->leader->name;
        $student->notify(new JoinRequestAcceptedNotification($group->id, $groupName, $leader->name));

        // Do not cancel other requests since they can join multiple groups

        return $membership;
    }

    /**
     * Reject a join request.
     *
     * @throws ValidationException
     */
    public function rejectJoinRequest(User $leader, GroupJoinRequest $request): GroupJoinRequest
    {
        $group = $request->group;

        if ($group->leader_id !== $leader->id) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak memiliki izin untuk menolak permintaan ini.',
            ]);
        }

        $request->update(['status' => 'rejected']);

        // Send notification
        $student = $request->user;
        $groupName = $group->activeSubmission?->company_name ?: $group->leader->name;
        $student->notify(new JoinRequestRejectedNotification($group->id, $groupName));

        return $request;
    }

    /**
     * Leave a group. Only non-leaders can leave, and only in allowed statuses.
     *
     * @throws ValidationException
     */
    public function leaveGroup(User $user, InternshipGroup $group): void
    {
        $membership = $group->memberships()->where('user_id', $user->id)->first();

        if (! $membership) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak tergabung dalam kelompok ini.',
            ]);
        }

        if ($group->leader_id === $user->id) {
            throw ValidationException::withMessages([
                'error' => 'Ketua kelompok tidak dapat keluar dari kelompok.',
            ]);
        }

        $allowedStatuses = ['forming', 'company_rejected'];
        if (! in_array($group->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak dapat keluar dari kelompok pada status ini.',
            ]);
        }

        $membership->delete();
    }

    /**
     * Disband a group. Only the leader can do this, and only in allowed statuses.
     *
     * @throws ValidationException
     */
    public function disbandGroup(User $user, InternshipGroup $group): void
    {
        if ($group->leader_id !== $user->id) {
            throw ValidationException::withMessages([
                'error' => 'Hanya ketua kelompok yang dapat membubarkan kelompok.',
            ]);
        }

        $allowedStatuses = ['forming', 'company_rejected'];
        if (! in_array($group->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'error' => 'Kelompok tidak dapat dibubarkan pada status ini.',
            ]);
        }

        $group->delete();
    }

    /**
     * Kick a member from the group. Only the leader can do this, and only in allowed statuses.
     *
     * @throws ValidationException
     */
    public function kickMember(User $leader, User $member, InternshipGroup $group): void
    {
        $membership = $group->memberships()->where('user_id', $leader->id)->first();

        if (! $membership) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak tergabung dalam kelompok ini.',
            ]);
        }

        if ($group->leader_id !== $leader->id) {
            throw ValidationException::withMessages([
                'error' => 'Hanya ketua kelompok yang dapat mengeluarkan anggota.',
            ]);
        }

        if ($member->id === $leader->id) {
            throw ValidationException::withMessages([
                'error' => 'Ketua kelompok tidak dapat mengeluarkan diri sendiri.',
            ]);
        }

        $allowedStatuses = ['forming', 'company_rejected'];
        if (! in_array($group->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'error' => 'Anggota tidak dapat dikeluarkan pada status kelompok saat ini.',
            ]);
        }

        $memberMembership = GroupMembership::where('group_id', $group->id)
            ->where('user_id', $member->id)
            ->first();

        if (! $memberMembership) {
            throw ValidationException::withMessages([
                'error' => 'Mahasiswa tersebut bukan merupakan anggota kelompok Anda.',
            ]);
        }

        $memberMembership->delete();

        // Send notification to the kicked user
        $groupName = $group->activeSubmission?->company_name ?: $group->leader->name;
        $member->notify(new KickedFromGroupNotification($groupName, $leader->name));
    }

    /**
     * Generate a unique 10-character uppercase alphanumeric group code.
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (InternshipGroup::where('code', $code)->exists());

        return $code;
    }
}
