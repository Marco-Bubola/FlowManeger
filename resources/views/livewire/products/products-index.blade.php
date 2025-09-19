<div class="flex h-full w-full flex-1 flex-col gap-6 p-6"
     x-data="{
        showFilters: false,
        showQuickActions: false,
        hasActiveFilters: {{ ($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque || $data_inicio || $data_fim) ? 'true' : 'false' }}
     }">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    <!-- Header Modernizado -->
    <x-products-header
        title="Catálogo de Produtos"
        description="Gerencie todo seu catálogo de produtos e kits"
        :total-products="$products->total() ?? 0"
        :total-categories="$categories->count()"
        :show-quick-actions="true"
    />



    <!-- Filtros Modernizados -->
    <x-products-filters
        :categories="$categories"
        :search="$search"
        :category="$category"
        :tipo="$tipo"
        :status_filtro="$status_filtro"
        :preco_min="$preco_min"
        :preco_max="$preco_max"
        :per-page="$perPage"
        :ordem="$ordem"
        :estoque_filtro="$estoque ?? ''"
        :data_filtro="$data_inicio ?? ''"
        :total-products="$products->total() ?? 0"
    />

    <!-- Barra de Controle Superior com Pesquisa e Paginação -->
    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/20 dark:border-slate-700/50 mb-6">
        <!-- Linha Principal: Pesquisa (50%) + Controles e Paginação (50%) -->
        <div class="flex items-center gap-6">
            <!-- Lado Esquerdo: Campo de Pesquisa (50%) -->
            <div class="flex-1">
                <div class="relative group">
                    <!-- Input principal -->
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Buscar produtos por nome, código, categoria ou descrição..."
                               class="w-full pl-12 pr-16 py-3 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900
                                      border border-slate-200/50 dark:border-slate-600/50 rounded-xl
                                      text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                      focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-400
                                      transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                      text-base font-medium">

                        <!-- Ícone de busca -->
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-search text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-purple-500 transition-colors duration-200"></i>
                        </div>

                        <!-- Botão limpar -->
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <button wire:click="$set('search', '')"
                                    x-show="$wire.search && $wire.search.length > 0"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-50"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-50"
                                    class="group/clear p-1.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500
                                           text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white
                                           rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                    title="Limpar busca">
                                <i class="bi bi-x-lg text-xs group-hover/clear:rotate-90 transition-transform duration-200"></i>
                            </button>
                        </div>

                        <!-- Indicador de carregamento -->
                        <div wire:loading.delay wire:target="search"
                             class="absolute right-12 top-1/2 transform -translate-y-1/2">
                            <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                        </div>

                        <!-- Efeito de brilho -->
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 via-transparent to-blue-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                </div>
            </div>

            <!-- Lado Direito: Informações + Paginação + Controles (50%) -->
            <div class="flex items-center gap-4">
                <!-- Contador de Resultados -->
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg text-white">
                        <i class="bi bi-box text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                            @if($products->total())
                                {{ $products->total() }} {{ $products->total() === 1 ? 'Produto' : 'Produtos' }}
                            @else
                                Nenhum produto
                            @endif
                        </h3>
                        @if($products->total() > 0)
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Paginação Compacta -->
                @if($products->hasPages())
                <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                    <!-- Primeira/Anterior -->
                    @if($products->currentPage() > 1)
                    <a href="{{ $products->url(1) }}"
                       class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                       title="Primeira página">
                        <i class="bi bi-chevron-double-left text-sm"></i>
                    </a>
                    @endif

                    @if($products->previousPageUrl())
                    <a href="{{ $products->previousPageUrl() }}"
                       class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                       title="Página anterior">
                        <i class="bi bi-chevron-left text-sm"></i>
                    </a>
                    @endif

                    <!-- Páginas -->
                    <div class="flex items-center px-3 py-1">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $products->currentPage() }} / {{ $products->lastPage() }}
                        </span>
                    </div>

                    <!-- Próxima/Última -->
                    @if($products->nextPageUrl())
                    <a href="{{ $products->nextPageUrl() }}"
                       class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                       title="Próxima página">
                        <i class="bi bi-chevron-right text-sm"></i>
                    </a>
                    @endif

                    @if($products->currentPage() < $products->lastPage())
                    <a href="{{ $products->url($products->lastPage()) }}"
                       class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                       title="Última página">
                        <i class="bi bi-chevron-double-right text-sm"></i>
                    </a>
                    @endif
                </div>
                @endif

                <!-- Botão de Filtros -->
                <button @click="showFilters = !showFilters"
                        class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-all duration-200"
                        :class="{ 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300': showFilters }"
                        title="Filtros avançados">
                    <i class="bi bi-funnel text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    @if($products->isEmpty())
    <!-- Estado vazio aprimorado -->
    <div class="empty-state flex flex-col items-center justify-center py-20 bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-2xl border-2 border-dashed border-neutral-300 dark:border-neutral-600">
        <div class="relative">
            <!-- Ícone animado -->
            <div class="w-32 h-32 mx-auto mb-6 text-neutral-400 relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 dark:from-purple-800 dark:to-blue-800 rounded-full opacity-20 animate-pulse"></div>
                <svg class="w-full h-full relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 5l7 7" />
                </svg>
            </div>

            <!-- Elementos decorativos -->
            <div class="absolute top-0 left-0 w-4 h-4 bg-purple-300 rounded-full opacity-50 animate-bounce"></div>
            <div class="absolute top-4 right-0 w-3 h-3 bg-blue-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 0.5s;"></div>
            <div class="absolute bottom-0 left-4 w-2 h-2 bg-pink-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 1s;"></div>
        </div>

        <h3 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 mb-3">📦 Nenhum produto encontrado</h3>
        <p class="text-neutral-600 dark:text-neutral-400 text-center mb-8 max-w-md text-lg">
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque || $data_inicio || $data_fim)
            Nenhum produto corresponde aos filtros aplicados. Tente ajustar os critérios de busca.
            @else
            Sua prateleira está vazia! Que tal começar adicionando seu primeiro produto ao catálogo?
            @endif
        </p>

        <div class="flex flex-col sm:flex-row gap-4">
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque || $data_inicio || $data_fim)
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
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
        @foreach($products as $product)
        @if($product->tipo === 'kit')
        <!-- Kit Card com informações extras -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl border-2 border-blue-200 dark:border-blue-700 hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            <div class="relative p-4">
                <div class="absolute top-2 right-2 flex gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        <i class="bi bi-boxes mr-1"></i>KIT
                    </span>
                    <!-- Badge de status -->
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-100 text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-800" title="Status">
                        <i class="bi bi-circle-fill mr-1"></i> {{ ucfirst($product->status) }}
                    </span>
                    <!-- Badge de novo -->
                    @if(\Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800" title="Novo Produto">
                        <i class="bi bi-stars mr-1"></i> Novo
                    </span>
                    @endif
                </div>

                <div class="text-center">
                    <input type="checkbox"
                           wire:model.live="selectedProducts"
                           value="{{ $product->id }}"
                           class="mb-2"
                           title="Selecionar">
                    <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                        alt="{{ $product->name }}"
                        class="w-24 h-24 mx-auto rounded-lg object-cover bg-neutral-100 dark:bg-neutral-700 mb-3">

                    <!-- Categoria com ícone -->
                    <div class="mb-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <i class="{{ $product->category->icone ?? 'bi bi-box' }} mr-1"></i> {{ $product->category->name ?? '-' }}
                        </span>
                    </div>

                    <h3 class="font-bold text-neutral-800 dark:text-neutral-100 text-sm mb-1" title="{{ $product->name }}">
                        {{ $product->name }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2" title="Código do produto">#{{ $product->product_code }}</p>

                    <div class="space-y-1 text-xs">
                        <div class="text-green-600 dark:text-green-400 font-semibold" title="Preço de venda">
                            <i class="bi bi-currency-dollar"></i> R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="mt-3 flex gap-2 justify-center">
                        <a href="{{ route('products.show', $product->product_code) }}"
                            class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                            title="Ver Detalhes">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('products.kit.edit', $product) }}"
                            class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                            title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Produto Simples com CSS customizado mantido -->
        <div class="product-card-modern">
            <!-- Botões flutuantes -->
            <div class="btn-action-group flex gap-2">
                <input type="checkbox"
                       wire:model.live="selectedProducts"
                       value="{{ $product->id }}"
                       title="Selecionar">
                <a href="{{ route('products.show', $product->product_code) }}" class="btn btn-secondary" title="Ver Detalhes">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button"
                        wire:click="confirmDelete({{ $product->id }})"
                        class="btn btn-danger"
                        title="Excluir">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <!-- Área da imagem com badges -->
            <div class="product-img-area">
                <img src="{{ asset('storage/products/' . $product->image) }}" class="product-img" alt="{{ $product->name }}">

                @if($product->stock_quantity == 0)
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
            </div>

            <span class="badge-price" title="Preço de Custo">
                <i class="bi bi-tag"></i>
                {{ number_format($product->price, 2, ',', '.') }}
            </span>

            <span class="badge-price-sale" title="Preço de Venda">
                <i class="bi bi-currency-dollar"></i>
                {{ number_format($product->price_sale, 2, ',', '.') }}
            </span>
        </div>
        @endif
        @endforeach
    </div>
    </form>

    <!-- Barra de Seleção em Massa (aparece quando há seleções) -->
    <div x-data="{ selectedCount: 0 }"
         x-show="selectedCount > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-30 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl p-4 shadow-2xl border border-white/20 dark:border-slate-700/50">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg text-white">
                    <i class="bi bi-check-square text-lg"></i>
                </div>
                <span class="font-semibold text-slate-800 dark:text-slate-200">
                    <span x-text="selectedCount"></span> selecionados
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-check-circle"></i> Ativar
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-files"></i> Duplicar
                </button>
                <button wire:click="confirmDeleteSelected" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-trash3"></i> Excluir
                </button>
                <button @click="selectedCount = 0" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-200">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Paginação Aprimorada Inferior -->
    <div class="mt-8 space-y-6">
        <!-- Estatísticas e Resumo -->
        @if($products->total() > 0)
        <div class="bg-gradient-to-br from-white/60 to-slate-50/60 dark:from-slate-800/60 dark:to-slate-900/60 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <!-- Total de Produtos -->
                <div class="text-center">
                    <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl text-white mx-auto w-fit mb-2">
                        <i class="bi bi-boxes text-2xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $products->total() }}</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Total de Produtos</div>
                </div>

                <!-- Produtos Ativos -->
                <div class="text-center">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl text-white mx-auto w-fit mb-2">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $products->where('status', 'ativo')->count() }}</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Produtos Ativos</div>
                </div>

                <!-- Estoque Baixo -->
                <div class="text-center">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl text-white mx-auto w-fit mb-2">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $products->where('stock_quantity', '<=', 5)->count() }}</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Estoque Baixo</div>
                </div>

                <!-- Sem Imagem -->
                <div class="text-center">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl text-white mx-auto w-fit mb-2">
                        <i class="bi bi-image text-2xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $products->whereNull('image')->count() }}</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Sem Imagem</div>
                </div>

                <!-- Valor Total -->
                <div class="text-center">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white mx-auto w-fit mb-2">
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
        @if($products->hasPages())
        <div class="bg-gradient-to-br from-white/70 to-slate-50/70 dark:from-slate-800/70 dark:to-slate-900/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <!-- Informações da Paginação -->
                <div class="flex items-center gap-4">
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        📊 Exibindo
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->firstItem() ?? 0 }}</span>
                        até
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->lastItem() ?? 0 }}</span>
                        de
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->total() }}</span>
                        produtos
                    </div>
                    <div class="text-xs text-purple-500">
                        ({{ $products->lastPage() }} {{ $products->lastPage() === 1 ? 'página' : 'páginas' }})
                    </div>
                </div>

                <!-- Controles de Paginação -->
                <div class="flex items-center gap-2">
                    <!-- Primeira -->
                    @if($products->currentPage() > 1)
                    <a href="{{ $products->url(1) }}"
                        class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                        title="Primeira página">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                    @endif

                    <!-- Anterior -->
                    @if($products->previousPageUrl())
                    <a href="{{ $products->previousPageUrl() }}"
                        class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                        title="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    @endif

                    <!-- Páginas -->
                    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                    @if($page == $products->currentPage())
                    <span class="px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold shadow-lg">
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
                    @if($products->nextPageUrl())
                    <a href="{{ $products->nextPageUrl() }}"
                        class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                        title="Próxima página">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    @endif

                    <!-- Última -->
                    @if($products->currentPage() < $products->lastPage())
                    <a href="{{ $products->url($products->lastPage()) }}"
                        class="p-3 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-600"
                        title="Última página">
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                    @endif
                </div>

                <!-- Ações Adicionais -->
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        <span class="hidden sm:inline">Exportar</span>
                    </button>
                    <button class="px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                        <i class="bi bi-printer"></i>
                        <span class="hidden sm:inline">Imprimir</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Ações Rápidas Inferiores -->
        <div class="bg-gradient-to-br from-white/70 to-slate-50/70 dark:from-slate-800/70 dark:to-slate-900/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
            <div class="flex flex-wrap items-center justify-center gap-3">
                <button class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-plus-square"></i> Novo Produto
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-boxes"></i> Novo Kit
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-upload"></i> Importar
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-graph-up-arrow"></i> Relatórios
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-gear"></i> Configurações
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Confirmação de Exclusão aprimorado -->
    @if($showDeleteModal)
    <div class="modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" style="z-index: 99999 !important;">
    <div class="modal-content bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-2xl max-w-md w-full mx-4 overflow-hidden" style="z-index: 100000 !important;">
        <!-- Header do modal -->
        <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6 text-white">
            <div class="flex items-center justify-center mb-4">
                <div class="p-3 bg-white/20 rounded-full">
                    <i class="bi bi-exclamation-triangle text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center mb-2">⚠️ Confirmar Exclusão</h3>
            <p class="text-red-100 text-center text-sm">Esta ação é irreversível!</p>
        </div>

        <!-- Corpo do modal -->
        <div class="p-6">
            @if(count($selectedProducts) > 0)
                <p class="text-neutral-700 dark:text-neutral-300 text-center mb-6">
                    Tem certeza que deseja excluir <strong>{{ count($selectedProducts) }} produto(s) selecionado(s)</strong>?
                </p>
            @else
                <p class="text-neutral-700 dark:text-neutral-300 text-center mb-6">
                    Tem certeza que deseja excluir o produto <strong>"{{ $deletingProduct->name ?? '' }}"</strong>?
                </p>
            @endif

            <div class="flex gap-4 justify-center">
                <button wire:click="$set('showDeleteModal', false)"
                    class="px-6 py-3 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-600 dark:hover:bg-neutral-500 text-neutral-700 dark:text-neutral-200 font-medium rounded-xl transition-all duration-200">
                    ❌ Cancelar
                </button>
                <button wire:click="delete"
                    class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    🗑️ Excluir
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
