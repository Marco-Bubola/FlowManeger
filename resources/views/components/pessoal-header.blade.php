@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi-person-heart',
    'gradient' => 'from-violet-500 via-purple-500 to-fuchsia-500',
    'active' => null,
    // Telas internas (quadro, criar, editar): [['label' => 'Metas', 'url' => ...], ['label' => 'Editar meta']]
    'crumbs' => [],
])

@php
    // Cabeçalho único da área Pessoal: as abas ligam Hoje, Metas, Hábitos, Conquistas e Insights.
    $tabs = [
        'hoje' => ['label' => 'Hoje', 'icon' => 'bi-sun', 'url' => route('conquistas.hub')],
        'metas' => ['label' => 'Metas', 'icon' => 'bi-bullseye', 'url' => route('goals.dashboard')],
        'habitos' => ['label' => 'Hábitos', 'icon' => 'bi-check2-square', 'url' => route('daily-habits.dashboard')],
        'conquistas' => ['label' => 'Conquistas', 'icon' => 'bi-trophy', 'url' => route('achievements.index')],
        'insights' => ['label' => 'Insights', 'icon' => 'bi-graph-up-arrow', 'url' => route('conquistas.hub', ['aba' => 'insights'])],
    ];
    $back = collect($crumbs)->filter(fn ($c) => ! empty($c['url']))->last();
@endphp

<div class="pes-ph relative overflow-hidden app-ph rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(245,243,255,0.92),rgba(253,244,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,27,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)] mb-4">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(139,92,246,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(236,72,153,0.10),transparent_32%)]"></div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="app-ph-main flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                @if($back)
                    <a href="{{ $back['url'] }}" wire:navigate aria-label="Voltar para {{ $back['label'] }}"
                       class="pes-ph-back flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-purple-700 dark:hover:text-purple-300">
                        <i class="bi bi-arrow-left text-lg"></i>
                    </a>
                @endif
                <div class="app-ph-icon flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br {{ $gradient }} shadow-lg">
                    <i class="bi {{ $icon }} text-white text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 min-w-0">
                        <span class="shrink-0"><i class="bi bi-person-heart mr-1"></i>Pessoal</span>
                        @foreach($crumbs as $c)
                            <i class="bi bi-chevron-right text-[10px] shrink-0"></i>
                            @if(! empty($c['url']))
                                <a href="{{ $c['url'] }}" wire:navigate class="truncate hover:text-purple-700 dark:hover:text-purple-300">{{ $c['label'] }}</a>
                            @else
                                <span class="truncate text-purple-600 dark:text-purple-300">{{ $c['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-purple-700 to-fuchsia-700 dark:from-slate-100 dark:via-purple-300 dark:to-fuchsia-300 bg-clip-text text-transparent">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="app-ph-sub mt-0.5 text-sm text-slate-600 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="app-ph-actions flex flex-wrap items-center gap-2 lg:justify-end">{{ $actions ?? '' }}<x-header-bell /></div>
        </div>

        @if(empty($crumbs))
            <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
                <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                    @foreach($tabs as $key => $tab)
                        <a href="{{ $tab['url'] }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                                  {{ $active === $key
                                        ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-md shadow-purple-500/25'
                                        : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-purple-700 dark:hover:text-purple-300' }}">
                            <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </div>
</div>
