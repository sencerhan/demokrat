<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Talep;
use App\Models\User;

class TalepPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Talep $talep): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function vote(User $user, Talep $talep): bool
    {
        return true;
    }

    public function update(User $user, Talep $talep): bool
    {
        if ($this->isModerator($user)) {
            return true;
        }

        return $user->id === $talep->user_id && $talep->votes()->doesntExist();
    }

    public function updateStatus(User $user, Talep $talep): bool
    {
        return $this->isModerator($user);
    }

    public function delete(User $user, Talep $talep): bool
    {
        if ($this->isModerator($user)) {
            return true;
        }

        return $user->id === $talep->user_id && $talep->votes()->doesntExist();
    }

    private function isModerator(User $user): bool
    {
        return $user->hasAnyRole(RoleName::moderatorAndAbove());
    }
}
