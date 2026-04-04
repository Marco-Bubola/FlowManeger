<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Heading --}}
    <div>
        <h1 class="auth-heading">Bem-vindo de volta 👋</h1>
        <p class="auth-subheading">Entre para continuar no FlowManager</p>
    </div>

    {{-- Session status --}}
    <x-auth-session-status :status="session('status')" />

    {{-- ── Social providers ── --}}
    <div class="auth-social-grid">
        {{-- Google --}}
        <button
            type="button"
            id="btn-google"
            onclick="FlowAuth.google(this)"
            class="auth-social-btn"
            title="Entrar com Google"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" data-icon><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            <svg data-spin style="display:none;animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
            <span data-label data-text="Google">Google</span>
        </button>

        {{-- GitHub --}}
        <button
            type="button"
            id="btn-github"
            onclick="FlowAuth.github(this)"
            class="auth-social-btn"
            title="Entrar com GitHub"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" data-icon><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <svg data-spin style="display:none;animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
            <span data-label data-text="GitHub">GitHub</span>
        </button>

        {{-- Microsoft --}}
        <button
            type="button"
            id="btn-microsoft"
            onclick="FlowAuth.microsoft(this)"
            class="auth-social-btn"
            title="Entrar com Microsoft"
        >
            <svg width="18" height="18" viewBox="0 0 21 21" data-icon><rect x="1" y="1" width="9" height="9" fill="#f25022"/><rect x="11" y="1" width="9" height="9" fill="#7fba00"/><rect x="1" y="11" width="9" height="9" fill="#00a4ef"/><rect x="11" y="11" width="9" height="9" fill="#ffb900"/></svg>
            <svg data-spin style="display:none;animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
            <span data-label data-text="Microsoft">Microsoft</span>
        </button>
    </div>

    {{-- Divider --}}
    <div class="auth-divider">ou entre com e-mail</div>

    {{-- ── Email / password form ── --}}
    <form wire:submit="login" style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Email --}}
        <div>
            <label class="auth-label" for="login-email">E-mail</label>
            <input
                id="login-email"
                wire:model="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="voce@exemplo.com"
                class="auth-input"
            >
            @error('email')
                <p class="auth-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
                <label class="auth-label" for="login-pw" style="margin-bottom:0;">Senha</label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="auth-link" style="font-size:0.78rem;">Esqueceu?</a>
                @endif
            </div>
            <div style="position:relative;">
                <input
                    id="login-pw"
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="auth-input auth-input-pw"
                >
                <button type="button" class="auth-pw-toggle pw-eye" onclick="toggleAuthPassword('login-pw')" aria-label="Mostrar senha" aria-pressed="false">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password')
                <p class="auth-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember --}}
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;user-select:none;">
            <input wire:model="remember" type="checkbox" class="auth-checkbox">
            <span style="font-size:0.82rem;color:rgba(255,255,255,0.5);">Lembrar-me por 30 dias</span>
        </label>

        {{-- Submit --}}
        <button
            type="submit"
            class="auth-btn-primary"
            wire:loading.attr="disabled"
            style="margin-top:0.25rem;"
        >
            <span wire:loading.remove wire:target="login">Entrar no FlowManager</span>
            <span wire:loading wire:target="login" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <svg style="animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                Entrando...
            </span>
        </button>
    </form>

    {{-- Register link --}}
    @if(Route::has('register'))
        <p style="text-align:center;font-size:0.83rem;color:rgba(255,255,255,0.4);">
            Ainda não tem conta?
            <a href="{{ route('register') }}" wire:navigate class="auth-link">Criar conta grátis</a>
        </p>
    @endif

</div>

@push('scripts')
    @include('partials.firebase-auth')
@endpush
