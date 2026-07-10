<?php

namespace App\Services;

use App\Models\Poll;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PollRanking
{
    private const CACHE_TTL_SECONDS = 60;

    /**
     * Ranking calculado em tempo de execução para uma votação.
     *
     * Cada linha: ['rank', 'item', 'points', 'counts'] — onde `counts` é um
     * array posição => número de vezes que o item recebeu aquela posição.
     *
     * @return Collection<int, array{rank: int, item: \App\Models\PollItem, points: int, counts: array<int, int>}>
     */
    public function for(Poll $poll): Collection
    {
        $cacheKey = $this->cacheKey($poll);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($poll) {
            return $this->calculate($poll);
        });
    }

    /**
     * Invalida o cache do ranking de uma votação.
     */
    public function invalidate(Poll $poll): void
    {
        Cache::forget($this->cacheKey($poll));
    }

    private function cacheKey(Poll $poll): string
    {
        return "poll_ranking:{$poll->id}";
    }

    /**
     * Calcula o ranking sem cache.
     *
     * @return Collection<int, array{rank: int, item: \App\Models\PollItem, points: int, counts: array<int, int>}>
     */
    private function calculate(Poll $poll): Collection
    {
        $poll->loadMissing('items');

        $counts = $this->countsByItem($poll);

        return $poll->items
            ->map(function ($item) use ($counts, $poll) {
                $byPosition = $counts[$item->id] ?? [];

                $points = 0;
                foreach ($byPosition as $position => $times) {
                    $points += $poll->pointsForPosition($position) * $times;
                }

                return [
                    'item' => $item,
                    'points' => $points,
                    'counts' => $byPosition,
                ];
            })
            ->sort($this->comparator($poll))
            ->values()
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;

                return $row;
            });
    }

    /**
     * Contagem [poll_item_id][position] => vezes.
     *
     * @return array<int, array<int, int>>
     */
    private function countsByItem(Poll $poll): array
    {
        $counts = [];

        foreach ($poll->votes()->with('items')->get() as $vote) {
            foreach ($vote->items as $voteItem) {
                $itemId = $voteItem->poll_item_id;
                $position = $voteItem->position;
                $counts[$itemId][$position] = ($counts[$itemId][$position] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Ordem de desempate (do PRD):
     * mais 1º lugares, depois 2º, ..., depois soma de pontos,
     * depois item mais antigo (created_at, e id como desempate final).
     */
    private function comparator(Poll $poll): callable
    {
        return function (array $a, array $b) use ($poll): int {
            for ($position = 1; $position <= $poll->podium_size; $position++) {
                $countA = $a['counts'][$position] ?? 0;
                $countB = $b['counts'][$position] ?? 0;

                if ($countA !== $countB) {
                    return $countB <=> $countA;
                }
            }

            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }

            return [$a['item']->created_at, $a['item']->id]
                <=> [$b['item']->created_at, $b['item']->id];
        };
    }
}
