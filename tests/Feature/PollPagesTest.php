<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollItem;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_open_and_finished_polls(): void
    {
        $user = User::factory()->create();
        $open = Poll::factory()->create(['title' => 'Votação Aberta']);
        $finished = Poll::factory()->expired()->create(['title' => 'Votação Antiga']);

        $this->actingAs($user)->get(route('polls.index'))
            ->assertOk()
            ->assertSee('Votação Aberta')
            ->assertSee('Votação Antiga');
    }

    public function test_show_renders_vote_form_when_open(): void
    {
        $user = User::factory()->create();
        $poll = Poll::factory()->create();
        PollItem::factory()->count(3)->for($poll)->create();

        $this->actingAs($user)->get(route('polls.show', $poll))
            ->assertOk()
            ->assertSee('Enviar voto');
    }

    public function test_show_hides_form_when_already_voted(): void
    {
        $user = User::factory()->create();
        $poll = Poll::factory()->create();
        PollItem::factory()->count(3)->for($poll)->create();
        Vote::factory()->for($user)->for($poll)->create();

        $this->actingAs($user)->get(route('polls.show', $poll))
            ->assertOk()
            ->assertDontSee('Enviar voto')
            ->assertSee('já votou');
    }

    public function test_show_hides_form_when_finished(): void
    {
        $user = User::factory()->create();
        $poll = Poll::factory()->expired()->create();
        PollItem::factory()->count(3)->for($poll)->create();

        $this->actingAs($user)->get(route('polls.show', $poll))
            ->assertOk()
            ->assertDontSee('Enviar voto')
            ->assertSee('finalizada');
    }

    public function test_results_page_shows_ranking(): void
    {
        $user = User::factory()->create();
        $poll = Poll::factory()->create();
        $winner = PollItem::factory()->for($poll)->create(['name' => 'Projeto Campeão']);

        $this->actingAs($user)->get(route('polls.results', $poll))
            ->assertOk()
            ->assertSee('Projeto Campeão');
    }

    public function test_guest_cannot_access_polls(): void
    {
        $poll = Poll::factory()->create();

        $this->get(route('polls.index'))->assertRedirect(route('login'));
        $this->get(route('polls.show', $poll))->assertRedirect(route('login'));
        $this->get(route('polls.results', $poll))->assertRedirect(route('login'));
    }
}
