@props([
    'label',
    'icon' => null,
    'tone' => 'indigo',
])
@php
    $tones = [
        'indigo'  => 'bg-indigo-500/12 text-indigo-600 dark:text-indigo-300',
        'emerald' => 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-300',
        'amber'   => 'bg-amber-500/12 text-amber-600 dark:text-amber-300',
        'rose'    => 'bg-rose-500/12 text-rose-600 dark:text-rose-300',
        'sky'     => 'bg-sky-500/12 text-sky-600 dark:text-sky-300',
        'slate'   => 'bg-slate-500/12 text-slate-600 dark:text-slate-300',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'dash-pill ' . ($tones[$tone] ?? $tones['indigo'])]) }}>
    @if($icon)<i class="bi {{ $icon }}"></i>@endif{{ $label }}
</span>
