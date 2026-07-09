<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'poll_id' => Poll::factory(),
        ];
    }
}
