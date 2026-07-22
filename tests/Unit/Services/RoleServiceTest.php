<?php

namespace Tests\Unit\Services;

use App\Enums\RoleName;
use App\Exceptions\CannotRemoveLastSuperAdminException;
use App\Models\User;
use App\Services\RoleService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function service(): RoleService
    {
        return app(RoleService::class);
    }

    public function test_assign_role_grants_role_and_logs_activity()
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->service()->assignRole($actor, $target, RoleName::Moderator);

        $this->assertTrue($target->fresh()->hasRole(RoleName::Moderator->value));
        $this->assertTrue(
            Activity::query()->where('log_name', 'role')->where('description', 'role_assigned')->exists()
        );
    }

    public function test_revoke_role_removes_role_and_logs_activity()
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $target->assignRole(RoleName::Moderator->value);

        $this->service()->revokeRole($actor, $target, RoleName::Moderator);

        $this->assertFalse($target->fresh()->hasRole(RoleName::Moderator->value));
        $this->assertTrue(
            Activity::query()->where('log_name', 'role')->where('description', 'role_revoked')->exists()
        );
    }

    public function test_revoke_role_throws_when_removing_the_last_superadmin()
    {
        $actor = User::factory()->create();
        $onlySuperAdmin = User::factory()->create();
        $onlySuperAdmin->assignRole(RoleName::SuperAdmin->value);

        $this->expectException(CannotRemoveLastSuperAdminException::class);
        $this->service()->revokeRole($actor, $onlySuperAdmin, RoleName::SuperAdmin);
    }

    public function test_revoke_superadmin_allowed_when_another_superadmin_exists()
    {
        $actor = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $first->assignRole(RoleName::SuperAdmin->value);
        $second->assignRole(RoleName::SuperAdmin->value);

        $this->service()->revokeRole($actor, $second, RoleName::SuperAdmin);

        $this->assertFalse($second->fresh()->hasRole(RoleName::SuperAdmin->value));
    }
}
