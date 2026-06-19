<?php

namespace App\Policies;

use App\Models\User;

class InternshipSubmissionPolicy
{
    /**
     * Determine whether the user can view any submissions.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }

    /**
     * Determine whether the user can approve the submission.
     */
    public function approve(User $user): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }

    /**
     * Determine whether the user can reject the submission.
     */
    public function reject(User $user): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }
}
