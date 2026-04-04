<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard.index', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div style="display:flex;flex-direction:column;gap:1.5rem;text-align:center;">

    <div>
        <div style="display:inline-flex;position:relative;margin-bottom:0.8rem;">
            <div style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:18px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.25);">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <span style="position:absolute;top:-4px;right:-4px;display:flex;width:16px;height:16px;">
                <span style="position:absolute;display:inline-flex;width:100%;height:100%;border-radius:50%;background:#3b82f6;opacity:0.5;animation:ping 1s cubic-bezier(0,0,0.2,1) infinite;"></span>
                <span style="position:relative;display:inline-flex;width:16px;height:16px;border-radius:50%;background:#3b82f6;"></span>
            </span>
        </div>
        <h1 class="auth-heading">Verifique seu e-mail</h1>
        <p class="auth-subheading" style="margin-top:0.4rem;line-height:1.6;">
            Enviamos um link de verificação para o seu endereço. Por favor, clique no link para continuar.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div style="display:flex;align-items:center;gap:0.5rem;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:0.75rem 1rem;font-size:0.83rem;color:#34d399;font-weight:500;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>Novo link de verificação enviado com sucesso!</span>
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:0.75rem;">
        <button wire:click="sendVerification" type="button" class="auth-btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendVerification">Reenviar e-mail de verificação</span>
            <span wire:loading wire:target="sendVerification" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <svg style="animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                Enviando...
            </span>
        </button>
        <button wire:click="logout" type="button" style="display:flex;width:100%;align-items:center;justify-content:center;gap:0.5rem;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);padding:0.7rem 1rem;font-size:0.875rem;font-weight:500;color:rgba(255,255,255,0.45);cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.75)';" onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='rgba(255,255,255,0.45)';">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair da conta
        </button>
    </div>

</div>

<style>
@keyframes ping { 75%,100% { transform:scale(2); opacity:0; } }
</style>
