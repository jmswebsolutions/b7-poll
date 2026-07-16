# B7Poll

Sistema de votação em tempo real desenvolvido com Laravel 13, focado em transparência, performance e experiência do usuário.

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Sistema de Pontuação](#sistema-de-pontuação)
- [API](#api)
- [Testes](#testes)
- [Segurança](#segurança)
- [Performance](#performance)
- [Acessibilidade](#acessibilidade)
- [Contribuindo](#contribuindo)
- [Licença](#licença)

## Visão Geral

B7Poll é um sistema de votação interativo que permite aos usuários votar em projetos ou itens em tempo real, com visualização do pódio atualizado dinamicamente. O sistema foi desenvolvido para ser usado em lives e eventos, proporcionando transparência nos resultados através de cálculos em tempo real.

### Características Principais

- **Votação em tempo real**: Os resultados são calculados e exibidos instantaneamente
- **Pódio dinâmico**: Visualização do ranking atualizado conforme os votos são recebidos
- **Transparência**: Exibição detalhada de pontos e contagem por posição
- **Sistema de pontuação ponderado**: Posições mais altas valem mais pontos
- **Critérios de desempate**: Regras claras para definir o ranking em caso de empate

## Funcionalidades

### Backend

- ✅ **Cache do ranking**: Implementado com Redis (TTL 60s) para reduzir carga no banco
- ✅ **Rate limiting**: Limite de 5 votos por minuto para prevenir abuso
- ✅ **Soft deletes**: Implementado em polls e poll items para permitir recuperação de dados
- ✅ **Event-driven architecture**: Sistema de eventos para notificações futuras
- ✅ **Authorization policies**: Controle de acesso centralizado para votos e resultados
- ✅ **Backed enums**: Uso de PHP 8.1+ enums para type-safe status management

### Frontend

- ✅ **Loading states**: Spinner animado durante envio de votos
- ✅ **Validação client-side**: Validação antes do envio para melhor UX
- ✅ **Toast notifications**: Feedback visual com animações
- ✅ **Animações CSS**: Transições suaves em elementos interativos
- ✅ **Skeleton loading**: Classes CSS para loading states

### Acessibilidade

- ✅ **Contraste melhorado**: Cores ajustadas para atender WCAG AA
- ✅ **Navegação por teclado**: Foco visível melhorado
- ✅ **Skip link**: Link para pular ao conteúdo principal

### Código

- ✅ **PHPDoc completo**: Documentação detalhada em controllers e models
- ✅ **Type hints expandidos**: Tipagem forte em todos os métodos
- ✅ **Design patterns**: Segue princípios SOLID e boas práticas Laravel

## Tecnologias

- **Framework**: Laravel 13
- **PHP**: 8.3+
- **Banco de Dados**: MySQL/PostgreSQL
- **Cache**: Redis
- **Frontend**: Blade Templates + CSS Vanilla
- **Testes**: PHPUnit
- **Queue**: Redis (para event listeners)

## Instalação

### Pré-requisitos

- PHP >= 8.3
- Composer
- Node.js & NPM (opcional, para assets)
- MySQL ou PostgreSQL
- Redis (opcional, para cache)

### Passos de Instalação

1. **Clone o repositório**
```bash
git clone https://github.com/jmswebsolutions/b7-poll.git
cd b7-poll
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure o banco de dados no arquivo `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=b7poll
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Execute as migrations**
```bash
php artisan migrate
```

6. **Execute os seeders (opcional)**
```bash
php artisan db:seed
```

7. **Inicie o servidor de desenvolvimento**
```bash
php artisan serve
```

8. **Acesse a aplicação**
```
http://localhost:8000
```

### Credenciais de Teste (após db:seed)

- **Email**: test@example.com
- **Senha**: password

## Configuração

### Redis (Opcional)

Para usar Redis como cache e queue, configure no `.env`:

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Rate Limiting

O rate limiting está configurado em `routes/web.php`:

```php
Route::post('/polls/{poll}/vote', [VoteController::class, 'store'])
    ->middleware(['auth', 'throttle:5,1'])
    ->name('polls.vote');
```

Isso limita a 5 votos por minuto por usuário.

## Estrutura do Projeto

```
b7-poll/
├── app/
│   ├── Enums/
│   │   └── PollStatus.php          # Enum para status de votação
│   ├── Events/
│   │   └── VoteRegistered.php       # Evento disparado ao votar
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PollController.php  # Controller de votações
│   │   │   └── VoteController.php # Controller de votos
│   │   └── Requests/
│   │       └── StoreVoteRequest.php # Validação de votos
│   ├── Listeners/
│   │   └── SendVoteNotification.php # Listener para notificações
│   ├── Models/
│   │   ├── Poll.php                # Model de votação
│   │   ├── PollItem.php            # Model de item de votação
│   │   ├── User.php                # Model de usuário
│   │   └── Vote.php                # Model de voto
│   ├── Policies/
│   │   └── PollPolicy.php          # Policy de autorização
│   ├── Providers/
│   │   └── AppServiceProvider.php   # Registro de eventos
│   └── Services/
│       └── PollRanking.php         # Serviço de cálculo de ranking
├── database/
│   ├── factories/
│   │   └── PollFactory.php          # Factory para testes
│   ├── migrations/
│   │   ├── create_polls_table.php
│   │   ├── create_poll_items_table.php
│   │   ├── create_votes_table.php
│   │   └── add_soft_deletes_*.php  # Migrations de soft deletes
│   └── seeders/
│       └── DatabaseSeeder.php      # Seeder de dados de teste
├── resources/
│   └── views/
│       ├── polls/
│       │   ├── index.blade.php     # Lista de votações
│       │   ├── show.blade.php      # Formulário de voto
│       │   └── results.blade.php   # Página de resultados
│       └── layouts/
│           └── app.blade.php       # Layout principal
├── routes/
│   └── web.php                     # Rotas da aplicação
├── tests/
│   ├── Feature/
│   │   ├── PollPagesTest.php       # Testes de páginas
│   │   └── VoteFlowTest.php        # Testes de fluxo de voto
│   └── Unit/
│       ├── PollModelTest.php       # Testes do model Poll
│       └── PollRankingTest.php     # Testes do serviço de ranking
└── public/
    └── assets/
        └── auth-premium/
            └── auth.css            # Estilos CSS
```

## Sistema de Pontuação

### Cálculo de Pontos

O sistema usa uma fórmula ponderada onde posições mais altas valem mais pontos:

```php
public function pointsForPosition(int $position): int
{
    $multiplier = 2;
    $base = 1;
    
    return $multiplier * ($this->podium_size - $position) + $base;
}
```

**Exemplo para pódio de 3 posições:**
- 1º lugar: 5 pontos (2 × (3-1) + 1)
- 2º lugar: 3 pontos (2 × (3-2) + 1)
- 3º lugar: 1 ponto (2 × (3-3) + 1)

### Critérios de Desempate

Quando há empate no total de pontos, o sistema usa os seguintes critérios:

1. **Maior contagem de 1º lugar**
2. **Maior contagem de 2º lugar**
3. **Maior contagem de 3º lugar**
4. **Item mais antigo (created_at)**

Isso é implementado no serviço `PollRanking`:

```php
private function comparator(Poll $poll): callable
{
    return function ($a, $b) use ($poll) {
        // 1. Total points
        if ($a['points'] !== $b['points']) {
            return $b['points'] <=> $a['points'];
        }
        
        // 2. First place count
        if ($a['counts'][1] !== $b['counts'][1]) {
            return $b['counts'][1] <=> $a['counts'][1];
        }
        
        // 3. Second place count
        if ($a['counts'][2] !== $b['counts'][2]) {
            return $b['counts'][2] <=> $a['counts'][2];
        }
        
        // 4. Oldest item wins
        return $a['item']->created_at <=> $b['item']->created_at;
    };
}
```

## API

### Rotas Principais

| Método | Rota | Descrição | Middleware |
|--------|------|-----------|------------|
| GET | `/polls` | Lista todas as votações | auth |
| GET | `/polls/{poll}` | Exibe formulário de voto | auth |
| POST | `/polls/{poll}/vote` | Registra um voto | auth, throttle:5,1 |
| GET | `/polls/{poll}/results` | Exibe resultados/ranking | auth |

### Endpoints de Autenticação

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/login` | Página de login |
| POST | `/login` | Processa login |
| POST | `/logout` | Processa logout |
| GET | `/register` | Página de registro |
| POST | `/register` | Processa registro |

## Testes

### Executar Todos os Testes

```bash
php artisan test
```

### Executar Testes Específicos

```bash
# Testes de unidade
php artisan test --testsuite=Unit

# Testes de feature
php artisan test --testsuite=Feature

# Teste específico
php artisan test --filter=test_points_for_position_uses_formula
```

### Cobertura de Testes

O projeto possui 37 testes cobrindo:
- Modelos e relacionamentos
- Lógica de cálculo de ranking
- Fluxo de votação
- Validação de formulários
- Autorização e autenticação
- Páginas e componentes

## Segurança

### Medidas de Segurança Implementadas

1. **Rate Limiting**: Prevenção de abuso com limite de 5 votos/minuto
2. **Authorization Policies**: Controle de acesso centralizado
3. **CSRF Protection**: Proteção contra ataques CSRF em todos os formulários
4. **Validation**: Validação rigorosa de entrada de dados
5. **Authentication**: Sistema de autenticação Laravel padrão
6. **Soft Deletes**: Proteção contra perda acidental de dados

### Boas Práticas

- Uso de prepared statements via Eloquent ORM
- Sanitização de entrada de dados
- Validação server-side e client-side
- Autorização baseada em policies
- Logs de atividades importantes

## Performance

### Otimizações Implementadas

1. **Cache do Ranking**: Redis com TTL de 60 segundos
   - Reduz carga no banco de dados
   - Invalidação automática após novos votos
   - Configurável via `CACHE_DRIVER`

2. **Eager Loading**: Carregamento antecipado de relacionamentos
   - `loadMissing('items')` no PollRanking
   - Reduz N+1 queries

3. **Database Indexing**: Índices em colunas frequentemente consultadas
   - `poll_id` em votes e poll_items
   - `user_id` em votes
   - `status` e `expires_at` em polls

### Monitoramento

Para monitorar performance, recomenda-se:
- Laravel Telescope (desenvolvimento)
- Laravel Horizon (filas em produção)
- New Relic ou Datadog (monitoramento de aplicação)

## Acessibilidade

### WCAG Compliance

O projeto segue diretrizes WCAG AA para acessibilidade:

- **Contraste**: Cores com razão de contraste mínima de 4.5:1
- **Navegação por teclado**: Foco visível em todos os elementos interativos
- **Skip links**: Links para pular ao conteúdo principal
- **ARIA labels**: Labels descritivos para leitores de tela
- **Semântica HTML**: Uso correto de elementos semânticos

### Testes de Acessibilidade

Para testar acessibilidade, recomenda-se:
- Lighthouse (Chrome DevTools)
- axe DevTools
- WAVE (Web Accessibility Evaluation Tool)

## Contribuindo

Contribuições são bem-vindas! Por favor:

1. Fork o repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'feat: adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

### Padrões de Commit

Seguimos o padrão Conventional Commits:

- `feat:` nova funcionalidade
- `fix:` correção de bug
- `refactor:` refatoração de código
- `docs:` mudanças na documentação
- `test:` adição ou correção de testes
- `chore:` mudanças no processo de build

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo LICENSE para detalhes.

## Suporte

Para suporte, abra uma issue no repositório ou entre em contato através do Discord do B7Web.

---

Desenvolvido com ❤️ usando Laravel
