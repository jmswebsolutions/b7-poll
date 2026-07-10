<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Votação B7Web')</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f4f5; color: #18181b; }
        .container { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { font-size: 1.5rem; margin-top: 0; }
        label { display: block; margin: .75rem 0 .25rem; font-weight: 600; }
        input, select { width: 100%; padding: .5rem; border: 1px solid #d4d4d8; border-radius: 6px; font-size: 1rem; }
        button { margin-top: 1rem; padding: .6rem 1.2rem; background: #4f46e5; color: #fff; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #4338ca; }
        a { color: #4f46e5; }
        .muted { color: #71717a; font-size: .9rem; }
        .errors { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .flash { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .8rem; }
        .badge-open { background: #dcfce7; color: #15803d; }
        .badge-finished { background: #f4f4f5; color: #71717a; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .podium { list-style: none; padding: 0; margin: 0; }
        .podium li { margin: .75rem 0; display: flex; align-items: center; gap: .75rem; padding: .75rem; border-radius: 8px; transition: all 0.3s ease; }
        .podium li:hover { background: #f9fafb; transform: translateX(4px); }
        .podium li strong { flex: 1; font-size: 1.1rem; }
        .podium li span { white-space: nowrap; }
        .podium .rank { font-size: 1.5rem; font-weight: bold; width: 40px; text-align: center; }
        .podium .rank-1 { color: #fbbf24; } /* Ouro */
        .podium .rank-2 { color: #9ca3af; } /* Prata */
        .podium .rank-3 { color: #b45309; } /* Bronze */
        .podium .points { font-weight: 600; color: #4f46e5; }
        .podium .counts { font-size: .85rem; color: #71717a; }
        .poll-meta { margin: 1rem 0; padding: 1rem; background: #f9fafb; border-radius: 6px; font-size: .9rem; }
        .poll-meta p { margin: .25rem 0; }
        .share-btn { background: #059669; margin-top: .5rem; }
        .share-btn:hover { background: #047857; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .podium li { animation: fadeIn 0.5s ease forwards; }
        .podium li:nth-child(1) { animation-delay: 0.1s; }
        .podium li:nth-child(2) { animation-delay: 0.2s; }
        .podium li:nth-child(3) { animation-delay: 0.3s; }
        .podium li:nth-child(4) { animation-delay: 0.4s; }
        .podium li:nth-child(5) { animation-delay: 0.5s; }
        @media (max-width: 640px) {
            .container { padding: 0 .5rem; }
            .card { padding: 1rem; }
            .podium li { flex-direction: column; align-items: flex-start; gap: .25rem; }
            .podium .rank { width: auto; }
        }
    </style>
</head>
<body>
    <div class="container">
        @auth
            <div class="topbar">
                <strong>Votação B7Web</strong>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            </div>
        @endauth

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
