<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <!-- Header com título e ações -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 mb-2">
                    <i class="bi bi-boxes text-neutral-600 dark:text-neutral-400"></i>
                    Produtos
                </h1>
                <p class="text-neutral-600 dark:text-neutral-400">Gerencie seu catálogo de produtos</p>
            </div>
            
            <!-- Botões de ação -->
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products.upload') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                    <i class="bi bi-file-earmark-arrow-up mr-2"></i>
                    Upload
                </a>
                
                <a href="{{ route('products.kit.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                    <i class="bi bi-boxes mr-2"></i>
                    Novo Kit
                </a>
                
                <a href="{{ route('products.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                    <i class="bi bi-plus-square mr-2"></i>
                    Novo Produto
                </a>
            </div>
        </div>

        <!-- Filtros e Pesquisa -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6 h-full">
            <!-- Filtros -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 h-fit">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-4 flex items-center">
                        <i class="bi bi-funnel-fill text-neutral-600 dark:text-neutral-400 mr-2"></i>
                        Filtros
                    </h3>
                    
                    <!-- Items por página -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-eye mr-1"></i>
                            Mostrar por página
                        </label>
                        <select wire:model.live="perPage" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                            <option value="18">18 itens</option>
                            <option value="30">30 itens</option>
                            <option value="48">48 itens</option>
                            <option value="96">96 itens</option>
                        </select>
                    </div>

                    <!-- Ordenação -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-sort-alpha-down mr-1"></i>
                            Ordenar por
                        </label>
                        <select wire:model.live="ordem" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                            <option value="">Padrão</option>
                            <option value="recentes">Mais Recentes</option>
                            <option value="antigas">Mais Antigas</option>
                            <option value="az">A-Z</option>
                            <option value="za">Z-A</option>
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-tags mr-1"></i>
                            Categoria
                        </label>
                        <select wire:model.live="category" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                            <option value="">Todas</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-box-seam mr-1"></i>
                            Tipo
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="tipo" value="" class="text-neutral-600 focus:ring-neutral-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Todos</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="tipo" value="simples" class="text-neutral-600 focus:ring-neutral-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Simples</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="tipo" value="kit" class="text-neutral-600 focus:ring-neutral-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Kit</span>
                            </label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-activity mr-1"></i>
                            Status
                        </label>
                        <select wire:model.live="status_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="descontinuado">Descontinuado</option>
                        </select>
                    </div>

                    <!-- Preço -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            <i class="bi bi-currency-exchange mr-1"></i>
                            Faixa de Preço
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" wire:model.live="preco_min" placeholder="Mín" step="0.01" min="0" 
                                   class="p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                            <input type="number" wire:model.live="preco_max" placeholder="Máx" step="0.01" min="0" 
                                   class="p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Limpar filtros -->
                    <button wire:click="clearFilters" 
                            class="w-full py-2 px-4 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="bi bi-x-circle mr-2"></i>
                        Limpar Filtros
                    </button>
                </div>
            </div>

            <!-- Pesquisa e Lista de Produtos -->
            <div class="lg:col-span-3">
                <!-- Barra de Pesquisa -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-search text-neutral-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               placeholder="Pesquisar por nome ou código..." 
                               class="w-full pl-10 pr-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-neutral-500 focus:border-transparent shadow-sm">
                    </div>
                </div>

                <!-- Lista de Produtos -->
                @if($products->isEmpty())
                    <!-- Estado vazio -->
                    <div class="flex flex-col items-center justify-center py-16 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700">
                        <div class="w-32 h-32 mx-auto mb-6 text-neutral-400">
                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 5l7 7" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 mb-2">Nenhum produto encontrado</h3>
                        <p class="text-neutral-600 dark:text-neutral-400 text-center mb-6">Sua prateleira está vazia. Cadastre seu primeiro produto!</p>
                        <a href="{{ route('products.create') }}" 
                           class="inline-flex items-center px-6 py-3 bg-neutral-800 hover:bg-neutral-900 dark:bg-neutral-200 dark:hover:bg-neutral-100 text-white dark:text-neutral-800 font-medium rounded-lg transition-all duration-200">
                            <i class="bi bi-plus-square mr-2"></i>
                            Criar Primeiro Produto
                        </a>
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
                                            
                                            <div class="mt-3">
                                                <a href="{{ route('products.kit.edit', $product) }}" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                                                    <i class="bi bi-pencil-square mr-1"></i>
                                                    Editar
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

                    <!-- Paginação -->
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
                        <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">Confirmar Exclusão</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                        Tem certeza que deseja excluir o produto <strong>{{ $deletingProduct?->name }}</strong>? Esta ação não pode ser desfeita.
                    </p>
                    <div class="flex space-x-3">
                        <button wire:click="$set('showDeleteModal', false)" 
                                class="flex-1 py-2 px-4 bg-neutral-300 hover:bg-neutral-400 text-neutral-700 font-medium rounded-lg transition-colors duration-200">
                            Cancelar
                        </button>
                        <button wire:click="delete" 
                                class="flex-1 py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
