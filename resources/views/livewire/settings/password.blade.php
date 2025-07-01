<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="''">
        <div class="flex items-center gap-2 mb-2">
            <!-- Ícone de cadeado no heading -->
            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5A4.5 4.5 0 0 0 7.5 7.5v3m9 0A2.25 2.25 0 0 1 18.75 12.75v6A2.25 2.25 0 0 1 16.5 21H7.5A2.25 2.25 0 0 1 5.25 18.75v-6A2.25 2.25 0 0 1 7.5 10.5m9 0h-9"/></svg>
            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('Atualizar senha') }}</span>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Garanta que sua conta esteja usando uma senha longa e aleatória para maior segurança') }}</div>
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Senha atual')"
                type="password"
                required
                autocomplete="current-password"
                icon="lock-closed"
            />
            <flux:input
                wire:model="password"
                :label="__('Nova senha')"
                type="password"
                required
                autocomplete="new-password"
                icon="lock-closed"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmar senha')"
                type="password"
                required
                autocomplete="new-password"
                icon="lock-closed"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full flex items-center justify-center gap-3 py-3 px-6 text-lg font-semibold rounded-xl shadow-md transition-all duration-200 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400">
                        <!-- Ícone de check moderno -->
                        <svg class="w-6 h-6 text-white dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.704 6.292a1 1 0 010 1.416l-6.25 6.25a1 1 0 01-1.416 0l-3.25-3.25a1 1 0 111.416-1.416l2.542 2.542 5.542-5.542a1 1 0 011.416 0z" clip-rule="evenodd"/></svg>
                        {{ __('Salvar') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Salvo.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
