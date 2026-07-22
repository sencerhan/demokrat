<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the actor may assign or revoke the given role on the target.
     *
     * Matrix:
     * - superadmin: may manage any role (including admin/superadmin).
     * - admin: may manage only moderator and member.
     * - everyone else: may manage no roles.
     */
    public function manageRoles(User $actor, User $target, RoleName $role): bool
    {
        if ($actor->hasRole(RoleName::SuperAdmin->value)) {
            return true;
        }

        if ($actor->hasRole(RoleName::Admin->value)) {
            return in_array($role, [RoleName::Moderator, RoleName::Member], true);
        }

        return false;
    }
}
