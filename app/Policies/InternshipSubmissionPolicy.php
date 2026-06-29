<?php

namespace App\Policies;

use App\Models\InternshipSubmission;
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

    /**
     * Determine whether the user can download the generated letter.
     */
    public function downloadLetter(User $user, InternshipSubmission $submission): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }

    /**
     * Determine whether the user can upload the company response.
     */
    public function uploadResponse(User $user, InternshipSubmission $submission): bool
    {
        return $submission->group && $submission->group->leader_id === $user->id;
    }
}
