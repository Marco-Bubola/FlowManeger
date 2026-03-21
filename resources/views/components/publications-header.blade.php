@props([
    'title' => 'Publicações ML',
    'description' => '',
    'totalPublications' => 0,
    'totalActive' => 0,
    'totalKits' => 0,
    'totalErrors' => 0,
    'totalOnlyOnMl' => 0,
    'showQuickActions' => true
])

{{-- Header Moderno — Publicações ML (Glassmorphism + Amber/Yellow theme) --}}
<div class="products-index-header relative overflow-hidden bg-gradient-to-r from-white/80 via-yellow-50/90 to-amber-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">

    {{-- Brilho animado --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    {{-- Blobs decorativos --}}
    <div class="absolute top-0 right-0 w-44 h-44 bg-gradient-to-br from-yellow-400/25 via-amber-400/20 to-orange-400/15 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-36 h-36 bg-gradient-to-tr from-amber-400/15 via-orange-400/10 to-yellow-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>
    <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-gradient-to-br from-yellow-300/10 to-amber-300/10 rounded-full transform -translate-y-1/2"></div>

    <div class="products-index-header-inner relative px-8 py-6">
        <div class="products-index-header-layout flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

            {{-- Esquerda: Ícone + Título + Estatísticas --}}
            <div class="products-index-header-left flex items-center gap-6">

                {{-- Ícone principal ML --}}
                <div class="products-index-header-icon relative flex-shrink-0 flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-2xl shadow-xl shadow-amber-500/30">
                    <i class="bi bi-list-check text-white text-3xl"></i>
                    {{-- Brilho interno --}}
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/25 to-transparent opacity-60"></div>
                    {{-- Marca ML no canto --}}
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-br from-yellow-300 to-amber-400 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center shadow-sm">
                        <span class="text-[7px] font-black text-amber-900">ML</span>
                    </div>
                </div>

                <div class="products-index-header-content space-y-2 min-w-0">

                    {{-- Breadcrumb slot --}}
                    @isset($breadcrumb)
                        {{ $breadcrumb }}
                    @endisset

                    {{-- Título com gradiente amber --}}
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700 dark:from-amber-300 dark:via-yellow-300 dark:to-orange-300 bg-clip-text text-transparent leading-tight">
                        {{ $title }}
                    </h1>

                    {{-- Estatísticas modernas --}}
                    <div class="products-index-header-stats flex items-center gap-3 flex-wrap">

                        {{-- Total publicações --}}
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/70 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/30 dark:border-slate-600/50 shadow-sm">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-400 to-amber-600 rounded-lg shadow-sm">
                                <i class="bi bi-megaphone text-white text-sm"></i>
                            </div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalPublications }}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm">publicações</span>
                        </div>

                        {{-- Ativas --}}
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/70 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/30 dark:border-slate-600/50 shadow-sm">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-lg shadow-sm">
                                <i class="bi bi-check-circle text-white text-sm"></i>
                            </div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalActive }}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm">ativas</span>
                        </div>

                        {{-- Kits (condicional) --}}
                        @if($totalKits > 0)
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/70 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/30 dark:border-slate-600/50 shadow-sm">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg shadow-sm">
                                <i class="bi bi-boxes text-white text-sm"></i>
                            </div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalKits }}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm">kits</span>
                        </div>
                        @endif

                        {{-- Erros (condicional, destaque em vermelho) --}}
                        @if($totalErrors > 0)
                        <div class="flex items-center gap-2 px-4 py-2 bg-red-50/80 dark:bg-red-950/40 backdrop-blur-sm rounded-xl border border-red-200/60 dark:border-red-700/50 shadow-sm">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-red-400 to-red-600 rounded-lg shadow-sm">
                                <i class="bi bi-exclamation-triangle text-white text-sm"></i>
                            </div>
                            <span class="font-bold text-red-600 dark:text-red-400">{{ $totalErrors }}</span>
                            <span class="text-red-500 dark:text-red-500 text-sm">erros</span>
                        </div>
                        @endif

                        {{-- Só no ML (condicional) --}}
                        @if($totalOnlyOnMl > 0)
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/70 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/30 dark:border-slate-600/50 shadow-sm">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg shadow-sm">
                                <i class="bi bi-cloud-download text-white text-sm"></i>
                            </div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalOnlyOnMl }}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm">só no ML</span>
                        </div>
                        @endif

                    </div>

                    @if($description)
                    <p class="text-slate-600 dark:text-slate-400 font-medium text-sm">{{ $description }}</p>
                    @endif

                </div>
            </div>

            {{-- Direita: Slot de controles --}}
            @php
                $hasSlot = isset($slot) && trim($slot) !== '';
            @endphp

            @if($hasSlot)
            <div class="products-index-header-slot w-full lg:flex-1 flex items-start lg:items-center justify-start lg:justify-end">
                {{ $slot }}
            </div>
            @endif

        </div>
    </div>
</div>
