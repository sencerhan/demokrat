<?php

namespace Tests\Feature;

use App\Enums\TalepStatus;
use App\Models\Talep;
use App\Models\TalepVote;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalepControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_member_can_create_a_talep()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('talepler.store'), [
            'body' => 'Bu benim ilk talebimdir.',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('talepler', [
            'user_id' => $user->id,
            'body' => 'Bu benim ilk talebimdir.',
        ]);
    }

    public function test_guest_cannot_create_a_talep()
    {
        $response = $this->post(route('talepler.store'), ['body' => 'guest talebi denemesi']);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('talepler', 0);
    }

    public function test_author_can_delete_their_own_talep_before_any_vote_exists()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('talepler.destroy', $talep));

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('talepler', ['id' => $talep->id]);
    }

    public function test_author_cannot_delete_their_talep_after_it_has_received_a_vote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create(['user_id' => $user->id]);
        TalepVote::factory()->up()->create(['talep_id' => $talep->id]);

        $response = $this->actingAs($user)->delete(route('talepler.destroy', $talep));

        $response->assertForbidden();
        $this->assertDatabaseHas('talepler', ['id' => $talep->id]);
    }

    public function test_moderator_can_delete_any_talep_even_with_votes()
    {
        $moderator = User::factory()->asModerator()->create();
        $talep = Talep::factory()->create();
        TalepVote::factory()->up()->create(['talep_id' => $talep->id]);

        $response = $this->actingAs($moderator)->delete(route('talepler.destroy', $talep));

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('talepler', ['id' => $talep->id]);
    }

    public function test_moderator_can_update_talep_status()
    {
        $moderator = User::factory()->asModerator()->create();
        $talep = Talep::factory()->pending()->create();

        $response = $this->actingAs($moderator)->patch(route('talepler.status.update', $talep), [
            'status' => TalepStatus::Accepted->value,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(TalepStatus::Accepted, $talep->fresh()->status);
    }

    public function test_moderator_can_edit_talep_body()
    {
        $moderator = User::factory()->asModerator()->create();
        $talep = Talep::factory()->create(['body' => 'orijinal']);
        TalepVote::factory()->up()->create(['talep_id' => $talep->id]);

        $response = $this->actingAs($moderator)->patch(route('talepler.update', $talep), [
            'body' => 'moderatör tarafından düzenlendi',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('moderatör tarafından düzenlendi', $talep->fresh()->body);
    }

    public function test_author_can_edit_their_own_talep_before_any_vote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create(['user_id' => $user->id, 'body' => 'ilk']);

        $response = $this->actingAs($user)->patch(route('talepler.update', $talep), [
            'body' => 'yazar tarafından düzenlendi',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('yazar tarafından düzenlendi', $talep->fresh()->body);
    }

    public function test_author_cannot_edit_their_talep_after_a_vote()
    {
        $user = User::factory()->create();
        $talep = Talep::factory()->create(['user_id' => $user->id, 'body' => 'ilk']);
        TalepVote::factory()->up()->create(['talep_id' => $talep->id]);

        $response = $this->actingAs($user)->patch(route('talepler.update', $talep), [
            'body' => 'değiştirmeye çalışıyorum',
        ]);

        $response->assertForbidden();
        $this->assertSame('ilk', $talep->fresh()->body);
    }

    public function test_member_cannot_edit_another_members_talep()
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $talep = Talep::factory()->create(['user_id' => $author->id, 'body' => 'ilk']);

        $response = $this->actingAs($other)->patch(route('talepler.update', $talep), [
            'body' => 'başkasının talebi',
        ]);

        $response->assertForbidden();
        $this->assertSame('ilk', $talep->fresh()->body);
    }

    public function test_member_cannot_update_talep_status()
    {
        $member = User::factory()->create();
        $talep = Talep::factory()->pending()->create();

        $response = $this->actingAs($member)->patch(route('talepler.status.update', $talep), [
            'status' => TalepStatus::Accepted->value,
        ]);

        $response->assertForbidden();
        $this->assertSame(TalepStatus::Pending, $talep->fresh()->status);
    }
}
