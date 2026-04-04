<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            // Sucesso: mensagem genérica para não vazar existência de conta
            session()->flash('status', __('A reset link will be sent if the account exists.'));
        } else {
            // Em caso de falha, logamos e mostramos uma mensagem amigável
            logger()->warning('Password reset link failed', ['email' => $this->email, 'status' => $status]);
            session()->flash('status', __('Não foi possível enviar o link. Tente novamente mais tarde.'));
        }
    }
}; ?>

<div style="display:flex;flex-direction:column;gap:1.4rem;">
    <div style="text-align:center;margin-bottom:0.2rem;">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(168,85,247,0.12);border:1px solid rgba(168,85,247,0.2);margin-bottom:0.8rem;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h1 class="auth-heading">Esqueceu a senha?</h1>
        <p class="auth-subheading" style="margin-top:0.3rem;">Digite seu e-mail e enviaremos um link de redefinição</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form wire:submit.prevent="sendPasswordResetLink" style="display:flex;flex-direction:column;gap:0.9rem;">
        <div>
            <label class="auth-label" for="fp-email">E-mail</label>
            <input id="fp-email" wire:model="email" type="email" required autofocus autocomplete="email" placeholder="voce@exemplo.com" class="auth-input">
            @error('email') <p class="auth-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="auth-btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendPasswordResetLink">Enviar link de redefinição →</span>
            <span wire:loading wire:target="sendPasswordResetLink" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <svg style="animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                Enviando...
            </span>
        </button>
    </form>

    <p style="text-align:center;font-size:0.83rem;color:rgba(255,255,255,0.4);">
        Lembrou a senha? <a href="{{ route('login') }}" wire:navigate class="auth-link">Voltar ao login</a>
    </p>
</div>
