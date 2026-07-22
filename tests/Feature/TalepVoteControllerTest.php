<?php

namespace Tests\Feature;

use App\Enums\VoteDirection;
use App\Events\TalepVoteCast;
use App\Models\Talep;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TalepVoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_member_can_cast_an_upvote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $response = $this->actingAs($user)->post(route('talepler.votes.store', $talep), [
            'direction' => VoteDirection::Up->value,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(1, $talep->fresh()->upvotes_count);
        $this->assertDatabaseHas('talep_votes', [
            'user_id' => $user->id,
            'talep_id' => $talep->id,
            'direction' => VoteDirection::Up->value,
        ]);
    }

    public function test_member_can_cast_a_downvote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->actingAs($user)->post(route('talepler.votes.store', $talep), [
            'direction' => VoteDirection::Down->value,
        ])->assertRedirect();

        $this->assertSame(1, $talep->fresh()->downvotes_count);
    }

    public function test_clicking_same_direction_again_removes_the_vote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->actingAs($user)->post(route('talepler.votes.store', $talep), ['direction' => 'up']);
        $this->actingAs($user)->post(route('talepler.votes.store', $talep), ['direction' => 'up']);

        $this->assertSame(0, $talep->fresh()->upvotes_count);
        $this->assertDatabaseMissing('talep_votes', [
            'user_id' => $user->id,
            'talep_id' => $talep->id,
        ]);
    }

    public function test_clicking_opposite_direction_switches_the_vote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->actingAs($user)->post(route('talepler.votes.store', $talep), ['direction' => 'up']);
        $this->actingAs($user)->post(route('talepler.votes.store', $talep), ['direction' => 'down']);

        $fresh = $talep->fresh();
        $this->assertSame(0, $fresh->upvotes_count);
        $this->assertSame(1, $fresh->downvotes_count);
        $this->assertSame(1, $fresh->total_votes_count);
    }

    public function test_vote_endpoint_dispatches_talep_vote_cast_event()
    {
        Event::fake([TalepVoteCast::class]);

        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->actingAs($user)->post(route('talepler.votes.store', $talep), ['direction' => 'up']);

        Event::assertDispatched(TalepVoteCast::class, fn (TalepVoteCast $e) => $e->talepId === $talep->id);
    }

    public function test_guest_cannot_vote()
    {
        $talep = Talep::factory()->create();

        $this->post(route('talepler.votes.store', $talep), ['direction' => 'up'])
            ->assertRedirect(route('login'));
    }
}
