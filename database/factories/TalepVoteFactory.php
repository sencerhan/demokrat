<?php

namespace Database\Factories;

use App\Enums\VoteDirection;
use App\Models\Talep;
use App\Models\TalepVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TalepVote>
 */
class TalepVoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'talep_id' => Talep::factory(),
            'direction' => fake()->randomElement(VoteDirection::cases()),
        ];
    }

    public function up(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => VoteDirection::Up,
        ]);
    }

    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => VoteDirection::Down,
        ]);
    }
}
