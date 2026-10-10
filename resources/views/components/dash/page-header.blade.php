@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi-speedometer2',
    'gradient' => 'from-indigo-500 via-purple-500 to-pink-500',
    'active' => null,
])

@php
    // Cabeçalho único dos dashboards: título, abas entre os painéis e, no slot "period", o filtro de período.
    $tabs = [
        'geral' => ['label' => 'Geral', 'icon' => 'bi-house', 'route' => 'dashboard.index'],
        'financeiro' => ['label' => 'Financeiro', 'icon' => 'bi-wallet2', 'route' => 'dashboard.cashbook'],
        'bancos' => ['label' => 'Bancos', 'icon' => 'bi-bank', 'route' => 'dashboard.banks'],
        'produtos' => ['label' => 'Produtos', 'icon' => 'bi-box-seam', 'route' => 'dashboard.products'],
        'vendas' => ['label' => 'Vendas', 'icon' => 'bi-graph-up-arrow', 'route' => 'dashboard.sales'],
        'clientes' => ['label' => 'Clientes', 'icon' => 'bi-people', 'route' => 'dashboard.clients'],
    ];
@endphp

<div class="dash-ph relative overflow-hidden app-ph rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)] mb-4">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_32%)]"></div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="app-ph-main flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="app-ph-icon flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br {{ $gradient }} shadow-lg">
                    <i class="bi {{ $icon }} text-white text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <span><i class="bi bi-speedometer2 mr-1"></i>Dashboards</span>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="text-indigo-600 dark:text-indigo-300 truncate">{{ $title }}</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="app-ph-sub mt-0.5 text-sm text-slate-600 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="app-ph-actions flex flex-wrap items-center gap-2 lg:justify-end">{{ $actions ?? '' }}<x-header-bell /></div>
        </div>

        <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
            <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                @foreach($tabs as $key => $tab)
                    @continue(! \Illuminate\Support\Facades\Route::has($tab['route']))
                    <a href="{{ route($tab['route']) }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                              {{ $active === $key
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                        <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        @isset($period)
            <div class="dash-period mt-2 flex flex-wrap items-center gap-2">{{ $period }}</div>
        @endisset
    </div>
</div>
