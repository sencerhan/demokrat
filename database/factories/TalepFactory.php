<?php

namespace Database\Factories;

use App\Enums\TalepStatus;
use App\Models\Talep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Talep>
 */
class TalepFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'status' => TalepStatus::Pending,
            'upvotes_count' => 0,
            'downvotes_count' => 0,
            'total_votes_count' => 0,
            'score' => 0,
            'random_key' => random_int(0, 2_000_000_000),
        ];
    }

    public function withVotes(int $up, int $down): static
    {
        return $this->state(fn (array $attributes) => [
            'upvotes_count' => $up,
            'downvotes_count' => $down,
            'total_votes_count' => $up + $down,
            'score' => $up - $down,
        ]);
    }

    public function status(TalepStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function pending(): static
    {
        return $this->status(TalepStatus::Pending);
    }

    public function accepted(): static
    {
        return $this->status(TalepStatus::Accepted);
    }

    public function rejected(): static
    {
        return $this->status(TalepStatus::Rejected);
    }
}
