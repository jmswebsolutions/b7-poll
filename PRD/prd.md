# PRD — Sistema de Votação (MVP)

## 1. Objetivo
Permitir que alunos da B7Web votem nos melhores itens apresentados em live.
Cada votação gera um pódio, cujo tamanho é configurável (nesta live: 3 lugares).

## 2. Escopo do MVP
- Login de aluno (uma conta por e-mail).
- Listar votações que estão valendo.
- Abrir uma votação e ver seus itens.
- Votar escolhendo um item para cada posição do pódio (1º, 2º, 3º...).
- Calcular o ranking (pódio) de cada votação.

Fora do escopo agora: painel administrativo, edição de itens pela interface,
recuperação de senha, notificações. O cadastro de votações e itens será feito
via seeder/tinker no MVP.

## 3. Stack
- Laravel 13 (PHP 8.3)
- SQLite
- Autenticação nativa do Laravel
- Blade para as telas

## 4. Entidades

### users (já existe)
- `id`
- `name`
- `email` (único)
- `password`
- `timestamps`

### polls (votações)
- `id`
- `title` — título da votação
- `description` — texto opcional
- `status` — `active` ou `inactive` (padrão `active`)
- `podium_size` — quantidade de posições do pódio (nesta live: 3)
- `expires_at` — data/hora em que a votação encerra
- `timestamps`

### poll_items (itens da votação)
Nome genérico: hoje são projetos, mas uma votação pode ser sobre qualquer coisa.
- `id`
- `poll_id` — pertence a uma votação
- `name` — nome do item
- `description` — texto opcional
- `timestamps` — `created_at` é usado como critério final de desempate

### votes (votos)
Cabeçalho do voto: um por aluno por votação.
- `id`
- `user_id` — quem votou
- `poll_id` — votação
- `timestamps`
- Índice único em (`user_id`, `poll_id`) — garante um voto por votação.

### vote_items (escolhas do voto)
Uma linha por posição escolhida. Suporta qualquer tamanho de pódio.
- `id`
- `vote_id` — pertence a um voto
- `poll_item_id` — item escolhido
- `position` — posição no pódio (1 = 1º lugar, 2 = 2º lugar...)
- `timestamps`
- Índice único em (`vote_id`, `position`) e em (`vote_id`, `poll_item_id`).

## 5. Regras de negócio

### Pontuação
Fórmula única, válida para qualquer tamanho de pódio:

```
pontos = 2 × (podium_size − position) + 1
```

O último lugar sempre vale 1 ponto e cada posição acima vale 2 pontos a mais.
Para o pódio de 3 desta live, isso resulta em:
- 1º lugar = 5 pontos
- 2º lugar = 3 pontos
- 3º lugar = 1 ponto

### Ranking / desempate (nesta ordem)
1. Nº de vezes marcado em 1º lugar.
2. Nº de vezes marcado em 2º lugar.
3. Nº de vezes marcado em 3º lugar.
4. (segue pelas demais posições, se houver.)
5. Soma total de pontos.
6. Item mais antigo (`created_at`) vence.

Nota de implementação: como os pontos são função direta das contagens por
posição, o critério 5 nunca desempata algo que os critérios 1–4 já não tenham
desempatado (é redundante, mantido por segurança). O critério final efetivo é o
`created_at` (com `id` como desempate determinístico caso as datas coincidam).

### Estados da votação (validado)
- **Em andamento** (aceita votos): `status = active` **e** `expires_at > now()`.
- **Finalizada** (só exibe resultados): `status = inactive` **ou** `expires_at <= now()`.

Nota: em `regras-de-negocio.md` os sinais de `expires_at` estão invertidos.
A definição acima é a validada — este PRD prevalece.

### Segurança do voto
- Um aluno vota uma única vez por votação.
- O voto exige todas as posições do pódio preenchidas (`podium_size` itens).
- Um mesmo item não pode ocupar mais de uma posição.
- Só é possível votar em votação em andamento (definição acima).
- Todos os itens escolhidos devem pertencer à votação votada.
- Uma votação só faz sentido com nº de itens ≥ `podium_size`
  (responsabilidade do seeder no MVP).

## 6. Fluxo do usuário
1. Aluno faz login (ou se cadastra e já entra logado).
2. Vê a home com votações **em andamento** e **finalizadas**.
3. Escolhe uma votação e vê seus dados e itens.
4. Se em andamento e ainda não votou: seleciona um item para cada posição
   do pódio e envia o voto.
