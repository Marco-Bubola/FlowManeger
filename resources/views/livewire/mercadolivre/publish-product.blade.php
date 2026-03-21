{{-- PUBLISH PRODUCT — FLUXO POR STEPS (1. Produtos | 2. Catálogo | 3. Config) --}}
<div class="publish-product-page min-h-screen flex flex-col mobile-393-base" x-data="{ autoSearched: false }">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publish-product-ultrawide.css') }}">
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
        }

        .product-card-modern {
            cursor: pointer;
            user-select: none;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }

        /* ======================================================
           PUBLISH-PRODUCT: LOADING ANIMATIONS
           ====================================================== */
        @keyframes pp-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pp-orbit {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pp-pulse-glow {

            0%,
            100% {
                transform: scale(0.9);
                opacity: .35;
            }

            50% {
                transform: scale(1.12);
                opacity: .9;
            }
        }

        @keyframes pp-step-in {
            from {
                opacity: 0;
                transform: translateX(-16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pp-shimmer {
            0% {
                background-position: 250% 0;
            }

            100% {
                background-position: -250% 0;
            }
        }

        @keyframes pp-enter {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pp-step-item {
            opacity: 0;
            animation: pp-step-in .45s cubic-bezier(.4, 0, .2, 1) both;
        }

        .pp-skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 250% 100%;
            animation: pp-shimmer 1.6s ease-in-out infinite;
        }

        .dark .pp-skeleton {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 250% 100%;
            animation: pp-shimmer 1.6s ease-in-out infinite;
        }

        .pp-step-enter {
            animation: pp-enter .4s cubic-bezier(.4, 0, .2, 1) both;
        }
    </style>

    {{-- HEADER COM BOTÕES DE NAVEGAÇÃO --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-amber-50/90 to-yellow-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-yellow-900/20 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="relative px-4 sm:px-6 py-4 sm:py-5">
            {{-- Linha 1: Título + Steps --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-3 sm:mb-0">
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="{{ route('mercadolivre.products') }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-amber-50 dark:hover:bg-slate-700 transition-all">
                        <i class="bi bi-arrow-left text-lg sm:text-xl text-amber-600 dark:text-amber-400"></i>
                    </a>
                    <div>
                        <h1 class="text-lg sm:text-2xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-300 dark:to-orange-300 bg-clip-text text-transparent leading-tight">Publicar no Mercado Livre</h1>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                            @if($currentStep === 1) Passo 1: Selecione os produtos
                            @elseif($currentStep === 2) Passo 2: Catálogo ML (opcional)
                            @else Passo 3: Valores e configuração
                            @endif
                        </p>
                    </div>
                </div>
                {{-- Step indicator --}}
                <div class="flex items-center gap-1.5 sm:gap-2 sm:ml-4">
                    @foreach([1 => 'Produtos', 2 => 'Catálogo', 3 => 'Config'] as $step => $label)
                    <div class="flex items-center">
                        <button type="button" wire:click="goToStep({{ $step }})"
                            class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl font-bold text-xs sm:text-sm transition-all
                                           {{ $currentStep === $step ? 'bg-amber-500 text-white shadow-lg' : ($currentStep > $step ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500') }}">
                            <span class="w-5 h-5 sm:w-6 sm:h-6 rounded-full flex items-center justify-center {{ $currentStep >= $step ? 'bg-white/20' : '' }}">
                                @if($currentStep > $step)<i class="bi bi-check-lg text-xs"></i>@else{{ $step }}@endif
                            </span>
                            <span class="hidden sm:inline">{{ $label }}</span>
                        </button>
                        @if($step < 3)<i class="bi bi-chevron-right text-slate-400 text-xs mx-0.5"></i>@endif
                    </div>
                    @endforeach
                </div>
                {{-- Botões de navegação no header --}}
                <div class="flex items-center gap-2 sm:ml-auto flex-wrap">
                    @if($currentStep === 2)
                    <button type="button" wire:click="searchCatalog" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold text-sm hover:shadow-lg transition-all disabled:opacity-50">
                        <i class="bi bi-search" wire:loading.remove wire:target="searchCatalog"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="searchCatalog"></i>
                        <span class="hidden sm:inline">Buscar </span>Catálogo
                    </button>
                    @endif
                    @if($currentStep > 1)
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        <i class="bi bi-arrow-left"></i> <span class="hidden sm:inline">Voltar</span>
                    </button>
                    @endif
                    @if($currentStep < 3)
                        @if($currentStep===1 && $this->hasSelectedProducts())
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm shadow-lg transition-all">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                        @elseif($currentStep === 2)
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm shadow-lg transition-all">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                        @endif
                        @else
                        <button type="submit" form="publish-form"
                            wire:loading.attr="disabled" wire:target="publishProduct"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold text-sm shadow-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="bi bi-rocket-takeoff-fill" wire:loading.remove wire:target="publishProduct"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="publishProduct"></i>
                            <span wire:loading.remove wire:target="publishProduct"><span class="hidden sm:inline">Publicar no </span>ML</span>
                            <span wire:loading wire:target="publishProduct">Publicando...</span>
                        </button>
                        @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         OVERLAY DE LOADING — Step 1→2 / Busca Catálogo
         Aparece quando nextStep() ou searchCatalog() estão em execução
         =================================================================== --}}
    <div wire:loading wire:target="nextStep, searchCatalog"
        class="fixed inset-0 z-[9998] flex flex-col items-center justify-center overflow-hidden py-6 px-4 bg-white/96 dark:bg-slate-900/97 backdrop-blur-md">

        {{-- Background blobs animados --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute w-[28rem] h-[28rem] rounded-full -top-16 left-1/3 -translate-x-1/2"
                style="background:radial-gradient(circle,rgba(245,158,11,.18) 0%,transparent 65%);animation:pp-pulse-glow 3.5s ease-in-out infinite"></div>
            <div class="absolute w-96 h-96 rounded-full bottom-0 right-1/3 translate-x-1/2"
                style="background:radial-gradient(circle,rgba(249,115,22,.14) 0%,transparent 65%);animation:pp-pulse-glow 4.5s ease-in-out infinite;animation-delay:1.5s"></div>
            <div class="absolute w-72 h-72 rounded-full top-1/2 right-8 -translate-y-1/2"
                style="background:radial-gradient(circle,rgba(20,184,166,.10) 0%,transparent 65%);animation:pp-pulse-glow 5s ease-in-out infinite;animation-delay:.8s"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center gap-5 w-full max-w-sm sm:max-w-md">

            {{-- Ícone ML com órbita animada --}}
            <div class="relative" style="width:96px;height:96px" aria-hidden="true">
                {{-- Anel externo pulsando --}}
                <div class="absolute inset-0 rounded-full border-4 border-amber-200 dark:border-amber-800/60 animate-pulse"></div>
                {{-- Arco girando --}}
                <div class="absolute inset-0 rounded-full"
                    style="border:4px solid transparent;border-top-color:#f59e0b;border-right-color:#f97316;animation:pp-spin 1.4s cubic-bezier(.4,0,.6,1) infinite"></div>
                {{-- Wrapper da órbita —  gira, o ponto acompanha --}}
                <div class="absolute inset-0" style="animation:pp-orbit 1.4s linear infinite">
                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-emerald-400"
                        style="box-shadow:0 0 12px 4px rgba(52,211,153,.65)"></div>
                </div>
                {{-- Ícone central --}}
                <div class="absolute rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-2xl"
                    style="inset:16px;box-shadow:0 0 28px rgba(245,158,11,.5)">
                    <i class="bi bi-shop-window text-2xl text-white"></i>
                </div>
            </div>

            {{-- Título --}}
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent leading-tight">
                    Buscando no Catálogo ML
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5">Aguarde, isso pode levar alguns segundos...</p>
            </div>

            {{-- Steps animados com fade-in sequencial --}}
            <div class="flex flex-col gap-2 w-full">
                <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl
                            bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50"
                    style="animation-delay:0s">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-cpu-fill text-amber-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-amber-700 dark:text-amber-300 flex-1">Analisando produto...</span>
                    <div class="flex gap-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:0s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.15s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.3s"></span>
                    </div>
                </div>
                <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl
                            bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/50"
                    style="animation-delay:.5s">
                    <div class="w-7 h-7 rounded-lg bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-tags-fill text-teal-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-teal-700 dark:text-teal-300 flex-1">Prevendo categoria ML...</span>
                    <div class="flex gap-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-bounce" style="animation-delay:.5s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-bounce" style="animation-delay:.65s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-bounce" style="animation-delay:.8s"></span>
                    </div>
                </div>
                <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl
                            bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/50"
                    style="animation-delay:1s">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-search text-blue-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-300 flex-1">Buscando no catálogo...</span>
                    <div class="flex gap-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1.15s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1.3s"></span>
                    </div>
                </div>
            </div>

            {{-- Skeleton de pé-visualização dos cards do catálogo --}}
            <div class="w-full">
                <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 dark:text-slate-500 mb-2.5">Pré-visualização dos resultados</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @for($__i = 0; $__i < 4; $__i++)
                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-800/80"
                        style="animation:pp-enter .4s cubic-bezier(.4,0,.2,1) both;animation-delay:{{ (.10 + $__i * .07) }}s">
                        <div class="pp-skeleton" style="height:68px"></div>
                        <div class="p-2 space-y-1.5">
                            <div class="h-2.5 rounded pp-skeleton w-full"></div>
                            <div class="h-2 rounded pp-skeleton w-4/5"></div>
                            <div class="h-3 rounded pp-skeleton w-1/2"></div>
                        </div>
                </div>
                @endfor
            </div>
        </div>

    </div>
</div>{{-- end overlay --}}

{{-- ===================================================================
     OVERLAY DE LOADING — Publicação no ML
     Aparece quando publishProduct() está em execução
     =================================================================== --}}
<div wire:loading wire:target="publishProduct"
    class="fixed inset-0 z-[9998] flex flex-col items-center justify-center overflow-hidden py-6 px-4 bg-white/96 dark:bg-slate-900/97 backdrop-blur-md">

    {{-- Background blobs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute w-[28rem] h-[28rem] rounded-full -top-16 left-1/3 -translate-x-1/2"
            style="background:radial-gradient(circle,rgba(16,185,129,.18) 0%,transparent 65%);animation:pp-pulse-glow 3.5s ease-in-out infinite"></div>
        <div class="absolute w-96 h-96 rounded-full bottom-0 right-1/3 translate-x-1/2"
            style="background:radial-gradient(circle,rgba(52,211,153,.14) 0%,transparent 65%);animation:pp-pulse-glow 4.5s ease-in-out infinite;animation-delay:1.5s"></div>
    </div>

    <div class="relative z-10 flex flex-col items-center gap-5 w-full max-w-sm sm:max-w-md">
        {{-- Ícone com spin --}}
        <div class="relative" style="width:96px;height:96px" aria-hidden="true">
            <div class="absolute inset-0 rounded-full border-4 border-emerald-200 dark:border-emerald-800/60 animate-pulse"></div>
            <div class="absolute inset-0 rounded-full"
                style="border:4px solid transparent;border-top-color:#10b981;border-right-color:#34d399;animation:pp-spin 1.4s cubic-bezier(.4,0,.6,1) infinite"></div>
            <div class="absolute inset-0" style="animation:pp-orbit 1.4s linear infinite">
                <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-amber-400"
                    style="box-shadow:0 0 12px 4px rgba(245,158,11,.65)"></div>
            </div>
            <div class="absolute rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-2xl"
                style="inset:16px;box-shadow:0 0 28px rgba(16,185,129,.5)">
                <i class="bi bi-rocket-takeoff-fill text-2xl text-white"></i>
            </div>
        </div>

        {{-- Título --}}
        <div class="text-center">
            <h2 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-400 bg-clip-text text-transparent leading-tight">
                Publicando no Mercado Livre
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5">Aguarde, isso pode levar alguns segundos...</p>
        </div>

        {{-- Steps animados --}}
        <div class="flex flex-col gap-2 w-full">
            <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50" style="animation-delay:0s">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-shield-check text-emerald-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 flex-1">Validando dados...</span>
                <div class="flex gap-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:.15s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:.3s"></span>
                </div>
            </div>
            <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50" style="animation-delay:.5s">
                <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-cloud-upload text-amber-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-amber-700 dark:text-amber-300 flex-1">Enviando para API do ML...</span>
                <div class="flex gap-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.5s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.65s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.8s"></span>
                </div>
            </div>
            <div class="pp-step-item flex items-center gap-3 px-4 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/50" style="animation-delay:1s">
                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-check2-all text-blue-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300 flex-1">Criando anúncio...</span>
                <div class="flex gap-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1.15s"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-bounce" style="animation-delay:1.3s"></span>
                </div>
            </div>
        </div>
    </div>
</div>{{-- end publish overlay --}}

{{-- STEP 1: SELEÇÃO DE PRODUTOS --}}
@if($currentStep === 1)
<div class="pp-step1-layout flex-1 flex flex-col xl:flex-row gap-4 min-h-0">

    {{-- ── COLUNA PRINCIPAL: grade de produtos ── --}}
    <div class="pp-s1-main flex-1 min-w-0 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-700 flex flex-col overflow-hidden shadow-sm">

        {{-- Cabeçalho com busca e filtros --}}
        <div class="pp-s1-header px-4 sm:px-5 pt-4 sm:pt-5 pb-3 border-b border-slate-100 dark:border-zinc-700/60">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/30">
                    <i class="bi bi-box-fill text-white text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight">Selecionar Produtos</h2>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-none mt-0.5">Somente produtos prontos (EAN + imagem + preço)</p>
                </div>
                {{-- Badge contador --}}
                @if(!empty($selectedProducts))
                <span class="ml-auto flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                    <i class="bi bi-check-circle-fill text-[10px]"></i> {{ count($selectedProducts) }} selecionado(s)
                </span>
                @endif
            </div>
            {{-- Barra de busca + filtro --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <div class="flex-1 relative">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                    <input type="text" wire:model.live.debounce.300ms="searchTerm"
                        placeholder="Nome, código ou EAN..."
                        class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-sm border border-slate-200 dark:border-zinc-600 rounded-xl bg-slate-50 dark:bg-zinc-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 transition-all">
                </div>
                <select wire:model.live="selectedCategory"
                    class="pp-cat-select px-3 py-2 sm:py-2.5 text-sm border border-slate-200 dark:border-zinc-600 rounded-xl bg-slate-50 dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 transition-all min-w-0 sm:w-44">
                    <option value="">Todas categorias</option>
                    @foreach($this->categories as $category)
                    <option value="{{ $category->id_category ?? $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Grade de produtos --}}
        <div class="pp-s1-grid flex-1 overflow-y-auto p-3 sm:p-4">
            @if($this->filteredProducts->isEmpty())
            <div class="flex flex-col items-center justify-center min-h-[220px] gap-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="bi bi-box-seam text-3xl text-slate-400 dark:text-slate-500"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300">Nenhum produto pronto</h3>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Cadastre produtos com nome, preço, estoque, imagem e EAN</p>
                </div>
            </div>
            @else
            {{-- Grid responsivo: 2 → 3 → 4 → 5 colunas conforme a tela --}}
            <div class="pp-products-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3">
                @foreach($this->filteredProducts as $product)
                @php $isSelected = $this->isProductSelected($product->id); @endphp
                <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                    wire:click="toggleProduct({{ $product->id }})" wire:key="p-{{ $product->id }}">
                    {{-- Checkbox overlay --}}
                    <div class="btn-action-group flex gap-2">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all
                                    {{ $isSelected ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-white/80 dark:bg-slate-700/80 border-slate-300 dark:border-slate-500 text-transparent backdrop-blur-sm' }}">
                            @if($isSelected)<i class="bi bi-check text-xs font-bold"></i>@endif
                        </div>
                    </div>
                    {{-- Imagem --}}
                    <div class="product-img-area">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                        <span class="badge-product-code"><i class="bi bi-upc-scan"></i> {{ $product->product_code }}</span>
                        <span class="badge-quantity"><i class="bi bi-stack"></i> {{ $product->stock_quantity }}</span>
                        @if($product->category)
                        <div class="category-icon-wrapper"><i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i></div>
                        @endif
                    </div>
                    {{-- Info do card --}}
                    <div class="card-body">
                        <div class="product-title">{{ ucwords($product->name) }}</div>
                        <div class="price-area mt-2 flex flex-col gap-1">
                            <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}</span>
                            <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Rodapé: total selecionados + botão Continuar --}}
        <div class="pp-s1-footer px-4 sm:px-5 py-3 border-t border-slate-100 dark:border-zinc-700/60 flex items-center justify-between gap-3 bg-slate-50/80 dark:bg-zinc-900/80">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ $this->filteredProducts->count() }} produto(s) disponíve{{ $this->filteredProducts->count() === 1 ? 'l' : 'is' }}
            </span>
            @if($this->hasSelectedProducts())
            <button type="button" wire:click="nextStep"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-500/30 transition-all active:scale-95">
                <i class="bi bi-check2-circle"></i>
                Continuar com {{ count($selectedProducts) }} produto(s)
                <i class="bi bi-arrow-right text-xs"></i>
            </button>
            @endif
        </div>
    </div>

    {{-- ── PAINEL LATERAL: produtos selecionados ── --}}
    <div class="pp-s1-sidebar xl:w-80 2xl:w-96 flex flex-col bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-700 overflow-hidden shadow-sm">

        {{-- Header do painel --}}
        <div class="px-4 py-3 border-b border-slate-100 dark:border-zinc-700/60 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-cart-check-fill text-white text-xs"></i>
                </div>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">Selecionados</span>
                <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-black min-w-[18px] text-center">{{ count($selectedProducts) }}</span>
            </div>
            @if(!empty($selectedProducts))
            <button type="button" wire:click="nextStep"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold hover:from-amber-600 hover:to-orange-600 transition-all shadow-md shadow-amber-500/25">
                Continuar <i class="bi bi-arrow-right"></i>
            </button>
            @endif
        </div>

        {{-- Lista de selecionados --}}
        <div class="flex-1 overflow-y-auto pp-selected-scroll">
            @if(empty($selectedProducts))
            <div class="flex flex-col items-center justify-center gap-3 py-10 px-4 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="bi bi-cart-x text-2xl text-slate-400 dark:text-slate-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Nenhum produto</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Clique em um produto para adicionar</p>
                </div>
                <div class="flex flex-col items-center gap-1 mt-1">
                    <i class="bi bi-arrow-left text-amber-400 text-lg animate-pulse xl:hidden"></i>
                    <i class="bi bi-arrow-up text-amber-400 text-lg animate-pulse hidden xl:block"></i>
                </div>
            </div>
            @else
            <div class="p-3 space-y-2">
                @foreach($selectedProducts as $idx => $p)
                <div class="pp-selected-card group relative rounded-xl border border-amber-200/70 dark:border-amber-800/40 bg-gradient-to-br from-amber-50 to-orange-50/60 dark:from-amber-950/25 dark:to-orange-950/15 overflow-hidden transition-all hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700">
                    <div class="flex gap-2.5 p-2.5">
                        {{-- Imagem --}}
                        <div class="relative flex-shrink-0">
                            <img src="{{ $p['image_url'] ?? '' }}" 
                                 class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover border border-slate-200 dark:border-slate-700"
                                 onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                            {{-- Número de ordem --}}
                            <span class="absolute -top-1 -left-1 w-4 h-4 rounded-full bg-amber-500 text-white text-[9px] font-black flex items-center justify-center">{{ $idx + 1 }}</span>
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white leading-tight line-clamp-2">{{ $p['name'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $p['product_code'] ?? '' }}</p>
                            {{-- Linha de preço e qtd --}}
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                {{-- Custo --}}
                                <div class="flex items-center gap-1 text-[10px] text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-tag text-[9px]"></i>
                                    <span class="font-semibold">Custo: R$ {{ number_format($p['unit_cost'] ?? $p['price_sale'] ?? 0, 2, ',', '.') }}</span>
                                </div>
                                {{-- Estoque --}}
                                <div class="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400">
                                    <i class="bi bi-stack text-[9px]"></i>
                                    <span class="font-semibold">{{ $p['stock_quantity'] ?? 0 }} em estoque</span>
                                </div>
                            </div>
                            {{-- Campo preço de venda --}}
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <span class="text-[10px] text-slate-500 font-medium whitespace-nowrap">R$ venda:</span>
                                <input type="number" wire:model.live="selectedProducts.{{ $idx }}.price_sale" 
                                       step="0.01" min="0"
                                       class="flex-1 min-w-0 py-1 px-2 text-xs rounded-lg border border-amber-300 dark:border-amber-700/60 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all">
                            </div>
                        </div>
                        {{-- Botão remover --}}
                        <button type="button" wire:click="toggleProduct({{ $p['id'] }})"
                            class="self-start flex-shrink-0 w-6 h-6 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-500 dark:text-red-400 flex items-center justify-center transition-all hover:bg-red-200 dark:hover:bg-red-900/60 opacity-0 group-hover:opacity-100"
                            title="Remover">
                            <i class="bi bi-x text-sm font-bold"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Rodapé do painel: resumo --}}
        @if(!empty($selectedProducts))
        <div class="shrink-0 px-4 py-3 border-t border-slate-100 dark:border-zinc-700/60 bg-slate-50/80 dark:bg-zinc-900/80 space-y-1.5">
            @php
                $totalCost = array_sum(array_map(fn($p) => ($p['unit_cost'] ?? $p['price_sale'] ?? 0), $selectedProducts));
                $totalSale = array_sum(array_map(fn($p) => ($p['price_sale'] ?? 0), $selectedProducts));
                $totalQty  = array_sum(array_map(fn($p) => ($p['stock_quantity'] ?? 0), $selectedProducts));
            @endphp
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1"><i class="bi bi-tag text-amber-500"></i> Custo total</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($totalCost, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1"><i class="bi bi-currency-dollar text-emerald-500"></i> Venda total</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($totalSale, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1"><i class="bi bi-stack text-blue-500"></i> Estoque total</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalQty }} un</span>
            </div>
            <button type="button" wire:click="nextStep"
                class="w-full mt-2 inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-500/30 transition-all active:scale-95">
                <i class="bi bi-rocket-takeoff"></i>
                Continuar para Catálogo
                <i class="bi bi-arrow-right text-xs"></i>
            </button>
        </div>
        @endif
    </div>
</div>
@endif

{{-- STEP 2: CATÁLOGO ML --}}
@if($currentStep === 2)
<div class="flex-1 flex flex-col lg:flex-row gap-6 pp-step-enter">
    {{-- Coluna: Resultados (4 por linha, imagens menores) --}}
    @if(!empty($catalogResults))
    <div class="flex-shrink-0 w-full lg:w-96 xl:w-[420px]">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sticky top-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">{{ count($catalogResults) }} resultado(s)</h3>
            <div class="grid grid-cols-2 gap-3 max-h-[60vh] overflow-y-auto">
                @foreach($catalogResults as $catalogProduct)
                @php
                $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                $domainId = $catalogProduct['domain_id'] ?? '';
                $isSelected = $catalogProductId === $cProductId;
                $imgUrl = $this->getCatalogResultImage($catalogProduct);
                $price = $this->getCatalogResultPrice($catalogProduct);
                @endphp
                <div wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $domainId }}')"
                    class="flex flex-col rounded-lg cursor-pointer transition-all border-2 overflow-hidden {{ $isSelected ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300' }}">
                    <div class="h-20 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden">
                        @if($imgUrl)
                        <img src="{{ $imgUrl }}" alt="" class="max-w-full max-h-full object-contain p-1">
                        @else
                        <i class="bi bi-box text-2xl text-slate-400"></i>
                        @endif
                    </div>
                    <div class="p-2 flex-1 min-h-0">
                        <p class="font-semibold text-xs text-slate-900 dark:text-white line-clamp-2">{{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}</p>
                        @if($price)
                        <p class="text-sm font-bold text-teal-600 dark:text-teal-400 mt-1">R$ {{ number_format($price, 2, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Coluna: Detalhes do catálogo + spinner overlay para selectCatalogProduct --}}
    <div class="flex-1 min-w-0 relative">

        {{-- Spinner overlay enquanto selectCatalogProduct processa --}}
        <div wire:loading wire:target="selectCatalogProduct"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl
                        bg-white/93 dark:bg-slate-900/93 backdrop-blur-sm
                        border-2 border-dashed border-amber-200 dark:border-amber-800/50 min-h-[200px]">
            <div class="flex flex-col items-center gap-4 text-center py-8">
                <div class="relative w-14 h-14">
                    <div class="absolute inset-0 rounded-full bg-amber-50 dark:bg-amber-900/20"></div>
                    <div class="absolute inset-0 rounded-full"
                        style="border:3px solid transparent;border-top-color:#f59e0b;border-right-color:#f97316;animation:pp-spin 0.8s linear infinite"></div>
                    <div class="absolute inset-3 flex items-center justify-center">
                        <i class="bi bi-shop-window text-xl text-amber-500"></i>
                    </div>
                </div>
                <div>
                    <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">Carregando produto...</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Buscando dados do catálogo ML</p>
                </div>
            </div>
        </div>

        {{-- Conteúdo: Informações COMPLETAS do catálogo selecionado --}}
        @if($catalogProductId && !empty($catalogProductData))
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            {{-- Título, Preço e link --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border-2 border-teal-200 dark:border-teal-800 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <h3 class="text-xl font-bold text-teal-800 dark:text-teal-300 flex items-center gap-2">
                            <i class="bi bi-patch-check-fill text-2xl"></i> Catálogo Selecionado
                        </h3>
                        <div class="flex items-center gap-3">
                            @if($catalogPrice)
                            <span class="text-2xl font-black text-teal-600 dark:text-teal-400">R$ {{ number_format($catalogPrice, 2, ',', '.') }}</span>
                            @endif
                            <a href="https://www.mercadolivre.com.br/p/{{ $catalogProductId }}" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-teal-500 text-white text-sm font-bold hover:bg-teal-600">
                                <i class="bi bi-box-arrow-up-right"></i> Ver no ML
                            </a>
                            <button type="button" wire:click="clearCatalogProduct"
                                class="text-sm text-red-600 hover:text-red-700 font-medium">
                                <i class="bi bi-x-circle mr-1"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $catalogProductName ?: '—' }}</p>
                </div>
            </div>

            {{-- Galeria --}}
            @if(!empty($catalogPictures))
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-b">
                    <h3 class="text-sm font-bold text-blue-700 dark:text-blue-400"><i class="bi bi-images mr-1"></i> Galeria ({{ count($catalogPictures) }})</h3>
                </div>
                <div class="p-3 flex gap-2 overflow-x-auto">
                    @foreach($catalogPictures as $idx => $pic)
                    @php $picUrl = $pic['secure_url'] ?? $pic['url'] ?? ''; @endphp
                    @if($picUrl)
                    <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                        <img src="{{ $picUrl }}" alt="Foto {{ $idx + 1 }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Descrição --}}
            @if($catalogDescription)
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border-b">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300"><i class="bi bi-file-text mr-1"></i> Descrição</h3>
                </div>
                <div class="p-4 max-h-48 overflow-y-auto">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $catalogDescription }}</p>
                </div>
            </div>
            @endif

            {{-- Atributos --}}
            @if(!empty($catalogAttributes))
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden xl:col-span-2">
                <div class="px-4 py-2 bg-purple-50 dark:bg-purple-900/20 border-b">
                    <h3 class="text-sm font-bold text-purple-700 dark:text-purple-400"><i class="bi bi-sliders mr-1"></i> Atributos ({{ count($catalogAttributes) }})</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        @foreach($catalogAttributes as $attr)
                        @if(!empty($attr['value_id']) || !empty($attr['value_name']))
                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">{{ $attr['name'] }}</p>
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $attr['value_name'] ?: '—' }}</p>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="h-full flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 min-h-[200px]">
            <div class="text-center p-6">
                @if(empty($catalogResults))
                <i class="bi bi-search text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Clique em "Buscar Catálogo" no header para buscar pelo EAN</p>
                @else
                <i class="bi bi-cursor text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Selecione um resultado à esquerda para ver os detalhes</p>
                @endif
            </div>
        </div>
        @endif
    </div>{{-- end right column wrapper --}}
</div>
@endif

{{-- STEP 3: CONFIGURAÇÃO COMPLETA (moderno, ícones, taxas, preço sugerido) --}}
@if($currentStep === 3)
<form id="publish-form" wire:submit.prevent="publishProduct" class="flex-1 pp-step-enter">
    @php
    $basePrice = (float)($publishPrice ?: 0) ?: $this->getTotalProductsPrice();
    $mlFee = match($listingType) { 'gold_special' => 0.16, 'gold_pro' => 0.17, 'gold' => 0.13, default => 0.11 };
    $mlFeeAmount = $basePrice * $mlFee;
    $shippingCost = $freeShipping ? 15.00 : 0;
    $netAmount = $basePrice - $mlFeeAmount - $shippingCost;
    $suggestedPrice = $this->getSuggestedPrice();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Coluna 1: Produtos + Calculadora --}}
        <aside class="space-y-6">
            {{-- Título da Publicação --}}
            <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 overflow-hidden shadow-lg">
                <div class="p-5">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <i class="bi bi-textarea-t text-xl text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">Título que será publicado</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white leading-snug break-words">{{ $this->getFinalTitle() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($catalogProductName)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-xs font-bold text-teal-700 dark:text-teal-300">
                            <i class="bi bi-patch-check-fill"></i> Do Catálogo ML
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400">
                            <i class="bi bi-box"></i> Produto Original
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                    <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                        <i class="bi bi-box-seam text-lg"></i> Produtos ({{ count($selectedProducts) }})
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($selectedProducts as $p)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $p['image_url'] ?? '' }}" class="w-14 h-14 rounded-lg object-cover" onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-slate-900 dark:text-white truncate">{{ $p['name'] }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400">R$ {{ number_format($p['price_sale'] ?? $p['unit_cost'] ?? 0, 2, ',', '.') }} · Est: {{ $p['stock_quantity'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            {{-- Descrição da Publicação --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i class="bi bi-file-text-fill text-lg"></i> Descrição do Anúncio
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">Será enviada ao Mercado Livre após a criação do anúncio</p>
                </div>
                <div class="p-4 space-y-2">
                    @if($catalogDescription)
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-xs font-bold text-teal-700 dark:text-teal-300">
                            <i class="bi bi-patch-check-fill"></i> Do Catálogo ML
                        </span>
                    </div>
                    @elseif($product && $product->description)
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400">
                            <i class="bi bi-box"></i> Do Produto
                        </span>
                    </div>
                    @else
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-xs font-bold text-amber-600 dark:text-amber-400">
                            <i class="bi bi-pencil-fill"></i> Digite abaixo
                        </span>
                    </div>
                    @endif
                    <textarea wire:model="catalogDescription" rows="5"
                        placeholder="Descreva o produto: características, especificações, diferenciais..."
                        class="w-full px-3 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm resize-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"></textarea>
                    <p class="text-[11px] text-slate-400 text-right">
                        {{ mb_strlen($catalogDescription ?: ($product?->description ?? '')) }} caracteres
                    </p>
                </div>
            </div>

        </aside>

        {{-- Coluna 2: Título, Preço, Tipo de Anúncio --}}
        <div class="space-y-6">
            {{-- Calculadora de taxas --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-emerald-200 dark:border-emerald-800">
                    <h4 class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                        <i class="bi bi-calculator-fill text-lg"></i> Resumo de Taxas
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Taxa ML ({{ $mlFee * 100 }}%)</span>
                        <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($mlFeeAmount, 2, ',', '.') }}</span>
                    </div>
                    @if($freeShipping)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Frete grátis</span>
                        <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($shippingCost, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Valor Líquido</span>
                        <span class="text-lg font-black {{ $netAmount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">R$ {{ number_format($netAmount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>


            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                    <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                        <i class="bi bi-currency-dollar text-lg"></i> Preço e Quantidade
                    </h4>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Preço do Anúncio</label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 font-bold">R$</span>
                                <input type="number" wire:model.live="publishPrice" step="0.01" min="0.01"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10 text-slate-900 dark:text-white font-bold text-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-[10px] text-slate-500 uppercase">Soma produtos</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($this->getTotalProductsPrice(), 2, ',', '.') }}</p>
                            </div>
                        </div>
                        @if($catalogPrice)
                        <p class="text-xs text-teal-600 dark:text-teal-400 mt-1"><i class="bi bi-patch-check"></i> Catálogo: R$ {{ number_format($catalogPrice, 2, ',', '.') }}</p>
                        @endif
                    </div>
                    @if($suggestedPrice > 0)
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Preço Sugerido</p>
                                <p class="text-xl font-black text-blue-700 dark:text-blue-300">R$ {{ number_format($suggestedPrice, 2, ',', '.') }}</p>
                            </div>
                            <button type="button" wire:click="applySuggestedPrice"
                                class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold transition-all">
                                <i class="bi bi-check2-square mr-1"></i> Aplicar
                            </button>
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Quantidade</label>
                        <input type="number" wire:model="publishQuantity" min="1"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-bold">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                    <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                        <i class="bi bi-bookmark-star-fill text-lg"></i> Tipo de Anúncio
                    </h4>
                    <p class="text-xs text-slate-500 mt-1">Valores fixos do Mercado Livre (não vêm da API) · Definem taxa e exposição</p>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['gold_special' => ['Clássico', '16%', 'bi-trophy-fill', 'Destaque na busca · Boa visibilidade · Conversões médias'], 'gold_pro' => ['Premium', '17%', 'bi-star-fill', 'Máxima visibilidade · Topo das buscas · Mais vendas'], 'gold' => ['Gold', '13%', 'bi-star', 'Visibilidade intermediária · Bom custo-benefício'], 'free' => ['Grátis', '11%', 'bi-bag', 'Taxa mais baixa · Menos destaque · Básico']] as $key => $data)
                        <label class="cursor-pointer">
                            <div class="p-3 rounded-xl border-2 transition-all {{ $listingType === $key ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 shadow-md' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300' }}">
                                <input type="radio" wire:model.live="listingType" value="{{ $key }}" class="sr-only">
                                <i class="bi {{ $data[2] }} block text-2xl mb-1 {{ $listingType === $key ? 'text-amber-600' : 'text-slate-400' }}"></i>
                                <p class="font-bold text-sm {{ $listingType === $key ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $data[0] }}</p>
                                <p class="text-xs font-semibold {{ $listingType === $key ? 'text-amber-600' : 'text-slate-500' }}">Taxa: {{ $data[1] }}</p>
                                <p class="text-[9px] text-slate-400 mt-1 leading-tight">{{ $data[3] ?? '' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna 3: Envio, Categoria --}}
        <div class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                    <h4 class="font-bold text-teal-700 dark:text-teal-400 flex items-center gap-2">
                        <i class="bi bi-truck text-lg"></i> Envio e Logística
                    </h4>
                    <p class="text-xs text-slate-500 mt-1">📦 Mercado Envios · ⏱️ Prazo 2–5 dias úteis · 📍 Rastreamento incluído</p>
                </div>
                <div class="p-4 space-y-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $freeShipping ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300' }}">
                        <input type="checkbox" wire:model.live="freeShipping" class="w-5 h-5 rounded text-amber-500">
                        <div class="flex-1">
                            <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-truck-front-fill text-teal-500 text-lg"></i> Frete Grátis</p>
                            <p class="text-xs text-slate-500 leading-relaxed">💰 Você paga ~R$ 15,00 por venda<br>⭐ Destaque "FRETE GRÁTIS" na busca<br>📈 Aumenta conversão em até 30%</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $localPickup ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
                        <input type="checkbox" wire:model.live="localPickup" class="w-5 h-5 rounded text-amber-500">
                        <div class="flex-1">
                            <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-shop-window text-blue-500 text-lg"></i> Retirada Local</p>
                            <p class="text-xs text-slate-500 leading-relaxed">💵 Sem custo adicional<br>🏪 Cliente retira no endereço cadastrado<br>⚡ Atendimento mais rápido</p>
                        </div>
                    </label>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2"><i class="bi bi-info-circle mr-1"></i>Informações de Envio</p>
                        <ul class="space-y-1 text-[11px] text-slate-500">
                            <li>✓ Modalidade: <strong>Mercado Envios Full</strong></li>
                            <li>✓ Proteção: <strong>Garantia de entrega</strong></li>
                            <li>✓ Embalagem: <strong>Responsabilidade do vendedor</strong></li>
                            <li>✓ Coleta: <strong>Agendada automaticamente</strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Condição e Garantia --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i class="bi bi-box-seam text-lg"></i> Condição e Garantia
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Condição do produto</label>
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $productCondition === 'new' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="productCondition" value="new" class="sr-only">
                                <i class="bi bi-star-fill text-blue-500"></i>
                                <span class="font-semibold text-sm">Novo</span>
                            </label>
                            <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $productCondition === 'used' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="productCondition" value="used" class="sr-only">
                                <i class="bi bi-box text-amber-500"></i>
                                <span class="font-semibold text-sm">Usado</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Garantia (opcional)</label>
                        <input type="text" wire:model="warranty" placeholder="Ex: 90 dias, 1 ano"
                            class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                    <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                        <i class="bi bi-tag-fill text-lg"></i> Categoria ML
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <input type="text" wire:model.live.debounce.500ms="categorySearch" placeholder="Buscar categoria..."
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                    <select wire:model.live="mlCategoryId"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium">
                        <option value="">Selecione</option>
                        @foreach($mlCategories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


        </div>
    </div>
</form>
@endif
</div>