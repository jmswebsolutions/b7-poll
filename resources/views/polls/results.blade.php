@extends('layouts.app')

@section('title', 'Resultados · '.$poll->title)

@section('content')
    <p><a href="{{ route('polls.index') }}">← Votações</a></p>

    <div class="card">
        <h1>🏆 Resultados — {{ $poll->title }}</h1>
        <p class="muted">Pódio de {{ $poll->podium_size }} · cálculo em tempo real.</p>

        <div class="poll-meta">
            <p><strong>Status:</strong> @if($poll->status === \App\Enums\PollStatus::ACTIVE && $poll->expires_at > now()) <span class="badge badge-open">Em andamento</span> @else <span class="badge badge-finished">Finalizada</span> @endif</p>
            <p><strong>Encerra em:</strong> {{ $poll->expires_at->format('d/m/Y H:i') }}</p>
            <p><strong>Total de votos:</strong> {{ $poll->votes()->count() }}</p>
        </div>

        <ul class="podium">
            @foreach ($ranking as $index => $row)
                <li>
                    <span class="rank rank-{{ $index + 1 }}">
                        @if($index === 0) 🥇
                        @elseif($index === 1) 🥈
                        @elseif($index === 2) 🥉
                        @else {{ $index + 1 }}
                        @endif
                    </span>
                    <strong>{{ $row['item']->name }}</strong>
                    <span class="points">{{ $row['points'] }} pts</span>
                    @if (! empty($row['counts']))
                        <span class="counts">
                            (@foreach ($row['counts'] as $position => $times){{ $position }}º×{{ $times }}@if (! $loop->last), @endif @endforeach)
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($ranking->isEmpty())
            <p class="muted">Esta votação ainda não tem itens.</p>
        @endif

        <button class="share-btn" onclick="shareResults()">📤 Compartilhar Resultados</button>
    </div>

    <script>
        function shareResults() {
            const text = '🏆 Resultados da votação: {{ $poll->title }}\n\n' +
                '@foreach($ranking->take(3) as $index => $row)' +
                '@if($index === 0)🥇 @elseif($index === 1)🥈 @elseif($index === 2)🥉 @else{{ $index + 1 }}. @endif{{ $row['item']->name }} - {{ $row['points'] }} pts\n' +
                '@endforeach';
            
            if (navigator.share) {
                navigator.share({
                    title: 'Resultados: {{ $poll->title }}',
                    text: text,
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Resultados copiados para a área de transferência!');
                });
            }
        }
    </script>
@endsection