5. Sistema valida, registra e redireciona para os resultados.

## 7. Telas
Detalhamento nos planos: `docs/plano-autenticacao.md` e `docs/plano-votacao.md`.

- **Login / cadastro** — Blade feito à mão, guard `web` (sem starter kit).
- **Home (`polls.index`)** — duas seções: em andamento (botão Votar) e
  finalizadas (botão Ver resultados). Cada card: título, tamanho do pódio, prazo.
- **Votação (`polls.show`)** — dados da poll (título, descrição, `podium_size`,
  prazo, status). Se em andamento e sem voto: formulário com um `select` por
  posição. Se já votou ou finalizada: aviso + link para resultados.
- **Resultados (`polls.results`)** — pódio calculado em tempo de execução,
  com pontos e contagem por posição de cada item.

## 8. Validação do voto (decidido)
Todas as regras concentradas em um `StoreVoteRequest`:
- Recebe um array de escolhas (posição → `poll_item_id`).
- Array com exatamente `podium_size` itens, um por posição.
- Cada `poll_item_id` com `exists:poll_items,id`.
- Itens distintos entre si (nenhum repetido em posições diferentes).
- Regra própria confirmando que cada item pertence à votação.
- No controller: votação em andamento e aluno ainda não votou.
- Os índices únicos em `votes` e `vote_items` são a última barreira no banco.

## 9. Decisões fechadas (revisão validada)
- **Estados da votação:** definição da seção 5 (a versão do
  `regras-de-negocio.md` estava com os sinais invertidos).
- **Ranking:** calculado em PHP, em tempo de execução, num serviço
  `PollRanking` — nada persistido. Desempate legível e testável.
- **Home:** exibe em andamento **e** finalizadas (amplia a regra original,
  que citava só as valendo).
- **Pós-login/cadastro:** redireciona para a home (`polls.index`, rota `/`).
- **Senha no cadastro:** `required`, `confirmed`, `min:8`. Sem "lembrar-me",
  sem recuperação de senha (MVP).
- **Formulário de voto:** um `select` por posição do pódio.
- **Resultados:** mostra pontos e contagem por posição (transparência do
  desempate).

## 10. Etapas de implementação (one-shot)
Planos detalhados: `docs/plano-autenticacao.md` e `docs/plano-votacao.md`.
As migrations já foram criadas e executadas (`polls`, `poll_items`, `votes`,
`vote_items`).

1. **Autenticação**
   - Form Requests: `StoreRegisterRequest`, `LoginRequest`.
   - Controllers: `RegisteredUserController` (create/store, login automático),
     `AuthenticatedSessionController` (create/store/destroy).
   - Rotas `guest`: GET/POST `/register`, GET/POST `/login`;
     rota `auth`: POST `/logout`.
   - Views: `layouts/app`, `auth/login`, `auth/register`, partial de erros.
   - Segurança: `@csrf`, `Hash`, `session()->regenerate()`, erro genérico
     de credenciais.
2. **Models**
   - `Poll` (casts, scopes `open`/`finished`, `isOpen()`,
     `pointsForPosition()`), `PollItem`, `Vote`, `VoteItem`;
     `User::votes()` e `hasVotedOn()`.
3. **Serviço de ranking**
   - `App\Services\PollRanking::for(Poll)`: pontos + contagem por posição,
     ordenação com os critérios da seção 5.
4. **Rotas e controllers da votação** (todas atrás de `auth`)
   - GET `/` → `PollController@index`; GET `/polls/{poll}` → `show`;
     GET `/polls/{poll}/results` → `results`;
     POST `/polls/{poll}/vote` → `VoteController@store`.
   - `StoreVoteRequest` (seção 8); gravação de `Vote` + `VoteItem`
     em transação; redirect para resultados.
5. **Views da votação**
   - `polls/index`, `polls/show`, `polls/results` (seção 7).
6. **Seeder de demonstração**
   - 1 poll em andamento (pódio 3, ≥ 4 itens), 1 finalizada com votos,
     alguns usuários de teste.
7. **Verificação**
   - Testes de feature: voto válido, voto duplicado, poll fechada/expirada,
     item repetido, item de outra poll; teste unitário do desempate.
   - Fluxo manual: cadastrar → votar → conferir pódio.
