<?php

namespace Tests\Unit;

use App\Enums\PollStatus;
use App\Models\Poll;
use App\Models\PollItem;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_points_for_position_uses_formula(): void
    {
        $poll = Poll::factory()->make(['podium_size' => 3]);

        $this->assertSame(5, $poll->pointsForPosition(1));
        $this->assertSame(3, $poll->pointsForPosition(2));
        $this->assertSame(1, $poll->pointsForPosition(3));
    }

    public function test_points_for_position_scales_with_podium_size(): void
    {
        $poll = Poll::factory()->make(['podium_size' => 5]);

        $this->assertSame(9, $poll->pointsForPosition(1));
        $this->assertSame(1, $poll->pointsForPosition(5));
    }

    public function test_is_open_true_when_active_and_future(): void
    {
        $poll = Poll::factory()->make([
            'status' => PollStatus::ACTIVE,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($poll->isOpen());
    }

    public function test_is_open_false_when_inactive_or_expired(): void
    {
        $inactive = Poll::factory()->make([
            'status' => PollStatus::INACTIVE,
            'expires_at' => now()->addDay(),
        ]);
        $expired = Poll::factory()->make([
            'status' => PollStatus::ACTIVE,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($inactive->isOpen());
        $this->assertFalse($expired->isOpen());
    }

    public function test_open_scope_returns_only_active_and_future(): void
    {
        $open = Poll::factory()->create();
        Poll::factory()->expired()->create();
        Poll::factory()->inactive()->create();

        $result = Poll::open()->get();

        $this->assertEquals([$open->id], $result->pluck('id')->all());
    }

    public function test_finished_scope_returns_inactive_or_expired(): void
    {
        Poll::factory()->create();
        $expired = Poll::factory()->expired()->create();
        $inactive = Poll::factory()->inactive()->create();

        $ids = Poll::finished()->pluck('id')->sort()->values()->all();

        $this->assertEquals([$expired->id, $inactive->id], $ids);
    }

    public function test_relations(): void
    {
        $poll = Poll::factory()->create();
        $item = PollItem::factory()->for($poll)->create();
        $vote = Vote::factory()->for($poll)->create();

        $this->assertTrue($poll->items->contains($item));
        $this->assertTrue($poll->votes->contains($vote));
        $this->assertTrue($item->poll->is($poll));
    }

    public function test_user_has_voted_on(): void
    {
        $user = User::factory()->create();
        $poll = Poll::factory()->create();

        $this->assertFalse($user->hasVotedOn($poll));

        Vote::factory()->for($user)->for($poll)->create();

        $this->assertTrue($user->fresh()->hasVotedOn($poll));
    }
}
