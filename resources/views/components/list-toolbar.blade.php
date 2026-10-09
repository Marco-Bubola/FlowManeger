@props([
    // Propriedade Livewire da busca (null = sem campo de busca).
    'model' => 'search',
    'placeholder' => 'Buscar...',
])

{{-- Barra de busca e filtros das listas (mesmo visual de Produtos): busca à esquerda e
     grupos de botões à direita. No celular os grupos rolam de lado numa linha só. --}}
<div {{ $attributes->merge(['class' => 'app-toolbar mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 shadow-sm xl:flex-row xl:items-center']) }}>
    @if($model)
        <div class="relative flex-1 min-w-0">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="{{ $model }}" placeholder="{{ $placeholder }}"
                   class="app-toolbar-input w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2.5 pl-10 pr-10 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30">
            <button type="button" wire:click="$set('{{ $model }}', '')" x-show="$wire.{{ $model }} && $wire.{{ $model }}.length > 0" x-cloak title="Limpar busca"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
            <div wire:loading.delay wire:target="{{ $model }}" class="absolute right-10 top-1/2 -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent"></div>
            </div>
        </div>
    @endif

    @if(trim($slot) !== '')
        <div class="app-toolbar-groups -mx-1 flex items-center gap-2 overflow-x-auto px-1 pb-1 xl:pb-0">
            {{ $slot }}
        </div>
    @endif
</div>
