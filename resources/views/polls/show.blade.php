@extends('layouts.app')

@section('title', $poll->title)

@push('styles')
<style>
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .button-loading {
        opacity: 0.7;
        pointer-events: none;
    }
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
        max-width: 350px;
    }
    .toast.success { border-left: 4px solid #18a058; }
    .toast.error { border-left: 4px solid #c0392b; }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>
@endpush

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

            <form method="POST" action="{{ route('polls.vote', $poll) }}" id="voteForm">
                @csrf

                @for ($position = 1; $position <= $poll->podium_size; $position++)
                    <label for="position-{{ $position }}">
                        {{ $position }}º lugar ({{ $poll->pointsForPosition($position) }} pts)
                    </label>
                    <select id="position-{{ $position }}" name="items[{{ $position }}]" required class="vote-select">
                        <option value="">— selecione —</option>
                        @foreach ($poll->items as $item)
                            <option value="{{ $item->id }}" @selected(old("items.$position") == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                @endfor

                <button type="submit" id="submitBtn">Enviar voto</button>
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

    @if(session('status'))
        <div id="toast" class="toast success">
            {{ session('status') }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('voteForm');
        const submitBtn = document.getElementById('submitBtn');
        const selects = document.querySelectorAll('.vote-select');
        const toast = document.getElementById('toast');

        // Client-side validation
        form.addEventListener('submit', function(e) {
            const selectedValues = [];
            let hasDuplicates = false;
            let hasEmpty = false;

            selects.forEach(select => {
                const value = select.value;
                if (!value) {
                    hasEmpty = true;
                } else if (selectedValues.includes(value)) {
                    hasDuplicates = true;
                }
                selectedValues.push(value);
            });

            if (hasEmpty) {
                e.preventDefault();
                showToast('Por favor, selecione um item para cada posição.', 'error');
                return;
            }

            if (hasDuplicates) {
                e.preventDefault();
                showToast('Um mesmo item não pode ocupar mais de uma posição.', 'error');
                return;
            }

            // Show loading state
            submitBtn.classList.add('button-loading');
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Enviando...';
        });

        // Auto-hide toast
        if (toast) {
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();

            const newToast = document.createElement('div');
            newToast.className = `toast ${type}`;
            newToast.textContent = message;
            document.body.appendChild(newToast);

            setTimeout(() => {
                newToast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => newToast.remove(), 300);
            }, 4000);
        }
    });
</script>
@endpush
