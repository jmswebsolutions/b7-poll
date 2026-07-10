<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PollPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Poll $poll): bool
    {
        return true;
    }

    /**
     * Determine whether the user can vote on the poll.
     */
    public function vote(User $user, Poll $poll): bool
    {
        return $poll->isOpen() && !$user->hasVotedOn($poll);
    }

    /**
     * Determine whether the user can view the poll results.
     */
    public function viewResults(User $user, Poll $poll): bool
    {
        return $user->hasVotedOn($poll) || !$poll->isOpen();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Poll $poll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Poll $poll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Poll $poll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Poll $poll): bool
    {
        return false;
    }
}
