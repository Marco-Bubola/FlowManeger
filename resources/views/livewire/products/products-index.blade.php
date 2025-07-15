<div class="flex h-full w-full flex-1 flex-col gap-3  p-3" x-data="{ showFilters: false, showQuickActions: false }">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-compact.css') }}">

   

    <!-- Header Compacto com Estatísticas -->
    <div class="relative overflow-hidden shadow-sm border ">
        <!-- Background decorativo sutil -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br rounded-full transform translate-x-16 -translate-y-16 opacity-30"></div>

        <div class="relative p-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <!-- Título Compacto -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg text-white shadow-lg">
                        <i class="bi bi-boxes text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">
                            📦 Catálogo de Produtos
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-400 text-sm">{{ $products->total() ?? 0 }} produtos • {{ $categories->count() }} categorias</p>
                    </div>
                </div>

                <!-- Ações Rápidas e Controles -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Estatísticas Compactas -->
                    <div class="hidden lg:flex items-center gap-2">
                        <div class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold">
                            ✅ {{ $products->where('stock_quantity', '>', 5)->count() }} em estoque
                        </div>
                        <div class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-xs font-semibold">
                            ⚠️ {{ $products->where('stock_quantity', '<=', 5)->count() }} baixo
                        </div>
                        <div class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-xs font-semibold">
                            📦 {{ $products->where('tipo', 'kit')->count() }} kits
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <button @click="showQuickActions = !showQuickActions"
                        class="px-3 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span class="hidden sm:inline">Novo</span>
                    </button>

                    <a href="{{ route('products.upload') }}"
                        class="px-3 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <span class="hidden sm:inline">Upload</span>
                    </a>

                    <button @click="showFilters = !showFilters"
                        class="px-3 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2"
                        :class="{'bg-gradient-to-r from-blue-500 to-blue-600': showFilters}">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="hidden sm:inline">Filtros</span>
                        @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
                        <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Menu Dropdown de Ações Rápidas -->
            <div x-show="showQuickActions" x-transition class="absolute top-full right-4 mt-2 w-64 bg-white dark:bg-neutral-800 rounded-xl shadow-xl border border-neutral-200 dark:border-neutral-700 z-50">
                <div class="p-3">
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3 flex items-center gap-2">
                        <i class="bi bi-lightning-charge text-yellow-500"></i>
                        Ações Rápidas
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('products.create') }}"
                            class="flex items-center gap-3 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg transition-colors duration-200">
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg">
                                <i class="bi bi-plus-square"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Novo Produto</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Adicionar produto simples</p>
                            </div>
                        </a>
                        <a href="{{ route('products.kit.create') }}"
                            class="flex items-center gap-3 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg transition-colors duration-200">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Novo Kit</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Combinar produtos</p>
                            </div>
                        </a>
                        <div class="flex items-center gap-3 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg transition-colors duration-200 cursor-pointer">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Relatórios</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Análises e estatísticas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Barra de Pesquisa Rápida -->
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-neutral-400 text-lg"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="🔍 Pesquisar produtos..."
                class="w-full pl-10 pr-10 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder:text-neutral-400">

            @if($search)
            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-red-500 transition-colors duration-200">
                <i class="bi bi-x-circle text-lg"></i>
            </button>
            @endif
        </div>
    </div>

    <!-- Seção de filtros escondida -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i class="bi bi-funnel-fill text-purple-500"></i>
                <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Filtros Avançados</h3>
            </div>

            <!-- Indicador de filtros ativos -->
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <i class="bi bi-filter-circle mr-1"></i>
                    Filtros ativos
                </span>
                <button wire:click="clearFilters" class="text-neutral-500 hover:text-red-500 transition-colors duration-200" title="Limpar todos os filtros">
                    <i class="bi bi-x-circle text-lg"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Grid de filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Itens por página -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-eye mr-1"></i>
                    Itens por página
                </label>
                <select wire:model.live="perPage" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="12">12 itens</option>
                    <option value="24">24 itens</option>
                    <option value="36">36 itens</option>
                    <option value="48">48 itens</option>
                </select>
            </div>

            <!-- Ordenação -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-sort-alpha-down mr-1"></i>
                    Ordenar por
                </label>
                <select wire:model.live="ordem" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📅 Padrão</option>
                    <option value="recentes">🆕 Mais Recentes</option>
                    <option value="antigas">📜 Mais Antigas</option>
                    <option value="az">🔤 A-Z</option>
                    <option value="za">🔤 Z-A</option>
                    <option value="preco_asc">💰 Menor Preço</option>
                    <option value="preco_desc">💎 Maior Preço</option>
                    <option value="estoque_asc">📦 Menor Estoque</option>
                    <option value="estoque_desc">📈 Maior Estoque</option>
                </select>
            </div>

            <!-- Categoria -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-tags mr-1"></i>
                    Categoria
                </label>
                <select wire:model.live="category" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">🏷️ Todas</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-activity mr-1"></i>
                    Status
                </label>
                <select wire:model.live="status_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📊 Todos</option>
                    <option value="ativo">✅ Ativo</option>
                    <option value="inativo">⏸️ Inativo</option>
                    <option value="descontinuado">❌ Descontinuado</option>
                </select>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-box-seam mr-1"></i>
                    Tipo
                </label>
                <select wire:model.live="tipo" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">🔄 Todos</option>
                    <option value="simples">📦 Simples</option>
                    <option value="kit">📦📦 Kit</option>
                </select>
            </div>

            <!-- Preço Mínimo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-currency-dollar mr-1"></i>
                    Preço Mínimo
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-neutral-500">R$</span>
                    <input type="number" wire:model.live="preco_min" placeholder="0,00" step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Preço Máximo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-currency-dollar mr-1"></i>
                    Preço Máximo
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-neutral-500">R$</span>
                    <input type="number" wire:model.live="preco_max" placeholder="∞" step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Estoque -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-boxes mr-1"></i>
                    Estoque
                </label>
                <select wire:model.live="estoque_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📦 Todos</option>
                    <option value="disponivel">✅ Disponível (>5)</option>
                    <option value="baixo">⚠️ Baixo (1-5)</option>
                    <option value="zerado">❌ Zerado (0)</option>
                </select>
            </div>

            <!-- Filtro por Data -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-calendar mr-1"></i>
                    Criado em
                </label>
                <select wire:model.live="data_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📅 Todos os períodos</option>
                    <option value="hoje">📅 Hoje</option>
                </select>
            </div>

            <!-- Filtro por Margem de Lucro -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                </label>
                <select wire:model.live="margem_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </select>
            </div>
        </div>

        <!-- Botão limpar filtros -->
        <div class="mt-4 flex justify-end">
            <button wire:click="clearFilters"
                class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                🗑️ Limpar Filtros
            </button>
        </div>
    </div>

    <!-- Seção de produtos -->
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg text-white">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <h2 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">
                    Produtos Encontrados
                    @if($products->total())
                    <span class="text-sm font-normal text-neutral-500 dark:text-neutral-400">
                        ({{ $products->total() }} {{ $products->total() === 1 ? 'item' : 'itens' }})
                    </span>
                    @endif
                </h2>
            </div>

            <!-- Views toggle e Paginação -->
            <div class="flex items-center gap-4">
                <!-- Paginação Horizontal Estilizada -->
                @if($products->hasPages())
                <div class="flex items-center gap-1 bg-neutral-100 dark:bg-neutral-700 rounded-lg p-1">
                    <!-- Primeiro -->
                    @if($products->currentPage() > 1)
                    <a href="{{ $products->url(1) }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Primeira página">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                    @endif

                    <!-- Anterior -->
                    @if($products->previousPageUrl())
                    <a href="{{ $products->previousPageUrl() }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    @endif

                    <!-- Páginas -->
                    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                    @if($page == $products->currentPage())
                    <span class="px-3 py-1 bg-purple-500 text-white rounded-md text-sm font-medium">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}"
                        class="px-3 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-600 rounded-md transition-colors duration-200 text-sm">
                        {{ $page }}
                    </a>
                    @endif
                    @endforeach

                    <!-- Próximo -->
                    @if($products->nextPageUrl())
                    <a href="{{ $products->nextPageUrl() }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Próxima página">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    @endif

                    <!-- Último -->
                    @if($products->currentPage() < $products->lastPage())
                        <a href="{{ $products->url($products->lastPage()) }}"
                            class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                            title="Última página">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                        @endif
                </div>
                @endif

                <!-- Views toggle -->
                <div class="flex items-center bg-neutral-100 dark:bg-neutral-700 rounded-lg p-1">
                    <button class="px-3 py-2 bg-purple-500 text-white rounded-md shadow-sm transition-all duration-200">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="px-3 py-2 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200">
                        <i class="bi bi-list-ul"></i>
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
                @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
                Nenhum produto corresponde aos filtros aplicados. Tente ajustar os critérios de busca.
                @else
                Sua prateleira está vazia! Que tal começar adicionando seu primeiro produto ao catálogo?
                @endif
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
            @foreach($products as $product)
            @if($product->tipo === 'kit')
            <!-- Kit Card -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl border-2 border-blue-200 dark:border-blue-700 hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="relative p-4">
                    <div class="absolute top-2 right-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <i class="bi bi-boxes mr-1"></i>KIT
                        </span>
                    </div>

                    <div class="text-center">
                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                            alt="{{ $product->name }}"
                            class="w-24 h-24 mx-auto rounded-lg object-cover bg-neutral-100 dark:bg-neutral-700 mb-3">

                        <h3 class="font-bold text-neutral-800 dark:text-neutral-100 text-sm mb-1">{{ $product->name }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">#{{ $product->product_code }}</p>

                        <div class="space-y-1 text-xs">
                            <div class="text-green-600 dark:text-green-400 font-semibold">
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
                <div class="btn-action-group">
                    <a href="{{ route('products.show', $product->product_code) }}" class="btn btn-secondary" title="Ver Detalhes">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <button type="button" wire:click="confirmDelete({{ $product->id }})" class="btn btn-danger" title="Excluir">
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

        <!-- Paginação aprimorada -->
        <div class="pagination-wrapper mt-12 flex flex-col items-center">
            <div class="bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-xl p-6 border border-neutral-200 dark:border-neutral-600 shadow-sm">
                {{ $products->links() }}
            </div>

            <!-- Informações da paginação -->
            <div class="mt-4 text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    📊 Exibindo
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->firstItem() ?? 0 }}</span>
                    até
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->total() }}</span>
                    produtos
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
</div>

