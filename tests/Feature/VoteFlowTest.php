<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollItem;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteFlowTest extends TestCase
{
    use RefreshDatabase;

    private function pollWithItems(int $count = 3, array $attributes = []): Poll
    {
        $poll = Poll::factory()->create($attributes);
        PollItem::factory()->count($count)->for($poll)->create();

        return $poll;
    }

    private function payload(Poll $poll): array
    {
        $items = $poll->items()->take($poll->podium_size)->pluck('id')->values();

        return ['items' => [
            1 => $items[0],
            2 => $items[1],
            3 => $items[2],
        ]];
    }

    public function test_valid_vote_is_recorded_and_redirects_to_results(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems(4);

        $response = $this->actingAs($user)
            ->post(route('polls.vote', $poll), $this->payload($poll));

        $response->assertRedirect(route('polls.results', $poll));
        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseCount('vote_items', 3);
        $this->assertTrue($user->fresh()->hasVotedOn($poll));
    }

    public function test_user_cannot_vote_twice(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems();

        $this->actingAs($user)->post(route('polls.vote', $poll), $this->payload($poll));

        $response = $this->actingAs($user)
            ->from(route('polls.show', $poll))
            ->post(route('polls.vote', $poll), $this->payload($poll));

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('votes', 1);
    }

    public function test_cannot_vote_on_expired_poll(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems(3, ['expires_at' => now()->subDay()]);

        $response = $this->actingAs($user)
            ->from(route('polls.index'))
            ->post(route('polls.vote', $poll), $this->payload($poll));

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_cannot_vote_on_inactive_poll(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems(3, ['status' => 'inactive']);

        $response = $this->actingAs($user)
            ->from(route('polls.index'))
            ->post(route('polls.vote', $poll), $this->payload($poll));

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_cannot_repeat_same_item_in_two_positions(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems();
        $items = $poll->items;

        $response = $this->actingAs($user)
            ->from(route('polls.show', $poll))
            ->post(route('polls.vote', $poll), ['items' => [
                1 => $items[0]->id,
                2 => $items[0]->id,
                3 => $items[1]->id,
            ]]);

        $response->assertSessionHasErrors('items.1');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_cannot_vote_with_item_from_another_poll(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems();
        $foreign = PollItem::factory()->for($this->pollWithItems())->create();
        $items = $poll->items;

        $response = $this->actingAs($user)
            ->from(route('polls.show', $poll))
            ->post(route('polls.vote', $poll), ['items' => [
                1 => $items[0]->id,
                2 => $items[1]->id,
                3 => $foreign->id,
            ]]);

        $response->assertSessionHasErrors('items.3');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_must_fill_all_podium_positions(): void
    {
        $user = User::factory()->create();
        $poll = $this->pollWithItems();
        $items = $poll->items;

        $response = $this->actingAs($user)
            ->from(route('polls.show', $poll))
            ->post(route('polls.vote', $poll), ['items' => [
                1 => $items[0]->id,
                2 => $items[1]->id,
            ]]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_guest_cannot_vote(): void
    {
        $poll = $this->pollWithItems();

        $this->post(route('polls.vote', $poll), $this->payload($poll))
            ->assertRedirect(route('login'));
    }
}
