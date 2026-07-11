<?php

namespace Database\Seeders;

use App\Enums\PollStatus;
use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Login de teste: test@example.com / password
        $test = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $students = User::factory(14)->create();

        // 1) Votação em andamento com votos parciais (resultado já visível).
        //    O test user NÃO votou aqui: ao logar, o formulário aparece.
        $live = Poll::factory()->create([
            'title' => 'Melhores Projetos da Live',
            'description' => 'Vote nos 3 melhores projetos apresentados hoje.',
            'status' => PollStatus::ACTIVE,
            'podium_size' => 3,
            'expires_at' => now()->addWeek(),
        ]);
        $live->items()->createMany([
            ['name' => 'TaskFlow', 'description' => 'Gerenciador de tarefas em tempo real.'],
            ['name' => 'FinBot', 'description' => 'Assistente financeiro com IA.'],
            ['name' => 'MapaVerde', 'description' => 'Mapa colaborativo de reciclagem.'],
            ['name' => 'CodeDuel', 'description' => 'Desafios de código 1x1.'],
            ['name' => 'PetMatch', 'description' => 'Adoção de pets por afinidade.'],
            ['name' => 'StudyLoop', 'description' => 'Revisão espaçada automática.'],
        ]);
        $this->castVotes($live, $students);

        // 2) Votação em andamento com pódio de 5 (mostra o tamanho variável).
        $big = Poll::factory()->create([
            'title' => 'Top 5 Ferramentas Dev',
            'description' => 'Monte seu top 5 favorito.',
            'status' => PollStatus::ACTIVE,
            'podium_size' => 5,
            'expires_at' => now()->addDays(3),
        ]);
        $big->items()->createMany([
            ['name' => 'VS Code'],
            ['name' => 'Neovim'],
            ['name' => 'PhpStorm'],
            ['name' => 'Docker'],
            ['name' => 'Postman'],
            ['name' => 'TablePlus'],
            ['name' => 'Warp'],
        ]);
        $this->castVotes($big, $students);

        // 3) Votação finalizada (expirada) com pódio consolidado.
        $past = Poll::factory()->expired()->create([
            'title' => 'Votação da Semana Passada',
            'description' => 'Resultado final disponível.',
            'podium_size' => 3,
        ]);
        $past->items()->createMany([
            ['name' => 'Projeto Alfa'],
            ['name' => 'Projeto Beta'],
            ['name' => 'Projeto Gama'],
            ['name' => 'Projeto Delta'],
        ]);
        $this->castVotes($past, $students->push($test));

        // 4) Votação desativada manualmente (aparece em "finalizadas", sem votos).
        Poll::factory()->inactive()->create([
            'title' => 'Votação Cancelada',
            'podium_size' => 3,
            'expires_at' => now()->addWeek(),
        ])->items()->createMany([
            ['name' => 'Opção 1'],
            ['name' => 'Opção 2'],
            ['name' => 'Opção 3'],
        ]);
    }

    /**
     * Registra votos válidos (itens distintos, uma escolha por posição)
     * para uma amostra aleatória de usuários.
     */
    private function castVotes(Poll $poll, Collection $users): void
    {
        $poll->load('items');

        foreach ($users as $user) {
            // ~70% dos usuários votam nesta votação.
            if (random_int(1, 10) > 7) {
                continue;
            }

            $picks = $poll->items->shuffle()->take($poll->podium_size)->values();

            $vote = Vote::create(['user_id' => $user->id, 'poll_id' => $poll->id]);

            foreach ($picks as $index => $item) {
                $vote->items()->create([
                    'poll_item_id' => $item->id,
                    'position' => $index + 1,
                ]);
            }
        }
    }
}
