<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>B7Web Votações — Entrar</title>
  <link rel="stylesheet" href="{{ asset('assets/auth-premium/auth.css') }}">
</head>
<body>
  <div class="auth-page">
    <header class="auth-topbar">
      <div class="topbar-inner">
        <a class="brand focus-ring" href="{{ route('login') }}" aria-label="B7Web Votações">
          <img src="{{ asset('assets/premium-home/brand-mark.svg') }}" alt="">
          <span>B7Web Votações</span>
        </a>
      </div>
    </header>

    <main class="auth-shell">
      @include('auth.partials.context')

      <section class="auth-card" aria-label="Autenticação">
        <div class="tab-switch" role="tablist" aria-label="Escolha o modo de acesso">
          <a class="is-active focus-ring" href="{{ route('login') }}" role="tab">Entrar</a>
          <a class="focus-ring" href="{{ route('register') }}" role="tab">Criar conta</a>
        </div>

        <div class="form-panel login-panel">
          <h2 class="form-title">Entre para votar</h2>
          <p class="form-copy">Acesse sua conta e escolha seus projetos favoritos.</p>

          <form class="form" action="{{ route('login') }}" method="post">
            @csrf

            <div class="field">
              <label for="login-email">E-mail</label>
              <div class="input-shell @error('email') has-error @enderror">
                <img src="{{ asset('assets/auth-premium/mail.svg') }}" alt="">
                <input id="login-email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@exemplo.com" required autofocus>
              </div>
              @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
              <label for="login-password">Senha</label>
              <div class="input-shell @error('password') has-error @enderror">
                <img src="{{ asset('assets/auth-premium/lock.svg') }}" alt="">
                <input id="login-password" name="password" type="password" placeholder="Sua senha" required>
              </div>
              @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <button class="button" type="submit">Entrar</button>
          </form>

          <div class="form-footer">
            Ainda não tem conta?
            <a class="link focus-ring" href="{{ route('register') }}">Criar minha conta</a>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Real-time podium updates for login page
    document.addEventListener('DOMContentLoaded', function() {
      const livePodium = document.getElementById('live-podium');

      if (livePodium) {
        // Update podium every 5 seconds using public API
        setInterval(async () => {
          try {
            const response = await fetch('/live-podium');
            const data = await response.json();

            if (livePodium && data.ranking && data.ranking.length > 0) {
              livePodium.innerHTML = data.ranking.map(row => `
                <li><span class="rank">${row.rank}</span>${row.item.url ? `<a href="${row.item.url}" target="_blank" style="color: inherit; text-decoration: none;">${row.item.name}</a>` : `<span>${row.item.name}</span>`}<span class="points">${row.points} pts</span></li>
              `).join('');
            } else if (livePodium) {
              livePodium.innerHTML = '<li><span class="rank">-</span><span>Aguardando votos...</span><span class="points">0 pts</span></li>';
            }
          } catch (error) {
            console.error('Error fetching live podium:', error);
          }
        }, 5000); // 5 seconds
      }
    });
  </script>
</body>
</html>
