<div class="edit-publication-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ultrawide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <style>
        /* ═══ LAYOUT PLANTA-BAIXA ═══ */
        .ep-zone-titulo    { grid-area: title; }
        .ep-zone-preco     { grid-area: price; }
        .ep-zone-sidebar   { grid-area: sidebar; }
        .ep-zone-produtos  { grid-area: products; }
        .ep-zone-imagens   { grid-area: images; }
        .ep-zone-atributos { grid-area: attrs; }

        .ep-grid {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-areas:
                "title"
                "price"
                "sidebar"
                "products"
                "images"
                "attrs";
            gap: clamp(0.5rem, 1vw, 0.75rem);
        }
        @media (min-width: 768px) {
            .ep-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-areas:
                    "title price"
                    "sidebar sidebar"
                    "products products"
                    "images attrs";
            }
        }
        @media (min-width: 1280px) {
            .ep-grid {
                grid-template-columns: 2fr 1.2fr 1fr;
                grid-template-areas:
                    "title price sidebar"
                    "products products sidebar"
                    "images attrs attrs";
            }
        }

        /* ═══ CARD BASE (cores do app) ═══ */
        .ep-card {
            background: var(--card-bg, #e6e6fa);
            border: 2.5px solid var(--card-border, #b39ddb);
            border-radius: 1.5em;
            box-shadow: 0 4px 18px var(--card-shadow, rgba(149,117,205,0.13));
            overflow: hidden;
            transition: box-shadow 0.18s, border-color 0.18s, transform 0.18s;
            position: relative;
        }
        .ep-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1.5em;
            pointer-events: none;
            box-shadow: 0 0 0 3px var(--card-accent, #ffe0b2) inset;
            z-index: 1;
        }
        .ep-card:hover {
            box-shadow: 0 8px 32px var(--shadow-strong, rgba(81,45,168,0.18));
            border-color: var(--primary, #9575cd);
            transform: translateY(-1px);
        }
        .dark .ep-card {
            background: linear-gradient(135deg, rgba(30,20,50,0.95) 0%, rgba(40,25,65,0.9) 100%);
            border-color: rgba(149,117,205,0.35);
        }
        .dark .ep-card::before { box-shadow: 0 0 0 2px rgba(255,224,178,0.08) inset; }
        .dark .ep-card:hover { border-color: var(--primary, #9575cd); }

        .ep-card-head {
            padding: clamp(0.4rem, 0.8vw, 0.6rem) clamp(0.6rem, 1vw, 0.85rem);
            border-bottom: 2px solid var(--card-accent, #ffe0b2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 2;
        }
        .dark .ep-card-head { border-color: rgba(255,224,178,0.12); }

        .ep-card-body { padding: clamp(0.5rem, 1vw, 0.85rem); position: relative; z-index: 2; }

        .ep-icon {
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
        .ep-title {
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
        .dark .ep-title {
            background: linear-gradient(92deg, #f8bbd0 0%, #d1c4e9 48%, #9575cd 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        /* ═══ INPUTS ═══ */
        .ep-input {
            width: 100%;
            padding: clamp(0.35rem, 0.7vw, 0.5rem) clamp(0.5rem, 0.9vw, 0.75rem);
            border: 2px solid var(--card-border, #b39ddb);
            border-radius: 0.75rem;
            background: var(--gray-100, #f3f3f7);
            color: var(--gray-700, #424242);
            font-size: clamp(0.72rem, 1vw, 0.85rem);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .ep-input:focus {
            border-color: var(--primary, #9575cd);
            box-shadow: 0 0 0 3px rgba(149,117,205,0.2);
            outline: none;
        }
        .dark .ep-input {
            background: rgba(20,15,35,0.7);
            border-color: rgba(149,117,205,0.3);
            color: #e2e8f0;
        }
        .ep-lbl {
            display: block;
            font-size: clamp(0.62rem, 0.85vw, 0.72rem);
            font-weight: 700;
            color: var(--primary, #9575cd);
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .dark .ep-lbl { color: #d1c4e9; }

        /* ═══ BADGES ═══ */
        .ep-badge {
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

        /* ═══ SAVE BAR ═══ */
        .ep-save-bar {
            background: linear-gradient(135deg, var(--card-bg, #e6e6fa) 0%, var(--card-accent2, #f8bbd0) 100%);
            border: 2.5px solid var(--card-border, #b39ddb);
            border-radius: 1.2rem;
            box-shadow: 0 -4px 24px var(--shadow-strong, rgba(81,45,168,0.18));
        }
        .dark .ep-save-bar {
            background: linear-gradient(135deg, rgba(30,20,50,0.95), rgba(60,30,80,0.9));
            border-color: rgba(149,117,205,0.4);
        }

        /* ═══ SEARCH ═══ */
        .ep-search-item {
            border: 2px solid var(--card-border, #b39ddb);
            border-radius: 0.75rem;
            background: var(--card-bg, #e6e6fa);
            transition: all 0.15s;
            cursor: pointer;
        }
        .ep-search-item:hover {
            border-color: var(--primary, #9575cd);
            background: var(--gray-100, #f3f3f7);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--shadow, rgba(149,117,205,0.15));
        }
        .dark .ep-search-item { background: rgba(30,20,50,0.6); border-color: rgba(149,117,205,0.25); }
        .dark .ep-search-item:hover { background: rgba(50,30,70,0.6); }

        /* ═══ SCROLL ═══ */
        .ep-scroll::-webkit-scrollbar { width: 4px; }
        .ep-scroll::-webkit-scrollbar-track { background: transparent; }
        .ep-scroll::-webkit-scrollbar-thumb { background: var(--primary-light, #d1c4e9); border-radius: 10px; }

        .ep-toggle { accent-color: var(--primary, #9575cd); }

        /* ═══ SIDEBAR ITEMS ═══ */
        .ep-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: clamp(0.6rem, 0.85vw, 0.72rem);
            border-bottom: 1px dashed rgba(179,157,219,0.2);
        }
        .ep-info-row:last-child { border-bottom: none; }
        .ep-info-label { color: var(--primary, #9575cd); font-weight: 600; }
        .dark .ep-info-label { color: #d1c4e9; }
        .ep-info-value { font-weight: 700; color: var(--gray-700, #424242); text-align: right; }
        .dark .ep-info-value { color: #e2e8f0; }

        /* ═══ ACTION BUTTONS ═══ */
        .ep-action-btn {
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

        /* ═══ ATTRIBUTE GRID ═══ */
        .ep-attr-item {
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
        .dark .ep-attr-item {
            background: rgba(20,15,35,0.5);
            border-color: rgba(255,224,178,0.1);
        }

        /* ═══ PRODUCT CARD OVERRIDES (escala menor p/ edit) ═══ */
        .ep-products-grid .product-card-modern {
            min-height: auto;
        }
        .ep-products-grid .product-card-modern .product-img-area {
            min-height: clamp(100px, 12vw, 180px);
            height: clamp(100px, 12vw, 180px);
        }
        .ep-products-grid .product-card-modern .card-body {
            padding: clamp(1.8rem, 3vw, 2.5rem) clamp(0.5rem, 0.8vw, 0.7rem) clamp(0.5rem, 0.8vw, 0.7rem);
        }
        .ep-products-grid .product-card-modern .product-title {
            font-size: clamp(0.65rem, 0.9vw, 0.8rem);
        }
        .ep-products-grid .product-card-modern .category-icon-wrapper {
            width: clamp(36px, 4vw, 48px);
            height: clamp(36px, 4vw, 48px);
            bottom: clamp(-18px, -2vw, -24px);
        }
        .ep-products-grid .product-card-modern .category-icon {
            font-size: clamp(1.2rem, 2vw, 1.6rem);
        }
        .ep-products-grid .product-card-modern .badge-product-code {
            font-size: clamp(0.55rem, 0.75vw, 0.68rem);
            padding: 0.1em 0.5em;
        }
        .ep-products-grid .product-card-modern .badge-quantity {
            font-size: clamp(0.5rem, 0.7vw, 0.62rem);
            padding: 0.08em 0.4em;
        }
        .ep-products-grid .product-card-modern .badge-price,
        .ep-products-grid .product-card-modern .badge-price-sale {
            font-size: clamp(0.55rem, 0.75vw, 0.68rem);
            padding: 0.12em 0.7em;
        }
        /* Overlay de edição no product-card */
        .ep-card-edit-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(30,20,50,0.92) 0%, rgba(30,20,50,0.6) 60%, transparent 100%);
            padding: clamp(0.35rem, 0.6vw, 0.5rem);
            z-index: 25;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            border-radius: 0 0 1.5em 1.5em;
        }
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
                            <i class="bi bi-pencil-square text-white text-lg"></i>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-br from-yellow-300 to-amber-400 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center shadow-sm">
                                <span class="text-[5px] font-black text-amber-900">ML</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1 text-[10px] text-slate-500 dark:text-slate-400">
                                <a href="{{ route('mercadolivre.publications') }}" class="hover:text-amber-600 transition-colors">Publicações</a>
                                <i class="bi bi-chevron-right text-[8px]"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-semibold">Editar</span>
                            </div>
                            <h1 class="text-base sm:text-xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700 dark:from-amber-300 dark:via-yellow-300 dark:to-orange-300 bg-clip-text text-transparent leading-tight truncate">
                                Editar Publicação
                            </h1>
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="ep-badge bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 font-mono border-slate-300 dark:border-slate-600">{{ $publication->ml_item_id }}</span>
                        <span class="ep-badge {{ $publication->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700' : '' }} {{ $publication->status === 'paused' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700' : '' }} {{ $publication->status === 'closed' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-300 dark:border-red-700' : '' }}">
                            <i class="bi {{ $publication->status === 'active' ? 'bi-check-circle-fill' : ($publication->status === 'paused' ? 'bi-pause-circle-fill' : 'bi-x-circle-fill') }}"></i>
                            {{ $publication->status === 'active' ? 'Ativo' : ($publication->status === 'paused' ? 'Pausado' : 'Fechado') }}
                        </span>
                        <span class="ep-badge bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-700">
                            <i class="bi {{ $publicationType === 'kit' ? 'bi-boxes' : 'bi-box' }}"></i>
                            {{ $publicationType === 'kit' ? 'Kit' : 'Simples' }}
                        </span>
                        <span class="ep-badge {{ $publication->sync_status === 'synced' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700' : '' }} {{ $publication->sync_status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700' : '' }} {{ $publication->sync_status === 'error' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-300 dark:border-red-700' : '' }}">
                            <i class="bi {{ $publication->sync_status === 'synced' ? 'bi-cloud-check' : ($publication->sync_status === 'error' ? 'bi-cloud-slash' : 'bi-cloud-arrow-up') }}"></i>
                            {{ $publication->sync_status === 'synced' ? 'Sincronizado' : ($publication->sync_status === 'error' ? 'Erro' : 'Pendente') }}
                        </span>
                    </div>

                    {{-- Ações --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button wire:click="refreshFromMl" wire:loading.attr="disabled" wire:target="refreshFromMl"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-lg transition-all shadow-md text-xs whitespace-nowrap">
                            <i class="bi bi-arrow-down-circle" wire:loading.remove wire:target="refreshFromMl"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshFromMl"></i>
                            <span class="hidden sm:inline">Atualizar do ML</span>
                        </button>
                        @if($publication->status === 'active')
                        <button wire:click="pausePublication"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 font-semibold rounded-lg transition-all text-xs whitespace-nowrap hover:bg-amber-200">
                            <i class="bi bi-pause-fill"></i> Pausar
                        </button>
                        @elseif($publication->status === 'paused')
                        <button wire:click="activatePublication"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 font-semibold rounded-lg transition-all text-xs whitespace-nowrap hover:bg-emerald-200">
                            <i class="bi bi-play-fill"></i> Ativar
                        </button>
                        @endif
                        @if($publication->ml_item_id)
                        @php $mlUrl = $publication->ml_permalink ?: 'https://articulo.mercadolibre.com.br/' . $publication->ml_item_id; @endphp
                        <a href="{{ $mlUrl }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 text-slate-900 font-bold rounded-lg shadow-md transition-all text-xs whitespace-nowrap">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span class="hidden sm:inline">Ver no ML</span>
                        </a>
                        @endif
                    </div>
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
        <div class="ep-grid">

            {{-- ▸ TÍTULO E DESCRIÇÃO ◂ --}}
            <div class="ep-zone-titulo ep-card">
                <div class="ep-card-head" style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(249,115,22,0.06));">
                    <div class="ep-icon bg-gradient-to-br from-amber-400 to-orange-500 shadow-md shadow-amber-500/20">
                        <i class="bi bi-type"></i>
                    </div>
                    <span class="ep-title">Título e Descrição</span>
                </div>
                <div class="ep-card-body space-y-2">
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="ep-lbl">Título do anúncio *</label>
                            <span class="text-[10px] font-mono font-bold {{ strlen($title) > 60 ? 'text-red-500' : 'text-slate-400 dark:text-slate-500' }}">{{ strlen($title) }}/60</span>
                        </div>
                        <input type="text" wire:model.live="title" maxlength="255"
                            class="ep-input {{ strlen($title) > 60 ? '!border-red-400' : '' }}"
                            placeholder="Título exibido no Mercado Livre">
                        @if(strlen($title) > 60)
                        <p class="mt-0.5 text-[10px] text-red-500 font-semibold"><i class="bi bi-exclamation-triangle mr-0.5"></i>O ML limita títulos a 60 caracteres</p>
                        @endif
                        @error('title')<p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="ep-lbl">Descrição</label>
                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">{{ strlen($description) }} caracteres</span>
                        </div>
                        <textarea wire:model="description" rows="5"
                            class="ep-input resize-y"
                            placeholder="Descrição detalhada (texto simples, sem HTML)"></textarea>
                    </div>
                </div>
            </div>

            {{-- ▸ PREÇO E DETALHES ◂ --}}
            <div class="ep-zone-preco ep-card">
                <div class="ep-card-head" style="background: linear-gradient(135deg, rgba(149,117,205,0.12), rgba(248,187,208,0.08));">
                    <div class="ep-icon bg-gradient-to-br from-purple-400 to-pink-400 shadow-md shadow-purple-500/20">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <span class="ep-title">Preço e Detalhes</span>
                </div>
                <div class="ep-card-body space-y-2">
                    <div>
                        <label class="ep-lbl">Preço de venda *</label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 font-bold text-xs" style="color: var(--primary, #9575cd);">R$</span>
                            <input type="number" wire:model="price" step="0.01" min="0.01"
                                class="ep-input !pl-9 !text-lg !font-bold" style="color: var(--primary-dark, #512da8);"
                                placeholder="0,00">
                        </div>
                        @error('price')<p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="ep-lbl">Tipo de anúncio</label>
                            <select wire:model="listingType" class="ep-input">
                                <option value="gold_special">Premium</option>
                                <option value="gold_pro">Gold Pro</option>
                                <option value="gold">Gold</option>
                                <option value="free">Clássico</option>
                            </select>
                        </div>
                        <div>
                            <label class="ep-lbl">Condição</label>
                            <select wire:model="condition" class="ep-input">
                                <option value="new">Novo</option>
                                <option value="used">Usado</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="ep-lbl">Garantia</label>
                        <input type="text" wire:model="warranty" class="ep-input" placeholder="Ex: 90 dias contra defeitos">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="ep-lbl">Categoria ML</label>
                            <input type="text" wire:model="mlCategoryId" class="ep-input font-mono" placeholder="MLB1234">
                        </div>
                        <div>
                            <label class="ep-lbl">Tipo publicação</label>
                            <select wire:model="publicationType" class="ep-input">
                                <option value="simple">Simples</option>
                                <option value="kit">Kit</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-1.5 border-t-2 border-dashed" style="border-color: var(--card-accent, #ffe0b2);">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" wire:model="freeShipping" class="ep-toggle w-3.5 h-3.5 rounded">
                            <div>
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Frete grátis</span>
                                <span class="block text-[9px] text-slate-400 leading-tight">Custo absorvido por você</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" wire:model="localPickup" class="ep-toggle w-3.5 h-3.5 rounded">
                            <div>
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Retirada local</span>
                                <span class="block text-[9px] text-slate-400 leading-tight">Comprador retira no local</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ▸ SIDEBAR: INFO + AÇÕES ◂ --}}
            <div class="ep-zone-sidebar ep-card">
                <div class="ep-card-head" style="background: linear-gradient(135deg, rgba(248,187,208,0.12), rgba(149,117,205,0.08));">
                    <div class="ep-icon bg-gradient-to-br from-pink-400 to-purple-500 shadow-md shadow-pink-500/20">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <span class="ep-title">Informações</span>
                </div>
                <div class="ep-card-body space-y-2">
                    {{-- Info --}}
                    <div class="space-y-0.5">
                        <div class="ep-info-row">
                            <span class="ep-info-label">ID ML</span>
                            <span class="ep-info-value font-mono text-[10px]">{{ $publication->ml_item_id ?? '—' }}</span>
                        </div>
                        <div class="ep-info-row">
                            <span class="ep-info-label">Disponível</span>
                            <span class="ep-info-value text-base font-black" style="color: var(--primary-dark, #512da8);">{{ $availableQuantity }}</span>
                        </div>
                        <div class="ep-info-row">
                            <span class="ep-info-label">Listagem</span>
                            <span class="ep-info-value">{{ ucwords(str_replace('_', ' ', $listingType)) }}</span>
                        </div>
                        <div class="ep-info-row">
                            <span class="ep-info-label">Frete grátis</span>
                            <span class="ep-info-value {{ $freeShipping ? 'text-emerald-600 dark:text-emerald-400' : '' }}">{{ $freeShipping ? 'Sim' : 'Não' }}</span>
                        </div>
                        @if($publication->last_sync_at)
                        <div class="ep-info-row">
                            <span class="ep-info-label">Última sync</span>
                            <span class="ep-info-value text-[10px]" title="{{ $publication->last_sync_at->format('d/m/Y H:i:s') }}">{{ $publication->last_sync_at->diffForHumans() }}</span>
                        </div>
                        @endif
                        <div class="ep-info-row">
                            <span class="ep-info-label">Criado</span>
                            <span class="ep-info-value text-[10px]">{{ $publication->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="ep-info-row">
                            <span class="ep-info-label">Atualizado</span>
                            <span class="ep-info-value text-[10px]">{{ $publication->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- Ações rápidas --}}
                    <div class="pt-1.5 border-t-2 border-dashed space-y-1" style="border-color: var(--card-accent, #ffe0b2);">
                        <p class="text-[9px] font-bold uppercase tracking-wider" style="color: var(--primary, #9575cd);"><i class="bi bi-lightning-charge-fill mr-0.5"></i>Ações Rápidas</p>
                        <button wire:click="refreshFromMl" wire:loading.attr="disabled" wire:target="refreshFromMl"
                            class="ep-action-btn bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/50 hover:bg-blue-100">
                            <i class="bi bi-arrow-down-circle" wire:loading.remove wire:target="refreshFromMl"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshFromMl"></i>
                            Atualizar do ML
                        </button>
                        <button wire:click="syncPublication" wire:loading.attr="disabled" wire:target="syncPublication"
                            class="ep-action-btn bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50 hover:bg-emerald-100">
                            <i class="bi bi-arrow-repeat" wire:loading.remove wire:target="syncPublication"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="syncPublication"></i>
                            Sincronizar estoque
                        </button>
                        @if($publication->ml_item_id)
                        @php $mlUrl2 = $publication->ml_permalink ?: 'https://articulo.mercadolibre.com.br/' . $publication->ml_item_id; @endphp
                        <a href="{{ $mlUrl2 }}" target="_blank" rel="noopener"
                            class="ep-action-btn bg-gradient-to-r from-yellow-400 to-amber-500 text-slate-900 hover:from-yellow-500 hover:to-amber-600 shadow-sm !border-amber-400">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Ver no Mercado Livre
                        </a>
                        @endif
                    </div>

                    {{-- Histórico --}}
                    <div class="pt-1.5 border-t-2 border-dashed space-y-1" style="border-color: var(--card-accent, #ffe0b2);">
                        <p class="text-[9px] font-bold uppercase tracking-wider" style="color: var(--primary, #9575cd);"><i class="bi bi-journal-text mr-0.5"></i>Histórico</p>
                        @if($stockLogs->count() > 0)
                        <div class="space-y-0.5 max-h-[140px] overflow-y-auto ep-scroll">
                            @foreach($stockLogs as $log)
                            <div class="px-2 py-1 rounded-md text-[9px] border-l-3
                                {{ $log->operation_type === 'ml_sale' ? 'bg-red-50 dark:bg-red-900/10 border-red-400' : '' }}
                                {{ $log->operation_type === 'sync_to_ml' ? 'bg-blue-50 dark:bg-blue-900/10 border-blue-400' : '' }}
                                {{ $log->operation_type === 'manual_update' ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-400' : '' }}">
                                <div class="flex justify-between">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $log->getOperationDescription() }}</span>
                                    <span class="text-slate-400">{{ $log->created_at->format('d/m H:i') }}</span>
                                </div>
                                <span class="text-slate-500 dark:text-slate-400">
                                    {{ $log->product->name ?? 'Produto' }}:
                                    <span class="font-mono">{{ $log->quantity_before }}→{{ $log->quantity_after }}</span>
                                    <span class="font-bold {{ $log->quantity_change > 0 ? 'text-emerald-600' : 'text-red-500' }}">({{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }})</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-center py-2 text-[9px] text-slate-400 dark:text-slate-500">Nenhum histórico</p>
                        @endif
                    </div>

                    {{-- Zona de perigo --}}
                    <div class="pt-1.5 border-t-2 border-dashed space-y-1" style="border-color: rgba(239,154,154,0.4);">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-red-500 dark:text-red-400"><i class="bi bi-exclamation-triangle-fill mr-0.5"></i>Zona de Perigo</p>
                        <button wire:click="deletePublication"
                            wire:confirm="Tem certeza que deseja deletar esta publicação? Esta ação não pode ser desfeita."
                            class="ep-action-btn bg-red-600 hover:bg-red-700 text-white !border-red-500 justify-center shadow-md">
                            <i class="bi bi-trash3"></i>
                            Deletar publicação
                        </button>
                    </div>
                </div>
            </div>

            {{-- ▸ PRODUTOS VINCULADOS ◂ --}}
            <div class="ep-zone-produtos ep-card">
                <div class="ep-card-head justify-between flex-wrap gap-1.5" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(248,187,208,0.06));">
                    <div class="flex items-center gap-2">
                        <div class="ep-icon bg-gradient-to-br from-emerald-400 to-teal-500 shadow-md shadow-emerald-500/20">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <span class="ep-title">Produtos Vinculados</span>
                        <span class="ep-badge border-purple-300 dark:border-purple-700" style="background: var(--card-accent2, #f8bbd0); color: var(--primary-dark, #512da8);">{{ count($products) }}</span>
                    </div>
                    <button wire:click="toggleProductSelector"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all
                            {{ $showProductSelector
                                ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border-2 border-red-300 dark:border-red-800 hover:bg-red-200'
                                : 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-md shadow-emerald-500/20'
                            }}">
                        <i class="bi {{ $showProductSelector ? 'bi-x-lg' : 'bi-plus-lg' }}"></i>
                        {{ $showProductSelector ? 'Fechar' : 'Adicionar' }}
                    </button>
                </div>

                <div class="ep-card-body space-y-3">
                    {{-- Busca inline --}}
                    @if($showProductSelector)
                    <div class="rounded-xl border-2 border-dashed p-2 space-y-2" style="border-color: var(--card-border, #b39ddb); background: rgba(149,117,205,0.05);">
                        <div class="relative">
                            <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-sm pointer-events-none" style="color: var(--primary, #9575cd);"></i>
                            <input type="text" wire:model.live.debounce.300ms="productSearch"
                                placeholder="Buscar por nome, código ou EAN..."
                                class="ep-input !pl-8 !text-xs">
                        </div>
                        @if($this->searchableProducts->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1.5 max-h-[240px] overflow-y-auto ep-scroll pr-0.5">
                            @foreach($this->searchableProducts as $sp)
                            <button type="button" wire:click="addProduct({{ $sp->id }})" wire:key="sp-{{ $sp->id }}"
                                class="ep-search-item flex items-center gap-2 p-2 text-left w-full group">
                                <img src="{{ $sp->image_url }}" alt=""
                                    class="w-9 h-9 rounded-lg object-cover flex-shrink-0" style="border: 2px solid var(--card-border, #b39ddb);"
                                    onerror="this.style.display='none'">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold text-slate-800 dark:text-white truncate">{{ ucwords($sp->name) }}</p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="text-[9px] font-mono" style="color: var(--primary, #9575cd);">{{ $sp->product_code }}</span>
                                        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">{{ $sp->stock_quantity }} un.</span>
                                    </div>
                                </div>
                                <i class="bi bi-plus-circle opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" style="color: var(--primary, #9575cd);"></i>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <p class="text-center py-3 text-[11px] text-slate-400 dark:text-slate-500">
                            <i class="bi bi-search mr-0.5"></i>
                            {{ strlen($productSearch) >= 2 ? 'Nenhum produto encontrado' : 'Digite para buscar...' }}
                        </p>
                        @endif
                    </div>
                    @endif

                    {{-- Cards dos produtos (product-card-modern) --}}
                    @if(!empty($products))
                    <div class="ep-products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                        @foreach($products as $idx => $product)
                        <div class="product-card-modern relative" wire:key="prod-{{ $product['id'] }}">
                            {{-- Botões de ação --}}
                            <div class="btn-action-group">
                                <button wire:click="removeProduct({{ $product['id'] }})" wire:confirm="Remover este produto da publicação?"
                                    class="btn btn-danger" title="Remover">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>

                            {{-- Área da imagem --}}
                            <div class="product-img-area">
                                <img src="{{ $product['image_url'] ?? asset('storage/products/product-placeholder.png') }}"
                                    alt="{{ $product['name'] }}"
                                    class="product-img"
                                    onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">

                                <span class="badge-product-code">
                                    <i class="bi bi-upc-scan"></i> {{ $product['product_code'] }}
                                </span>

                                <span class="badge-quantity" style="{{ ($product['stock_quantity'] ?? 0) <= 5 ? 'background: linear-gradient(90deg, #ef9a9a 0%, #f8bbd0 100%);' : '' }}">
                                    <i class="bi bi-stack"></i> {{ $product['stock_quantity'] ?? 0 }}
                                </span>

                                <div class="category-icon-wrapper">
                                    <i class="bi bi-box-seam category-icon"></i>
                                </div>
                            </div>

                            {{-- Corpo --}}
                            <div class="card-body">
                                <div class="product-title">{{ ucwords($product['name']) }}</div>
                                <div class="price-area mt-2">
                                    <div class="flex flex-col gap-1 w-full">
                                        <span class="badge-price">
                                            <i class="bi bi-tag"></i> R$ {{ number_format($product['unit_cost'] ?? 0, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Overlay de edição --}}
                            <div class="ep-card-edit-overlay">
                                <div class="flex items-center justify-between gap-1">
                                    <label class="text-[9px] font-bold text-white/80 whitespace-nowrap">Qtd/venda:</label>
                                    <input type="number" min="1" value="{{ $product['quantity'] }}"
                                        wire:change="updateProductQuantity({{ $product['id'] }}, $event.target.value)"
                                        class="w-14 px-1.5 py-0.5 text-center text-[11px] rounded-md font-bold text-white bg-white/15 border border-white/25 focus:ring-1 focus:ring-purple-400 focus:border-purple-400">
                                    @if($publicationType === 'kit' && ($product['quantity'] ?? 1) > 0)
                                    <span class="text-[8px] text-amber-300 font-bold">= {{ floor(($product['stock_quantity'] ?? 0) / max(1, $product['quantity'])) }} vendas</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Resumo --}}
                    @php
                        $totalCost = array_sum(array_map(fn($p) => ($p['unit_cost'] ?? 0) * ($p['quantity'] ?? 1), $products));
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-2 px-2.5 py-1.5 rounded-xl border-2" style="background: var(--card-bg, #e6e6fa); border-color: var(--card-accent, #ffe0b2);">
                        <div class="flex items-center gap-2 text-[11px]">
                            <span style="color: var(--gray-700, #424242);"><strong>{{ count($products) }}</strong> produto(s)</span>
                            <span style="color: var(--card-border, #b39ddb);">·</span>
                            <span style="color: var(--gray-700, #424242);">Custo: <strong style="color: var(--primary-dark, #512da8);">R$ {{ number_format($totalCost, 2, ',', '.') }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1 text-[11px] font-bold" style="color: var(--primary-dark, #512da8);">
                            <i class="bi bi-box-seam"></i>
                            {{ $availableQuantity }} un. disponíveis
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-2" style="background: var(--card-accent, #ffe0b2);">
                            <i class="bi bi-box-seam text-xl" style="color: var(--primary, #9575cd);"></i>
                        </div>
                        <p class="text-xs font-bold" style="color: var(--gray-700, #424242);">Nenhum produto vinculado</p>
                        <p class="text-[10px]" style="color: var(--primary, #9575cd);">Clique em "Adicionar" para vincular produtos</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ▸ IMAGENS ◂ --}}
            <div class="ep-zone-imagens ep-card">
                <div class="ep-card-head" style="background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(149,117,205,0.06));">
                    <div class="ep-icon bg-gradient-to-br from-sky-400 to-purple-400 shadow-md shadow-sky-500/20">
                        <i class="bi bi-images"></i>
                    </div>
                    <span class="ep-title">Imagens do Anúncio</span>
                    @if(!empty($pictures))
                    <span class="ep-badge border-sky-300 dark:border-sky-700" style="background: var(--card-accent2, #f8bbd0); color: var(--primary-dark, #512da8);">{{ count($pictures) }}</span>
                    @endif
                </div>
                <div class="ep-card-body">
                    @if(!empty($pictures))
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
                        @foreach($pictures as $pic)
                        <div class="aspect-square rounded-lg overflow-hidden border-2 hover:shadow-lg group cursor-pointer transition-all" style="border-color: var(--card-border, #b39ddb);">
                            <img src="{{ is_array($pic) ? ($pic['secure_url'] ?? $pic['url'] ?? '') : $pic }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                onerror="this.parentElement.style.display='none'">
                        </div>
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
            <div class="ep-zone-atributos ep-card">
                <div class="ep-card-head" style="background: linear-gradient(135deg, rgba(249,115,22,0.08), rgba(248,187,208,0.06));">
                    <div class="ep-icon bg-gradient-to-br from-orange-400 to-pink-500 shadow-md shadow-orange-500/20">
                        <i class="bi bi-list-columns-reverse"></i>
                    </div>
                    <span class="ep-title">Atributos do Mercado Livre</span>
                    @if(!empty($mlAttributes))
                    <span class="ep-badge border-orange-300 dark:border-orange-700" style="background: var(--card-accent, #ffe0b2); color: var(--primary-dark, #512da8);">{{ count($mlAttributes) }}</span>
                    @endif
                </div>
                <div class="ep-card-body">
                    @if(!empty($mlAttributes) && is_array($mlAttributes))
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1 max-h-[260px] overflow-y-auto ep-scroll">
                        @foreach($mlAttributes as $attr)
                        @php
                            $attrName = '—';
                            $attrValue = '—';
                            if (is_array($attr)) {
                                $attrName = $attr['name'] ?? $attr['id'] ?? '—';
                                $attrValue = $attr['value_name'] ?? $attr['value_struct']['number'] ?? $attr['value_id'] ?? '—';
                                if ($attrValue === null || $attrValue === '') $attrValue = '—';
                            } elseif (is_string($attr)) {
                                $attrName = $attr;
                            }
                        @endphp
                        @if($attrValue !== '—' || $attrName !== '—')
                        <div class="ep-attr-item">
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

        </div>{{-- /ep-grid --}}

        {{-- ═════════════════════════════════════════════════════════ --}}
        {{-- BARRA DE SALVAR                                          --}}
        {{-- ═════════════════════════════════════════════════════════ --}}
        <div class="sticky bottom-2 z-30 pointer-events-none">
            <div class="max-w-sm mx-auto pointer-events-auto">
                <div class="ep-save-bar flex items-center justify-between gap-2 px-3 py-2">
                    <div class="flex items-center gap-1.5 text-[11px]" style="color: var(--primary, #9575cd);">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span class="font-semibold">Salvar alterações</span>
                    </div>
                    <button wire:click="updatePublication" wire:loading.attr="disabled" wire:target="updatePublication"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold shadow-lg transition-all disabled:opacity-50 text-white"
                        style="background: linear-gradient(90deg, var(--primary, #9575cd), var(--primary-dark, #512da8)); box-shadow: 0 4px 16px var(--shadow-strong, rgba(81,45,168,0.18));">
                        <i class="bi bi-check-lg" wire:loading.remove wire:target="updatePublication"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="updatePublication"></i>
                        Salvar e enviar ao ML
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>