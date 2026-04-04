<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div style="display:flex;flex-direction:column;gap:1.4rem;">

    <div style="text-align:center;margin-bottom:0.2rem;">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);margin-bottom:0.8rem;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h1 class="auth-heading">Área Segura</h1>
        <p class="auth-subheading" style="margin-top:0.3rem;">Confirme sua senha para acessar esta área protegida</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form wire:submit="confirmPassword" style="display:flex;flex-direction:column;gap:0.9rem;">
        <div>
            <label class="auth-label" for="confirm-pw">Senha atual</label>
            <div style="position:relative;">
                <input id="confirm-pw" wire:model="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="auth-input auth-input-pw">
                <button type="button" class="auth-pw-toggle" onclick="toggleAuthPassword('confirm-pw')" aria-label="Mostrar senha">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password') <p class="auth-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="auth-btn-primary" wire:loading.attr="disabled" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
            <span wire:loading.remove wire:target="confirmPassword">Confirmar acesso →</span>
            <span wire:loading wire:target="confirmPassword" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <svg style="animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                Verificando...
            </span>
        </button>
    </form>
</div>

@push('scripts')
    @include('partials.firebase-auth')
@endpush
