<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="''">
        <div class="flex items-center gap-2 mb-2">
            <!-- Ícone de paleta de cores no heading -->
            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3C7.03 3 3 7.03 3 12c0 2.21 1.79 4 4 4h1a1 1 0 0 1 1 1v1c0 2.21 1.79 4 4 4s4-1.79 4-4c0-4.97-4.03-9-9-9z"/><circle cx="8.5" cy="10.5" r="1.5"/><circle cx="15.5" cy="10.5" r="1.5"/><circle cx="12" cy="15.5" r="1.5"/></svg>
            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('Aparência') }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <!-- Ícone de ajustes no subtítulo -->
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>{{ __('Atualize as configurações de aparência da sua conta') }}</span>
        </div>
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Escuro') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
        </flux:radio.group>

        <form wire:submit.prevent="saveAppearance" class="mt-8 space-y-8">
            <!-- Seleção de Fonte -->
            <div>
                <label class="block font-medium mb-2">{{ __('Fonte do sistema') }}</label>
                <div class="flex gap-4">
                    <flux:radio.group wire:model="font_family" variant="segmented">
                        <flux:radio value="sans">Sans</flux:radio>
                        <flux:radio value="serif">Serif</flux:radio>
                        <flux:radio value="mono">Mono</flux:radio>
                    </flux:radio.group>
                </div>
            </div>
            <!-- Estilo de Bordas -->
            <div>
                <label class="block font-medium mb-2">{{ __('Estilo de borda') }}</label>
                <div class="flex gap-4">
                    <flux:radio.group wire:model="border_style" variant="segmented">
                        <flux:radio value="rounded">Arredondada</flux:radio>
                        <flux:radio value="square">Quadrada</flux:radio>
                        <flux:radio value="shadow">Com sombra</flux:radio>
                    </flux:radio.group>
                </div>
            </div>
            <!-- Cor Secundária -->
            <div>
                <label class="block font-medium mb-2">{{ __('Cor secundária') }}</label>
                <input type="color" wire:model="secondary_color" class="w-12 h-12 p-0 border-0 bg-transparent cursor-pointer" />
            </div>
            <!-- Desenho do Background -->
            <div>
                <label class="block font-medium mb-2">{{ __('Desenho do background') }}</label>
                <div class="flex gap-4">
                    <flux:radio.group wire:model="background_pattern" variant="segmented">
                        <flux:radio value="none">Nenhum</flux:radio>
                        <flux:radio value="gradient">Gradiente</flux:radio>
                        <flux:radio value="dots">Pontos</flux:radio>
                        <flux:radio value="stripes">Listras</flux:radio>
                        <flux:radio value="image">Imagem</flux:radio>
                    </flux:radio.group>
                </div>
            </div>
            <div>
                <button type="submit" class="mt-4 px-6 py-3 rounded-xl bg-primary-600 text-white font-semibold shadow hover:bg-primary-700 transition">Salvar preferências</button>
            </div>
        </form>
    </x-settings.layout>
</section>
