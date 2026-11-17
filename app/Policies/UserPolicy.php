<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Activate / deactivate an account. Admin accounts are out of reach on purpose:
     * an admin cannot lock themselves out, and a stolen admin session cannot
     * disable the other admins who would respond to the incident.
     */
    public function changeStatus(User $actor, User $target): bool
    {
        return $actor->isAdmin()
            && $actor->is_active
            && $actor->id !== $target->id
            && ! $target->isAdmin();
    }
}
