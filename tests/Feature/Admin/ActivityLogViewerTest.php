<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_view_activity_log_page()
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.activity-log.index'))
            ->assertOk();
    }

    public function test_superadmin_can_view_activity_log_page()
    {
        $superadmin = User::factory()->asSuperAdmin()->create();

        $this->actingAs($superadmin)
            ->get(route('admin.activity-log.index'))
            ->assertOk();
    }

    public function test_moderator_cannot_view_activity_log_page()
    {
        $moderator = User::factory()->asModerator()->create();

        $this->actingAs($moderator)->get(route('admin.activity-log.index'))->assertForbidden();
    }

    public function test_member_cannot_view_activity_log_page()
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.activity-log.index'))->assertForbidden();
    }
}
