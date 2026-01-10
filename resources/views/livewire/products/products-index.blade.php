<div class=" w-full " x-data="{
    showFilters: false,
    showQuickActions: false,
    hasActiveFilters: {{ $search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque || $data_inicio || $data_fim ? 'true' : 'false' }},
    fullHd: false,
    ultra: false,
    initResponsiveWatcher() {
        const mq = window.matchMedia('(min-width: 1920px)');
        const mqUltra = window.matchMedia('(min-width: 2498px)');

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

        sync();
        syncUltra();

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
    }
}" x-init="initResponsiveWatcher()">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
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
        <div class="space-y-4 w-full">
            <!-- Linha 1: Campo de Pesquisa + Botões de Ação -->
            <div class="flex items-center gap-4 w-full">
                <!-- Campo de Pesquisa (flex-1) -->
                <div class="flex-1">
                    <div class="relative group">
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar produtos por nome, código, categoria ou descrição..."
                                class="w-full pl-12 pr-16 py-3 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900 border border-slate-200/50 dark:border-slate-600/50 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-400 transition-all duration-300 shadow-sm backdrop-blur-sm text-base font-medium">

                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-purple-500 transition-colors duration-200"></i>
                            </div>

                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-50"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-50"
                                    class="group/clear p-1.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                    title="Limpar busca">
                                    <i class="bi bi-x-lg text-xs group-hover/clear:rotate-90 transition-transform duration-200"></i>
                                </button>
                            </div>

                            <div wire:loading.delay wire:target="search" class="absolute right-12 top-1/2 transform -translate-y-1/2">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Controle -->
                <div class="flex items-center gap-3">
                    <button @click="showFilters = !showFilters"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                        :class="showFilters ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300'"
                        title="Filtros avançados">
                        <i class="bi bi-funnel"></i>
                        <span class="hidden xl:inline text-sm font-semibold">Filtros</span>
                    </button>

                    <button wire:click="toggleTips"
                        class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold"
                        title="Ver Dicas">
                        <i class="bi bi-lightbulb"></i>
                        <span class="hidden xl:inline text-sm">Dicas</span>
                    </button>
                </div>

                <!-- Ações Principais -->
                <div class="flex items-center gap-3 pl-3 border-l-2 border-slate-200 dark:border-slate-600">
                    <a href="{{ route('products.create') }}"
                        class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="bi bi-plus-lg text-lg"></i>
                        <span class="hidden lg:inline">Novo Produto</span>
                    </a>

                    <a href="{{ route('products.kit.create') }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="bi bi-boxes"></i>
                        <span class="hidden xl:inline text-sm">Novo Kit</span>
                    </a>

                    <a href="{{ route('products.upload') }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <span class="hidden xl:inline text-sm">Upload</span>
                    </a>
                </div>
            </div>

            <!-- Linha 2: Informações + Paginação -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg text-white">
                        <i class="bi bi-box text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                            @if ($products->total())
                                {{ $products->total() }} {{ $products->total() === 1 ? 'Produto' : 'Produtos' }}
                            @else
                                Nenhum produto
                            @endif
                        </h3>
                        @if ($products->total() > 0)
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($products->hasPages())
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                        @if ($products->currentPage() > 1)
                            <a href="{{ $products->url(1) }}" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200" title="Primeira página">
                                <i class="bi bi-chevron-double-left text-sm"></i>
                            </a>
                        @endif

                        @if ($products->previousPageUrl())
                            <a href="{{ $products->previousPageUrl() }}" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200" title="Página anterior">
                                <i class="bi bi-chevron-left text-sm"></i>
                            </a>
                        @endif

                        <div class="flex items-center px-3 py-1">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                        </div>

                        @if ($products->nextPageUrl())
                            <a href="{{ $products->nextPageUrl() }}" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200" title="Próxima página">
                                <i class="bi bi-chevron-right text-sm"></i>
                            </a>
                        @endif

                        @if ($products->currentPage() < $products->lastPage())
                            <a href="{{ $products->url($products->lastPage()) }}" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200" title="Última página">
                                <i class="bi bi-chevron-double-right text-sm"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    </x-products-header>



    <!-- Filtros Modernizados -->
    <x-products-filters :categories="$categories" :search="$search" :category="$category" :tipo="$tipo" :status_filtro="$status_filtro"
        :preco_min="$preco_min" :preco_max="$preco_max" :per-page="$perPage" :per-page-options="$perPageOptions" :ordem="$ordem" :estoque_filtro="$estoque ?? ''"
        :data_filtro="$data_inicio ?? ''" :total-products="$products->total() ?? 0" :sem-estoque="$semEstoque" />

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
        <div class="mt-8 space-y-6">
            <!-- Estatísticas e Resumo -->
            @if ($products->total() > 0)
                <div
                    class="bg-gradient-to-br from-white/60 to-slate-50/60 dark:from-slate-800/60 dark:to-slate-900/60 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                        <!-- Total de Produtos -->
                        <div class="text-center">
                            <div
                                class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl text-white mx-auto w-fit mb-2">
                                <i class="bi bi-boxes text-2xl"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                {{ $products->total() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Total de Produtos</div>
                        </div>

                        <!-- Produtos Ativos -->
                        <div class="text-center">
                            <div
                                class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl text-white mx-auto w-fit mb-2">
                                <i class="bi bi-check-circle text-2xl"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                {{ $products->where('status', 'ativo')->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Produtos Ativos</div>
                        </div>

                        <!-- Estoque Baixo -->
                        <div class="text-center">
                            <div
                                class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl text-white mx-auto w-fit mb-2">
                                <i class="bi bi-exclamation-triangle text-2xl"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                {{ $products->where('stock_quantity', '<=', 5)->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Estoque Baixo</div>
                        </div>

                        <!-- Sem Imagem -->
                        <div class="text-center">
                            <div
                                class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl text-white mx-auto w-fit mb-2">
                                <i class="bi bi-image text-2xl"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                {{ $products->whereNull('image')->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Sem Imagem</div>
                        </div>

                        <!-- Valor Total -->
                        <div class="text-center">
                            <div
                                class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white mx-auto w-fit mb-2">
                                <i class="bi bi-currency-dollar text-2xl"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                R$ {{ number_format($products->sum('price_sale'), 0, ',', '.') }}
                            </div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Valor Total</div>
                        </div>
                    </div>
                </div>
            @endif

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
        }" x-show="$wire.showTipsModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

            <!-- Modal Content -->
            <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
                class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Header with Progress Bar -->
                <div class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 px-8 py-6 text-white">
                    <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>

                    <div class="pr-12">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="bi bi-lightbulb-fill text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold">Dicas de Produtos</h2>
                                <p class="text-blue-100 text-sm mt-1">Aprenda a gerenciar seus produtos com eficiência</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex gap-2 mt-6">
                            <template x-for="step in totalSteps" :key="step">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                    <div class="h-full bg-white rounded-full transition-all duration-500"
                                         :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8">
                    <!-- Step 1: Visão Geral -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-grid-3x3-gap text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Visão Geral</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Conheça as funcionalidades principais da listagem de produtos</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-search text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Busca Inteligente</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Pesquise por nome, código, categoria ou descrição do produto</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-funnel text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Filtros Avançados</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Filtre por categoria, tipo, status, preço, estoque e muito mais</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-grid text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Visualização em Cards</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Veja seus produtos em cards visuais com imagens e informações</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-orange-500 rounded-xl">
                                        <i class="bi bi-lightning text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Ações Rápidas</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Edite, duplique, exporte ou exclua produtos com um clique</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Busca e Filtros -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-search-heart text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Busca e Filtros</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Encontre rapidamente o que procura</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-lg font-bold">1</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Campo de Busca</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">Digite no campo de pesquisa para buscar por:</p>
                                <ul class="space-y-2 ml-6">
                                    <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                        Nome do produto
                                    </li>
                                    <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                        Código ou SKU
                                    </li>
                                    <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                        Categoria
                                    </li>
                                    <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                        Descrição
                                    </li>
                                </ul>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-purple-500 text-white rounded-lg font-bold">2</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Filtros Avançados</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">Clique no ícone de funil <i class="bi bi-funnel mx-1"></i> para acessar:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-tag-fill text-blue-500"></i>
                                        Categoria
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-box-seam text-purple-500"></i>
                                        Tipo (Produto/Kit)
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-toggle-on text-green-500"></i>
                                        Status (Ativo/Inativo)
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-cash text-emerald-500"></i>
                                        Faixa de Preço
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-boxes text-orange-500"></i>
                                        Nível de Estoque
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <i class="bi bi-calendar text-red-500"></i>
                                        Período de Criação
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Ações com Produtos -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-lightning-charge text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Ações com Produtos</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Gerencie seus produtos de forma eficiente</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-plus-circle text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Criar Produto</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Adicione novos produtos ao catálogo</p>
                                        <button class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs font-semibold transition-all">
                                            <i class="bi bi-plus-lg mr-1"></i> Novo Produto
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-boxes text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Criar Kit</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Crie kits combinando vários produtos</p>
                                        <button class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-semibold transition-all">
                                            <i class="bi bi-boxes mr-1"></i> Novo Kit
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-file-earmark-arrow-up text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Upload em Massa</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Importe múltiplos produtos via Excel/CSV</p>
                                        <button class="px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-xs font-semibold transition-all">
                                            <i class="bi bi-file-earmark-arrow-up mr-1"></i> Upload
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-orange-500 rounded-xl">
                                        <i class="bi bi-pencil text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Editar Produto</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Clique no card para editar informações</p>
                                        <div class="flex gap-2 mt-2">
                                            <span class="px-2 py-1 bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300 rounded text-xs">✏️ Editar</span>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded text-xs">📋 Duplicar</span>
                                            <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded text-xs">🗑️ Excluir</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Seleção e Ações em Massa -->
                    <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-check2-square text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Seleção e Ações em Massa</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Gerencie múltiplos produtos de uma vez</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-indigo-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-indigo-500 text-white rounded-lg font-bold">1</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Selecione Produtos</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-4">Cada card de produto tem uma checkbox no canto superior esquerdo:</p>
                                <div class="flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" checked class="w-5 h-5 text-indigo-600 rounded">
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Clique para selecionar</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" checked class="w-5 h-5 text-indigo-600 rounded">
                                        <input type="checkbox" checked class="w-5 h-5 text-indigo-600 rounded">
                                        <input type="checkbox" checked class="w-5 h-5 text-indigo-600 rounded">
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Selecione múltiplos</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-purple-500 text-white rounded-lg font-bold">2</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Ações Disponíveis</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-4">Com produtos selecionados, você pode:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                            <i class="bi bi-trash text-lg"></i>
                                            <span class="font-semibold">Excluir em massa</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Remove todos selecionados</p>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                            <i class="bi bi-download text-lg"></i>
                                            <span class="font-semibold">Exportar seleção</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Baixe em Excel/PDF</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="bi bi-lightbulb-fill text-2xl text-amber-500"></i>
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Dica Especial</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">Use a checkbox do cabeçalho para <strong>selecionar todos</strong> os produtos da página atual de uma só vez!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Organização e Paginação -->
                    <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-layout-three-columns text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Organização e Visualização</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Personalize a forma como você vê seus produtos</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-blue-500 rounded-lg">
                                        <i class="bi bi-sort-down text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Ordenação</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">Ordene seus produtos por:</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-arrow-down-up text-blue-500"></i>
                                        Nome (A-Z ou Z-A)
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-arrow-down-up text-blue-500"></i>
                                        Preço (Menor/Maior)
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-arrow-down-up text-blue-500"></i>
                                        Data de criação
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-arrow-down-up text-blue-500"></i>
                                        Estoque disponível
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-purple-500 rounded-lg">
                                        <i class="bi bi-layout-text-window text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Itens por Página</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">Ajuste quantos produtos quer ver por página:</p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-4 py-2 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-lg font-semibold">12</span>
                                    <span class="px-4 py-2 bg-purple-500 text-white rounded-lg font-semibold">18 ✓</span>
                                    <span class="px-4 py-2 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-lg font-semibold">24</span>
                                    <span class="px-4 py-2 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-lg font-semibold">30</span>
                                    <span class="px-4 py-2 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-lg font-semibold">48</span>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-green-500 rounded-lg">
                                        <i class="bi bi-arrows-angle-expand text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Layout Responsivo</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">O layout se adapta automaticamente ao tamanho da tela:</p>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-phone text-green-500"></i>
                                        <strong>Mobile:</strong> 1 card por linha
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-tablet text-green-500"></i>
                                        <strong>Tablet:</strong> 2-3 cards por linha
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-laptop text-green-500"></i>
                                        <strong>Desktop:</strong> 4-6 cards por linha
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-display text-green-500"></i>
                                        <strong>Ultra Wide:</strong> 8+ cards por linha
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-amber-50 via-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-amber-300 dark:border-amber-600">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="bi bi-emoji-smile-fill text-3xl text-amber-500"></i>
                                    <div>
                                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Você está pronto!</h4>
                                        <p class="text-slate-600 dark:text-slate-300 text-sm">Agora você sabe tudo sobre a gestão de produtos</p>
                                    </div>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">Continue explorando e se tiver dúvidas, sempre pode voltar aqui clicando no botão <i class="bi bi-lightbulb text-amber-500 mx-1"></i> de Dicas!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer with Navigation -->
                <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <!-- Previous Button -->
                        <button @click="prevStep()" x-show="currentStep > 1"
                            class="flex items-center gap-2 px-6 py-3 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all duration-200 hover:scale-105">
                            <i class="bi bi-arrow-left"></i>
                            Anterior
                        </button>
                        <div x-show="currentStep === 1"></div>

                        <!-- Step Indicators -->
                        <div class="flex items-center gap-2">
                            <template x-for="step in totalSteps" :key="step">
                                <button @click="currentStep = step"
                                    class="transition-all duration-300 rounded-full"
                                    :class="currentStep === step ? 'w-8 h-3 bg-gradient-to-r from-blue-600 to-purple-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'">
                                </button>
                            </template>
                        </div>

                        <!-- Next/Finish Button -->
                        <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                            class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                            <span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'"></span>
                            <i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Componente de Exportação de Card -->
    @livewire('products.export-product-card')
</div>
