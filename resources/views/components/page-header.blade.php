@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi-grid',
    'backRoute' => null,
    // Seção da trilha "Seção > Título" (vendas, clientes, categorias, produtos, financas, gestao).
    'section' => null,
    // Abas da seção (x-section-tabs); use o slot "tabs" para abas próprias.
    'tabsSection' => null,
    'tabsActive' => null,
])

@php
    // Cabeçalho padrão das telas (mesmo visual de Produtos). No celular fica compacto:
    // voltar + título + ações só com ícone na mesma linha, e as abas roláveis embaixo.
    $crumbs = [
        'vendas' => ['Vendas', 'bi-cart3', 'sales.index'],
        'clientes' => ['Clientes', 'bi-people', 'clients.index'],
        'categorias' => ['Categorias', 'bi-tags', 'categories.index'],
        'produtos' => ['Produtos', 'bi-box-seam', 'products.index'],
        'financas' => ['Finanças', 'bi-wallet2', 'cashbook.index'],
        'gestao' => ['Gestão', 'bi-kanban', 'dashboard'],
    ];
    $crumb = $section ? ($crumbs[$section] ?? null) : null;
    // Na tela principal da seção a trilha começa no Início (evita "Vendas > Vendas").
    if ($crumb && $crumb[0] === $title) {
        $crumb = ['Início', 'bi-house', 'dashboard'];
    }
    $headerIcon = str_contains($icon, ' ') ? $icon : 'bi ' . $icon;
@endphp

<div {{ $attributes->merge(['class' => "app-ph relative mb-4 sm:mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]"]) }}>
    <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[28px]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_32%)]"></div>
        <div class="absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>
    </div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="app-ph-main flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                @if($backRoute)
                    <a href="{{ $backRoute }}" title="Voltar"
                       class="app-ph-back group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                        <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                    </a>
                @endif

                <div class="app-ph-icon hidden sm:flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
                    <i class="{{ $headerIcon }} text-white text-2xl"></i>
                </div>

                <div class="min-w-0">
                    @if($crumb && \Illuminate\Support\Facades\Route::has($crumb[2]))
                        <nav class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <a href="{{ route($crumb[2]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi {{ $crumb[1] }} mr-1"></i>{{ $crumb[0] }}</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="truncate text-indigo-600 dark:text-indigo-300">{{ $title }}</span>
                        </nav>
                    @endif
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    @if($subtitle)
                        <p class="app-ph-sub mt-0.5 text-sm text-slate-600 dark:text-slate-400">{!! $subtitle !!}</p>
                    @endif
                    @isset($meta)
                        <div class="app-ph-meta mt-1 flex flex-wrap items-center gap-1.5">{{ $meta }}</div>
                    @endisset
                </div>
            </div>

                <div class="app-ph-actions flex flex-wrap items-center gap-2 lg:justify-end">
                    {{ $actions ?? '' }}
                    <x-header-bell />
                </div>
        </div>

        @isset($tabs)
            <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
                <nav class="flex min-w-max items-center gap-1 px-1 pb-1">{{ $tabs }}</nav>
            </div>
        @elseif($tabsSection)
            <x-section-tabs :section="$tabsSection" :active="$tabsActive" />
        @endif
    </div>
</div>
