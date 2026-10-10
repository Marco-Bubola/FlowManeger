@props([
    'label',
    'method' => 'shiftMonth',
    'canNext' => true,
])

{{-- Navegação de mês: ‹ Outubro 2026 › --}}
<div class="dash-chips inline-flex items-center gap-1 rounded-2xl bg-slate-100/80 dark:bg-slate-800/70 p-1" role="group" aria-label="Mês">
    <button type="button" wire:click="{{ $method }}(-1)" class="w-7 h-7 rounded-xl flex items-center justify-center text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-indigo-700" aria-label="Mês anterior"><i class="bi bi-chevron-left"></i></button>
    <span class="min-w-[7.5rem] text-center rounded-xl bg-white dark:bg-slate-700 px-3 py-1 text-xs font-bold text-indigo-700 dark:text-indigo-200 shadow-sm">{{ $label }}</span>
    <button type="button" wire:click="{{ $method }}(1)" @disabled(! $canNext) class="w-7 h-7 rounded-xl flex items-center justify-center text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-indigo-700 disabled:opacity-30 disabled:pointer-events-none" aria-label="Próximo mês"><i class="bi bi-chevron-right"></i></button>
    <span wire:loading.inline-flex class="items-center px-1 text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i></span>
</div>
