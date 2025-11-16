@props([
    'title' => 'Estatística',
    'value' => '0',
    'icon' => 'fa-chart-line',
    'color' => 'blue',
    'subtitle' => null
])

@php
    $colors = [
        'blue' => [
            'gradient' => 'from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700',
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-600 dark:text-blue-400'
        ],
        'emerald' => [
            'gradient' => 'from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700',
            'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'text' => 'text-emerald-600 dark:text-emerald-400'
        ],
        'orange' => [
            'gradient' => 'from-orange-500 to-red-500 dark:from-orange-600 dark:to-red-600',
            'bg' => 'bg-orange-100 dark:bg-orange-900/30',
            'text' => 'text-orange-600 dark:text-orange-400'
        ],
        'purple' => [
            'gradient' => 'from-purple-500 to-pink-600 dark:from-purple-600 dark:to-pink-700',
            'bg' => 'bg-purple-100 dark:bg-purple-900/30',
            'text' => 'text-purple-600 dark:text-purple-400'
        ]
    ];

    $selectedColor = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden transform hover:scale-105 transition-all duration-300">
    <div class="bg-gradient-to-br {{ $selectedColor['gradient'] }} px-4 py-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="bg-white bg-opacity-20 rounded-xl p-3">
                    <i class="fas {{ $icon }} text-2xl text-white"></i>
                </div>
            </div>
            <div class="ml-4 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-white/80 truncate">{{ $title }}</dt>
                    <dd class="text-2xl font-bold text-white">{{ $value }}</dd>
                </dl>
            </div>
        </div>
    </div>
    @if($subtitle)
    <div class="px-4 py-3">
        <div class="text-sm text-slate-600 dark:text-slate-400">
            {{ $subtitle }}
        </div>
    </div>
    @endif
</div>
