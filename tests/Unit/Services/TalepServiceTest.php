<?php

namespace Tests\Unit\Services;

use App\Enums\TalepStatus;
use App\Models\Talep;
use App\Models\TalepVote;
use App\Models\User;
use App\Services\TalepService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class TalepServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TalepService
    {
        return app(TalepService::class);
    }

    public function test_create_persists_talep_with_random_key_assigned()
    {
        $user = User::factory()->create();

        $talep = $this->service()->create($user, 'Bu bir talep metnidir.');

        $this->assertDatabaseHas('talepler', [
            'id' => $talep->id,
            'user_id' => $user->id,
            'body' => 'Bu bir talep metnidir.',
            'status' => TalepStatus::Pending->value,
        ]);
        $this->assertGreaterThanOrEqual(0, $talep->random_key);
    }

    public function test_update_body_persists_and_creates_activity_log_entry()
    {
        $talep = Talep::factory()->create(['body' => 'eski']);

        $this->service()->updateBody($talep, 'yeni metin');

        $this->assertSame('yeni metin', $talep->fresh()->body);
        $this->assertTrue(
            Activity::query()->where('log_name', 'talep')->where('description', 'updated')->exists()
        );
    }

    public function test_update_status_persists_and_creates_activity_log_entry()
    {
        $talep = Talep::factory()->pending()->create();

        $this->service()->updateStatus($talep, TalepStatus::Accepted);

        $this->assertSame(TalepStatus::Accepted, $talep->fresh()->status);
        $this->assertTrue(
            Activity::query()->where('log_name', 'talep')->where('description', 'updated')->exists()
        );
    }

    public function test_delete_removes_talep_and_cascades_votes()
    {
        $talep = Talep::factory()->create();
        TalepVote::factory()->up()->create(['talep_id' => $talep->id]);

        $this->service()->delete($talep);

        $this->assertDatabaseMissing('talepler', ['id' => $talep->id]);
        $this->assertDatabaseMissing('talep_votes', ['talep_id' => $talep->id]);
    }
}
