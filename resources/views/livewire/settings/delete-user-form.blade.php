<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <div class="flex items-center gap-2 mb-1">
            <!-- Ícone de alerta/exclusão -->
            <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v-.008H12v.008zm-7.5-7.5A9 9 0 1 1 21 12A9 9 0 0 1 4.5 4.5z" />
            </svg>
            <flux:heading>{{ __('Excluir conta') }}</flux:heading>
        </div>
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <!-- Ícone de aviso -->
            <svg class="w-5 h-5 text-red-400 dark:text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 19.5A1.5 1.5 0 0 1 19.5 21h-15A1.5 1.5 0 0 1 3 19.5v-15A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15z" />
            </svg>
            <flux:subheading>{{ __('Exclua sua conta e todos os seus recursos') }}</flux:subheading>
        </div>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="flex items-center gap-2">
            <!-- Ícone de lixeira -->
            <svg class="w-5 h-5 text-white dark:text-red-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3m2 0v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h12z" />
            </svg>
            {{ __('Excluir conta') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tem certeza de que deseja excluir sua conta?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente apagados. Por favor, digite sua senha para confirmar que deseja excluir sua conta permanentemente.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Senha')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit" class="flex items-center gap-2">
                    <!-- Ícone de lixeira -->
                    <svg class="w-5 h-5 text-white dark:text-red-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3m2 0v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h12z" />
                    </svg>
                    {{ __('Excluir conta') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>