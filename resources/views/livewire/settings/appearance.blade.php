<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="settings-appearance-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-appearance-ultrawide.css') }}">
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

        <div class="mt-6 rounded-2xl border border-slate-200 dark:border-zinc-700 p-4 bg-white/70 dark:bg-zinc-900/40">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Navegação no iPad deitado</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Escolha se no iPad em modo paisagem deseja usar barra lateral fixa ou tab bar inferior.</p>
                </div>
            </div>

            <div class="mt-4" x-data="{
                mode: localStorage.getItem('flowmanager:tablet-nav-mode') === 'tabbar' ? 'tabbar' : 'sidebar',
                save(next) {
                    this.mode = next;
                    localStorage.setItem('flowmanager:tablet-nav-mode', next);
                    window.dispatchEvent(new CustomEvent('flowmanager:tablet-nav-mode-changed', { detail: { mode: next } }));
                }
            }">
                <div class="inline-flex rounded-xl border border-slate-200 dark:border-zinc-700 p-1 bg-slate-50 dark:bg-zinc-800">
                    <button type="button"
                        @click="save('sidebar')"
                        :class="mode === 'sidebar' ? 'bg-indigo-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700'"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition">
                        Sidebar
                    </button>
                    <button type="button"
                        @click="save('tabbar')"
                        :class="mode === 'tabbar' ? 'bg-indigo-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700'"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition">
                        Tab Bar
                    </button>
                </div>
            </div>
        </div>

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
