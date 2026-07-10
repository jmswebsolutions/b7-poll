<?php

namespace App\Events;

use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Vote $vote,
        public readonly User $user,
        public readonly Poll $poll
    ) {}
}
