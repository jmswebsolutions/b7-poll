<?php

namespace Database\Factories;

use App\Enums\PollStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Poll>
 */
class PollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => PollStatus::ACTIVE,
            'podium_size' => 3,
            'expires_at' => now()->addWeek(),
        ];
    }

    /** Votação finalizada por expiração. */
    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    /** Votação desativada manualmente. */
    public function inactive(): static
    {
        return $this->state(fn () => ['status' => PollStatus::INACTIVE]);
    }
}
