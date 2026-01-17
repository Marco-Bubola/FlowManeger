@props(['rarity' => 'bronze', 'count' => 0, 'size' => 'md'])

@php
    $rarityConfig = [
        'bronze' => [
            'gradient' => 'from-amber-700 to-orange-800',
            'icon' => 'bi-trophy-fill',
            'glow' => 'shadow-amber-500/50',
        ],
        'silver' => [
            'gradient' => 'from-slate-400 to-slate-600',
            'icon' => 'bi-trophy-fill',
            'glow' => 'shadow-slate-400/50',
        ],
        'gold' => [
            'gradient' => 'from-yellow-400 to-yellow-600',
            'icon' => 'bi-trophy-fill',
            'glow' => 'shadow-yellow-500/50',
        ],
        'platinum' => [
            'gradient' => 'from-cyan-300 to-blue-500',
            'icon' => 'bi-gem',
            'glow' => 'shadow-cyan-400/50',
        ],
    ];

    $config = $rarityConfig[$rarity] ?? $rarityConfig['bronze'];

    $sizes = [
        'sm' => 'w-12 h-12 text-lg',
        'md' => 'w-16 h-16 text-2xl',
        'lg' => 'w-20 h-20 text-3xl',
        'xl' => 'w-24 h-24 text-4xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="relative inline-flex items-center justify-center group">
    <!-- Glow effect -->
    <div class="absolute inset-0 bg-gradient-to-br {{ $config['gradient'] }} rounded-full blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-300 {{ $config['glow'] }}"></div>

    <!-- Trophy container -->
    <div class="relative {{ $sizeClass }} rounded-full bg-gradient-to-br {{ $config['gradient'] }} flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
        <i class="{{ $config['icon'] }} text-white"></i>
    </div>

    <!-- Count badge -->
    @if($count > 0)
        <div class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center shadow-lg animate-pulse">
            <span class="text-xs font-bold text-white">{{ $count }}</span>
        </div>
    @endif
</div>
