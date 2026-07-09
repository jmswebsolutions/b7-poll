@extends('layouts.app')

@section('title', $poll->title)

@section('content')
    <p><a href="{{ route('polls.index') }}">← Votações</a></p>

    <div class="card">
        <h1>{{ $poll->title }}</h1>
        @if ($poll->description)
            <p>{{ $poll->description }}</p>
        @endif
        <p class="muted">
            Pódio de {{ $poll->podium_size }} lugares ·
            {{ $poll->isOpen() ? 'encerra '.$poll->expires_at->format('d/m/Y H:i') : 'finalizada' }}
        </p>
    </div>

    @if ($poll->isOpen() && ! $alreadyVoted)
        <div class="card">
            <h2>Seu voto</h2>

            @include('partials.errors')

            <form method="POST" action="{{ route('polls.vote', $poll) }}">
                @csrf

                @for ($position = 1; $position <= $poll->podium_size; $position++)
                    <label for="position-{{ $position }}">
                        {{ $position }}º lugar ({{ $poll->pointsForPosition($position) }} pts)
                    </label>
                    <select id="position-{{ $position }}" name="items[{{ $position }}]" required>
                        <option value="">— selecione —</option>
                        @foreach ($poll->items as $item)
                            <option value="{{ $item->id }}" @selected(old("items.$position") == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                @endfor

                <button type="submit">Enviar voto</button>
            </form>
        </div>
    @else
        <div class="card">
            @if ($alreadyVoted)
                <p>Você já votou nesta votação.</p>
            @else
                <p>Esta votação está finalizada.</p>
            @endif
            <a href="{{ route('polls.results', $poll) }}">Ver resultados</a>
        </div>
    @endif
@endsection
