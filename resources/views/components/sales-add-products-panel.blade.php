@props([
    'newProducts' => [],
    'filteredProducts' => collect(),
    'hasSelectedProducts' => false,
    'totalPrice' => 0,
    'sale'
])

<!-- Header do painel direito -->
<div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
        <i class="bi bi-cart-plus text-emerald-600 dark:text-emerald-400 mr-2 text-sm"></i>
        Novos Produtos ({{ count($newProducts) }})
    </h3>
</div>

<!-- Lista de produtos selecionados com scroll -->
<div class="flex-1 overflow-y-auto">
    @if(!$hasSelectedProducts)
        <div class="p-4 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30 rounded-2xl flex items-center justify-center">
                <i class="bi bi-cart-x text-2xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                Nenhum produto selecionado
            </h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Clique nos produtos à esquerda para adicioná-los à venda
            </p>
        </div>
    @else
        <div class="p-2 space-y-3">
            @foreach($newProducts as $index => $productItem)
                @if($productItem['product_id'])
                    @php
                        $selectedProduct = $filteredProducts->find($productItem['product_id']);
                    @endphp

                    @if($selectedProduct)
                        <!-- Card de produto selecionado modernizado -->
                        <div class="bg-gradient-to-r from-white to-emerald-50 dark:from-zinc-800 dark:to-emerald-900/20 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-zinc-700 hover:shadow-md transition-all duration-200">
                            <!-- Header do produto com imagem -->
                            <div class="flex items-center mb-3">
                                <div class="flex-shrink-0 mr-3">
                                    <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $selectedProduct->name }}"
                                         class="w-10 h-10 rounded-lg object-cover ring-2 ring-emerald-200 dark:ring-emerald-700">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $selectedProduct->name }}">
                                        {{ $selectedProduct->name }}
                                    </h4>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                            #{{ $selectedProduct->product_code }}
                                        </p>
                                        <button type="button"
                                                wire:click="removeProductRow({{ $index }})"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded transition-colors">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </div>
                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                                        Est: {{ $selectedProduct->stock_quantity }}
                                    </div>
                                </div>
                            </div>

                            <!-- Controles em grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Quantidade -->
                                <div>
                                    <label class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1 block">Quantidade</label>
                                    <input type="number"
                                           wire:model="newProducts.{{ $index }}.quantity"
                                           min="1"
                                           max="{{ $selectedProduct->stock_quantity }}"
                                           class="w-full h-7 text-center text-xs border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>

                                <!-- Preço Unitário -->
                                <div>
                                    <label class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1 block">Preço Unit.</label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-xs text-gray-500">R$</span>
                                        <input type="number"
                                               wire:model="newProducts.{{ $index }}.price_sale"
                                               step="0.01"
                                               min="0"
                                               class="w-full h-7 text-xs border border-gray-300 dark:border-zinc-600 rounded-lg pl-7 pr-2 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Total do item destacado -->
                            <div class="mt-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-lg p-2 border border-emerald-200 dark:border-emerald-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-emerald-700 dark:text-emerald-300 font-medium flex items-center">
                                        <i class="bi bi-calculator mr-1"></i>
                                        Subtotal:
                                    </span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                        R$ {{ number_format($productItem['quantity'] * $productItem['price_sale'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    @endif
</div>

<!-- Footer: Total Geral e Navegação -->
<div class="p-3 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
    <!-- Total Geral -->
    @if($hasSelectedProducts)
        <div class="mb-3 p-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-lg border border-emerald-200 dark:border-emerald-700">
            <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Total:</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    R$ {{ number_format($totalPrice, 2, ',', '.') }}
                </span>
            </div>
        </div>
    @endif

    <!-- Navegação -->
    <div class="flex gap-2">
        <a href="{{ route('sales.show', $sale->id) }}"
           class="group relative flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white text-xs font-medium transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
            <i class="bi bi-arrow-left mr-1 group-hover:scale-110 transition-transform duration-200"></i>
            Voltar
            <!-- Efeito hover ring -->
            <div class="absolute inset-0 rounded-xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </a>

        <button type="button"
                wire:click="addProducts"
                @if(!$hasSelectedProducts) disabled @endif
                class="group relative flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm {{ $hasSelectedProducts ? 'bg-gradient-to-br from-emerald-500 via-green-500 to-teal-600 hover:from-emerald-600 hover:via-green-600 hover:to-teal-700 border border-emerald-300' : 'bg-gray-400 cursor-not-allowed border border-gray-300' }}">
            <span class="flex items-center">
                <i class="bi bi-plus-circle mr-1 group-hover:scale-110 transition-transform duration-200"></i>
                Adicionar
            </span>
            @if($hasSelectedProducts)
            <!-- Efeito hover ring -->
            <div class="absolute inset-0 rounded-xl bg-emerald-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            @endif
        </button>
    </div>
</div>
