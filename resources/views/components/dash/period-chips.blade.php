@props([
    'options' => [],
    'current' => null,
    'method' => 'setPeriod',
])

{{-- Filtro de período em chips (ex.: Mês, 3 meses, Ano). Chama o método do componente com a chave. --}}
<div class="dash-chips inline-flex items-center gap-1 rounded-2xl bg-slate-100/80 dark:bg-slate-800/70 p-1 overflow-x-auto max-w-full" role="group" aria-label="Período">
    @foreach($options as $key => $label)
        <button type="button" wire:click="{{ $method }}('{{ $key }}')"
                class="shrink-0 rounded-xl px-3 py-1 text-xs font-bold transition
                       {{ (string) $current === (string) $key
                            ? 'bg-white dark:bg-slate-700 text-indigo-700 dark:text-indigo-200 shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white' }}">
            {{ $label }}
        </button>
    @endforeach
    <span wire:loading.inline-flex class="items-center px-1 text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i></span>
</div>
