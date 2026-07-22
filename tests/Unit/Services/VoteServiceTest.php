<?php

namespace Tests\Unit\Services;

use App\Enums\VoteDirection;
use App\Models\Talep;
use App\Models\TalepVote;
use App\Models\User;
use App\Services\VoteResult;
use App\Services\VoteService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): VoteService
    {
        return app(VoteService::class);
    }

    public function test_cast_vote_increments_upvote_and_score()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $result = $this->service()->castVote($user, $talep, VoteDirection::Up);

        $this->assertSame(VoteResult::Cast, $result);
        $talep->refresh();
        $this->assertSame(1, $talep->upvotes_count);
        $this->assertSame(0, $talep->downvotes_count);
        $this->assertSame(1, $talep->total_votes_count);
        $this->assertSame(1, $talep->score);
    }

    public function test_cast_vote_increments_downvote_and_decrements_score()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->service()->castVote($user, $talep, VoteDirection::Down);

        $talep->refresh();
        $this->assertSame(0, $talep->upvotes_count);
        $this->assertSame(1, $talep->downvotes_count);
        $this->assertSame(1, $talep->total_votes_count);
        $this->assertSame(-1, $talep->score);
    }

    public function test_casting_same_direction_twice_toggles_vote_off_and_decrements_counters()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->service()->castVote($user, $talep, VoteDirection::Up);
        $result = $this->service()->castVote($user, $talep, VoteDirection::Up);

        $this->assertSame(VoteResult::ToggledOff, $result);
        $talep->refresh();
        $this->assertSame(0, $talep->upvotes_count);
        $this->assertSame(0, $talep->total_votes_count);
        $this->assertSame(0, $talep->score);
        $this->assertDatabaseMissing('talep_votes', [
            'user_id' => $user->id,
            'talep_id' => $talep->id,
        ]);
    }

    public function test_switching_vote_direction_updates_both_counters_and_keeps_total_votes_count_unchanged()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        $this->service()->castVote($user, $talep, VoteDirection::Up);
        $result = $this->service()->castVote($user, $talep, VoteDirection::Down);

        $this->assertSame(VoteResult::Switched, $result);
        $talep->refresh();
        $this->assertSame(0, $talep->upvotes_count);
        $this->assertSame(1, $talep->downvotes_count);
        $this->assertSame(1, $talep->total_votes_count);
        $this->assertSame(-1, $talep->score);
        $this->assertDatabaseHas('talep_votes', [
            'user_id' => $user->id,
            'talep_id' => $talep->id,
            'direction' => VoteDirection::Down->value,
        ]);
    }

    public function test_unique_constraint_prevents_duplicate_vote_rows_for_same_user_and_talep()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create();

        TalepVote::factory()->up()->create(['user_id' => $user->id, 'talep_id' => $talep->id]);

        $this->expectException(QueryException::class);
        TalepVote::factory()->down()->create(['user_id' => $user->id, 'talep_id' => $talep->id]);
    }

    public function test_two_users_voting_produce_correct_final_counters()
    {
        $talep = Talep::factory()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->service()->castVote($a, $talep, VoteDirection::Up);
        $this->service()->castVote($b, $talep, VoteDirection::Down);

        $talep->refresh();
        $this->assertSame(1, $talep->upvotes_count);
        $this->assertSame(1, $talep->downvotes_count);
        $this->assertSame(2, $talep->total_votes_count);
        $this->assertSame(0, $talep->score);
    }
}
