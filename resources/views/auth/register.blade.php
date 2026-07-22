<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>B7Web Votações — Criar conta</title>
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
          <a class="focus-ring" href="{{ route('login') }}" role="tab">Entrar</a>
          <a class="is-active focus-ring" href="{{ route('register') }}" role="tab">Criar conta</a>
        </div>

        <div class="form-panel register-panel">
          <h2 class="form-title">Crie sua conta</h2>
          <p class="form-copy">Cadastro rápido para alunos da live. Depois disso, você já entra logado.</p>

          <form class="form" action="{{ route('register') }}" method="post">
            @csrf

            <div class="field">
              <label for="register-name">Nome</label>
              <div class="input-shell @error('name') has-error @enderror">
                <img src="{{ asset('assets/auth-premium/user.svg') }}" alt="">
                <input id="register-name" name="name" type="text" value="{{ old('name') }}" placeholder="Seu nome" required autofocus>
              </div>
              @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
              <label for="register-email">E-mail</label>
              <div class="input-shell @error('email') has-error @enderror">
                <img src="{{ asset('assets/auth-premium/mail.svg') }}" alt="">
                <input id="register-email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@exemplo.com" required>
              </div>
              @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
              <label for="register-password">Senha</label>
              <div class="input-shell @error('password') has-error @enderror">
                <img src="{{ asset('assets/auth-premium/lock.svg') }}" alt="">
                <input id="register-password" name="password" type="password" placeholder="Mínimo de 8 caracteres" required>
              </div>
              @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
              <label for="register-password-confirmation">Confirmar senha</label>
              <div class="input-shell">
                <img src="{{ asset('assets/auth-premium/lock.svg') }}" alt="">
                <input id="register-password-confirmation" name="password_confirmation" type="password" placeholder="Repita sua senha" required>
              </div>
            </div>

            <button class="button" type="submit">Criar conta e entrar</button>
          </form>

          <p class="note"><strong>Regra do MVP:</strong> cada e-mail cria uma única conta e pode votar uma vez em cada votação.</p>

          <div class="form-footer">
            Já tem conta?
            <a class="link focus-ring" href="{{ route('login') }}">Entrar agora</a>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Real-time podium updates for register page
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
