<?php

namespace Tests\Feature;

use App\Models\Talep;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TalepFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function visitFeed(array $query = []): TestResponse
    {
        return $this->get(route('home', $query));
    }

    public function test_guest_can_view_the_feed_page()
    {
        Talep::factory()->count(3)->create();

        $this->visitFeed()
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Talepler/Index')
                ->has('talepler', 3)
            );
    }

    public function test_feed_respects_most_supported_sort_mode()
    {
        $low = Talep::factory()->withVotes(1, 4)->create();
        $high = Talep::factory()->withVotes(8, 0)->create();
        $mid = Talep::factory()->withVotes(3, 2)->create();

        $this->visitFeed(['mode' => 'most_supported'])
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('talepler.0.id', $high->id)
                ->where('talepler.1.id', $mid->id)
                ->where('talepler.2.id', $low->id)
            );
    }

    public function test_feed_respects_least_supported_sort_mode()
    {
        $low = Talep::factory()->withVotes(1, 4)->create();
        $high = Talep::factory()->withVotes(8, 0)->create();

        $this->visitFeed(['mode' => 'least_supported'])
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('talepler.0.id', $low->id)
                ->where('talepler.1.id', $high->id)
            );
    }

    public function test_infinite_scroll_next_page_does_not_duplicate_first_page()
    {
        Talep::factory()->count(25)->create();

        $firstIds = [];
        $cursor = null;

        $this->visitFeed(['mode' => 'most_supported'])
            ->assertInertia(function (AssertableInertia $page) use (&$firstIds, &$cursor) {
                $talepler = $page->toArray()['props']['talepler'];
                $firstIds = array_column($talepler, 'id');
                $cursor = $page->toArray()['props']['nextCursor'];
            });

        $this->assertNotNull($cursor);

        $secondIds = [];
        $this->visitFeed(['mode' => 'most_supported', 'cursor' => $cursor])
            ->assertInertia(function (AssertableInertia $page) use (&$secondIds) {
                $talepler = $page->toArray()['props']['talepler'];
                $secondIds = array_column($talepler, 'id');
            });

        $this->assertNotEmpty($secondIds);
        $this->assertEmpty(array_intersect($firstIds, $secondIds), 'Pages must not overlap.');
    }
}
