<?php

namespace Tests\Unit;

use App\Models\Poll;
use App\Models\PollItem;
use App\Models\Vote;
use App\Models\VoteItem;
use App\Services\PollRanking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollRankingTest extends TestCase
{
    use RefreshDatabase;

    private function rank(Poll $poll)
    {
        return (new PollRanking())->for($poll);
    }

    /**
     * Registra um voto atribuindo itens a posições ([posição => PollItem]).
     */
    private function castVote(Poll $poll, array $itemsByPosition): void
    {
        $vote = Vote::factory()->for($poll)->create();

        foreach ($itemsByPosition as $position => $item) {
            VoteItem::factory()->for($vote)->create([
                'poll_item_id' => $item->id,
                'position' => $position,
            ]);
        }
    }

    public function test_ranks_by_points_in_simple_case(): void
    {
        $poll = Poll::factory()->create(['podium_size' => 3]);
        $a = PollItem::factory()->for($poll)->create();
        $b = PollItem::factory()->for($poll)->create();
        $c = PollItem::factory()->for($poll)->create();

        $this->castVote($poll, [1 => $a, 2 => $b, 3 => $c]);

        $ranking = $this->rank($poll);

        $this->assertSame([$a->id, $b->id, $c->id], $ranking->pluck('item.id')->all());
        $this->assertSame([1, 2, 3], $ranking->pluck('rank')->all());
        $this->assertSame(5, $ranking->firstWhere('item.id', $a->id)['points']);
        $this->assertSame(3, $ranking->firstWhere('item.id', $b->id)['points']);
        $this->assertSame(1, $ranking->firstWhere('item.id', $c->id)['points']);
    }

    public function test_first_place_count_beats_total_points(): void
    {
        $poll = Poll::factory()->create(['podium_size' => 3]);
        $a = PollItem::factory()->for($poll)->create();
        $b = PollItem::factory()->for($poll)->create();

        // A: um 1º lugar (5 pts, um 1º). B: dois 2º lugares (6 pts, zero 1º).
        $this->castVote($poll, [1 => $a]);
        $this->castVote($poll, [2 => $b]);
        $this->castVote($poll, [2 => $b]);

        $ranking = $this->rank($poll);

        $this->assertSame([$a->id, $b->id], $ranking->pluck('item.id')->all());
        $this->assertSame(5, $ranking->firstWhere('item.id', $a->id)['points']);
        $this->assertSame(6, $ranking->firstWhere('item.id', $b->id)['points']);
    }

    public function test_second_place_count_breaks_first_place_tie(): void
    {
        $poll = Poll::factory()->create(['podium_size' => 3]);
        $a = PollItem::factory()->for($poll)->create();
        $b = PollItem::factory()->for($poll)->create();

        // Empatam em 1º (1 cada) e em pontos (8 cada). A tem um 2º, B nenhum.
        $this->castVote($poll, [1 => $a]);
        $this->castVote($poll, [2 => $a]);
        $this->castVote($poll, [1 => $b]);
        $this->castVote($poll, [3 => $b]);
        $this->castVote($poll, [3 => $b]);
        $this->castVote($poll, [3 => $b]);

        $ranking = $this->rank($poll);

        $this->assertSame(8, $ranking->firstWhere('item.id', $a->id)['points']);
        $this->assertSame(8, $ranking->firstWhere('item.id', $b->id)['points']);
        $this->assertSame([$a->id, $b->id], $ranking->pluck('item.id')->all());
    }

    public function test_oldest_item_wins_when_everything_ties(): void
    {
        $poll = Poll::factory()->create(['podium_size' => 3]);

        // Newer criado antes (id menor) mas com data mais recente.
        $newer = PollItem::factory()->for($poll)->create(['created_at' => now()]);
        $older = PollItem::factory()->for($poll)->create(['created_at' => now()->subDay()]);

        // Nenhum voto: contagens e pontos zerados para ambos.
        $ranking = $this->rank($poll);

        $this->assertSame([$older->id, $newer->id], $ranking->pluck('item.id')->all());
    }

    public function test_scales_to_larger_podium(): void
    {
        $poll = Poll::factory()->create(['podium_size' => 5]);
        $a = PollItem::factory()->for($poll)->create();
        $b = PollItem::factory()->for($poll)->create();

        // podium_size 5 → 1º vale 9 pts, 5º vale 1 pt.
        $this->castVote($poll, [1 => $a, 5 => $b]);

        $ranking = $this->rank($poll);

        $this->assertSame(9, $ranking->firstWhere('item.id', $a->id)['points']);
        $this->assertSame(1, $ranking->firstWhere('item.id', $b->id)['points']);
        $this->assertSame([$a->id, $b->id], $ranking->pluck('item.id')->all());
    }
}
