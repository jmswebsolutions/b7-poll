<?php

namespace Database\Factories;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PollItem>
 */
class PollItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
