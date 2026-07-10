<?php

namespace App\Http\Controllers;

use App\Events\VoteRegistered;
use App\Http\Requests\StoreVoteRequest;
use App\Models\Poll;
use App\Services\PollRanking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Controller para gerenciamento de votos.
 */
class VoteController extends Controller
{
    /**
     * Registra um novo voto em uma votação.
     *
     * @param  \App\Http\Requests\StoreVoteRequest  $request
     * @param  \App\Models\Poll  $poll
     * @param  \App\Services\PollRanking  $ranking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreVoteRequest $request, Poll $poll, PollRanking $ranking): RedirectResponse
    {
        $this->authorize('vote', $poll);

        $user = $request->user();

        $vote = DB::transaction(function () use ($request, $poll, $user) {
            $vote = $poll->votes()->create(['user_id' => $user->id]);

            foreach ($request->validated('items') as $position => $itemId) {
                $vote->items()->create([
                    'poll_item_id' => $itemId,
                    'position' => $position,
                ]);
            }

            return $vote;
        });

        $ranking->invalidate($poll);

        VoteRegistered::dispatch($vote, $user, $poll);

        return redirect()
            ->route('polls.results', $poll)
            ->with('status', 'Voto registrado! Confira o resultado parcial.');
    }
}
