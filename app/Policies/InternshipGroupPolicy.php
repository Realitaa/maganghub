<?php

namespace App\Policies;

use App\Models\InternshipGroup;
use App\Models\User;

class InternshipGroupPolicy
{
    /**
     * Determine whether the user can update the internship group banner.
     */
    public function updateBanner(User $user, InternshipGroup $group): bool
    {
        return $group->leader_id === $user->id;
    }

    /**
     * Determine whether the user can disband/delete the internship group.
     */
    public function delete(User $user, InternshipGroup $group): bool
    {
        return $group->leader_id === $user->id;
    }

    /**
     * Determine whether the user can kick a member from the internship group.
     */
    public function kick(User $user, InternshipGroup $group): bool
    {
        return $group->leader_id === $user->id;
    }
}
