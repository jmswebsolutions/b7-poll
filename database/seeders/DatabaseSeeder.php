<?php

namespace Database\Seeders;

use App\Enums\PollStatus;
use App\Models\Poll;
use App\Models\PollItem;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Login de teste: test@example.com / password
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Criar usuários de teste para simular votos
        $students = User::factory(20)->create();

        // Criar a votação da competição de projetos
        $poll = Poll::firstOrCreate(
            ['title' => 'B7Web Dev Showdown — Competição de Projetos'],
            [
                'description' => 'Votação ao vivo dos projetos apresentados pelos alunos do curso B7Web. Escolha seu pódio: 1º, 2º e 3º lugar.',
                'status' => PollStatus::ACTIVE,
                'podium_size' => 3,
                'expires_at' => now()->addWeek(),
            ]
        );

        // Criar os 4 projetos apresentados
        $items = [
            [
                'name' => 'Hacker News Dashboard',
                'author' => 'Jonatan',
                'description' => 'Dashboard interativo que consome a API do Hacker News em tempo real. Funcionalidades: scroll infinito, busca e filtros, sistema de favoritos, PWA com suporte offline, atualizações em tempo real e testes automatizados (hooks + e2e). Projeto open source aberto a contribuições.',
                'url' => 'https://jmswebsolutions.com.br/Project-AI-Tech-Dashboard/',
            ],
            [
                'name' => 'Go Quadra',
                'author' => 'Erick',
                'description' => 'Plataforma de agendamento de quadras esportivas com foco em futebol. Desenvolvida em Laravel + Inertia.js, com integração de pagamento online. Já validada com 4 empresas interessadas.',
                'url' => null,
            ],
            [
                'name' => 'Sistema de Gestão Escolar',
                'author' => 'Manuel',
                'description' => 'Sistema completo de gestão escolar desenvolvido para uma rede municipal de educação, substituindo uma solução que custava R$15.000/mês. Recursos: multi-tenant, geração de planos de aula com IA, auditoria de ações, controle de acesso por perfil e backup automatizado.',
                'url' => null,
            ],
            [
                'name' => 'Alertas de Processos Jurídicos com LLM',
                'author' => 'Kelvin',
                'description' => 'Sistema que monitora andamentos de processos jurídicos e gera resumos automáticos usando LLM. Focado em nichos do mercado jurídico com pouca cobertura tecnológica.',
                'url' => null,
            ],
        ];

        foreach ($items as $itemData) {
            PollItem::firstOrCreate(
                [
                    'poll_id' => $poll->id,
                    'name' => $itemData['name'],
                ],
                [
                    'author' => $itemData['author'],
                    'description' => $itemData['description'],
                    'url' => $itemData['url'],
                ]
            );
        }

        // Simular votos dos usuários de teste
        $this->castVotes($poll, $students);
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
