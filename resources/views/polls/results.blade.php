@extends('layouts.app')

@section('title', 'Resultados · '.$poll->title)

@section('content')
    <p><a href="{{ route('polls.index') }}">← Votações</a></p>

    <div class="card">
        <h1>Resultados — {{ $poll->title }}</h1>
        <p class="muted">Pódio de {{ $poll->podium_size }} · cálculo em tempo real.</p>

        <ol class="podium">
            @foreach ($ranking as $row)
                <li>
                    <strong>{{ $row['item']->name }}</strong>
                    — {{ $row['points'] }} pts
                    @if (! empty($row['counts']))
                        <span class="muted">
                            (@foreach ($row['counts'] as $position => $times){{ $position }}º×{{ $times }}@if (! $loop->last), @endif @endforeach)
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($ranking->isEmpty())
            <p class="muted">Esta votação ainda não tem itens.</p>
        @endif
    </div>
@endsection
