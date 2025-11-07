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

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Esqueceu a senha')" :description="__('Digite seu e-mail para receber um link de redefinição de senha')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Endereço de e-mail')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Enviar link de redefinição de senha') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('Ou, voltar para') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('login') }}</flux:link>
    </div>
</div>
