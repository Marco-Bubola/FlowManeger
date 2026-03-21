<div class="w-full publication-show-page mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-publication-ultrawide.css') }}">

    <style>
        /* ═══ LAYOUT PLANTA-BAIXA ═══ */
        .sp-zone-info      { grid-area: info; }
        .sp-zone-detalhes  { grid-area: details; }
        .sp-zone-sidebar   { grid-area: sidebar; }
        .sp-zone-produtos  { grid-area: products; }
        .sp-zone-imagens   { grid-area: images; }
        .sp-zone-atributos { grid-area: attrs; }
        .sp-zone-historico { grid-area: history; }

        .sp-grid {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-areas:
                "info"
                "details"
                "sidebar"
                "products"
                "images"
                "attrs"
                "history";
            gap: clamp(0.5rem, 1vw, 0.75rem);
        }
        @media (min-width: 768px) {
            .sp-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-areas:
                    "info details"
                    "sidebar sidebar"
                    "products products"
                    "images attrs"
                    "history history";
            }
        }
        @media (min-width: 1280px) {
            .sp-grid {
                grid-template-columns: 2fr 1.2fr 1fr;
                grid-template-areas:
                    "info details sidebar"
                    "products products sidebar"
                    "images attrs attrs"
                    "history history history";
            }
        }

        /* ═══ CARD BASE (cores do app) ═══ */
        .sp-card {
            background: var(--card-bg, #e6e6fa);
            border: 2.5px solid var(--card-border, #b39ddb);
            border-radius: 1.5em;
            box-shadow: 0 4px 18px var(--card-shadow, rgba(149,117,205,0.13));
            overflow: hidden;
            transition: box-shadow 0.18s, border-color 0.18s, transform 0.18s;
            position: relative;
        }
        .sp-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1.5em;
            pointer-events: none;
            box-shadow: 0 0 0 3px var(--card-accent, #ffe0b2) inset;
            z-index: 1;
        }
        .sp-card:hover {
            box-shadow: 0 8px 32px var(--shadow-strong, rgba(81,45,168,0.18));
            border-color: var(--primary, #9575cd);
            transform: translateY(-1px);
        }
        .dark .sp-card {
            background: linear-gradient(135deg, rgba(30,20,50,0.95) 0%, rgba(40,25,65,0.9) 100%);
            border-color: rgba(149,117,205,0.35);
        }
        .dark .sp-card::before { box-shadow: 0 0 0 2px rgba(255,224,178,0.08) inset; }
        .dark .sp-card:hover { border-color: var(--primary, #9575cd); }

        .sp-card-head {
            padding: clamp(0.4rem, 0.8vw, 0.6rem) clamp(0.6rem, 1vw, 0.85rem);
            border-bottom: 2px solid var(--card-accent, #ffe0b2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 2;
        }
        .dark .sp-card-head { border-color: rgba(255,224,178,0.12); }

        .sp-card-body { padding: clamp(0.5rem, 1vw, 0.85rem); position: relative; z-index: 2; }

        .sp-icon {
            width: clamp(1.6rem, 2.5vw, 2rem);
            height: clamp(1.6rem, 2.5vw, 2rem);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: clamp(0.65rem, 1vw, 0.8rem);
            flex-shrink: 0;
        }
        .sp-title {
            font-family: 'Poppins', 'Montserrat', sans-serif;
            font-size: clamp(0.72rem, 1.1vw, 0.88rem);
            font-weight: 800;
            background: linear-gradient(92deg, #f8bbd0 0%, #9575cd 48%, #512da8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dark .sp-title {
            background: linear-gradient(92deg, #f8bbd0 0%, #d1c4e9 48%, #9575cd 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        /* ═══ BADGES ═══ */
        .sp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.5rem;
            border-radius: 1em;
            font-size: clamp(0.55rem, 0.8vw, 0.68rem);
            font-weight: 700;
            white-space: nowrap;
            border: 1.5px solid transparent;
        }

        /* ═══ INFO ROWS ═══ */
        .sp-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: clamp(0.6rem, 0.85vw, 0.72rem);
            border-bottom: 1px dashed rgba(179,157,219,0.2);
        }
        .sp-info-row:last-child { border-bottom: none; }
        .sp-info-label { color: var(--primary, #9575cd); font-weight: 600; }
        .dark .sp-info-label { color: #d1c4e9; }
        .sp-info-value { font-weight: 700; color: var(--gray-700, #424242); text-align: right; max-width: 60%; }
        .dark .sp-info-value { color: #e2e8f0; }

        /* ═══ STAT CARDS ═══ */
        .sp-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(0.4rem, 0.8vw, 0.6rem);
            border-radius: 1rem;
            border: 2px solid var(--card-accent, #ffe0b2);
            background: var(--gray-100, #f3f3f7);
            text-align: center;
            transition: all 0.15s;
        }
        .sp-stat:hover { border-color: var(--primary, #9575cd); transform: translateY(-1px); }
        .dark .sp-stat { background: rgba(20,15,35,0.5); border-color: rgba(255,224,178,0.12); }
        .sp-stat-value {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(1rem, 1.8vw, 1.5rem);
            font-weight: 900;
            line-height: 1.1;
        }
        .sp-stat-label {
            font-size: clamp(0.5rem, 0.7vw, 0.6rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--primary, #9575cd);
            margin-top: 0.1rem;
        }
        .dark .sp-stat-label { color: #d1c4e9; }

        /* ═══ SCROLLBAR ═══ */
        .sp-scroll::-webkit-scrollbar { width: 4px; }
        .sp-scroll::-webkit-scrollbar-track { background: transparent; }
        .sp-scroll::-webkit-scrollbar-thumb { background: var(--primary-light, #d1c4e9); border-radius: 10px; }

        /* ═══ ACTION BUTTONS ═══ */
        .sp-action-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: clamp(0.3rem, 0.6vw, 0.45rem) clamp(0.5rem, 0.8vw, 0.65rem);
            border-radius: 0.65rem;
            font-size: clamp(0.6rem, 0.85vw, 0.72rem);
            font-weight: 700;
            transition: all 0.15s;
            border: 2px solid transparent;
        }

        /* ═══ ATTRIBUTE ITEMS ═══ */
        .sp-attr-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: clamp(0.25rem, 0.5vw, 0.35rem) clamp(0.4rem, 0.7vw, 0.55rem);
            border-radius: 0.5rem;
            background: var(--gray-100, #f3f3f7);
            border: 1.5px solid var(--card-accent, #ffe0b2);
            font-size: clamp(0.58rem, 0.8vw, 0.68rem);
        }
        .dark .sp-attr-item { background: rgba(20,15,35,0.5); border-color: rgba(255,224,178,0.1); }

        /* ═══ DESCRIPTION ═══ */
        .sp-description {
            font-size: clamp(0.65rem, 0.9vw, 0.78rem);
            line-height: 1.5;
            color: var(--gray-700, #424242);
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 200px;
            overflow-y: auto;
        }
        .dark .sp-description { color: #e2e8f0; }

        /* ═══ HISTORY LOG ═══ */
        .sp-log-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: clamp(0.25rem, 0.5vw, 0.4rem) clamp(0.4rem, 0.7vw, 0.55rem);
            border-radius: 0.5rem;
            font-size: clamp(0.55rem, 0.8vw, 0.68rem);
            border-left: 3px solid;
            transition: all 0.12s;
        }
        .sp-log-item:hover { transform: translateX(2px); }

        /* ═══ PRODUCT CARD OVERRIDES ═══ */
        .sp-products-grid .product-card-modern { min-height: auto; }
        .sp-products-grid .product-card-modern .product-img-area {
            min-height: clamp(100px, 12vw, 180px);
            height: clamp(100px, 12vw, 180px);
        }
        .sp-products-grid .product-card-modern .card-body {
            padding: clamp(1.8rem, 3vw, 2.5rem) clamp(0.5rem, 0.8vw, 0.7rem) clamp(0.5rem, 0.8vw, 0.7rem);
        }
        .sp-products-grid .product-card-modern .product-title { font-size: clamp(0.65rem, 0.9vw, 0.8rem); }
        .sp-products-grid .product-card-modern .category-icon-wrapper {
            width: clamp(36px, 4vw, 48px);
            height: clamp(36px, 4vw, 48px);
            bottom: clamp(-18px, -2vw, -24px);
        }
        .sp-products-grid .product-card-modern .category-icon { font-size: clamp(1.2rem, 2vw, 1.6rem); }
        .sp-products-grid .product-card-modern .badge-product-code { font-size: clamp(0.55rem, 0.75vw, 0.68rem); padding: 0.1em 0.5em; }
        .sp-products-grid .product-card-modern .badge-quantity { font-size: clamp(0.5rem, 0.7vw, 0.62rem); padding: 0.08em 0.4em; }
        .sp-products-grid .product-card-modern .badge-price,
        .sp-products-grid .product-card-modern .badge-price-sale { font-size: clamp(0.55rem, 0.75vw, 0.68rem); padding: 0.12em 0.7em; }

        /* ═══ OVERLAY QTD VENDA ═══ */
        .sp-qty-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(30,20,50,0.92) 0%, rgba(30,20,50,0.6) 60%, transparent 100%);
            padding: clamp(0.35rem, 0.6vw, 0.5rem);
            z-index: 25;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border-radius: 0 0 1.5em 1.5em;
        }

        /* ═══ IMAGE GRID ═══ */
        .sp-img-thumb {
            aspect-ratio: 1;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 2px solid var(--card-border, #b39ddb);
            cursor: pointer;
            transition: all 0.18s;
            position: relative;
        }
        .sp-img-thumb:hover {
            border-color: var(--primary, #9575cd);
            box-shadow: 0 4px 16px var(--shadow, rgba(149,117,205,0.15));
            transform: scale(1.03);
        }
        .sp-img-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .sp-img-thumb:hover img { transform: scale(1.08); }
        .sp-img-thumb .sp-img-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .sp-img-thumb:hover .sp-img-overlay { background: rgba(81,45,168,0.2); }
        .sp-img-thumb .sp-img-overlay i { color: #fff; font-size: 1.2rem; opacity: 0; transition: opacity 0.2s; }
        .sp-img-thumb:hover .sp-img-overlay i { opacity: 1; }
    </style>

    <div class="px-1.5 sm:px-3 lg:px-4 py-2 sm:py-3 space-y-2 sm:space-y-3">

        {{-- ═════════════════════════════════════════════════════════ --}}
        {{-- HEADER ML                                                --}}
        {{-- ═════════════════════════════════════════════════════════ --}}
        <div class="products-index-header relative overflow-hidden bg-gradient-to-r from-white/80 via-yellow-50/90 to-amber-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
            <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-yellow-400/25 via-amber-400/20 to-orange-400/15 rounded-full transform translate-x-10 -translate-y-10"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-gradient-to-tr from-amber-400/15 via-orange-400/10 to-yellow-400/10 rounded-full transform -translate-x-6 translate-y-6"></div>

            <div class="relative px-3 sm:px-4 py-3 sm:py-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    {{-- Esquerda --}}
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <a href="{{ route('mercadolivre.publications') }}"
                            class="group inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-white to-amber-50 dark:from-slate-800 dark:to-slate-700 hover:from-amber-50 hover:to-yellow-100 transition-all shadow-sm border border-white/40 dark:border-slate-600/40 shrink-0">
                            <i class="bi bi-arrow-left text-base text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform"></i>
                        </a>
                        <div class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-xl shadow-lg shadow-amber-500/25 shrink-0">
                            <i class="bi bi-eye-fill text-white text-lg"></i>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-br from-yellow-300 to-amber-400 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center shadow-sm">
                                <span class="text-[5px] font-black text-amber-900">ML</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1 text-[10px] text-slate-500 dark:text-slate-400">
                                <a href="{{ route('mercadolivre.publications') }}" class="hover:text-amber-600 transition-colors">Publicações</a>
                                <i class="bi bi-chevron-right text-[8px]"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-semibold">Detalhes</span>
                            </div>
                            <h1 class="text-sm sm:text-lg lg:text-xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700 dark:from-amber-300 dark:via-yellow-300 dark:to-orange-300 bg-clip-text text-transparent leading-tight truncate">
                                {{ $publication->title }}
                            </h1>
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if($publication->ml_item_id)
                        <span class="sp-badge bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 font-mono border-slate-300 dark:border-slate-600">
                            <i class="bi bi-link-45deg"></i> {{ $publication->ml_item_id }}
                        </span>
                        @endif
                        @php
                            $statusCfg = match($publication->status) {
                                'active' => ['cls' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700', 'icon' => 'bi-check-circle-fill', 'text' => 'Ativo'],
                                'paused' => ['cls' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700', 'icon' => 'bi-pause-circle-fill', 'text' => 'Pausado'],
                                'closed' => ['cls' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-300 dark:border-red-700', 'icon' => 'bi-x-circle-fill', 'text' => 'Fechado'],
                                'under_review' => ['cls' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-700', 'icon' => 'bi-search', 'text' => 'Em Revisão'],
                                default => ['cls' => 'bg-slate-100 dark:bg-slate-700/40 text-slate-600 dark:text-slate-400 border-slate-300 dark:border-slate-600', 'icon' => 'bi-question-circle', 'text' => ucfirst($publication->status ?? 'Desconhecido')],
                            };
                        @endphp
                        <span class="sp-badge {{ $statusCfg['cls'] }}"><i class="bi {{ $statusCfg['icon'] }}"></i> {{ $statusCfg['text'] }}</span>
                        <span class="sp-badge bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-700">
                            <i class="bi {{ $publication->publication_type === 'kit' ? 'bi-boxes' : 'bi-box' }}"></i>
                            {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                        </span>
                        @php
                            $syncCfg = match($publication->sync_status) {
                                'synced' => ['cls' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700', 'icon' => 'bi-cloud-check', 'text' => 'Sincronizado'],
                                'pending' => ['cls' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700', 'icon' => 'bi-cloud-arrow-up', 'text' => 'Pendente'],
                                'error' => ['cls' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-300 dark:border-red-700', 'icon' => 'bi-cloud-slash', 'text' => 'Erro'],
                                default => ['cls' => 'bg-slate-100 dark:bg-slate-700/40 text-slate-600 dark:text-slate-400 border-slate-300 dark:border-slate-600', 'icon' => 'bi-cloud', 'text' => 'Desconhecido'],
                            };
                        @endphp
                        <span class="sp-badge {{ $syncCfg['cls'] }}"><i class="bi {{ $syncCfg['icon'] }}"></i> {{ $syncCfg['text'] }}</span>
                    </div>

                    {{-- Ações --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-600 hover:to-indigo-600 text-white font-semibold rounded-lg transition-all shadow-md text-xs whitespace-nowrap">
                            <i class="bi bi-pencil-square"></i>
                            <span class="hidden sm:inline">Editar</span>
                        </a>
                        <button wire:click="syncToMercadoLivre" wire:loading.attr="disabled" wire:target="syncToMercadoLivre"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-lg transition-all shadow-md text-xs whitespace-nowrap">
                            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin" wire:target="syncToMercadoLivre"></i>
                            <span class="hidden sm:inline" wire:loading.remove wire:target="syncToMercadoLivre">Sincronizar</span>
                            <span class="hidden sm:inline" wire:loading wire:target="syncToMercadoLivre">Sincronizando...</span>
                        </button>
                        @if($publication->ml_permalink)
                        <a href="{{ $publication->ml_permalink }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 text-slate-900 font-bold rounded-lg shadow-md transition-all text-xs whitespace-nowrap">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span class="hidden sm:inline">Ver no ML</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats compactos --}}
            <div class="relative grid grid-cols-3 sm:grid-cols-5 gap-2 px-3 sm:px-4 pb-3 sm:pb-4">
                <div class="sp-stat" style="border-color: var(--card-accent2, #f8bbd0);">
                    <span class="sp-stat-value" style="color: var(--primary-dark, #512da8);">R$ {{ number_format($publication->price, 2, ',', '.') }}</span>
                    <span class="sp-stat-label"><i class="bi bi-tag-fill mr-0.5"></i>Preço</span>
                </div>
                <div class="sp-stat">
                    <span class="sp-stat-value" style="color: var(--primary-dark, #512da8);">{{ $stats['total_stock_available'] }}</span>
                    <span class="sp-stat-label"><i class="bi bi-stack mr-0.5"></i>Disponível</span>
                </div>
                <div class="sp-stat">
                    <span class="sp-stat-value" style="color: var(--primary-dark, #512da8);">{{ $stats['total_products'] }}</span>
                    <span class="sp-stat-label"><i class="bi bi-box-seam mr-0.5"></i>Produtos</span>
                </div>
                <div class="sp-stat hidden sm:flex">
                    <span class="sp-stat-value text-emerald-600 dark:text-emerald-400">{{ $stats['total_sales'] }}</span>
                    <span class="sp-stat-label"><i class="bi bi-cart-check mr-0.5"></i>Vendas</span>
                </div>
                <div class="sp-stat hidden sm:flex">
                    <span class="sp-stat-value text-emerald-600 dark:text-emerald-400">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</span>
                    <span class="sp-stat-label"><i class="bi bi-graph-up-arrow mr-0.5"></i>Receita</span>
                </div>
            </div>
        </div>

        {{-- Erro --}}
        @if($publication->error_message)
        <div class="flex items-start gap-2 px-3 py-2 rounded-xl bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5 flex-shrink-0 text-sm"></i>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-red-800 dark:text-red-200">Erro na última sincronização</p>
                <p class="text-[10px] text-red-600 dark:text-red-300 mt-0.5">{{ $publication->error_message }}</p>
            </div>
        </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════ --}}
        {{-- GRID PLANTA-BAIXA                                        --}}
        {{-- ═════════════════════════════════════════════════════════ --}}
        <div class="sp-grid">

            {{-- ▸ INFORMAÇÕES BÁSICAS ◂ --}}
            <div class="sp-zone-info sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(249,115,22,0.06));">
                    <div class="sp-icon bg-gradient-to-br from-amber-400 to-orange-500 shadow-md shadow-amber-500/20">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <span class="sp-title">Informações da Publicação</span>
                </div>
                <div class="sp-card-body space-y-2">
                    {{-- Descrição --}}
                    @if($publication->description)
                    <div>
                        <div class="flex items-center gap-1 mb-0.5">
                            <i class="bi bi-text-paragraph text-[10px]" style="color: var(--primary, #9575cd);"></i>
                            <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--primary, #9575cd);">Descrição</span>
                        </div>
                        <div class="sp-description sp-scroll rounded-md p-2" style="background: var(--gray-100, #f3f3f7);">{{ $publication->description }}</div>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-text-paragraph text-lg block mb-0.5" style="color: var(--card-border, #b39ddb);"></i>
                        <p class="text-[10px]" style="color: var(--primary, #9575cd);">Sem descrição</p>
                    </div>
                    @endif

                    {{-- Criado por --}}
                    <div class="flex items-center gap-2 pt-1.5 border-t-2 border-dashed" style="border-color: var(--card-accent, #ffe0b2);">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background: var(--card-accent2, #f8bbd0);">
                            <i class="bi bi-person-fill text-xs" style="color: var(--primary-dark, #512da8);"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold" style="color: var(--gray-700, #424242);">{{ $publication->user->name ?? '—' }}</p>
                            <p class="text-[9px]" style="color: var(--primary, #9575cd);">Criado em {{ $publication->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ▸ DETALHES E PREÇO ◂ --}}
            <div class="sp-zone-detalhes sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(149,117,205,0.12), rgba(248,187,208,0.08));">
                    <div class="sp-icon bg-gradient-to-br from-purple-400 to-pink-400 shadow-md shadow-purple-500/20">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <span class="sp-title">Detalhes do Anúncio</span>
                </div>
                <div class="sp-card-body space-y-0.5">
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-tag mr-0.5"></i>Preço</span>
                        <span class="sp-info-value text-base font-black" style="color: var(--primary-dark, #512da8);">R$ {{ number_format($publication->price, 2, ',', '.') }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-grid-3x3-gap mr-0.5"></i>Categoria ML</span>
                        <span class="sp-info-value font-mono text-[10px]">{{ $publication->ml_category_id ?? '—' }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-megaphone mr-0.5"></i>Tipo Anúncio</span>
                        <span class="sp-info-value">{{ ucfirst(str_replace('_', ' ', $publication->listing_type ?? '—')) }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-stars mr-0.5"></i>Condição</span>
                        <span class="sp-info-value">{{ $publication->condition === 'new' ? 'Novo' : ($publication->condition === 'used' ? 'Usado' : ($publication->condition ?? '—')) }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-truck mr-0.5"></i>Frete Grátis</span>
                        <span class="sp-info-value {{ $publication->free_shipping ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                            <i class="bi {{ $publication->free_shipping ? 'bi-check-circle-fill text-emerald-500' : 'bi-x-circle text-slate-400' }} mr-0.5"></i>
                            {{ $publication->free_shipping ? 'Sim' : 'Não' }}
                        </span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-geo-alt mr-0.5"></i>Retirada Local</span>
                        <span class="sp-info-value {{ $publication->local_pickup ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                            <i class="bi {{ $publication->local_pickup ? 'bi-check-circle-fill text-emerald-500' : 'bi-x-circle text-slate-400' }} mr-0.5"></i>
                            {{ $publication->local_pickup ? 'Sim' : 'Não' }}
                        </span>
                    </div>
                    @if($publication->warranty)
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-shield-check mr-0.5"></i>Garantia</span>
                        <span class="sp-info-value">{{ $publication->warranty }}</span>
                    </div>
                    @endif
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-boxes mr-0.5"></i>Tipo</span>
                        <span class="sp-info-value">{{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label"><i class="bi bi-stack mr-0.5"></i>Qtd Disponível</span>
                        <span class="sp-info-value text-sm font-black" style="color: var(--primary-dark, #512da8);">{{ $publication->available_quantity ?? $stats['total_stock_available'] }}</span>
                    </div>
                </div>
            </div>

            {{-- ▸ SIDEBAR ◂ --}}
            <div class="sp-zone-sidebar sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(248,187,208,0.12), rgba(149,117,205,0.08));">
                    <div class="sp-icon bg-gradient-to-br from-pink-400 to-purple-500 shadow-md shadow-pink-500/20">
                        <i class="bi bi-cloud-check-fill"></i>
                    </div>
                    <span class="sp-title">Sincronização e Ações</span>
                </div>
                <div class="sp-card-body space-y-2">
                    {{-- Sync Info --}}
                    <div class="space-y-0.5">
                        <div class="sp-info-row">
                            <span class="sp-info-label"><i class="bi bi-cloud mr-0.5"></i>Status Sync</span>
                            <span class="sp-info-value">
                                @php $syncLabel = match($publication->sync_status) { 'synced' => 'Sincronizado', 'pending' => 'Pendente', 'error' => 'Erro', default => $publication->sync_status ?? '—' }; @endphp
                                {{ $syncLabel }}
                            </span>
                        </div>
                        @if($publication->last_sync_at)
                        <div class="sp-info-row">
                            <span class="sp-info-label"><i class="bi bi-clock-history mr-0.5"></i>Última Sync</span>
                            <span class="sp-info-value text-[10px]" title="{{ $publication->last_sync_at->format('d/m/Y H:i:s') }}">{{ $publication->last_sync_at->diffForHumans() }}</span>
                        </div>
                        @endif
                        <div class="sp-info-row">
                            <span class="sp-info-label"><i class="bi bi-link-45deg mr-0.5"></i>ID ML</span>
                            <span class="sp-info-value font-mono text-[10px]">{{ $publication->ml_item_id ?? '—' }}</span>
                        </div>
                        <div class="sp-info-row">
                            <span class="sp-info-label"><i class="bi bi-calendar-plus mr-0.5"></i>Criado</span>
                            <span class="sp-info-value text-[10px]">{{ $publication->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="sp-info-row">
                            <span class="sp-info-label"><i class="bi bi-calendar-check mr-0.5"></i>Atualizado</span>
                            <span class="sp-info-value text-[10px]">{{ $publication->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- Ações rápidas --}}
                    <div class="pt-1.5 border-t-2 border-dashed space-y-1" style="border-color: var(--card-accent, #ffe0b2);">
                        <p class="text-[9px] font-bold uppercase tracking-wider" style="color: var(--primary, #9575cd);"><i class="bi bi-lightning-charge-fill mr-0.5"></i>Ações Rápidas</p>
                        <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}"
                            class="sp-action-btn bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/50 hover:bg-purple-100">
                            <i class="bi bi-pencil-square"></i>
                            Editar publicação
                        </a>
                        <button wire:click="syncToMercadoLivre" wire:loading.attr="disabled" wire:target="syncToMercadoLivre"
                            class="sp-action-btn bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/50 hover:bg-blue-100">
                            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin" wire:target="syncToMercadoLivre"></i>
                            Sincronizar com ML
                        </button>
                        @if($publication->ml_permalink)
                        <a href="{{ $publication->ml_permalink }}" target="_blank" rel="noopener"
                            class="sp-action-btn bg-gradient-to-r from-yellow-400 to-amber-500 text-slate-900 hover:from-yellow-500 hover:to-amber-600 shadow-sm !border-amber-400">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Ver no Mercado Livre
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ▸ PRODUTOS VINCULADOS ◂ --}}
            <div class="sp-zone-produtos sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(248,187,208,0.06));">
                    <div class="sp-icon bg-gradient-to-br from-emerald-400 to-teal-500 shadow-md shadow-emerald-500/20">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <span class="sp-title">Produtos Vinculados</span>
                    <span class="sp-badge border-purple-300 dark:border-purple-700" style="background: var(--card-accent2, #f8bbd0); color: var(--primary-dark, #512da8);">{{ $publication->products->count() }}</span>
                </div>
                <div class="sp-card-body">
                    @if($publication->products->count() > 0)
                    <div class="sp-products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                        @foreach($publication->products as $product)
                        <div class="product-card-modern relative" wire:key="prod-{{ $product->id }}">
                            {{-- Área da imagem --}}
                            <div class="product-img-area">
                                <img src="{{ $product->image_url ?? asset('storage/products/product-placeholder.png') }}"
                                    alt="{{ $product->name }}"
                                    class="product-img"
                                    onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">

                                <span class="badge-product-code">
                                    <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                </span>

                                <span class="badge-quantity">
                                    <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                </span>

                                <div class="category-icon-wrapper">
                                    @if($product->category && $product->category->icon)
                                    <i class="{{ $product->category->icon }} category-icon"></i>
                                    @else
                                    <i class="bi bi-box-seam category-icon"></i>
                                    @endif
                                </div>
                            </div>

                            {{-- Corpo --}}
                            <div class="card-body">
                                <div class="product-title">{{ ucwords($product->name) }}</div>
                                <div class="price-area mt-1">
                                    @if($product->pivot->unit_cost)
                                    <span class="badge-price">
                                        <i class="bi bi-tag"></i> R$ {{ number_format($product->pivot->unit_cost, 2, ',', '.') }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Overlay qtd/venda --}}
                            <div class="sp-qty-overlay">
                                <span class="text-[9px] font-bold text-white/80"><i class="bi bi-cart-check mr-0.5"></i>Por venda:</span>
                                <span class="text-[11px] font-black text-white">{{ $product->pivot->quantity }}x</span>
                                @if($publication->publication_type === 'kit')
                                <span class="text-[8px] text-amber-300 font-bold ml-1">≈ {{ floor($product->stock_quantity / max(1, $product->pivot->quantity)) }} vendas</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Resumo --}}
                    @php
                        $totalCost = $publication->products->sum(fn($p) => ($p->pivot->unit_cost ?? 0) * ($p->pivot->quantity ?? 1));
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-2 mt-2 px-2.5 py-1.5 rounded-xl border-2" style="background: var(--card-bg, #e6e6fa); border-color: var(--card-accent, #ffe0b2);">
                        <div class="flex items-center gap-2 text-[11px]">
                            <span style="color: var(--gray-700, #424242);"><strong>{{ $publication->products->count() }}</strong> produto(s)</span>
                            <span style="color: var(--card-border, #b39ddb);">·</span>
                            <span style="color: var(--gray-700, #424242);">Custo: <strong style="color: var(--primary-dark, #512da8);">R$ {{ number_format($totalCost, 2, ',', '.') }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1 text-[11px] font-bold" style="color: var(--primary-dark, #512da8);">
                            <i class="bi bi-stack"></i>
                            {{ $stats['total_stock_available'] }} un. disponíveis
                        </div>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-2" style="background: var(--card-accent, #ffe0b2);">
                            <i class="bi bi-box-seam text-xl" style="color: var(--primary, #9575cd);"></i>
                        </div>
                        <p class="text-xs font-bold" style="color: var(--gray-700, #424242);">Nenhum produto vinculado</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ▸ IMAGENS ◂ --}}
            <div class="sp-zone-imagens sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(149,117,205,0.06));">
                    <div class="sp-icon bg-gradient-to-br from-sky-400 to-purple-400 shadow-md shadow-sky-500/20">
                        <i class="bi bi-images"></i>
                    </div>
                    <span class="sp-title">Imagens do Anúncio</span>
                    @if($publication->pictures && count($publication->pictures) > 0)
                    <span class="sp-badge border-sky-300 dark:border-sky-700" style="background: var(--card-accent2, #f8bbd0); color: var(--primary-dark, #512da8);">{{ count($publication->pictures) }}</span>
                    @endif
                </div>
                <div class="sp-card-body">
                    @if($publication->pictures && count($publication->pictures) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                        @foreach($publication->pictures as $pic)
                        @php
                            $picUrl = is_array($pic) ? ($pic['secure_url'] ?? $pic['url'] ?? '') : (is_string($pic) ? $pic : '');
                        @endphp
                        @if($picUrl)
                        <a href="{{ $picUrl }}" target="_blank" rel="noopener" class="sp-img-thumb">
                            <img src="{{ $picUrl }}" alt="Imagem" onerror="this.parentElement.style.display='none'">
                            <div class="sp-img-overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-image text-xl block mb-1" style="color: var(--card-border, #b39ddb);"></i>
                        <p class="text-[10px]" style="color: var(--primary, #9575cd);">Sem imagens disponíveis</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ▸ ATRIBUTOS DO ML ◂ --}}
            <div class="sp-zone-atributos sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(249,115,22,0.08), rgba(248,187,208,0.06));">
                    <div class="sp-icon bg-gradient-to-br from-orange-400 to-pink-500 shadow-md shadow-orange-500/20">
                        <i class="bi bi-list-columns-reverse"></i>
                    </div>
                    <span class="sp-title">Atributos do Mercado Livre</span>
                    @if($publication->ml_attributes && count($publication->ml_attributes) > 0)
                    <span class="sp-badge border-orange-300 dark:border-orange-700" style="background: var(--card-accent, #ffe0b2); color: var(--primary-dark, #512da8);">{{ count($publication->ml_attributes) }}</span>
                    @endif
                </div>
                <div class="sp-card-body">
                    @if($publication->ml_attributes && is_array($publication->ml_attributes) && count($publication->ml_attributes) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-[320px] overflow-y-auto sp-scroll">
                        @foreach($publication->ml_attributes as $attr)
                        @php
                            $attrName = '—';
                            $attrValue = '—';
                            if (is_array($attr)) {
                                $attrName = $attr['name'] ?? $attr['id'] ?? '—';
                                $attrValue = $attr['value_name'] ?? ($attr['value_struct']['number'] ?? null) ?? $attr['value_id'] ?? '—';
                                if ($attrValue === null || $attrValue === '') $attrValue = '—';
                            } elseif (is_string($attr)) {
                                $attrName = $attr;
                            }
                        @endphp
                        @if($attrValue !== '—' || $attrName !== '—')
                        <div class="sp-attr-item">
                            <span class="font-medium truncate" style="color: var(--primary, #9575cd); max-width: 45%;">{{ $attrName }}</span>
                            <span class="font-bold truncate text-right" style="color: var(--gray-700, #424242); max-width: 55%;">{{ $attrValue }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-list-ul text-xl block mb-1" style="color: var(--card-border, #b39ddb);"></i>
                        <p class="text-[10px] font-bold" style="color: var(--primary, #9575cd);">Nenhum atributo salvo</p>
                        <p class="text-[9px]" style="color: var(--card-border, #b39ddb);">Atributos aparecem após sincronizar com o ML</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ▸ HISTÓRICO DE MOVIMENTAÇÕES ◂ --}}
            <div class="sp-zone-historico sp-card">
                <div class="sp-card-head" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(14,165,233,0.06));">
                    <div class="sp-icon bg-gradient-to-br from-emerald-400 to-cyan-500 shadow-md shadow-emerald-500/20">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="sp-title">Histórico de Movimentações</span>
                    @if(count($stockHistory) > 0)
                    <span class="sp-badge border-emerald-300 dark:border-emerald-700" style="background: var(--card-accent2, #f8bbd0); color: var(--primary-dark, #512da8);">{{ count($stockHistory) }}</span>
                    @endif
                </div>
                <div class="sp-card-body">
                    @if(count($stockHistory) > 0)
                    <div class="space-y-0.5 max-h-[300px] overflow-y-auto sp-scroll">
                        @foreach($stockHistory as $log)
                        @php
                            $opType = $log['operation_type'] ?? '';
                            $opCfg = match($opType) {
                                'ml_sale' => ['icon' => 'bi-cart-check-fill', 'color' => 'emerald', 'label' => 'Venda ML'],
                                'sync_to_ml' => ['icon' => 'bi-arrow-repeat', 'color' => 'blue', 'label' => 'Sync ML'],
                                'manual_update', 'manual_adjustment' => ['icon' => 'bi-pencil-fill', 'color' => 'amber', 'label' => 'Ajuste Manual'],
                                'rollback' => ['icon' => 'bi-arrow-counterclockwise', 'color' => 'red', 'label' => 'Estorno'],
                                default => ['icon' => 'bi-arrow-left-right', 'color' => 'slate', 'label' => ucfirst(str_replace('_', ' ', $opType ?: 'Operação'))],
                            };
                            $bgMap = ['emerald' => 'bg-emerald-50 dark:bg-emerald-900/10', 'blue' => 'bg-blue-50 dark:bg-blue-900/10', 'amber' => 'bg-amber-50 dark:bg-amber-900/10', 'red' => 'bg-red-50 dark:bg-red-900/10', 'slate' => 'bg-slate-50 dark:bg-slate-800/40'];
                            $borderMap = ['emerald' => 'border-emerald-400', 'blue' => 'border-blue-400', 'amber' => 'border-amber-400', 'red' => 'border-red-400', 'slate' => 'border-slate-400'];
                            $iconColorMap = ['emerald' => 'text-emerald-500', 'blue' => 'text-blue-500', 'amber' => 'text-amber-500', 'red' => 'text-red-500', 'slate' => 'text-slate-500'];
                            $qtyChange = $log['quantity_change'] ?? 0;
                        @endphp
                        <div class="sp-log-item {{ $bgMap[$opCfg['color']] }} {{ $borderMap[$opCfg['color']] }}">
                            <i class="bi {{ $opCfg['icon'] }} {{ $iconColorMap[$opCfg['color']] }} text-sm flex-shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $opCfg['label'] }}</span>
                                    @if(isset($log['product']) && $log['product'])
                                    <span class="text-[9px] truncate" style="color: var(--primary, #9575cd);">— {{ $log['product']['name'] ?? '' }}</span>
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-400 dark:text-slate-500">
                                    {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') : '—' }}
                                    · <span class="font-mono">{{ $log['quantity_before'] ?? '?' }} → {{ $log['quantity_after'] ?? '?' }}</span>
                                </span>
                            </div>
                            <span class="text-xs font-black flex-shrink-0 {{ $qtyChange > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                {{ $qtyChange > 0 ? '+' : '' }}{{ $qtyChange }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-xl block mb-1" style="color: var(--card-border, #b39ddb);"></i>
                        <p class="text-[10px] font-bold" style="color: var(--primary, #9575cd);">Nenhuma movimentação registrada</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /sp-grid --}}
    </div>
</div>