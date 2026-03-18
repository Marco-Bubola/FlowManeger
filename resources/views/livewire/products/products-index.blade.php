<div class="w-full  app-viewport-fit products-index-page mobile-393-base" x-data="{
    showFilters: false,
    showQuickActions: false,
    isCompactModal: false,
    isPhone15: false,
    isIpad11: false,
    hasActiveFilters: {{ $search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque || $data_inicio || $data_fim ? 'true' : 'false' }},
    fullHd: false,
    ultra: false,
    openFiltersModal() {
        this.showFilters = true;
        document.documentElement.style.overflow = 'hidden';
    },
    closeFiltersModal() {
        this.showFilters = false;
        document.documentElement.style.overflow = '';
    },
    applyFiltersModal() {
        this.syncActiveFilters();
        this.closeFiltersModal();
    },
    async clearFiltersModal() {
        if ($wire && typeof $wire.clearFilters === 'function') {
            await $wire.clearFilters();
        }
        this.hasActiveFilters = false;
    },
    syncActiveFilters() {
        if (!$wire) {
            return;
        }

        const isFilled = (value) => {
            if (value === null || value === undefined) {
                return false;
            }

            if (typeof value === 'boolean') {
                return value;
            }

            const text = String(value).trim();
            return text !== '';
        };

        this.hasActiveFilters = [
            $wire.search,
            $wire.category,
            $wire.tipo,
            $wire.status_filtro,
            $wire.preco_min,
            $wire.preco_max,
            $wire.estoque,
            $wire.estoque_valor,
            $wire.data_inicio,
            $wire.data_fim,
            $wire.sem_imagem,
            $wire.semEstoque
        ].some(isFilled);
    },
    initResponsiveWatcher() {
        const mq = window.matchMedia('(min-width: 1920px)');
        const mqUltra = window.matchMedia('(min-width: 2498px)');
        const mqCompact = window.matchMedia('(max-width: 1024px)');
        const mqPhone15 = window.matchMedia('(max-width: 430px)');
        const mqIpad11 = window.matchMedia('(min-width: 768px) and (max-width: 1194px)');

        const sync = () => {
            this.fullHd = mq.matches;
            if ($wire) {
                $wire.set('fullHdLayout', mq.matches);
            }
        };

        const syncUltra = () => {
            this.ultra = mqUltra.matches;
            if ($wire) {
                $wire.set('ultraLayout', mqUltra.matches);
            }
        };

        const syncDevice = () => {
            this.isCompactModal = mqCompact.matches;
            this.isPhone15 = mqPhone15.matches;
            this.isIpad11 = mqIpad11.matches;
        };

        sync();
        syncUltra();
        syncDevice();

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', sync);
        } else {
            mq.addListener(sync);
        }

        if (typeof mqUltra.addEventListener === 'function') {
            mqUltra.addEventListener('change', syncUltra);
        } else {
            mqUltra.addListener(syncUltra);
        }

        if (typeof mqCompact.addEventListener === 'function') {
            mqCompact.addEventListener('change', syncDevice);
        } else {
            mqCompact.addListener(syncDevice);
        }

        if (typeof mqPhone15.addEventListener === 'function') {
            mqPhone15.addEventListener('change', syncDevice);
        } else {
            mqPhone15.addListener(syncDevice);
        }

        if (typeof mqIpad11.addEventListener === 'function') {
            mqIpad11.addEventListener('change', syncDevice);
        } else {
            mqIpad11.addListener(syncDevice);
        }
    }
}" x-init="initResponsiveWatcher()">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-header.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-mobile.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-iphone15.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ipad-portrait.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ipad-landscape.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-notebook.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ultrawide.css') }}">
    @endpush

    <!-- Header Modernizado (com slot para controles) -->
    <x-products-header title="Catálogo de Produtos" description="" :total-products="$products->total() ?? 0" :total-categories="$categories->count()"
        :show-quick-actions="false">

        <!-- Breadcrumb dentro do header -->
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    <i class="fas fa-box mr-1"></i>Produtos
                </span>
            </div>
        </x-slot>

        <!-- Barra de Controle integrada ao header (slot) -->
        <div class="w-full products-index-controls">

            <!-- ── LINHA 1: Busca + Novo Produto ─────────────────────── -->
            <div class="prod-header-row-1">
                <!-- Campo de Pesquisa -->
                <div class="prod-header-search relative group">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar produtos por nome, código, categoria..."
                        class="w-full pl-11 pr-10 py-2.5 bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500/40 focus:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium backdrop-blur-sm">
                    <div class="absolute left-3.5 top-1/2 transform -translate-y-1/2">
                        <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                    </div>
                    <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                        class="absolute right-2.5 top-1/2 transform -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                        <i class="bi bi-x text-sm"></i>
                    </button>
                    <div wire:loading.delay wire:target="search" class="absolute right-10 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                    </div>
                </div>

                <!-- Novo Produto (destaque) -->
                <a href="{{ route('products.create') }}" class="prod-header-btn-create group">
                    <i class="bi bi-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                    <span>Novo Produto</span>
                </a>
            </div>

            <!-- ── LINHA 2: Pills de filtro + Paginação + Ações ───────── -->
            <div class="prod-header-row-2">

                <!-- Lado Esquerdo: Filtros pill group -->
                <div class="prod-header-row-2-left">

                    <!-- Tipo pills -->
                    <div class="sale-filter-pills hidden md:inline-flex">
                        <button type="button" wire:click="$set('tipo', '')"
                            class="sale-filter-pill prod-pill-type {{ $tipo === '' ? 'active' : '' }}" title="Todos os tipos">
                            <i class="bi bi-box-seam"></i>
                            <span>Prods.</span>
                        </button>
                        <button type="button" wire:click="$set('tipo', 'kit')"
                            class="sale-filter-pill prod-pill-type {{ $tipo === 'kit' ? 'active' : '' }}" title="Somente kits">
                            <i class="bi bi-boxes"></i>
                            <span>Kits</span>
                        </button>
                    </div>

                    <!-- Ordenação -->
                    <div class="sale-filter-pills sale-sort-pills hidden md:flex">
                        <span class="sale-filter-pill-label"><i class="bi bi-arrow-down-up"></i><span>Ord.</span></span>
                        <button type="button" wire:click="toggleSort('data')"
                            class="sale-filter-pill {{ in_array($ordem, ['recentes','data_desc','data_asc']) ? 'active' : '' }}" title="Ordenar por data">
                            <span>Recentes</span>
                        </button>
                        <button type="button" wire:click="toggleSort('nome')"
                            class="sale-filter-pill {{ in_array($ordem, ['az','nome_asc','nome_desc']) ? 'active' : '' }}" title="Ordenar A-Z">
                            <span>A-Z</span>
                        </button>
                        <button type="button" wire:click="toggleSort('preco')"
                            class="sale-filter-pill {{ in_array($ordem, ['preco_desc','preco_asc']) ? 'active' : '' }}" title="Ordenar por preço">
                            <span>Preço</span>
                        </button>
                    </div>

                    <!-- Per-page pills -->
                    <div class="sale-filter-pills sale-perpage-pills hidden md:inline-flex">
                        @php $currentPerPage = $products->perPage(); @endphp
                        @foreach(array_slice($perPageOptions, 0, 5) as $pp)
                        <button type="button" wire:click="$set('perPage', {{ $pp }})"
                            class="sale-filter-pill pill-perpage {{ $currentPerPage == $pp ? 'active' : '' }}" title="{{ $pp }} por página">
                            <span>{{ $pp }}</span>
                        </button>
                        @endforeach
                    </div>

                </div>

                <!-- Lado Direito: Paginação compact + Ações -->
                <div class="prod-header-row-2-right">

                    @if ($products->hasPages())
                    <div class="sale-pagination-compact">
                        @if ($products->currentPage() > 1)
                        <button type="button" wire:click.prevent="previousPage" class="sale-pagination-btn" title="Página anterior">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        @endif
                        <span class="sale-pagination-indicator">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                        @if ($products->hasMorePages())
                        <button type="button" wire:click.prevent="nextPage" class="sale-pagination-btn" title="Próxima página">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        @endif
                    </div>
                    @endif

                    <button type="button" wire:click="toggleTips"
                        class="sale-action-btn sale-action-tips" title="Dicas">
                        <i class="bi bi-lightbulb"></i>
                    </button>

                    <button type="button" @click="openFiltersModal()"
                        class="sale-action-btn sale-action-filter"
                        :class="{ 'active': showFilters }" title="Filtros Avançados">
                        <i class="bi bi-sliders"></i>
                    </button>

                    <!-- Kit e Upload como ícones compactos -->
                    <a href="{{ route('products.kit.create') }}"
                        class="sale-action-btn prod-action-kit" title="Novo Kit">
                        <i class="bi bi-boxes"></i>
                    </a>

                    <a href="{{ route('products.upload') }}"
                        class="sale-action-btn prod-action-upload" title="Upload em Lote">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                    </a>

                </div>
            </div>
        </div>

    </x-products-header>



    <!-- Filtros em Modal (Todas as Telas) -->
    <div x-show="showFilters" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="closeFiltersModal()"
        class="fixed inset-0 z-[9999] products-mobile-filter-modal">
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-md" @click="closeFiltersModal()"></div>

        <div class="absolute inset-x-0 bottom-0 md:bottom-auto md:inset-0 md:flex md:items-center md:justify-center p-0 md:p-5">
            <div
                class="w-full md:max-w-3xl lg:max-w-4xl rounded-t-3xl md:rounded-3xl shadow-2xl border border-white/40 dark:border-slate-700/70 overflow-hidden h-[90dvh] md:h-auto md:max-h-[90vh] bg-white dark:bg-slate-900">
                <div class="pt-2 pb-1 md:hidden flex justify-center">
                    <span class="h-1.5 w-14 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                </div>

                <div class="relative px-4 md:px-6 py-4 border-b border-white/25 dark:border-slate-700/70 bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 relative z-10">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg shadow-indigo-500/25">
                                    <i class="bi bi-funnel"></i>
                                </span>
                                <h3 class="text-base md:text-lg font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">Filtros Avancados</h3>
                            </div>
                            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-300">Refine por categoria, tipo, status, preco, estoque e periodo.</p>
                        </div>

                        <button type="button" @click="closeFiltersModal()"
                            class="relative z-10 w-10 h-10 inline-flex items-center justify-center rounded-xl bg-white/80 hover:bg-white dark:bg-slate-800/90 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-sm">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="relative z-10 mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                            <i class="bi bi-phone mr-1"></i>Phone 15
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <i class="bi bi-tablet-landscape mr-1"></i>iPad 11 A16
                        </span>
                        <span x-show="hasActiveFilters" class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <i class="bi bi-check-circle mr-1"></i>Filtros ativos
                        </span>
                    </div>
                </div>

                <div class="overflow-y-auto px-3 md:px-5 py-3 md:py-4 max-h-[calc(90dvh-214px)] md:max-h-[calc(90vh-226px)] bg-gradient-to-b from-slate-50/80 to-white dark:from-slate-900/40 dark:to-slate-900">
                    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/85 backdrop-blur-sm p-2 md:p-3 shadow-sm">
                        <x-products-filters :categories="$categories" :search="$search" :category="$category" :tipo="$tipo" :status_filtro="$status_filtro"
                            :preco_min="$preco_min" :preco_max="$preco_max" :per-page="$perPage" :per-page-options="$perPageOptions" :ordem="$ordem" :estoque_filtro="$estoque ?? ''"
                            :estoque_valor="$estoque_valor ?? ''" :data_inicio="$data_inicio ?? ''" :data_fim="$data_fim ?? ''"
                            :sem_imagem="$sem_imagem ?? false" :total-products="$products->total() ?? 0" :sem-estoque="$semEstoque" />
                    </div>
                </div>

                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl sticky bottom-0">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-2 md:gap-3">
                        <button type="button" @click="closeFiltersModal()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </button>

                        <div class="flex items-center gap-2 md:gap-3 sm:ml-auto">
                            <button type="button" @click="clearFiltersModal()"
                                class="inline-flex items-center justify-center gap-2 px-3.5 md:px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-colors flex-1 sm:flex-none">
                            <i class="bi bi-eraser"></i>
                            Limpar
                            </button>
                            <button type="button" @click="applyFiltersModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 md:px-5 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 hover:from-indigo-700 hover:via-purple-700 hover:to-blue-700 text-white shadow-lg shadow-indigo-500/30 transition-all duration-200 flex-1 sm:flex-none">
                                <i class="bi bi-check2-circle"></i>
                                Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Controle removida daqui (integrada ao header via slot) -->

    <!-- Lista de Produtos -->
    @if ($products->isEmpty())
        <!-- Estado vazio aprimorado -->
        <div
            class="empty-state flex flex-col items-center justify-center py-20 bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-2xl border-2 border-dashed border-neutral-300 dark:border-neutral-600">
            <div class="relative">
                <!-- Ícone animado -->
                <div class="w-32 h-32 mx-auto mb-6 text-neutral-400 relative">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 dark:from-purple-800 dark:to-blue-800 rounded-full opacity-20 animate-pulse">
                    </div>
                    <svg class="w-full h-full relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 5l7 7" />
                    </svg>
                </div>

                <!-- Elementos decorativos -->
                <div class="absolute top-0 left-0 w-4 h-4 bg-purple-300 rounded-full opacity-50 animate-bounce"></div>
                <div class="absolute top-4 right-0 w-3 h-3 bg-blue-300 rounded-full opacity-50 animate-bounce"
                    style="animation-delay: 0.5s;"></div>
                <div class="absolute bottom-0 left-4 w-2 h-2 bg-pink-300 rounded-full opacity-50 animate-bounce"
                    style="animation-delay: 1s;"></div>
            </div>

            <h3 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 mb-3">📦 Nenhum produto encontrado</h3>
            <p class="text-neutral-600 dark:text-neutral-400 text-center mb-8 max-w-md text-lg">
                @if (
                    $search ||
                        $category ||
                        $tipo ||
                        $status_filtro ||
                        $preco_min ||
                        $preco_max ||
                        $estoque ||
                        $data_inicio ||
                        $data_fim)
                    Nenhum produto corresponde aos filtros aplicados. Tente ajustar os critérios de busca.
                @else
                    Sua prateleira está vazia! Que tal começar adicionando seu primeiro produto ao catálogo?
                @endif
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                @if (
                    $search ||
                        $category ||
                        $tipo ||
                        $status_filtro ||
                        $preco_min ||
                        $preco_max ||
                        $estoque ||
                        $data_inicio ||
                        $data_fim)
                    <button wire:click="clearFilters"
                        class="btn-gradient inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="bi bi-x-circle mr-2 icon-rotate"></i>
                        🔄 Limpar Filtros
                    </button>
                @else
                    <a href="{{ route('products.create') }}"
                        class="btn-gradient inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="bi bi-plus-square mr-3 text-xl floating-badge"></i>
                        ✨ Criar Primeiro Produto
                    </a>
                @endif

                <a href="{{ route('products.upload') }}"
                    class="btn-gradient inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="bi bi-file-earmark-arrow-up mr-2 icon-pulse"></i>
                    📂 Upload em Lote
                </a>
            </div>
        </div>
    @else
        <!-- Grid de Produtos com CSS customizado mantido -->
        <form>
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 ultrawind:grid-cols-8 gap-6"
                data-ultrawind="{{ $ultraLayout ?? false ? 'true' : 'false' }}"
                data-full-hd="{{ $fullHdLayout ?? false ? 'true' : 'false' }}"
                x-bind:data-ultrawind="ultra ? 'true' : 'false'" x-bind:data-full-hd="fullHd ? 'true' : 'false'">
                @foreach ($products as $product)
                    @if ($product->tipo === 'kit')
                        <!-- Kit Card no estilo produto simples -->
                        <div class="product-card-modern">
                            <!-- Botões flutuantes -->
                            <div class="btn-action-group">
                                <a href="{{ route('products.show', $product->product_code) }}"
                                    class="btn btn-secondary" title="Ver Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.kit.edit', $product) }}" class="btn btn-primary"
                                    title="Editar Kit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                    wire:click="$dispatch('openExportModal', { productId: {{ $product->id }} })"
                                    class="btn btn-success" title="Exportar Card">
                                    <i class="bi bi-file-earmark-image"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $product->id }})"
                                    class="btn btn-danger" title="Excluir">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>

                            <!-- Área da imagem com badges -->
                            <div class="product-img-area">
                                <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                    class="product-img"
                                    alt="{{ $product->name }}">

                                <!-- Badge KIT -->
                                <div class="absolute top-2 left-2 z-10">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg border border-blue-400">
                                        <i class="bi bi-boxes mr-1"></i>KIT
                                    </span>
                                </div>

                                <!-- Kits não têm estoque próprio - apenas os componentes têm -->

                                <!-- Código do produto -->
                                <span class="badge-product-code" title="Código do Produto">
                                    <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                </span>

                                <!-- Quantidade de componentes do kit -->
                                @php
                                    $componentesCount = $product->componentes()->count();
                                @endphp
                                <span class="badge-quantity" title="Quantidade de Produtos no Kit" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                    <i class="bi bi-grid-3x3-gap"></i> {{ $componentesCount }} {{ $componentesCount === 1 ? 'item' : 'itens' }}
                                </span>

                                <!-- Ícone da categoria -->
                                <div class="category-icon-wrapper">
                                    <i class="{{ $product->category->icone ?? 'bi bi-box-seam' }} category-icon"></i>
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="card-body">
                                <div class="product-title" title="{{ $product->name }}">
                                    {{ ucwords($product->name) }}
                                </div>

                                <!-- Badge de status -->
                                <div class="mt-2 mb-3 flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-100 text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-800 dark:bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-900 dark:text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-200">
                                        <i class="bi bi-circle-fill mr-1"></i> {{ ucfirst($product->status) }}
                                    </span>

                                    <!-- Badge de novo -->
                                    @if (\Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            <i class="bi bi-stars mr-1"></i> Novo
                                        </span>
                                    @endif
                                </div>

                                <!-- Área de preços dentro do card-body -->
                                <div class="price-area mt-3">
                                    <div class="flex flex-col gap-2">
                                        <span class="badge-price" title="Preço de Custo Total">
                                            <i class="bi bi-tag"></i>
                                            R$ {{ number_format($product->price, 2, ',', '.') }}
                                        </span>

                                        <span class="badge-price-sale" title="Preço de Venda do Kit">
                                            <i class="bi bi-currency-dollar"></i>
                                            R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Produto Simples com CSS customizado mantido -->
                        <div class="product-card-modern">
                            <!-- Botões flutuantes -->
                            <div class="btn-action-group">

                                <a href="{{ route('products.show', $product->product_code) }}"
                                    class="btn btn-secondary" title="Ver Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"
                                    title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                    wire:click="$dispatch('openExportModal', { productId: {{ $product->id }} })"
                                    class="btn btn-success" title="Exportar Card">
                                    <i class="bi bi-file-earmark-image"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $product->id }})"
                                    class="btn btn-danger" title="Excluir">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>

                            <!-- Área da imagem com badges -->
                            <div class="product-img-area">
                                <img src="{{ asset('storage/products/' . $product->image) }}" class="product-img"
                                    alt="{{ $product->name }}">

                                @if ($product->stock_quantity == 0)
                                    <div class="out-of-stock">
                                        <i class="bi bi-x-circle"></i> Fora de Estoque
                                    </div>
                                @endif

                                <!-- Código do produto -->
                                <span class="badge-product-code" title="Código do Produto">
                                    <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                </span>

                                <!-- Quantidade -->
                                <span class="badge-quantity" title="Quantidade em Estoque">
                                    <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                </span>

                                <!-- Ícone da categoria -->
                                <div class="category-icon-wrapper">
                                    <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="card-body">
                                <div class="product-title" title="{{ $product->name }}">
                                    {{ ucwords($product->name) }}
                                </div>

                                <!-- Área de preços dentro do card-body -->
                                <div class="price-area mt-3">
                                    <div class="flex flex-col gap-2">
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
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </form>

        <!-- Paginação Aprimorada Inferior -->
        <div class="mt-8 space-y-6 products-pagination-area">
          
            <!-- Paginação Principal -->
            @if ($products->hasPages())
                <div
                    class="bg-gradient-to-br from-white/70 to-slate-50/70 dark:from-slate-800/70 dark:to-slate-900/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                        <!-- Informações da Paginação -->
                        <div class="flex items-center gap-4">
                            <div class="text-sm text-slate-600 dark:text-slate-400">
                                📊 Exibindo
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->firstItem() ?? 0 }}</span>
                                até
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->lastItem() ?? 0 }}</span>
                                de
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->total() }}</span>
                                produtos
                            </div>
                            <div class="text-xs text-purple-500">
                                ({{ $products->lastPage() }} {{ $products->lastPage() === 1 ? 'página' : 'páginas' }})
                            </div>
                        </div>

                        <!-- Controles de Paginação -->
                        <div class="flex items-center gap-2">
                            <!-- Primeira -->
                            @if ($products->currentPage() > 1)
                                <a href="{{ $products->url(1) }}"
                                    class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                                    title="Primeira página">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            @endif

                            <!-- Anterior -->
                            @if ($products->previousPageUrl())
                                <a href="{{ $products->previousPageUrl() }}"
                                    class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                                    title="Página anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            @endif

                            <!-- Páginas -->
                            @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                                @if ($page == $products->currentPage())
                                    <span
                                        class="px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold shadow-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                        class="px-4 py-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            <!-- Próxima -->
                            @if ($products->nextPageUrl())
                                <a href="{{ $products->nextPageUrl() }}"
                                    class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                                    title="Próxima página">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @endif

                            <!-- Última -->
                            @if ($products->currentPage() < $products->lastPage())
                                <a href="{{ $products->url($products->lastPage()) }}"
                                    class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                                    title="Última página">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            @endif
                        </div>

                        <!-- Ações Adicionais -->
                        <div class="flex items-center gap-2">
                            <button
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                                <span class="hidden sm:inline">Exportar</span>
                            </button>
                            <button
                                class="px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <i class="bi bi-printer"></i>
                                <span class="hidden sm:inline">Imprimir</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif

    <!-- Modal de Confirmação de Exclusão Ultra Moderno -->
    @if ($showDeleteModal)
        <div x-data="{ modalOpen: true }" x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[99999] overflow-y-auto">
            <!-- Backdrop com blur e gradiente -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md">
            </div>

            <!-- Container do Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Modal -->
                <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                    x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
                    class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                    <!-- Efeitos visuais de fundo -->
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                    <div
                        class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl">
                    </div>
                    <div
                        class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-br from-pink-400/20 to-red-600/20 rounded-full blur-3xl">
                    </div>

                    <!-- Conteúdo do Modal -->
                    <div class="relative z-10">
                        <!-- Header com ícone animado -->
                        <div class="text-center pt-8 pb-4">
                            <div class="relative inline-flex items-center justify-center">
                                <!-- Círculos de fundo animados -->
                                <div
                                    class="absolute w-24 h-24 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-full animate-pulse">
                                </div>
                                <div
                                    class="absolute w-20 h-20 bg-gradient-to-r from-red-500/40 to-pink-600/40 rounded-full animate-ping">
                                </div>

                                <!-- Ícone principal -->
                                <div
                                    class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-exclamation-triangle text-2xl text-white animate-bounce"></i>
                                </div>
                            </div>

                            <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                <i class="bi bi-shield-exclamation text-red-500 mr-2"></i>
                                Confirmar Exclusão
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                <i class="bi bi-info-circle text-amber-500 mr-1"></i>
                                Esta ação não pode ser desfeita
                            </p>
                        </div>

                        <!-- Corpo com informações -->
                        <div class="px-8 pb-4">
                            <div
                                class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-4 border border-red-200/50 dark:border-red-700/50">
                                @if (count($selectedProducts) > 0)
                                    <div class="text-center">
                                        <i class="bi bi-boxes text-3xl text-red-500 mb-2"></i>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            Você está prestes a excluir
                                            <span
                                                class="font-bold text-red-600 dark:text-red-400">{{ count($selectedProducts) }}
                                                produto(s)</span>
                                            selecionado(s).
                                        </p>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <i class="bi bi-box text-3xl text-red-500 mb-2"></i>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            Você está prestes a excluir o produto:
                                        </p>
                                        <p class="font-bold text-red-600 dark:text-red-400 text-lg mt-1">
                                            "{{ $deletingProduct->name ?? '' }}"
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Aviso adicional -->
                            <div
                                class="mt-4 flex items-center justify-center gap-2 text-amber-600 dark:text-amber-400">
                                <i class="bi bi-clock-history"></i>
                                <span class="text-sm font-medium">Esta ação é permanente</span>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="px-8 pb-8">
                            <div class="flex gap-4">
                                <!-- Botão Cancelar -->
                                <button wire:click="$set('showDeleteModal', false)" @click="modalOpen = false"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 dark:hover:from-gray-600 dark:hover:to-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 dark:border-gray-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-x-circle mr-2"></i>
                                    Cancelar
                                </button>

                                <!-- Botão Excluir -->
                                <button wire:click="delete"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border-2 border-red-400/50">
                                    <i class="bi bi-trash3 mr-2"></i>
                                    Excluir
                                </button>
                            </div>

                            <!-- Botão de escape -->
                            <div class="mt-3 text-center">
                                <button @click="modalOpen = false; $wire.set('showDeleteModal', false)"
                                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200">
                                    <i class="bi bi-escape mr-1"></i>
                                    Pressione ESC para cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Dicas (Wizard) -->
    @if($showTipsModal)
        <div x-data="{
            currentStep: 1,
            totalSteps: 5,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }"
            x-show="$wire.showTipsModal"
            x-cloak
            @keydown.escape.window="$wire.toggleTips()"
            class="fixed inset-0 z-[9999]">

            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/70 via-slate-900/70 to-blue-950/60 backdrop-blur-md" @click="$wire.toggleTips()"></div>

            <div class="relative h-full md:p-5 lg:p-8 flex items-end md:items-center justify-center">
                <div
                    class="w-full h-[94dvh] md:h-auto md:max-h-[92vh] md:max-w-6xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl rounded-t-3xl md:rounded-3xl shadow-2xl border border-white/30 dark:border-slate-700/70 overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <div class="grid grid-cols-1 md:grid-cols-12 h-full md:max-h-[92vh]">
                        <aside class="md:col-span-4 lg:col-span-3 bg-gradient-to-b from-cyan-600 via-blue-700 to-indigo-800 text-white p-5 md:p-6">
                            <div class="flex items-center justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm">
                                        <i class="bi bi-lightbulb-fill text-xl"></i>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-lg">Guia Rapido</h3>
                                        <p class="text-xs text-blue-100">Produtos em 5 passos</p>
                                    </div>
                                </div>

                                <button @click="$wire.toggleTips()" class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 transition-colors">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="step in totalSteps" :key="step">
                                    <button @click="currentStep = step"
                                        class="w-full text-left px-3 py-2.5 rounded-xl transition-all duration-200"
                                        :class="currentStep === step ? 'bg-white text-slate-900 shadow-lg font-semibold' : 'bg-white/10 hover:bg-white/20 text-blue-50'">
                                        <span class="text-sm" x-text="step + '. ' + ['Visao geral', 'Busca e filtros', 'Acoes rapidas', 'Selecao em massa', 'Layout e paginacao'][step - 1]"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="mt-5 bg-white/15 rounded-2xl p-3">
                                <p class="text-xs uppercase tracking-wide text-blue-100">Progresso</p>
                                <div class="mt-2 h-2 rounded-full bg-white/25 overflow-hidden">
                                    <div class="h-full bg-white rounded-full transition-all duration-300" :style="'width:' + ((currentStep / totalSteps) * 100) + '%' "></div>
                                </div>
                                <p class="mt-2 text-xs text-blue-100" x-text="'Etapa ' + currentStep + ' de ' + totalSteps"></p>
                            </div>
                        </aside>

                        <section class="md:col-span-8 lg:col-span-9 flex flex-col min-h-0">
                            <div class="p-4 md:p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg md:text-2xl font-extrabold text-slate-900 dark:text-slate-100" x-text="['Visao Geral do Catalogo','Busca e Filtros Inteligentes','Acoes com Produtos','Selecao e Acoes em Massa','Organizacao e Visualizacao'][currentStep - 1]"></h2>
                                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Interface otimizada para phone e tablet, com foco em produtividade.</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300" x-text="'Passo ' + currentStep"></span>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-gradient-to-b from-slate-50/80 to-white dark:from-slate-900/70 dark:to-slate-900">
                                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                                        <div class="rounded-2xl border border-cyan-200 dark:border-cyan-900/60 bg-cyan-50/80 dark:bg-cyan-900/20 p-4">
                                            <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-search mr-1 text-cyan-600"></i> Busca direta</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Encontre por nome, codigo, categoria ou descricao em tempo real.</p>
                                        </div>
                                        <div class="rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/80 dark:bg-indigo-900/20 p-4">
                                            <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-funnel mr-1 text-indigo-600"></i> Filtros avancados</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Refine por preco, status, estoque, tipo e periodo.</p>
                                        </div>
                                        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-900/20 p-4">
                                            <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-grid mr-1 text-emerald-600"></i> Cards visuais</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Visual limpo com imagem, estoque, codigo e preco no mesmo bloco.</p>
                                        </div>
                                        <div class="rounded-2xl border border-orange-200 dark:border-orange-900/60 bg-orange-50/80 dark:bg-orange-900/20 p-4">
                                            <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-lightning-charge mr-1 text-orange-600"></i> Fluxo rapido</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Edite, exporte e exclua sem sair da tela principal.</p>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">Como buscar com eficiencia</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-300">
                                            <p><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Nome do produto</p>
                                            <p><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Codigo/SKU</p>
                                            <p><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Categoria</p>
                                            <p><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Descricao</p>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">No modal de filtros voce pode combinar</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-sm text-slate-700 dark:text-slate-300">
                                            <p><i class="bi bi-tag-fill text-blue-500 mr-1"></i> Categoria</p>
                                            <p><i class="bi bi-box-seam text-indigo-500 mr-1"></i> Tipo</p>
                                            <p><i class="bi bi-toggle-on text-emerald-500 mr-1"></i> Status</p>
                                            <p><i class="bi bi-cash text-cyan-500 mr-1"></i> Faixa de preco</p>
                                            <p><i class="bi bi-boxes text-orange-500 mr-1"></i> Estoque</p>
                                            <p><i class="bi bi-calendar-event text-rose-500 mr-1"></i> Periodo</p>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                                    <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-900/20 p-4">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-plus-circle text-emerald-600 mr-1"></i> Novo produto</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Cadastre itens unitarios com nome, categoria e precos.</p>
                                    </div>
                                    <div class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/80 dark:bg-blue-900/20 p-4">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-boxes text-blue-600 mr-1"></i> Novo kit</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Monte kits com varios itens e defina valor final de venda.</p>
                                    </div>
                                    <div class="rounded-2xl border border-violet-200 dark:border-violet-900/60 bg-violet-50/80 dark:bg-violet-900/20 p-4">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-file-earmark-arrow-up text-violet-600 mr-1"></i> Upload em massa</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Importe lotes por planilha para acelerar cadastro inicial.</p>
                                    </div>
                                    <div class="rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/80 dark:bg-amber-900/20 p-4">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-1"><i class="bi bi-pencil-square text-amber-600 mr-1"></i> Edicao rapida</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Use os botoes do card para editar, exportar e excluir em 1 toque.</p>
                                    </div>
                                </div>

                                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">Selecao inteligente</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Marque produtos individualmente ou use o seletor do cabecalho para selecionar todos da pagina.</p>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="rounded-2xl border border-red-200 dark:border-red-900/60 bg-red-50/80 dark:bg-red-900/20 p-4">
                                            <h5 class="font-semibold text-slate-900 dark:text-white mb-1"><i class="bi bi-trash text-red-500 mr-1"></i> Excluir em massa</h5>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Remove varios itens de uma vez com confirmacao.</p>
                                        </div>
                                        <div class="rounded-2xl border border-sky-200 dark:border-sky-900/60 bg-sky-50/80 dark:bg-sky-900/20 p-4">
                                            <h5 class="font-semibold text-slate-900 dark:text-white mb-1"><i class="bi bi-download text-sky-500 mr-1"></i> Exportar selecao</h5>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">Gere arquivos para compartilhamento e impressao.</p>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">Ordenacao e pagina</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Defina ordem por nome, preco, data ou estoque e escolha quantos itens ver por pagina.</p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-900/20 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">Layout responsivo real</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-300">
                                            <p><i class="bi bi-phone text-emerald-600 mr-1"></i> Phone 15: foco em toque e leitura vertical</p>
                                            <p><i class="bi bi-tablet-landscape text-emerald-600 mr-1"></i> iPad 11: espacamento para produtividade</p>
                                            <p><i class="bi bi-laptop text-emerald-600 mr-1"></i> Desktop: mais colunas e acoes simultaneas</p>
                                            <p><i class="bi bi-display text-emerald-600 mr-1"></i> Ultra wide: densidade alta sem perder clareza</p>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-amber-300 dark:border-amber-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-4 md:p-5">
                                        <h4 class="font-bold text-slate-900 dark:text-white mb-1">Tudo pronto</h4>
                                        <p class="text-sm text-slate-700 dark:text-slate-300">Quando quiser revisar o fluxo, abra novamente o botao de dicas.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 md:p-5 border-t border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/85">
                                <div class="flex items-center justify-between gap-3">
                                    <button @click="prevStep()"
                                        x-show="currentStep > 1"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold transition-colors">
                                        <i class="bi bi-arrow-left"></i>
                                        Anterior
                                    </button>
                                    <div x-show="currentStep === 1"></div>

                                    <div class="flex items-center gap-1.5">
                                        <template x-for="step in totalSteps" :key="step">
                                            <button @click="currentStep = step" class="h-2.5 rounded-full transition-all duration-200" :class="currentStep === step ? 'w-8 bg-blue-600' : 'w-2.5 bg-slate-300 dark:bg-slate-600'"></button>
                                        </template>
                                    </div>

                                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                                        class="inline-flex items-center gap-2 px-4 md:px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg transition-all duration-200">
                                        <span x-text="currentStep < totalSteps ? 'Proximo' : 'Concluir'"></span>
                                        <i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i>
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Componente de Exportação de Card -->
    @livewire('products.export-product-card')
</div>
