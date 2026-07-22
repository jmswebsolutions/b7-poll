<section class="context-panel" aria-labelledby="context-title">
    <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
    <div>
        <p class="eyebrow">Live B7Web</p>
        <h1 id="context-title">Entre no pódio da votação</h1>
        <p class="context-copy">Acesse sua conta para votar nos projetos favoritos da live e acompanhar os resultados com transparência.</p>
    </div>

    <div class="podium-wrap">
        <img src="{{ asset('assets/auth-premium/auth-podium.svg') }}" alt="Ilustração de pódio com três posições">

        <aside class="ranking-card" aria-label="Prévia do pódio">
            <h2>Pódio da Live</h2>
            <ol class="ranking-list" id="live-podium">
                @php
                    $livePoll = \App\Models\Poll::open()->first();
                    $ranking = $livePoll ? app(\App\Services\PollRanking::class)->for($livePoll)->take(3) : collect();
                @endphp
                @if($ranking->isNotEmpty())
                    @foreach($ranking as $row)
                        <li><span class="rank">{{ $row['rank'] }}</span>@if($row['item']->url)<a href="{{ $row['item']->url }}" target="_blank" style="color: inherit; text-decoration: none;">{{ $row['item']->name }}</a>@else<span>{{ $row['item']->name }}</span>@endif<span class="points">{{ $row['points'] }} pts</span></li>
                    @endforeach
                @else
                    <li><span class="rank">-</span><span>Aguardando votos...</span><span class="points">0 pts</span></li>
                @endif
            </ol>
        </aside>
    </div>

    <div class="rules" aria-label="Regras principais">
        <div class="rule">
            <img src="{{ asset('assets/auth-premium/rule-badge.svg') }}" alt="">
            <div>
                <strong>1 conta por e-mail</strong>
                <span>Use o mesmo e-mail da sua conta de aluno.</span>
            </div>
        </div>

        <div class="rule">
            <img src="{{ asset('assets/premium-home/trophy.svg') }}" alt="">
            <div>
                <strong>1 voto por votação</strong>
                <span>Escolha todos os lugares do pódio antes de enviar.</span>
            </div>
        </div>
    </div>
</section>
