<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RevokeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = RoleName::tryFrom((string) $this->route('role'));
        $target = $this->route('user');

        if ($role === null || ! $target instanceof User) {
            return false;
        }

        return Gate::allows('manageRoles', [$target, $role]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function roleName(): RoleName
    {
        return RoleName::from((string) $this->route('role'));
    }
}
