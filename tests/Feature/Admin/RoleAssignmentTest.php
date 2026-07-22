<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    public function test_superadmin_can_assign_admin_role()
    {
        $superadmin = User::factory()->asSuperAdmin()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($superadmin)->post(route('admin.roles.store', $target), [
            'role' => RoleName::Admin->value,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole(RoleName::Admin->value));
    }

    public function test_admin_can_assign_moderator_role()
    {
        $admin = User::factory()->asAdmin()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.roles.store', $target), [
            'role' => RoleName::Moderator->value,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole(RoleName::Moderator->value));
    }

    public function test_admin_cannot_assign_admin_role()
    {
        $admin = User::factory()->asAdmin()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.roles.store', $target), [
            'role' => RoleName::Admin->value,
        ]);

        $response->assertForbidden();
        $this->assertFalse($target->fresh()->hasRole(RoleName::Admin->value));
    }

    public function test_moderator_cannot_access_role_management_page()
    {
        $moderator = User::factory()->asModerator()->create();

        $this->actingAs($moderator)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_member_cannot_access_role_management_page()
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_superadmin_cannot_revoke_their_own_role_if_last_superadmin()
    {
        $superadmin = User::factory()->asSuperAdmin()->create();

        $response = $this->actingAs($superadmin)->delete(
            route('admin.roles.destroy', ['user' => $superadmin, 'role' => RoleName::SuperAdmin->value])
        );

        $response->assertRedirect();
        $this->assertTrue($superadmin->fresh()->hasRole(RoleName::SuperAdmin->value));
    }

    public function test_admin_can_view_role_management_page()
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk();
    }
}
