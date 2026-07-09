<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Services\PollRanking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(Request $request, PollRanking $ranking): View
    {
        $user = $request->user();
        $votedIds = $user->votes()->pluck('poll_id')->all();

        $openPolls = Poll::open()->withCount('votes')->latest()->get();
        $finishedPolls = Poll::finished()->latest()->get();

        // Prévia do pódio ao vivo (top 3) para cada votação aberta.
        $previews = $openPolls->mapWithKeys(fn (Poll $poll) => [
            $poll->id => $ranking->for($poll)->take(3),
        ]);

        return view('polls.index', [
            'openPolls' => $openPolls,
            'finishedPolls' => $finishedPolls,
            'previews' => $previews,
            'votedIds' => $votedIds,
            'myVotesCount' => count($votedIds),
            'lastVote' => $user->votes()->with('poll')->latest()->first(),
        ]);
    }

    public function show(Request $request, Poll $poll): View
    {
        $poll->load('items');

        return view('polls.show', [
            'poll' => $poll,
            'alreadyVoted' => $request->user()->hasVotedOn($poll),
        ]);
    }

    public function results(Poll $poll, PollRanking $ranking): View
    {
        return view('polls.results', [
            'poll' => $poll,
            'ranking' => $ranking->for($poll),
        ]);
    }
}
