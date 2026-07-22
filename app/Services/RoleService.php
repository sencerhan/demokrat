<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Exceptions\CannotRemoveLastSuperAdminException;
use App\Models\User;

class RoleService
{
    public function assignRole(User $actor, User $target, RoleName $role): void
    {
        $target->assignRole($role->value);

        activity('role')
            ->causedBy($actor)
            ->performedOn($target)
            ->withProperties(['role' => $role->value, 'action' => 'assigned'])
            ->log('role_assigned');
    }

    public function revokeRole(User $actor, User $target, RoleName $role): void
    {
        if ($role === RoleName::SuperAdmin && $this->isLastSuperAdmin($target)) {
            throw new CannotRemoveLastSuperAdminException;
        }

        $target->removeRole($role->value);

        activity('role')
            ->causedBy($actor)
            ->performedOn($target)
            ->withProperties(['role' => $role->value, 'action' => 'revoked'])
            ->log('role_revoked');
    }

    private function isLastSuperAdmin(User $target): bool
    {
        if (! $target->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return User::role(RoleName::SuperAdmin->value)->count() <= 1;
    }
}
