<?php

namespace Database\Factories;

use App\Models\PollItem;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\VoteItem>
 */
class VoteItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vote_id' => Vote::factory(),
            'poll_item_id' => PollItem::factory(),
            'position' => 1,
        ];
    }
}
