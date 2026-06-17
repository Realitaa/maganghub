<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $currentUser): bool
    {
        return in_array($currentUser->role, ['administrator', 'operator']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $currentUser): bool
    {
        return in_array($currentUser->role, ['administrator', 'operator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->role === 'administrator') {
            return true;
        }

        if ($currentUser->role === 'operator') {
            // Cannot update administrator
            if ($targetUser->role === 'administrator') {
                return false;
            }
            // Cannot update other operators
            if ($targetUser->role === 'operator' && $targetUser->id !== $currentUser->id) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->role === 'administrator') {
            return true;
        }

        return false; // Operators can't delete anyone
    }
}
