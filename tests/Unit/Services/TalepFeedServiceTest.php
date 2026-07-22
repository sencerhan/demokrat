<?php

namespace Tests\Unit\Services;

use App\Enums\TalepSortMode;
use App\Models\Talep;
use App\Services\TalepFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalepFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TalepFeedService
    {
        return app(TalepFeedService::class);
    }

    public function test_most_votes_mode_orders_by_total_votes_count_descending()
    {
        $low = Talep::factory()->withVotes(1, 0)->create();
        $high = Talep::factory()->withVotes(5, 3)->create();
        $mid = Talep::factory()->withVotes(2, 1)->create();

        $page = $this->service()->page(TalepSortMode::MostVotes);
        $ids = $page['items']->pluck('id')->all();

        $this->assertSame([$high->id, $mid->id, $low->id], $ids);
    }

    public function test_most_supported_mode_orders_by_score_descending()
    {
        $neg = Talep::factory()->withVotes(1, 5)->create();
        $pos = Talep::factory()->withVotes(9, 1)->create();
        $zero = Talep::factory()->withVotes(2, 2)->create();

        $page = $this->service()->page(TalepSortMode::MostSupported);
        $ids = $page['items']->pluck('id')->all();

        $this->assertSame([$pos->id, $zero->id, $neg->id], $ids);
    }

    public function test_least_supported_mode_orders_by_score_ascending()
    {
        $neg = Talep::factory()->withVotes(1, 5)->create();
        $pos = Talep::factory()->withVotes(9, 1)->create();
        $zero = Talep::factory()->withVotes(2, 2)->create();

        $page = $this->service()->page(TalepSortMode::LeastSupported);
        $ids = $page['items']->pluck('id')->all();

        $this->assertSame([$neg->id, $zero->id, $pos->id], $ids);
    }

    public function test_random_mode_with_fixed_seed_returns_stable_order_across_repeated_calls()
    {
        Talep::factory()->count(10)->create();
        $seed = 123456;

        $first = $this->service()->page(TalepSortMode::Random, null, $seed, 5);
        $second = $this->service()->page(TalepSortMode::Random, null, $seed, 5);

        $this->assertSame(
            $first['items']->pluck('id')->all(),
            $second['items']->pluck('id')->all(),
        );
    }

    public function test_random_mode_pagination_across_multiple_pages_does_not_repeat_or_skip_any_talep()
    {
        $created = Talep::factory()->count(47)->create()->pluck('id')->sort()->values()->all();
        $seed = 987654;
        $perPage = 10;

        $collected = [];
        $cursor = null;
        $guard = 0;

        do {
            $page = $this->service()->page(TalepSortMode::Random, $cursor, $seed, $perPage);
            $collected = array_merge($collected, $page['items']->pluck('id')->all());
            $cursor = $page['nextCursor'];
            $guard++;
        } while ($page['hasMore'] && $guard < 100);

        $this->assertCount(47, $collected);
        $this->assertSame(47, count(array_unique($collected)), 'No talep should be repeated.');

        sort($collected);
        $this->assertSame($created, $collected, 'Every talep should appear exactly once.');
    }

    public function test_random_mode_different_seeds_can_produce_different_orderings()
    {
        Talep::factory()->count(30)->create();

        $a = $this->service()->page(TalepSortMode::Random, null, 111, 30);
        $b = $this->service()->page(TalepSortMode::Random, null, 999999999, 30);

        $this->assertNotSame(
            $a['items']->pluck('id')->all(),
            $b['items']->pluck('id')->all(),
        );
    }
}
