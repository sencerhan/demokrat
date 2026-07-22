<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuthenticationActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_creates_activity_log_entry()
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue(
            Activity::query()->where('log_name', 'auth')->where('description', 'login')->exists()
        );
    }

    public function test_failed_login_creates_activity_log_entry()
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertTrue(
            Activity::query()->where('log_name', 'auth')->where('description', 'failed_login')->exists()
        );
    }

    public function test_logout_creates_activity_log_entry()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->assertTrue(
            Activity::query()->where('log_name', 'auth')->where('description', 'logout')->exists()
        );
    }

    public function test_registration_creates_activity_log_entry()
    {
        $this->post(route('register.store'), [
            'name' => 'Yeni Üye',
            'email' => 'yeni@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertTrue(
            Activity::query()->where('log_name', 'auth')->where('description', 'registered')->exists()
        );
    }
}
