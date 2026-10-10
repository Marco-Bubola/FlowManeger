<x-portal-auth-layout title="Entrar" heading="Bem-vindo de volta 👋" subtitle="Entre com seu e-mail ou com o login enviado pela loja.">
    <form method="POST" action="{{ route('portal.login.post') }}" class="pa-form">
        @csrf

        <div>
            <label class="pa-label" for="login">E-mail ou login</label>
            <div class="pa-input-wrap">
                <i class="fas fa-user"></i>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                    placeholder="voce@email.com" autocapitalize="none" class="pa-input @error('login') is-invalid @enderror">
            </div>
            @error('login')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="pa-label" for="password">
                <span>Senha</span>
                <a href="{{ route('portal.password.request') }}" class="pa-link" style="text-transform:none;letter-spacing:0">Esqueceu?</a>
            </label>
            <div class="pa-input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••" class="pa-input pa-has-eye @error('password') is-invalid @enderror">
                <button type="button" class="pa-eye" onclick="paTogglePass(this)" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
            </div>
            @error('password')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <label class="pa-check"><input type="checkbox" name="remember"> Lembrar-me neste aparelho</label>

        <button type="submit" class="pa-btn">Entrar <i class="fas fa-arrow-right"></i></button>
    </form>

    <div class="pa-divider">ou</div>

    <a href="{{ route('portal.google.redirect') }}" class="pa-btn pa-btn-google">
        <svg width="18" height="18" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.243 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917Z"/>
            <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4c-7.682 0-14.347 4.337-17.694 10.691Z"/>
            <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.146 35.091 26.705 36 24 36c-5.222 0-9.618-3.317-11.283-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44Z"/>
            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.084 5.571l.003-.002 6.19 5.238C36.971 39.19 44 34 44 24c0-1.341-.138-2.651-.389-3.917Z"/>
        </svg>
        Entrar com Google
    </a>

    <div class="pa-foot">
        @if(request()->cookie('portal_store'))
            <p class="pa-muted">Primeira compra por aqui?</p>
            <a href="{{ route('portal.register', session('portal_intended') === 'cart' ? ['redirect' => 'cart'] : []) }}" class="pa-btn pa-btn-ghost">
                <i class="fas fa-user-plus"></i> Criar minha conta
            </a>
            <a href="{{ route('portal.catalog', ['userId' => request()->cookie('portal_store')]) }}" class="pa-back">
                <i class="fas fa-arrow-left"></i> Voltar ao catálogo
            </a>
        @else
            <p class="pa-muted">Não tem acesso? Abra o link do catálogo enviado pela loja para criar sua conta.</p>
        @endif
    </div>
</x-portal-auth-layout>
