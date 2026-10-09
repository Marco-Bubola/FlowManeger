@props(['label', 'value', 'icon' => 'bi-graph-up', 'tone' => 'indigo', 'hint' => null])

@php
    $tones = [
        'indigo' => ['from-indigo-500 to-purple-600', 'text-slate-900 dark:text-white'],
        'emerald' => ['from-emerald-500 to-teal-600', 'text-emerald-700 dark:text-emerald-300'],
        'rose' => ['from-rose-500 to-pink-600', 'text-rose-700 dark:text-rose-300'],
        'amber' => ['from-amber-400 to-orange-500', 'text-amber-700 dark:text-amber-300'],
        'sky' => ['from-sky-500 to-indigo-600', 'text-slate-900 dark:text-white'],
        'slate' => ['from-slate-400 to-slate-600', 'text-slate-700 dark:text-slate-200'],
    ];
    [$gradient, $valueClass] = $tones[$tone] ?? $tones['indigo'];
@endphp

<div class="gestao-stat flex items-center gap-2.5 sm:gap-3 rounded-xl sm:rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-2.5 sm:p-4 shadow-sm min-w-0">
    <div class="flex h-8 w-8 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-md">
        <i class="bi {{ $icon }} text-base sm:text-lg"></i>
    </div>
    <div class="min-w-0 max-w-full">
        <p class="truncate text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="truncate sm:whitespace-normal sm:break-words text-[14px] sm:text-xl font-black leading-tight {{ $valueClass }}" title="{{ $value }}">{{ $value }}</p>
        @if($hint)
            <p class="truncate text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400">{{ $hint }}</p>
        @endif
    </div>
</div>
