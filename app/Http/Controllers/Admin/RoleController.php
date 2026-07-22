<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Exceptions\CannotRemoveLastSuperAdminException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\RevokeRoleRequest;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->all(),
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'users' => $users,
            'availableRoles' => array_map(fn (RoleName $r) => $r->value, RoleName::cases()),
        ]);
    }

    public function store(AssignRoleRequest $request, User $user, RoleService $service): RedirectResponse
    {
        $service->assignRole($request->user(), $user, $request->roleName());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Rol atandı.')]);

        return back();
    }

    public function destroy(RevokeRoleRequest $request, User $user, string $role, RoleService $service): RedirectResponse
    {
        try {
            $service->revokeRole($request->user(), $user, $request->roleName());
        } catch (CannotRemoveLastSuperAdminException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Rol kaldırıldı.')]);

        return back();
    }
}