<!-- Modal de Confirmação de Exclusão aprimorado -->
@if($showDeleteModal)
<div class="modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="modal-content bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-2xl max-w-md w-full mx-4 overflow-hidden">
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
            <div class="text-center mb-6">
                <p class="text-neutral-700 dark:text-neutral-300 mb-3">
                    Tem certeza que deseja excluir o produto:
                </p>
                <div class="bg-neutral-100 dark:bg-neutral-700 rounded-lg p-4 border border-neutral-200 dark:border-neutral-600">
                    <p class="font-bold text-neutral-800 dark:text-neutral-100 text-lg">
                        📦 {{ $deletingProduct?->name }}
                    </p>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Código: #{{ $deletingProduct?->product_code }}
                    </p>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-3">
                    🗑️ Todos os dados relacionados a este produto serão removidos permanentemente.
                </p>
            </div>

            <!-- Botões de ação -->
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                    class="btn-gradient flex-1 py-3 px-4 bg-gradient-to-r from-neutral-300 to-neutral-400 hover:from-neutral-400 hover:to-neutral-500 text-neutral-700 font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="bi bi-x-circle icon-rotate"></i>
                    ❌ Cancelar
                </button>
                <button wire:click="delete"
                    class="btn-gradient flex-1 py-3 px-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="bi bi-trash3 floating-badge"></i>
                    🗑️ Excluir
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>