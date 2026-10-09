@props([
    'tabsSection' => null,
    'tabsActive' => null,
    'title' => 'Nova Venda',
    'description' => null,
    // Algumas telas passam "subtitle"; vale como sinônimo de "description".
    'subtitle' => null,
    'icon' => 'bi-plus-circle',
    'iconColor' => null,
    'backRoute' => null,
    'currentStep' => 1,
    'steps' => []
])

@php
    $headerDescription = $description ?? $subtitle;
    $headerIcon = str_contains($icon, ' ') ? $icon : 'bi ' . $icon;
    $headerIconGradients = [
        'blue' => 'from-blue-500 via-sky-500 to-cyan-500 shadow-blue-500/20',
        'orange' => 'from-orange-500 via-amber-500 to-yellow-500 shadow-orange-500/20',
        'green' => 'from-emerald-500 via-green-500 to-teal-500 shadow-emerald-500/20',
        'purple' => 'from-purple-500 via-fuchsia-500 to-pink-500 shadow-purple-500/20',
        'red' => 'from-rose-500 via-red-500 to-orange-500 shadow-rose-500/20',
    ];
    // Mesmo gradiente dos outros cabeçalhos (Clientes, Produtos...) para todas as telas.
    $headerIconGradient = 'from-indigo-500 via-purple-500 to-pink-500 shadow-indigo-500/20';
    $sectionCrumbs = [
        'vendas' => ['Vendas', 'bi-cart3', 'sales.index'],
        'categorias' => ['Categorias', 'bi-tags', 'categories.index'],
        'clientes' => ['Clientes', 'bi-people', 'clients.index'],
    ];
    $sectionCrumb = $tabsSection ? ($sectionCrumbs[$tabsSection] ?? null) : null;
@endphp

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="sales-create-header relative overflow-hidden border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.92),rgba(238,242,255,0.88),rgba(224,231,255,0.92))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl rounded-[28px] shadow-[0_24px_80px_rgba(15,23,42,0.16)]">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_30%)] dark:bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.24),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.16),transparent_30%)]"></div>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/70 to-transparent dark:via-slate-400/30"></div>

    <!-- Background decorativo -->
    <div class="absolute -top-10 right-8 h-32 w-32 rounded-full bg-indigo-400/20 blur-2xl"></div>
    <div class="absolute -bottom-8 left-0 h-28 w-28 rounded-full bg-emerald-400/15 blur-2xl"></div>

    <div class="sales-create-header-inner relative px-3 sm:px-6 py-4">
        <div class="sales-create-header-main flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
                @if($backRoute)
                <!-- Botão voltar compacto -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 transition-all duration-200 shadow-sm border border-slate-200/70 dark:border-slate-700/70 backdrop-blur-sm shrink-0">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform duration-150"></i>
                    <div class="absolute inset-0 rounded-xl bg-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                @endif

                <!-- Ícone principal e título (compacto) -->
                <div class="relative flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br {{ $headerIconGradient }} shadow-lg shrink-0">
                    <i class="{{ $headerIcon }} text-white text-2xl"></i>
                    <div class="absolute inset-[1px] rounded-2xl border border-white/25"></div>
                </div>

                <div class="space-y-1 min-w-0">
                    @if($sectionCrumb)
                        <nav class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <a href="{{ route($sectionCrumb[2]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi {{ $sectionCrumb[1] }} mr-1"></i>{{ $sectionCrumb[0] }}</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="truncate text-indigo-600 dark:text-indigo-300">{{ $title }}</span>
                        </nav>
                    @elseif(isset($breadcrumb))
                        <div class="hidden sm:block">{{ $breadcrumb }}</div>
                    @endif
                    <h1 class="sales-create-header-title text-lg sm:text-2xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent truncate">
                        {{ $title }}
                    </h1>
@if($headerDescription)
                    <p class="sales-create-header-subtitle mt-0.5 text-sm text-slate-600 dark:text-slate-400 line-clamp-2 sm:line-clamp-none">{!! $headerDescription !!}</p>
                    @endif
                    @isset($meta)
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">{{ $meta }}</div>
                    @endisset
                </div>
            </div>

            <div class="sales-create-header-actions flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-3 w-full lg:w-auto">
                {{-- Slot de ações (botões) passado pelo componente pai --}}
                {!! $actions ?? '' !!}

            </div>
        </div>
    </div>
    @if($tabsSection || count($steps) > 0)
        <div class="relative px-4 sm:px-6 pb-3 -mt-2 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            @if($tabsSection)
                <x-section-tabs :section="$tabsSection" :active="$tabsActive" class="!mt-0 min-w-0" />
            @endif
            @if(count($steps) > 0)
                {{-- Etapas do fluxo em pílulas compactas (mesma altura das abas) --}}
                <div class="hidden sm:flex items-center gap-1.5 shrink-0">
                    @foreach($steps as $index => $step)
                        @php $stepNumber = $index + 1; @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-xs font-semibold transition"
                              :class="$wire.currentStep === {{ $stepNumber }}
                                    ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-200 dark:ring-indigo-500/30'
                                    : ($wire.currentStep > {{ $stepNumber }} ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400')">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold"
                                  :class="$wire.currentStep === {{ $stepNumber }} ? 'bg-gradient-to-br from-indigo-600 to-purple-600 text-white' : ($wire.currentStep > {{ $stepNumber }} ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300')">
                                <span x-show="$wire.currentStep <= {{ $stepNumber }}">{{ $stepNumber }}</span>
                                <i class="bi bi-check-lg" x-show="$wire.currentStep > {{ $stepNumber }}"></i>
                            </span>
                            {{ $step['title'] }}
                        </span>
                        @if(!$loop->last)
                            <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
