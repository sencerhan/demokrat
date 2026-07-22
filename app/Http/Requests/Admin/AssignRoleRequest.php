<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = RoleName::tryFrom((string) $this->input('role'));
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
        return [
            'role' => ['required', Rule::enum(RoleName::class)],
        ];
    }

    public function roleName(): RoleName
    {
        return RoleName::from((string) $this->input('role'));
    }
}
