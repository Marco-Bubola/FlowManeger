@props([
    'icon' => 'bi-inbox',
    'title',
    'text' => null,
    'tone' => 'indigo',
])

@php
    // Lista vazia padrão do app: ícone, título curto, uma frase e os botões no slot.
    $tones = [
        'indigo' => 'bg-indigo-100 text-indigo-500 dark:bg-indigo-900/30 dark:text-indigo-300',
        'emerald' => 'bg-emerald-100 text-emerald-500 dark:bg-emerald-900/30 dark:text-emerald-300',
        'purple' => 'bg-purple-100 text-purple-500 dark:bg-purple-900/30 dark:text-purple-300',
        'amber' => 'bg-amber-100 text-amber-500 dark:bg-amber-900/30 dark:text-amber-300',
        'sky' => 'bg-sky-100 text-sky-500 dark:bg-sky-900/30 dark:text-sky-300',
        'rose' => 'bg-rose-100 text-rose-500 dark:bg-rose-900/30 dark:text-rose-300',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'fm-empty flex flex-col items-center justify-center text-center py-12 px-4 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/70 dark:bg-slate-800/50']) }}>
    <span class="flex items-center justify-center w-16 h-16 rounded-full mb-4 {{ $tones[$tone] ?? $tones['indigo'] }}">
        <i class="bi {{ $icon }} text-3xl"></i>
    </span>
    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">{{ $title }}</h3>
    @if($text)
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-5">{{ $text }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
