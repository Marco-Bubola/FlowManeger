{{-- Componente Header Moderno Reutilizável --}}
@props([
    'icon' => 'fas fa-chart-line',
    'title' => 'Título',
    'subtitle' => null,
    'breadcrumb' => [],
    'actions' => null,
    'gradient' => 'from-indigo-500 via-purple-500 to-pink-500',
    'bg' => 'from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30',
    'iconBg' => 'from-indigo-500 via-purple-500 to-pink-500',
    'iconColor' => 'text-white',
    'ringColor' => 'ring-white/50 dark:ring-slate-700/50',
    'extra' => null
])
<div class="relative overflow-hidden bg-gradient-to-r {{ $bg }} backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl mb-6 w-full">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-16 -translate-y-16 blur-2xl"></div>
    <div class="absolute bottom-0 left-0 w-28 h-28 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-12 translate-y-12 blur-xl"></div>
    <div class="relative px-6 py-5">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="relative group">
                    <div class="w-20 h-20 bg-gradient-to-br {{ $iconBg }} rounded-2xl shadow-2xl flex items-center justify-center ring-4 {{ $ringColor }} group-hover:scale-105 transition-transform duration-300">
                        <i class="{{ $icon }} {{ $iconColor }} text-3xl"></i>
                        <div class="absolute inset-0 bg-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-3 border-white dark:border-slate-800 shadow-lg">
                        <div class="w-full h-full bg-green-400 rounded-full animate-ping opacity-75"></div>
                    </div>
                </div>
                <div class="space-y-1">
                    @if($breadcrumb && is_array($breadcrumb) && count($breadcrumb) > 0)
                        <div class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">
                            @foreach($breadcrumb as $item)
                                @if(isset($item['icon']))
                                    <i class="{{ $item['icon'] }} {{ $item['iconColor'] ?? 'text-indigo-600 dark:text-indigo-400' }}"></i>
                                @endif
                                @if(isset($item['url']))
                                    <a href="{{ $item['url'] }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $item['label'] }}</a>
                                @else
                                    <span class="text-slate-800 dark:text-white">{{ $item['label'] }}</span>
                                @endif
                                @if(!$loop->last)
                                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    @if($subtitle)
                        <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-300">{!! $subtitle !!}</div>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {!! $actions !!}
            </div>
        </div>
        @if($extra)
            <div class="mt-4">{!! $extra !!}</div>
        @endif
    </div>
</div>
