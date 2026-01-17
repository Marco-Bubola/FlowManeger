@props(['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'subtitle' => null])

@php
    $colorClasses = [
        'blue' => 'from-blue-500 to-indigo-600',
        'green' => 'from-green-500 to-emerald-600',
        'purple' => 'from-purple-500 to-pink-600',
        'orange' => 'from-orange-500 to-red-600',
        'cyan' => 'from-cyan-500 to-blue-600',
        'pink' => 'from-pink-500 to-rose-600',
    ];
    $gradient = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="group relative bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-slate-200 dark:border-slate-700">
    <!-- Gradient background animado -->
    <div class="absolute inset-0 bg-gradient-to-br {{ $gradient }} opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

    <!-- Ícone decorativo de fundo -->
    <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br {{ $gradient }} opacity-10 rounded-full blur-2xl"></div>

    <div class="relative z-10">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">{{ $title }}</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white flex items-baseline gap-2">
                    {{ $value }}
                    @if($trend)
                        <span class="text-sm font-medium {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="bi bi-{{ $trend > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($trend) }}%
                        </span>
                    @endif
                </h3>
                @if($subtitle)
                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>

            <!-- Ícone -->
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                <i class="{{ $icon }} text-2xl text-white"></i>
            </div>
        </div>

        <!-- Slot para conteúdo adicional -->
        @if($slot->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
