<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function store(StoreVoteRequest $request, Poll $poll): RedirectResponse
    {
        $user = $request->user();

        if (! $poll->isOpen()) {
            return back()->withErrors(['items' => 'Esta votação não está aberta para votos.']);
        }

        if ($user->hasVotedOn($poll)) {
            return back()->withErrors(['items' => 'Você já votou nesta votação.']);
        }

        DB::transaction(function () use ($request, $poll, $user) {
            $vote = $poll->votes()->create(['user_id' => $user->id]);

            foreach ($request->validated('items') as $position => $itemId) {
                $vote->items()->create([
                    'poll_item_id' => $itemId,
                    'position' => $position,
                ]);
            }
        });

        return redirect()
            ->route('polls.results', $poll)
            ->with('status', 'Voto registrado! Confira o resultado parcial.');
    }
}
