<div class="w-full app-viewport-fit mobile-393-base publications-page" x-data="{ filterOpen: false, tipsOpen: false }">

    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ultrawide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-carousel.css') }}">

    {{-- ═══════════════════════════════════════════════════════════
         HEADER ESTILO SALES-INDEX (search, filtros, paginação)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-purple-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-indigo-400/20 to-blue-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

        <div class="relative px-4 py-3 lg:px-5 lg:py-4">
            {{-- Primeira Linha: Título + Badges + Controles --}}
            <div class="publications-header-row-1 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 xl:gap-6">
                {{-- Esquerda: Ícone + Título + Stats --}}
                <div class="publications-header-left flex items-start sm:items-center gap-3 sm:gap-5 min-w-0">
                    <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-500 rounded-2xl shadow-xl shadow-purple-500/25">
                        <i class="bi bi-list-check text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                    </div>

                    <div class="space-y-1.5 min-w-0 flex-1">
                        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <a href="{{ route('mercadolivre.products') }}" class="hover:text-purple-600 transition-colors">Mercado Livre</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">Publicações</span>
                        </nav>
                        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 dark:from-purple-300 dark:via-indigo-300 dark:to-blue-300 bg-clip-text text-transparent truncate">
                            Publicações ML
                        </h1>

                        <div class="publications-header-stats hidden md:flex items-center gap-2 lg:gap-3 flex-wrap">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-xs"></i>
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $stats['total'] }} publicações</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-lg border border-emerald-200 dark:border-emerald-700">
                                <i class="bi bi-check-circle text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $stats['active'] }} ativas</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg border border-purple-200 dark:border-purple-700">
                                <i class="bi bi-boxes text-purple-600 dark:text-purple-400 text-xs"></i>
                                <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">{{ $stats['kits'] }} kits</span>
                            </div>
                            @if($stats['errors'] > 0)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-red-500/20 to-rose-500/20 rounded-lg border border-red-200 dark:border-red-700">
                                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-xs"></i>
                                <span class="text-xs font-semibold text-red-700 dark:text-red-300">{{ $stats['errors'] }} erros</span>
                            </div>
                            @endif
                            @if(($stats['only_on_ml'] ?? 0) > 0)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-amber-500/20 to-orange-500/20 rounded-lg border border-amber-200 dark:border-amber-700">
                                <i class="bi bi-cloud-download text-amber-600 dark:text-amber-400 text-xs"></i>
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $stats['only_on_ml'] }} só no ML</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Direita: Controles em 2 linhas (estilo produto index) --}}
                <div class="publications-header-controls flex flex-col gap-2 w-full xl:w-auto">

                    {{-- ── LINHA 1: Pesquisa + Nova Publicação ─────────────────── --}}
                    <div class="flex items-center gap-2 w-full">
                        {{-- Campo de Pesquisa --}}
                        <div class="relative group flex-1 xl:flex-none">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar publicações..."
                                class="w-full xl:w-64 pl-9 pr-8 py-2.5 bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500/40 focus:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium backdrop-blur-sm">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                            </div>
                            @if($search)
                            <button wire:click="$set('search', '')"
                                class="absolute right-2.5 top-1/2 transform -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                                <i class="bi bi-x text-sm"></i>
                            </button>
                            @endif
                            <div wire:loading.delay wire:target="search" class="absolute right-9 top-1/2 transform -translate-y-1/2">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                            </div>
                        </div>

                        {{-- Botão Nova Publicação --}}
                        <a href="{{ route('mercadolivre.products') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 text-sm whitespace-nowrap">
                            <i class="bi bi-plus-circle"></i>
                            <span class="hidden sm:inline">Nova Publicação</span>
                        </a>
                    </div>

                    {{-- ── LINHA 2: Pills de filtro + Paginação + Ações ──────────── --}}
                    <div class="flex items-center gap-2 flex-wrap justify-start xl:justify-end">

                        {{-- Lado Esquerdo: pills de status, tipo, sync --}}
                        <div class="flex items-center gap-1.5 flex-wrap">

                            {{-- Status pills --}}
                            <div class="sale-filter-pills hidden md:inline-flex">
                                <button type="button" wire:click="$set('statusFilter', 'all')"
                                    class="sale-filter-pill {{ $statusFilter === 'all' ? 'active' : '' }}" title="Todos os status">
                                    <i class="bi bi-list-check"></i>
                                    <span>Todos</span>
                                </button>
                                <button type="button" wire:click="$set('statusFilter', 'active')"
                                    class="sale-filter-pill pill-success {{ $statusFilter === 'active' ? 'active' : '' }}" title="Apenas ativas">
                                    <i class="bi bi-check-circle"></i>
                                    <span>Ativas</span>
                                </button>
                                <button type="button" wire:click="$set('statusFilter', 'paused')"
                                    class="sale-filter-pill pill-warning {{ $statusFilter === 'paused' ? 'active' : '' }}" title="Apenas pausadas">
                                    <i class="bi bi-pause-circle"></i>
                                    <span>Pausadas</span>
                                </button>
                            </div>

                            {{-- Tipo pills --}}
                            <div class="sale-filter-pills hidden md:inline-flex">
                                <button type="button" wire:click="$set('typeFilter', 'all')"
                                    class="sale-filter-pill {{ $typeFilter === 'all' ? 'active' : '' }}" title="Todos os tipos">
                                    <i class="bi bi-box-seam"></i>
                                    <span>Tipos</span>
                                </button>
                                <button type="button" wire:click="$set('typeFilter', 'simple')"
                                    class="sale-filter-pill {{ $typeFilter === 'simple' ? 'active' : '' }}" title="Simples">
                                    <i class="bi bi-box"></i>
                                    <span>Simples</span>
                                </button>
                                <button type="button" wire:click="$set('typeFilter', 'kit')"
                                    class="sale-filter-pill {{ $typeFilter === 'kit' ? 'active' : '' }}" title="Kits">
                                    <i class="bi bi-boxes"></i>
                                    <span>Kits</span>
                                </button>
                            </div>

                            {{-- Sync pills --}}
                            <div class="sale-filter-pills hidden lg:inline-flex">
                                <button type="button" wire:click="$set('syncFilter', 'all')"
                                    class="sale-filter-pill {{ $syncFilter === 'all' ? 'active' : '' }}" title="Todos sync">
                                    <i class="bi bi-arrow-repeat"></i>
                                    <span>Sync</span>
                                </button>
                                <button type="button" wire:click="$set('syncFilter', 'synced')"
                                    class="sale-filter-pill pill-success {{ $syncFilter === 'synced' ? 'active' : '' }}" title="Sincronizados">
                                    <span>OK</span>
                                </button>
                                <button type="button" wire:click="$set('syncFilter', 'pending')"
                                    class="sale-filter-pill pill-warning {{ $syncFilter === 'pending' ? 'active' : '' }}" title="Pendentes">
                                    <span>Pendente</span>
                                </button>
                                <button type="button" wire:click="$set('syncFilter', 'error')"
                                    class="sale-filter-pill {{ $syncFilter === 'error' ? 'active' : '' }}" style="{{ $syncFilter === 'error' ? 'background: #ef4444; color: #fff;' : '' }}" title="Com erro">
                                    <span>Erro</span>
                                </button>
                            </div>
                        </div>

                        {{-- Paginação Compacta --}}
                        @if($publications->hasPages())
                        <div class="sale-pagination-compact">
                            @if($publications->currentPage() > 1)
                            <button wire:click.prevent="previousPage" class="sale-pagination-btn" title="Página anterior">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            @endif
                            <span class="sale-pagination-indicator">{{ $publications->currentPage() }} / {{ $publications->lastPage() }}</span>
                            @if($publications->hasMorePages())
                            <button wire:click.prevent="nextPage" class="sale-pagination-btn" title="Próxima página">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            @endif
                        </div>
                        @endif

                        {{-- Toggle Visualização --}}
                        <div class="sale-pagination-compact">
                            <button wire:click="$set('viewMode', 'cards')"
                                    title="Cards"
                                    class="sale-pagination-btn {{ $viewMode === 'cards' ? 'bg-purple-500 text-white' : '' }}">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button wire:click="$set('viewMode', 'table')"
                                    title="Tabela"
                                    class="sale-pagination-btn {{ $viewMode === 'table' ? 'bg-purple-500 text-white' : '' }}">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>

                        {{-- Botão Filtros Avançados --}}
                        <button @click="filterOpen = true"
                                class="sale-action-btn" title="Filtros avançados">
                            <i class="bi bi-funnel"></i>
                            <span class="hidden sm:inline">Filtros</span>
                            @if($statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-purple-500 rounded-full border-2 border-white dark:border-slate-800"></span>
                            @endif
                        </button>

                        {{-- Botão Dicas --}}
                        <button @click="tipsOpen = true"
                                class="sale-action-btn" title="Dicas de publicação">
                            <i class="bi bi-lightbulb"></i>
                            <span class="hidden sm:inline">Dicas</span>
                        </button>

                        {{-- Botão Configurações --}}
                        <a href="{{ route('mercadolivre.settings') }}"
                           class="sale-action-btn" title="Configurações ML">
                            <i class="bi bi-gear"></i>
                            <span class="hidden sm:inline">Config</span>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         INDICADOR DE SINCRONIZAÇÃO AUTOMÁTICA (INLINE)
    ═══════════════════════════════════════════════ --}}
    @if($isSyncing)
    <div class="mb-4 rounded-xl overflow-hidden border-2 border-blue-400 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-950/30 dark:to-cyan-950/30">
        <div class="p-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                        <i class="bi bi-arrow-repeat text-white animate-spin"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-blue-700 dark:text-blue-300">🔄 Sincronizando com o Mercado Livre...</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $syncedCount }} de {{ $totalToSync }} publicações sincronizadas</p>
                        @if($totalToSync > 0)
                        <div class="mt-1.5 w-full bg-blue-200 dark:bg-blue-900 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-500"
                                 style="width: {{ ($syncedCount / $totalToSync) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
                

            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════
         CONTEÚDO PRINCIPAL - GRID DE PUBLICAÇÕES
    ═══════════════════════════════════════════════ --}}
    <div class="pb-6">
        
        @if($viewMode === 'cards')
            {{-- GRID DE PUBLICAÇÕES - 5 colunas --}}
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 gap-6">
                @forelse($publications as $publication)
                    @php
                        $statusConfig = match($publication->status) {
                            'active' => ['color' => 'from-green-500 to-green-600', 'icon' => 'check-circle-fill', 'text' => 'Ativo'],
                            'paused' => ['color' => 'from-amber-500 to-amber-600', 'icon' => 'pause-circle-fill', 'text' => 'Pausado'],
                            'closed' => ['color' => 'from-red-500 to-red-600', 'icon' => 'x-circle-fill', 'text' => 'Fechado'],
                            'under_review' => ['color' => 'from-purple-500 to-purple-600', 'icon' => 'search', 'text' => 'Em Revisão'],
                            default => ['color' => 'from-slate-500 to-slate-600', 'icon' => 'question-circle', 'text' => ucfirst($publication->status)],
                        };
                        
                        $syncConfig = match($publication->sync_status) {
                            'synced' => ['color' => 'from-blue-500 to-blue-600', 'icon' => 'arrow-repeat', 'text' => 'Sincronizado'],
                            'pending' => ['color' => 'from-yellow-500 to-yellow-600', 'icon' => 'clock', 'text' => 'Pendente'],
                            'error' => ['color' => 'from-red-500 to-red-600', 'icon' => 'exclamation-triangle', 'text' => 'Erro'],
                            default => ['color' => 'from-slate-500 to-slate-600', 'icon' => 'question-circle', 'text' => 'Desconhecido'],
                        };

                        $availableQty = $publication->calculateAvailableQuantity();
                        
                        // Usar imagens do catálogo ML (pictures) ou fallback para imagem do produto
                        $catalogImages = $publication->pictures ?? [];
                        $mainImage = !empty($catalogImages) ? $catalogImages[0] : null;
                        
                        if (!$mainImage && $publication->products->first()) {
                            $firstProduct = $publication->products->first();
                            if ($firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
                                $mainImage = asset('storage/' . $firstProduct->image);
                            }
                        }
                    @endphp

                    {{-- ═════════════════════════════════════════════════════
                         PUBLICATION CARD: carousel de product cards
                         (cada produto aparece como card idêntico ao products-index,
                          um por vez com navegação por setas e dots)
                    ═════════════════════════════════════════════════════ --}}
                    <div class="product-card-modern pub-publication-card"
                         wire:key="pub-{{ $publication->id }}"
                         x-data="{ slide: 0, count: {{ $publication->products->count() }} }">

                        {{-- ══ BOTÕES DE AÇÃO DA PUBLICAÇÃO (flutuantes, z-index alto) ══ --}}
                        <div class="btn-action-group">
                            @if($publication->ml_permalink)
                                <a href="{{ $publication->ml_permalink }}" target="_blank" rel="noopener"
                                   title="Ver no Mercado Livre"
                                   class="btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; border: 2px solid #fcd34d;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                            <a href="{{ route('mercadolivre.publications.show', $publication->id) }}"
                               title="Ver Publicação" class="btn btn-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($publication->ml_item_id)
                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}"
                                   title="Editar Publicação" class="btn btn-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button wire:click="syncPublication({{ $publication->id }})"
                                        wire:loading.attr="disabled"
                                        title="Sincronizar com ML"
                                        class="btn" style="background: #3b82f6; color: #fff; border: 2px solid #93c5fd;">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                @if($publication->status === 'active')
                                    <button wire:click="pausePublication({{ $publication->id }})"
                                            wire:confirm="Pausar esta publicação?"
                                            title="Pausar Publicação"
                                            class="btn" style="background: #f59e0b; color: #fff; border: 2px solid #fcd34d;">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                @elseif($publication->status === 'paused')
                                    <button wire:click="activatePublication({{ $publication->id }})"
                                            title="Ativar Publicação"
                                            class="btn" style="background: #10b981; color: #fff; border: 2px solid #6ee7b7;">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()?->id) }}"
                                   title="Publicar no Mercado Livre"
                                   class="btn btn-success" style="animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite;">
                                    <i class="bi bi-upload"></i>
                                </a>
                            @endif
                            <button wire:click="deletePublication({{ $publication->id }})"
                                    wire:confirm="Tem certeza que deseja excluir esta publicação?"
                                    title="Excluir Publicação"
                                    class="btn btn-danger">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>

                        {{-- ══ CARROSSEL DE CARDS DE PRODUTO (estilo products-index) ══ --}}
                        <div class="pub-product-carousel">

                            {{-- Slides: um card completo por produto --}}
                            @foreach($publication->products as $idx => $product)
                                <div x-show="slide === {{ $idx }}"
                                     x-transition:enter="transition ease-in-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="pub-product-slide">

                                    {{-- ── Área de imagem (idêntica ao products-index) ────── --}}
                                    <div class="product-img-area pub-img-area">
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <img src="{{ asset('storage/products/' . $product->image) }}"
                                                 class="product-img"
                                                 alt="{{ $product->name }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center"
                                                 style="background: var(--card-bg);">
                                                <i class="bi bi-image text-5xl" style="color: var(--card-border);"></i>
                                            </div>
                                        @endif

                                        {{-- Fora de estoque do produto --}}
                                        @if($product->stock_quantity == 0)
                                            <div class="out-of-stock">
                                                <i class="bi bi-x-circle"></i> Fora de Estoque
                                            </div>
                                        @endif

                                        {{-- Overlay "Não Publicado" (publicação ainda não enviada ao ML) --}}
                                        @if(!$publication->ml_item_id)
                                            <div class="out-of-stock"
                                                 style="background: linear-gradient(90deg, rgba(245,158,11,0.95), rgba(217,119,6,0.95)); transform: translateY(-50%) rotate(-12deg); z-index: 12;">
                                                <i class="bi bi-clock-history"></i> Não Publicado
                                            </div>
                                        @endif

                                        {{-- Badge código do produto (top-left) --}}
                                        <span class="badge-product-code"
                                              title="Código: {{ $product->product_code }}">
                                            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                        </span>

                                        {{-- Badge estoque do produto (bottom-right) --}}
                                        <span class="badge-quantity"
                                              title="Estoque: {{ $product->stock_quantity }} unidades">
                                            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                        </span>

                                        {{-- Ícone da categoria (bottom-center, idêntico ao products-index) --}}
                                        <div class="category-icon-wrapper"
                                             title="{{ $product->category?->name ?? 'Categoria' }}">
                                            <i class="{{ $product->category?->icone ?? 'bi bi-box' }} category-icon"></i>
                                        </div>
                                    </div>{{-- /product-img-area --}}

                                    {{-- ── Conteúdo do produto (idêntico ao products-index) ── --}}
                                    <div class="card-body">
                                        <div class="product-title" title="{{ $product->name }}">
                                            {{ ucwords($product->name) }}
                                        </div>

                                        {{-- Status + badge Novo (igual products-index) --}}
                                        <div class="mt-1 mb-1 flex items-center gap-1.5 flex-wrap justify-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                                bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-100
                                                text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-800
                                                dark:bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-900
                                                dark:text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-200">
                                                <i class="bi bi-circle-fill mr-0.5" style="font-size:6px;"></i>
                                                {{ ucfirst($product->status) }}
                                            </span>
                                            @if(\Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    <i class="bi bi-stars mr-0.5"></i> Novo
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Preços do produto (idêntico ao products-index) --}}
                                        <div class="price-area mt-1">
                                            <div class="flex flex-col gap-1">
                                                <span class="badge-price" title="Preço de Custo">
                                                    <i class="bi bi-tag"></i>
                                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                                </span>
                                                <span class="badge-price-sale" title="Preço de Venda">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>{{-- /card-body produto --}}

                                </div>{{-- /pub-product-slide --}}
                            @endforeach

                            {{-- Setas de navegação (só quando há mais de 1 produto) --}}
                            @if($publication->products->count() > 1)
                                <button x-on:click.prevent="slide = (slide - 1 + count) % count"
                                        class="pub-carousel-arrow pub-carousel-arrow-left"
                                        title="Produto anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button x-on:click.prevent="slide = (slide + 1) % count"
                                        class="pub-carousel-arrow pub-carousel-arrow-right"
                                        title="Próximo produto">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @endif

                        </div>{{-- /pub-product-carousel --}}

                        {{-- Dots indicadores + contador (só quando há mais de 1 produto) --}}
                        @if($publication->products->count() > 1)
                            <div class="pub-carousel-nav">
                                @foreach($publication->products as $idx => $product)
                                    <button x-on:click.prevent="slide = {{ $idx }}"
                                            :class="['pub-carousel-dot', slide === {{ $idx }} ? 'pub-carousel-dot-active' : '']"
                                            title="{{ $product->name }}"></button>
                                @endforeach
                                <span class="pub-carousel-counter"
                                      x-text="(slide + 1) + ' / ' + count"></span>
                            </div>
                        @endif

                        {{-- ══ FOOTER: DADOS DA PUBLICAÇÃO NO ML ══ --}}
                        <div class="pub-info-footer">

                            {{-- Título da publicação --}}
                            <div class="product-title" title="{{ $publication->title }}" style="font-size:0.82rem;">
                                <i class="bi bi-megaphone-fill"
                                   style="color:var(--card-accent); font-size:0.65rem; vertical-align:middle;"></i>
                                {{ $publication->title }}
                            </div>

                            {{-- ML ID + Tipo de publicação --}}
                            <div class="flex items-center gap-1 flex-wrap justify-center mt-1">
                                <span class="pub-ml-badge"
                                      title="{{ $publication->ml_item_id ? 'ID ML: '.$publication->ml_item_id : 'Aguardando publicação' }}">
                                    @if($publication->ml_item_id)
                                        <i class="bi bi-tag-fill"></i> {{ substr($publication->ml_item_id, 0, 10) }}
                                    @else
                                        <i class="bi bi-clock"></i> Pendente
                                    @endif
                                </span>
                                <span class="pub-type-badge {{ $publication->publication_type === 'kit' ? 'pub-type-kit' : 'pub-type-simple' }}">
                                    <i class="bi bi-{{ $publication->publication_type === 'kit' ? 'boxes' : 'box' }}"></i>
                                    {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                </span>
                            </div>

                            {{-- Status ML + Status Sync --}}
                            <div class="flex items-center gap-1 flex-wrap justify-center mt-1">
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-gradient-to-r {{ $statusConfig['color'] }} text-white shadow-sm">
                                    <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['text'] }}
                                </span>
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-gradient-to-r {{ $syncConfig['color'] }} text-white shadow-sm">
                                    <i class="bi bi-{{ $syncConfig['icon'] }}"></i>
                                    {{ $syncConfig['text'] }}
                                </span>
                            </div>

                            {{-- Mensagem de erro ML --}}
                            @if($publication->error_message)
                                <div class="mt-1 px-2 py-1 rounded-lg w-full"
                                     style="background: var(--card-danger); border: 1px solid #ef9a9a;">
                                    <p class="text-[9px] truncate text-center" style="color: #b71c1c;"
                                       title="{{ $publication->error_message }}">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        {{ \Illuminate\Support\Str::limit($publication->error_message, 35) }}
                                    </p>
                                </div>
                            @endif

                            {{-- Preço no ML + Qtd disponível --}}
                            <div class="price-area mt-1">
                                <span class="badge-price" title="Preço no Mercado Livre">
                                    <i class="bi bi-tag-fill"></i>
                                    R$ {{ number_format($publication->price, 2, ',', '.') }}
                                </span>
                                <span class="badge-price-sale" title="Quantidade disponível">
                                    <i class="bi bi-stack"></i> {{ $availableQty }}
                                </span>
                            </div>

                        </div>{{-- /pub-info-footer --}}

                    </div>{{-- /pub-publication-card --}}
            @empty
                {{-- Estado Vazio --}}
                <div class="col-span-full">
                    <div class="rounded-2xl bg-slate-100 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-inbox text-4xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                            @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                                Ajuste os filtros ou pesquise novamente
                            @else
                                Crie sua primeira publicação no Mercado Livre
                            @endif
                        </p>
                        <a href="{{ route('mercadolivre.products') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                            <i class="bi bi-plus-lg"></i> Nova Publicação
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
        
        @else
            {{-- VISUALIZAÇÃO EM TABELA --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/50 dark:to-indigo-950/50 border-b-2 border-purple-200 dark:border-purple-800">
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Publicação
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Produtos
                                </th>
                                <th class="px-4 py-4 text-right text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Preço
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Estoque
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($publications as $publication)
                                @php
                                    $statusConfig = match($publication->status) {
                                        'active' => ['color' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-green-300', 'icon' => 'check-circle-fill'],
                                        'paused' => ['color' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-300', 'icon' => 'pause-circle-fill'],
                                        'closed' => ['color' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-300', 'icon' => 'x-circle-fill'],
                                        'under_review' => ['color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 border-purple-300', 'icon' => 'search'],
                                        default => ['color' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300 border-slate-300', 'icon' => 'question-circle'],
                                    };
                                    
                                    $syncConfig = match($publication->sync_status) {
                                        'synced' => ['color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-300', 'icon' => 'arrow-repeat'],
                                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-yellow-300', 'icon' => 'clock'],
                                        'error' => ['color' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-300', 'icon' => 'exclamation-triangle'],
                                        default => ['color' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300 border-slate-300', 'icon' => 'question-circle'],
                                    };

                                    $availableQty = $publication->calculateAvailableQuantity();
                                    $catalogImages = $publication->pictures ?? [];
                                    $mainImage = !empty($catalogImages) ? $catalogImages[0] : null;
                                    
                                    if (!$mainImage && $publication->products->first()) {
                                        $firstProduct = $publication->products->first();
                                        if ($firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
                                            $mainImage = asset('storage/products/' . $firstProduct->image);
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    {{-- Publicação (com imagem e título) --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shadow-md">
                                                @if($mainImage)
                                                    <img src="{{ is_array($mainImage) ? ($mainImage['url'] ?? $mainImage['secure_url'] ?? '') : $mainImage }}" 
                                                         alt="{{ $publication->title }}"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="bi bi-image text-2xl text-slate-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 line-clamp-2 mb-1">
                                                    {{ $publication->title }}
                                                </h4>
                                                @if($publication->ml_item_id)
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                        <i class="bi bi-tag-fill"></i> {{ $publication->ml_item_id }}
                                                    </p>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-semibold">
                                                        <i class="bi bi-clock"></i> Não publicado
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    {{-- Status --}}
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $statusConfig['color'] }} text-xs font-semibold border w-fit">
                                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                                {{ ucfirst($publication->status) }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $syncConfig['color'] }} text-xs font-semibold border w-fit">
                                                <i class="bi bi-{{ $syncConfig['icon'] }}"></i>
                                                {{ $publication->sync_status === 'synced' ? 'Sincronizado' : ($publication->sync_status === 'error' ? 'Erro' : 'Pendente') }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    {{-- Tipo --}}
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $publication->publication_type === 'kit' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            <i class="bi bi-{{ $publication->publication_type === 'kit' ? 'boxes' : 'box' }}"></i>
                                            {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                        </span>
                                    </td>
                                    
                                    {{-- Produtos --}}
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($publication->products->take(2) as $product)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300" title="{{ $product->name }}">
                                                    <i class="bi bi-box"></i>
                                                    {{ \Illuminate\Support\Str::limit($product->name, 20) }}
                                                </span>
                                            @endforeach
                                            @if($publication->products->count() > 2)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-bold">
                                                    +{{ $publication->products->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    {{-- Preço --}}
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-lg font-black text-green-600 dark:text-green-400">
                                            R$ {{ number_format($publication->price, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    
                                    {{-- Estoque --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold">
                                            <i class="bi bi-stack"></i>
                                            {{ $availableQty }}
                                        </span>
                                    </td>
                                    
                                    {{-- Ações --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            @if($publication->ml_permalink)
                                                <a href="{{ $publication->ml_permalink }}" 
                                                   target="_blank"
                                                   title="Ver no ML"
                                                   class="p-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                            
                                            {{-- Ver Publicação (sempre visível) --}}
                                            <a href="{{ route('mercadolivre.publications.show', $publication->id) }}" 
                                               title="Ver Publicação"
                                               class="p-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-all">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            @if($publication->ml_item_id)
                                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}" 
                                                   title="Editar"
                                                   class="p-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button wire:click="syncPublication({{ $publication->id }})" 
                                                        title="Sincronizar"
                                                        class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()->id) }}" 
                                                   title="Publicar"
                                                   class="p-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-upload"></i>
                                                </a>
                                            @endif
                                            
                                            <button wire:click="deletePublication({{ $publication->id }})" 
                                                    wire:confirm="Excluir esta publicação?"
                                                    title="Excluir"
                                                    class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12">
                                        <div class="text-center">
                                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                                                <i class="bi bi-inbox text-4xl text-slate-400"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                                @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                                                    Ajuste os filtros ou pesquise novamente
                                                @else
                                                    Crie sua primeira publicação no Mercado Livre
                                                @endif
                                            </p>
                                            <a href="{{ route('mercadolivre.products') }}"
                                               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                                                <i class="bi bi-plus-lg"></i> Nova Publicação
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Paginação Completa (footer) --}}
        @if($publications->hasPages())
        <div class="mt-8">
            {{ $publications->links() }}
        </div>
        @endif

        {{-- Outras publicações no ML (não criadas pelo sistema) --}}
        @if($onlyOnMlItems->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-cloud text-amber-500"></i>
                Outras publicações no Mercado Livre
                <span class="text-sm font-normal text-slate-500 dark:text-slate-400">(criadas direto no ML — importe para editar aqui)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($onlyOnMlItems as $item)
                    <div class="rounded-2xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/30 p-4 shadow-lg">
                        @if(!empty($item['thumbnail']))
                            <img src="{{ $item['thumbnail'] }}" alt="" class="w-full h-40 object-contain rounded-xl bg-white dark:bg-slate-800 mb-3">
                        @else
                            <div class="w-full h-40 rounded-xl bg-slate-200 dark:bg-slate-700 flex items-center justify-center mb-3">
                                <i class="bi bi-image text-3xl text-slate-400"></i>
                            </div>
                        @endif
                        <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm line-clamp-2 mb-2" title="{{ $item['title'] }}">{{ $item['title'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mb-3">{{ $item['id'] }}</p>
                        <div class="flex items-center gap-2">
                            @if(!empty($item['permalink']))
                                <a href="{{ $item['permalink'] }}" target="_blank" rel="noopener"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-all">
                                    <i class="bi bi-box-arrow-up-right"></i> Ver no ML
                                </a>
                            @endif
                            <button wire:click="importFromMl('{{ $item['id'] ?? '' }}')" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition-all disabled:opacity-50">
                                <i class="bi bi-download" wire:loading.remove wire:target="importFromMl"></i>
                                <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="importFromMl"></i>
                                Importar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL DE FILTROS AVANÇADOS
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="filterOpen"
         x-cloak
         class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center"
         @keydown.escape.window="filterOpen = false">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"
             @click="filterOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Painel do Modal --}}
        <div class="relative w-full sm:w-auto sm:min-w-[500px] sm:max-w-lg mx-0 sm:mx-4 bg-white/98 dark:bg-slate-900/98 backdrop-blur-xl rounded-t-3xl sm:rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0">

            {{-- Decoração topo (mobile handle) --}}
            <div class="sm:hidden mx-auto mt-3 mb-1 w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-purple-50/80 to-indigo-50/80 dark:from-purple-950/30 dark:to-indigo-950/30">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg shadow-purple-500/20">
                        <i class="bi bi-funnel-fill text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Filtros Avançados</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Filtre suas publicações</p>
                    </div>
                </div>
                <button @click="filterOpen = false"
                        class="flex items-center justify-center w-9 h-9 rounded-xl hover:bg-slate-200/80 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all duration-200">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5 max-h-[60vh] sm:max-h-none overflow-y-auto">

                {{-- Status --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-3">
                        <i class="bi bi-circle-half text-purple-500"></i> Status da Publicação
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['value' => 'all',          'label' => 'Todos',     'icon' => 'list-check',    'color' => 'purple'],
                            ['value' => 'active',       'label' => 'Ativas',    'icon' => 'check-circle',  'color' => 'emerald'],
                            ['value' => 'paused',       'label' => 'Pausadas',  'icon' => 'pause-circle',  'color' => 'amber'],
                            ['value' => 'closed',       'label' => 'Fechadas',  'icon' => 'x-circle',      'color' => 'red'],
                            ['value' => 'under_review', 'label' => 'Em Revisão','icon' => 'search',        'color' => 'violet'],
                        ] as $opt)
                        <button wire:click="$set('statusFilter', '{{ $opt['value'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold transition-all duration-200 border-2
                                       {{ $statusFilter === $opt['value']
                                          ? 'bg-'.$opt['color'].'-500 border-'.$opt['color'].'-500 text-white shadow-md'
                                          : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-'.$opt['color'].'-300' }}">
                            <i class="bi bi-{{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-3">
                        <i class="bi bi-boxes text-blue-500"></i> Tipo de Publicação
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['value' => 'all',    'label' => 'Todos',   'icon' => 'box-seam', 'color' => 'blue'],
                            ['value' => 'simple', 'label' => 'Simples', 'icon' => 'box',      'color' => 'blue'],
                            ['value' => 'kit',    'label' => 'Kits',    'icon' => 'boxes',    'color' => 'purple'],
                        ] as $opt)
                        <button wire:click="$set('typeFilter', '{{ $opt['value'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold transition-all duration-200 border-2
                                       {{ $typeFilter === $opt['value']
                                          ? 'bg-'.$opt['color'].'-500 border-'.$opt['color'].'-500 text-white shadow-md'
                                          : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-'.$opt['color'].'-300' }}">
                            <i class="bi bi-{{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Sincronização --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-3">
                        <i class="bi bi-arrow-repeat text-teal-500"></i> Status de Sincronização
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['value' => 'all',     'label' => 'Todos',        'icon' => 'arrow-repeat',        'color' => 'teal'],
                            ['value' => 'synced',  'label' => 'Sincronizados','icon' => 'check-circle',        'color' => 'emerald'],
                            ['value' => 'pending', 'label' => 'Pendentes',    'icon' => 'clock',               'color' => 'amber'],
                            ['value' => 'error',   'label' => 'Com Erros',    'icon' => 'exclamation-triangle','color' => 'red'],
                        ] as $opt)
                        <button wire:click="$set('syncFilter', '{{ $opt['value'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold transition-all duration-200 border-2
                                       {{ $syncFilter === $opt['value']
                                          ? 'bg-'.$opt['color'].'-500 border-'.$opt['color'].'-500 text-white shadow-md'
                                          : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-'.$opt['color'].'-300' }}">
                            <i class="bi bi-{{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Resumo dos filtros ativos --}}
                @if($statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all' || $search)
                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800">
                    <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 mb-1.5">
                        <i class="bi bi-funnel-fill mr-1"></i> Filtros ativos:
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        @if($search)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 text-xs font-medium">
                                <i class="bi bi-search"></i> "{{ $search }}"
                            </span>
                        @endif
                        @if($statusFilter !== 'all')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 text-xs font-medium">
                                Status: {{ $statusFilter }}
                            </span>
                        @endif
                        @if($typeFilter !== 'all')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 text-xs font-medium">
                                Tipo: {{ $typeFilter }}
                            </span>
                        @endif
                        @if($syncFilter !== 'all')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 text-xs font-medium">
                                Sync: {{ $syncFilter }}
                            </span>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="px-6 pb-6 pt-4 flex items-center justify-between gap-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button wire:click="clearFilters" @click="filterOpen = false"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold transition-all text-sm">
                    <i class="bi bi-x-circle"></i> Limpar Tudo
                </button>
                <button @click="filterOpen = false"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all text-sm">
                    <i class="bi bi-check-lg"></i> Aplicar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL DE DICAS DE PUBLICAÇÃO
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="tipsOpen"
         x-cloak
         class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center"
         @keydown.escape.window="tipsOpen = false">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"
             @click="tipsOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Painel do Modal --}}
        <div class="relative w-full sm:w-auto sm:min-w-[540px] sm:max-w-xl mx-0 sm:mx-4 bg-white/98 dark:bg-slate-900/98 backdrop-blur-xl rounded-t-3xl sm:rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0">

            {{-- Mobile handle --}}
            <div class="sm:hidden mx-auto mt-3 mb-1 w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-amber-50/80 to-orange-50/80 dark:from-amber-950/30 dark:to-orange-950/30">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/20">
                        <i class="bi bi-lightbulb-fill text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Dicas de Publicação</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Melhores práticas no Mercado Livre</p>
                    </div>
                </div>
                <button @click="tipsOpen = false"
                        class="flex items-center justify-center w-9 h-9 rounded-xl hover:bg-amber-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all duration-200">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-3 max-h-[65vh] overflow-y-auto">

                {{-- Dica 1: Título --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-type-h1 text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-0.5">Título Otimizado</p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">Use 60–80 caracteres com palavras-chave relevantes. Inclua marca, modelo e características principais. Evite símbolos e maiúsculas excessivas.</p>
                    </div>
                </div>

                {{-- Dica 2: Imagens --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-purple-50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-purple-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-images text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-purple-800 dark:text-purple-300 mb-0.5">Imagens de Qualidade</p>
                        <p class="text-xs text-purple-700 dark:text-purple-400 leading-relaxed">Use no mínimo 6 fotos com fundo branco. Mínimo 500x500px. A primeira imagem é a mais importante — mostre o produto com clareza.</p>
                    </div>
                </div>

                {{-- Dica 3: Preço --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-currency-dollar text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300 mb-0.5">Preço Competitivo</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">Pesquise a concorrência antes de definir o preço. Lembre-se das taxas do ML (cerca de 11–16% para conta clássica). Ofereça frete grátis quando possível.</p>
                    </div>
                </div>

                {{-- Dica 4: Estoque --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-0.5">Estoque Atualizado</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">Mantenha o estoque sempre sincronizado para evitar vendas canceladas. Publicações pausadas por falta de estoque afetam a reputação do vendedor.</p>
                    </div>
                </div>

                {{-- Dica 5: Sincronização --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-teal-50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-teal-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-arrow-repeat text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-teal-800 dark:text-teal-300 mb-0.5">Sincronização Regular</p>
                        <p class="text-xs text-teal-700 dark:text-teal-400 leading-relaxed">Use o botão de sincronizar para atualizar preço e estoque no ML. Erros de sincronização ficam marcados em vermelho — resolva-os rapidamente.</p>
                    </div>
                </div>

                {{-- Dica 6: Kits --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-indigo-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-gift text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-indigo-800 dark:text-indigo-300 mb-0.5">Kits Aumentam o Ticket</p>
                        <p class="text-xs text-indigo-700 dark:text-indigo-400 leading-relaxed">Publicações do tipo "Kit" combinam produtos complementares. O estoque disponível é calculado automaticamente com base no produto de menor estoque do kit.</p>
                    </div>
                </div>

                {{-- Dica 7: Reputação --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-rose-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-star-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-rose-800 dark:text-rose-300 mb-0.5">Proteja sua Reputação</p>
                        <p class="text-xs text-rose-700 dark:text-rose-400 leading-relaxed">Responda perguntas em menos de 24h. Envie dentro do prazo. Reputação verde ou amarela garante maior visibilidade nos resultados do ML.</p>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-6 pb-5 pt-3 flex items-center justify-end border-t border-slate-200/60 dark:border-slate-700/60">
                <button @click="tipsOpen = false"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all text-sm">
                    <i class="bi bi-check-lg"></i> Entendido!
                </button>
            </div>
        </div>
    </div>

</div>
