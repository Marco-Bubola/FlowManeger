<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-800 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-72 h-72 bg-white dark:bg-gray-200 opacity-10 rounded-full -translate-x-36 -translate-y-36"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white dark:bg-gray-200 opacity-10 rounded-full translate-x-48 -translate-y-48"></div>
            <div class="absolute bottom-0 left-1/2 w-80 h-80 bg-white dark:bg-gray-200 opacity-10 rounded-full translate-x-40 translate-y-40"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    🏷️ <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-purple-200">Categorias</span>
                </h1>
                <p class="text-lg md:text-xl text-purple-100 max-w-3xl mx-auto leading-relaxed">
                    Organize e gerencie suas categorias de produtos e transações de forma inteligente e eficiente
                </p>
            </div>
        </div>
    </div>

    <!-- Dashboard de Estatísticas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Categorias de Produtos -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 px-6 py-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                                🛍️
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                    Categorias de Produtos
                                </dt>
                                <dd class="text-3xl font-bold text-white">
                                    {{ $productCategories->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ round(($productCategories->count() / max(($productCategories->count() + $transactionCategories->count()), 1)) * 100) }}% do total
                    </div>
                </div>
            </div>

            <!-- Categorias de Transações -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 px-6 py-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                                💰
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                    Categorias de Transações
                                </dt>
                                <dd class="text-3xl font-bold text-white">
                                    {{ $transactionCategories->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ round(($transactionCategories->count() / max(($productCategories->count() + $transactionCategories->count()), 1)) * 100) }}% do total
                    </div>
                </div>
            </div>

            <!-- Categorias Ativas -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="bg-gradient-to-br from-orange-500 to-red-500 dark:from-orange-600 dark:to-red-600 px-6 py-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                                ⚡
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                    Categorias Ativas
                                </dt>
                                <dd class="text-3xl font-bold text-white">
                                    {{ $categories->where('is_active', 1)->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $categories->where('is_active', 0)->count() }} inativas
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sistema de Navegação por Abas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <!-- Aba Categorias de Produtos -->
                <button wire:click="setActiveTab('products')" 
                        class="flex-1 flex items-center justify-center px-6 py-4 text-sm font-semibold transition-all duration-300 {{ $activeTab === 'products' ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center">
                        <span>🛍️ Produtos</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activeTab === 'products' ? 'bg-white bg-opacity-20 text-white' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                            {{ $paginatedProductCategories->total() }}
                        </span>
                    </div>
                </button>
                
                <!-- Aba Categorias de Transações -->
                <button wire:click="setActiveTab('transactions')" 
                        class="flex-1 flex items-center justify-center px-6 py-4 text-sm font-semibold transition-all duration-300 {{ $activeTab === 'transactions' ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center">
                        <span>💰 Transações</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activeTab === 'transactions' ? 'bg-white bg-opacity-20 text-white' : 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' }}">
                            {{ $paginatedTransactionCategories->total() }}
                        </span>
                    </div>
                </button>
                
                <!-- Aba Todas as Categorias -->
                <button wire:click="setActiveTab('all')" 
                        class="flex-1 flex items-center justify-center px-6 py-4 text-sm font-semibold transition-all duration-300 {{ $activeTab === 'all' ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-lg' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center">
                        <span>📋 Todas</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activeTab === 'all' ? 'bg-white bg-opacity-20 text-white' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                            {{ $categories->total() }}
                        </span>
                    </div>
                </button>
                
                <!-- Aba Dicas Inteligentes -->
                <button wire:click="setActiveTab('tips')" 
                        class="flex-1 flex items-center justify-center px-6 py-4 text-sm font-semibold transition-all duration-300 {{ $activeTab === 'tips' ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center">
                        <span>💡 Dicas</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros Avançados (Apenas para aba 'all') -->
    @if($activeTab === 'all')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer" 
                 wire:click="$toggle('showFilters')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        🔍
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Filtros Inteligentes</h3>
                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">({{ $categories->total() }} resultados)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-400 dark:text-gray-500 transform transition-transform duration-200 {{ $showFilters ? 'rotate-180' : '' }}">
                            ⬇️
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="transition-all duration-300 ease-in-out {{ $showFilters ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0 overflow-hidden' }}">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                🔍 Buscar
                            </label>
                            <div class="relative">
                                <input wire:model.live="search" type="text" placeholder="Nome, descrição ou tags..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <span class="absolute left-3 top-3.5 text-gray-400 dark:text-gray-500">🔍</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                🏷️ Tipo
                            </label>
                            <select wire:model.live="typeFilter" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="">Todos os tipos</option>
                                <option value="product">🛍️ Produtos</option>
                                <option value="transaction">💰 Transações</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                ✅ Status
                            </label>
                            <select wire:model.live="statusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="">Todos os status</option>
                                <option value="1">✅ Ativo</option>
                                <option value="0">❌ Inativo</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                📄 Por página
                            </label>
                            <select wire:model.live="perPage" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="12">12 itens</option>
                                <option value="18">18 itens</option>
                                <option value="24">24 itens</option>
                                <option value="36">36 itens</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                🔀 Ordenar
                            </label>
                            <select wire:model.live="sortBy" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="name">Nome A-Z</option>
                                <option value="name_desc">Nome Z-A</option>
                                <option value="created_at">Mais recentes</option>
                                <option value="created_at_desc">Mais antigas</option>
                                <option value="type">Por tipo</option>
                                <option value="favorites">⭐ Favoritas primeiro</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Filtros Rápidos -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex flex-wrap gap-3">
                            <span class="text-sm font-medium text-gray-700 mr-4">Filtros rápidos:</span>
                            <button wire:click="quickFilter('recent')" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 hover:from-blue-200 hover:to-blue-300 transition-all duration-200">
                                🆕 Recentes
                            </button>
                            <button wire:click="quickFilter('active')" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-green-100 to-green-200 text-green-800 hover:from-green-200 hover:to-green-300 transition-all duration-200">
                                ✅ Ativas
                            </button>
                            <button wire:click="quickFilter('products')" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 hover:from-purple-200 hover:to-purple-300 transition-all duration-200">
                                🛍️ Produtos
                            </button>
                            <button wire:click="quickFilter('transactions')" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 hover:from-orange-200 hover:to-orange-300 transition-all duration-200">
                                💰 Transações
                            </button>
                            <button wire:click="quickFilter('favorites')" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 hover:from-yellow-200 hover:to-yellow-300 transition-all duration-200">
                                ⭐ Favoritas
                            </button>
                            <button wire:click="clearFilters" 
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 hover:from-gray-200 hover:to-gray-300 transition-all duration-200">
                                🗑️ Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Conteúdo das Abas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Aba de Categorias de Produtos -->
        @if($activeTab === 'products')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-xl p-3 mr-4">
                            🛍️
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Categorias de Produtos</h2>
                            <p class="text-blue-100 mt-1">Organize seus produtos por categoria</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="bg-white bg-opacity-20 rounded-xl px-4 py-2">
                            <span class="text-white font-semibold">{{ $paginatedProductCategories->total() }} categorias</span>
                        </div>
                        <!-- Botão Criar Categoria de Produto -->
                        <button wire:click="createProductCategory" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-xl transition-all duration-300 flex items-center">
                            <span class="mr-2">➕</span>
                            Criar Produto
                        </button>
                        <button class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-xl transition-all duration-300">
                            📤
                        </button>
                    </div>
                </div>
            </div>

            @if($paginatedProductCategories->count() > 0)
                <div class="p-8">
                    <!-- Grid 2 por linha -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:sortable="updateProductOrder">
                        @foreach($paginatedProductCategories as $category)
                            <div wire:sortable.item="{{ $category->id_category }}" wire:key="product-{{ $category->id_category }}"
                                 class="group bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 hover:shadow-lg transition-all duration-300 cursor-move">
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <!-- Informações da Categoria -->
                                        <div class="flex items-center flex-1">
                                            <!-- Handle para arrastar -->
                                            <div wire:sortable.handle class="mr-4 text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1V3a1 1 0 00-1-1H7zM7 8a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1V9a1 1 0 00-1-1H7zM7 14a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1v-2a1 1 0 00-1-1H7z"/>
                                                </svg>
                                            </div>
                                            
                                            <!-- Ícone da Categoria -->
                                            <div class="relative mr-4">
                                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white shadow-lg transform group-hover:scale-110 transition-transform duration-300" 
                                                     style="background: linear-gradient(135deg, {{ $category->hexcolor_category ?? '#3B82F6' }}, {{ $category->hexcolor_category ?? '#3B82F6' }}dd)">
                                                    @if($category->icone)
                                                        <i class="{{ $category->icone }}" style="font-size: 1.8rem;"></i>
                                                    @else
                                                        <span class="text-2xl">
                                                            @switch($category->name)
                                                                @case('Perfume')
                                                                    🌸
                                                                    @break
                                                                @case('Eletrônicos')
                                                                    📱
                                                                    @break
                                                                @case('Roupas')
                                                                    👕
                                                                    @break
                                                                @case('Casa')
                                                                    🏠
                                                                    @break
                                                                @case('Alimentação')
                                                                    🍕
                                                                    @break
                                                                @default
                                                                    🛍️
                                                            @endswitch
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Badge de Status -->
                                                <div class="absolute -top-1 -right-1 w-6 h-6 {{ $category->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs">{{ $category->is_active ? '✓' : '✗' }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Detalhes -->
                                            <div class="flex-1">
                                                <div class="flex items-center mb-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg">{{ $category->name }}</h4>
                                                    <!-- Botão de Favorita -->
                                                    <button wire:click="toggleFavorite({{ $category->id_category }})" 
                                                            class="ml-2 p-1 rounded-full hover:bg-yellow-100 dark:hover:bg-yellow-900 transition-colors">
                                                        ⭐
                                                    </button>
                                                </div>
                                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $category->description ?? 'Categoria de produto' }}</p>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                        Produto
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Ações -->
                                        <div class="flex items-center space-x-2 ml-4">
                                            <!-- Botão Compartilhar -->
                                            <button wire:click="shareCategory({{ $category->id_category }})" 
                                                    class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900 rounded-xl transition-colors" 
                                                    title="Compartilhar categoria">
                                                📤
                                            </button>
                                            <a href="#" class="p-2 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-xl transition-colors">
                                                ✏️
                                            </a>
                                            <button wire:click="confirmDelete({{ $category->id_category }})" 
                                                    class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900 rounded-xl transition-colors">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Paginação de Produtos -->
                    <div class="mt-8">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4">
                            {{ $paginatedProductCategories->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">📦</div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Nenhuma categoria de produto encontrada</h3>
                    <p class="text-gray-500 dark:text-gray-400">Crie sua primeira categoria de produto para organizar seus itens.</p>
                    <button wire:click="createProductCategory" 
                            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl transition-all duration-300 flex items-center mx-auto">
                        <span class="mr-2">➕</span>
                        Criar Primeira Categoria
                    </button>
                </div>
            @endif
        </div>
        @endif

        <!-- Aba de Categorias de Transações -->
        @if($activeTab === 'transactions')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-xl p-3 mr-4">
                            💰
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Categorias de Transações</h2>
                            <p class="text-emerald-100 mt-1">Classifique suas receitas e despesas</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="bg-white bg-opacity-20 rounded-xl px-4 py-2">
                            <span class="text-white font-semibold">{{ $paginatedTransactionCategories->total() }} categorias</span>
                        </div>
                        <!-- Botão Criar Categoria de Transação -->
                        <button wire:click="createTransactionCategory" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-xl transition-all duration-300 flex items-center">
                            <span class="mr-2">➕</span>
                            Criar Transação
                        </button>
                        <button class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-xl transition-all duration-300">
                            📤
                        </button>
                    </div>
                </div>
            </div>

            @if($paginatedTransactionCategories->count() > 0)
                <div class="p-8">
                    <!-- Grid 2 por linha -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:sortable="updateTransactionOrder">
                        @foreach($paginatedTransactionCategories as $category)
                            <div wire:sortable.item="{{ $category->id_category }}" wire:key="transaction-{{ $category->id_category }}"
                                 class="group bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl border border-emerald-200 dark:border-emerald-800 hover:shadow-lg transition-all duration-300 cursor-move">
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <!-- Informações da Categoria -->
                                        <div class="flex items-center flex-1">
                                            <!-- Handle para arrastar -->
                                            <div wire:sortable.handle class="mr-4 text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1V3a1 1 0 00-1-1H7zM7 8a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1V9a1 1 0 00-1-1H7zM7 14a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1v-2a1 1 0 00-1-1H7z"/>
                                                </svg>
                                            </div>
                                            
                                            <!-- Ícone da Categoria -->
                                            <div class="relative mr-4">
                                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white shadow-lg transform group-hover:scale-110 transition-transform duration-300" 
                                                     style="background: linear-gradient(135deg, {{ $category->hexcolor_category ?? '#10B981' }}, {{ $category->hexcolor_category ?? '#10B981' }}dd)">
                                                    @if($category->icone)
                                                        <i class="{{ $category->icone }}" style="font-size: 1.8rem;"></i>
                                                    @else
                                                        <span class="text-2xl">
                                                            @switch($category->name)
                                                                @case('Nubank')
                                                                    🏛️
                                                                    @break
                                                                @case('Salario')
                                                                    💼
                                                                    @break
                                                                @case('Alimentação')
                                                                    🍴
                                                                    @break
                                                                @case('Transporte')
                                                                    🚗
                                                                    @break
                                                                @case('Entretenimento')
                                                                    🎬
                                                                    @break
                                                                @case('Saúde')
                                                                    🏥
                                                                    @break
                                                                @case('Educação')
                                                                    📚
                                                                    @break
                                                                @case('Investimento')
                                                                    📈
                                                                    @break
                                                                @default
                                                                    💰
                                                            @endswitch
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Badge de Status -->
                                                <div class="absolute -top-1 -right-1 w-6 h-6 {{ $category->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs">{{ $category->is_active ? '✓' : '✗' }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Detalhes -->
                                            <div class="flex-1">
                                                <div class="flex items-center mb-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg">{{ $category->name }}</h4>
                                                    <!-- Botão de Favorita -->
                                                    <button wire:click="toggleFavorite({{ $category->id_category }})" 
                                                            class="ml-2 p-1 rounded-full hover:bg-yellow-100 dark:hover:bg-yellow-900 transition-colors">
                                                        ⭐
                                                    </button>
                                                </div>
                                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $category->description ?? 'Categoria de transação' }}</p>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                                        @if($category->tipo === 'gasto')
                                                            💸 Despesa
                                                        @elseif($category->tipo === 'receita')
                                                            💵 Receita
                                                        @else
                                                            💰 Ambos
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Ações -->
                                        <div class="flex items-center space-x-2 ml-4">
                                            <!-- Botão Compartilhar -->
                                            <button wire:click="shareCategory({{ $category->id_category }})" 
                                                    class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900 rounded-xl transition-colors" 
                                                    title="Compartilhar categoria">
                                                📤
                                            </button>
                                            <a href="#" class="p-2 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-xl transition-colors">
                                                ✏️
                                            </a>
                                            <button wire:click="confirmDelete({{ $category->id_category }})" 
                                                    class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900 rounded-xl transition-colors">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Paginação de Transações -->
                    <div class="mt-8">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4">
                            {{ $paginatedTransactionCategories->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">💸</div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Nenhuma categoria de transação encontrada</h3>
                    <p class="text-gray-500 dark:text-gray-400">Crie sua primeira categoria de transação para organizar suas finanças.</p>
                    <button wire:click="createTransactionCategory" 
                            class="mt-4 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl transition-all duration-300 flex items-center mx-auto">
                        <span class="mr-2">➕</span>
                        Criar Primeira Categoria
                    </button>
                </div>
            @endif
        </div>
        @endif

        <!-- Aba de Todas as Categorias -->
        @if($activeTab === 'all')
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-xl p-3 mr-4">
                            📋
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Todas as Categorias</h2>
                            <p class="text-purple-100 mt-1">Visão completa de todas as categorias</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="bg-white bg-opacity-20 rounded-xl px-4 py-2">
                            <span class="text-white font-semibold">{{ $categories->total() }} categorias</span>
                        </div>
                        <button class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-xl transition-all duration-300">
                            📤
                        </button>
                    </div>
                </div>
            </div>

            @if($categories->count() > 0)
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($categories as $category)
                            <div class="group bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-lg hover:shadow-2xl border border-gray-200 transition-all duration-300 transform hover:-translate-y-2 category-card" 
                                 data-category-id="{{ $category->id_category }}">
                                <div class="relative p-6">
                                    <!-- Botão de Favorito -->
                                    <button class="absolute top-4 right-4 text-gray-400 hover:text-yellow-500 transition-colors duration-200 group">
                                        ⭐
                                    </button>

                                    <div class="text-center">
                                        <div class="rounded-2xl p-4 inline-block mb-4" 
                                             style="background: linear-gradient(135deg, {{ $category->hexcolor_category ?? '#8B5CF6' }}, {{ $category->hexcolor_category ?? '#8B5CF6' }}dd)">
                                            <span class="text-3xl text-white">
                                                @if($category->icone)
                                                    <i class="{{ $category->icone }}" style="font-size: 2rem;"></i>
                                                @elseif($category->type === 'product') 
                                                    🛍️ 
                                                @else 
                                                    💰 
                                                @endif
                                            </span>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors duration-300">
                                            {{ $category->name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            {{ $category->description ?? 'Categoria de ' . ($category->type === 'product' ? 'produto' : 'transação') }}
                                        </p>
                                        
                                        <!-- Status Badge -->
                                        <div class="mb-4">
                                            @if($category->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Ativa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ❌ Inativa
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Ações -->
                                        <div class="flex justify-center space-x-2 mt-4">
                                            <button class="flex-1 bg-gradient-to-r from-purple-500 to-pink-600 text-white py-2 px-4 rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-300 text-sm font-semibold">
                                                ✏️ Editar
                                            </button>
                                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-xl transition-all duration-300">
                                                📤
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Indicador de Arrastar -->
                                    <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="bg-gray-200 rounded-lg p-1">
                                            ↕️
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Paginação de Todas as Categorias -->
                    <div class="mt-8">
                        <div class="bg-purple-50 rounded-2xl p-4">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">📂</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhuma categoria encontrada</h3>
                    <p class="text-gray-500">Crie suas primeiras categorias para organizar melhor.</p>
                </div>
            @endif
        </div>
        @endif

        <!-- Aba de Dicas Inteligentes -->
        @if($activeTab === 'tips')
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-xl p-3 mr-4">
                            💡
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Dicas Inteligentes</h2>
                            <p class="text-orange-100 mt-1">Otimize o uso das suas categorias</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Dica 1 -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            🎨
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Use Cores Contrastantes</h4>
                        <p class="text-gray-600 text-sm">Escolha cores distintas para facilitar a identificação visual das categorias.</p>
                    </div>
                    
                    <!-- Dica 2 -->
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-6 border border-emerald-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            📝
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Descrições Claras</h4>
                        <p class="text-gray-600 text-sm">Adicione descrições detalhadas para ajudar na organização futura.</p>
                    </div>
                    
                    <!-- Dica 3 -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            🏷️
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Use Tags</h4>
                        <p class="text-gray-600 text-sm">Utilize tags para facilitar a busca e filtragem das categorias.</p>
                    </div>

                    <!-- Dica 4 -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            📊
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Organize por Frequência</h4>
                        <p class="text-gray-600 text-sm">Coloque as categorias mais usadas no topo da lista para acesso rápido.</p>
                    </div>

                    <!-- Dica 5 -->
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-6 border border-indigo-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            🔄
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Revise Regularmente</h4>
                        <p class="text-gray-600 text-sm">Faça revisões mensais para manter suas categorias atualizadas e relevantes.</p>
                    </div>

                    <!-- Dica 6 -->
                    <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-2xl p-6 border border-pink-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mb-4 text-2xl">
                            ⭐
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Marque Favoritas</h4>
                        <p class="text-gray-600 text-sm">Use o sistema de favoritas para destacar as categorias mais importantes.</p>
                    </div>
                </div>

                <!-- Seção de Estatísticas -->
                <div class="mt-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-8">
                    <h4 class="text-xl font-bold text-gray-900 mb-6 text-center">📈 Estatísticas das suas Categorias</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $productCategories->count() }}</div>
                            <div class="text-sm text-gray-600">Produtos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-emerald-600">{{ $transactionCategories->count() }}</div>
                            <div class="text-sm text-gray-600">Transações</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $categories->where('is_active', 1)->count() }}</div>
                            <div class="text-sm text-gray-600">Ativas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ $categories->where('is_active', 0)->count() }}</div>
                            <div class="text-sm text-gray-600">Inativas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-md w-full transform transition-all duration-300 scale-100">
                <!-- Header do Modal -->
                <div class="relative bg-gradient-to-r from-red-500 to-red-600 rounded-t-3xl px-8 py-6">
                    <div class="flex items-center justify-center">
                        <div class="w-16 h-16 bg-white bg-opacity-20 backdrop-blur-sm rounded-full flex items-center justify-center">
                            ⚠️
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-white text-center mt-4">Confirmar Exclusão</h3>
                </div>
                
                <!-- Conteúdo do Modal -->
                <div class="p-8">
                    @if($deletingCategory)
                        <!-- Informações da Categoria -->
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg text-3xl" 
                                 style="background: linear-gradient(135deg, {{ $deletingCategory->hexcolor_category }}, {{ $deletingCategory->hexcolor_category }}cc)">
                                🏷️
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $deletingCategory->description }}</h4>
                            <p class="text-gray-600">{{ $deletingCategory->desc_category }}</p>
                        </div>
                        
                        <!-- Aviso -->
                        <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl p-6 mb-6">
                            <div class="flex items-start">
                                <span class="text-red-600 mr-3 mt-1 flex-shrink-0 text-2xl">⚠️</span>
                                <div>
                                    <h5 class="font-bold text-red-900 mb-1">Atenção!</h5>
                                    <p class="text-red-800 text-sm">
                                        Esta ação não pode ser desfeita. A categoria será permanentemente removida do sistema.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button wire:click="cancelDelete" 
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 font-bold rounded-2xl hover:from-gray-200 hover:to-gray-300 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            ❌ Cancelar
                        </button>
                        <button wire:click="deleteCategory" 
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-2xl hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            🗑️ Excluir Categoria
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
