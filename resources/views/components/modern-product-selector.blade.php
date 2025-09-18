@props([
    'products' => [],
    'selectedProducts' => [],
    'categories' => [],
    'searchTerm' => '',
    'selectedCategory' => '',
    'title' => 'Selecionar Produtos',
    'emptyMessage' => 'Nenhum produto disponível',
    'showQuantityInput' => true,
    'showPriceInput' => true,
    'wireModel' => 'selectedProducts'
])

<!-- CSS dos produtos necessário para os cards -->
<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

<div class="w-full h-[75vh] flex">
    <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
    <div class="w-3/4 bg-white dark:bg-zinc-800 flex flex-col">
        <!-- Header com Controles -->
        <div class="p-4 border-b border-gray-200 dark:border-zinc-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-grid text-indigo-600 dark:text-indigo-400 mr-2"></i>
                {{ $title }}
            </h2>

            <!-- Controles de pesquisa e filtro -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Campo de pesquisa -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-search text-gray-400"></i>
                        </div>
                        <input type="text"
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Pesquisar produtos por nome ou código..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                    </div>
                </div>

                <!-- Filtro de categoria -->
                @if(count($categories) > 0)
                <div class="flex items-center">
                    <select wire:model.live="selectedCategory"
                            class="px-3 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                        <option value="">Todas as categorias</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id_category ?? $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        <!-- Grid de Produtos com Scroll -->
        <div class="flex-1 p-6 overflow-y-auto">
            @if(count($products) === 0)
                <!-- Estado vazio -->
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8v2m0 4h.01M21 21l-5-5m5 5v-4a1 1 0 00-1-1h-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        {{ $emptyMessage }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">
                        @if($searchTerm)
                            Tente pesquisar com outros termos
                        @else
                            Cadastre produtos para começar
                        @endif
                    </p>
                </div>
            @else
                <!-- Grid de Cards de Produtos -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($products as $product)
                        <x-modern-product-card
                            :product="$product"
                            :selected="collect($selectedProducts)->pluck('id')->contains($product->id)"
                            clickAction="toggleProduct({{ $product->id }})"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Lado Direito: Produtos Selecionados (1/4 da tela) -->
    <div class="w-1/4 flex flex-col">
        <x-selected-products-panel
            :selectedProducts="$selectedProducts"
            :showQuantityInput="$showQuantityInput"
            :showPriceInput="$showPriceInput"
            :wireModel="$wireModel"
            wire:key="selected-products-{{ count($selectedProducts) }}"
        />
    </div>
</div>

<style>
    /* Apenas estilos específicos de seleção, sem conflitar com o CSS dos produtos */
    .product-card-modern.selected {
        border-color: #9575cd !important;
        transform: scale(1.02);
        box-shadow: 0 8px 32px rgba(149, 117, 205, 0.3) !important;
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
</style>
