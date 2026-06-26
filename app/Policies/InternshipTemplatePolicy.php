<?php

namespace App\Policies;

use App\Models\User;

class InternshipTemplatePolicy
{
    /**
     * Determine whether the user can manage internship templates.
     */
    public function manage(User $user): bool
    {
        return in_array($user->role, ['operator', 'administrator']);
    }
}
