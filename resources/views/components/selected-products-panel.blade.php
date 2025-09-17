@props([
    'selectedProducts' => [],
    'showQuantityInput' => true,
    'showPriceInput' => true,
    'wireModel' => 'selectedProducts',
    'title' => 'Produtos Selecionados',
    'showTotal' => true,
    'showActions' => false,
    'cancelAction' => null,
    'confirmAction' => null,
    'cancelText' => 'Cancelar',
    'confirmText' => 'Confirmar'
])

<!-- Header do painel direito -->
<div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
        <i class="bi bi-cart text-green-600 dark:text-green-400 mr-2 text-sm"></i>
        {{ $title }} ({{ count($selectedProducts) }})
    </h3>
</div>

<!-- Lista de produtos selecionados com scroll -->
<div class="flex-1 overflow-y-auto">
    @if(empty($selectedProducts))
        <div class="p-3 text-center">
            <div class="text-gray-400 mb-2">
                <i class="bi bi-cart-x text-2xl"></i>
            </div>
            <p class="text-gray-500 dark:text-gray-500 text-xs">
                Clique nos produtos à esquerda para adicioná-los
            </p>
        </div>
    @else
        <div class="p-2 space-y-2">
            @foreach($selectedProducts as $index => $productItem)
                @if(isset($productItem['id']) && $productItem['id'])
                    @php
                        // Buscar o produto completo se necessário
                        $selectedProduct = $productItem['product'] ?? $productItem;
                    @endphp

                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-zinc-700">
                        <!-- Header do produto -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <img src="{{ ($selectedProduct['image'] ?? null) ? asset('storage/products/' . $selectedProduct['image']) : asset('storage/products/product-placeholder.png') }}"
                                     alt="{{ $selectedProduct['name'] ?? 'Produto' }}"
                                     class="w-8 h-8 rounded object-cover">

                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-xs truncate">{{ $selectedProduct['name'] ?? 'Produto' }}</h4>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedProduct['product_code'] ?? '' }}</p>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Est: {{ $selectedProduct['stock_quantity'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controles -->
                        <div class="space-y-2">
                            <!-- Quantidade e Preço na mesma linha -->
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Quantidade -->
                                @if($showQuantityInput)
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Qtd:</label>
                                    <input type="number"
                                           value="{{ $productItem['quantity'] ?? 1 }}"
                                           wire:change="updateProductQuantity({{ $productItem['id'] }}, $event.target.value)"
                                           min="1"
                                           max="{{ $selectedProduct['stock_quantity'] ?? 999 }}"
                                           class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                @endif

                                <!-- Preço -->
                                @if($showPriceInput)
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Preço:</label>
                                    <input type="number"
                                           value="{{ number_format($productItem['salePrice'] ?? 0, 2, '.', '') }}"
                                           wire:change="updateProductPrice({{ $productItem['id'] }}, $event.target.value)"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                @endif
                            </div>

                            <!-- Preço de Venda (se habilitado) -->
                            @if($showPriceInput && isset($productItem['salePrice']))
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Preço Venda:</label>
                                <input type="number"
                                       wire:model.live="{{ $wireModel }}.{{ $index }}.salePrice"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            @endif

                            <!-- Total do item -->
                            @if($showTotal)
                            <div class="pt-1 border-t border-gray-200 dark:border-zinc-600">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Total:</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-green-600 dark:text-green-400">
                                            R$ {{ number_format(($productItem['quantity'] ?? 1) * ($productItem['price'] ?? $productItem['salePrice'] ?? 0), 2, ',', '.') }}
                                        </span>
                                        <!-- Botão de excluir -->
                                        <button type="button"
                                                wire:click="removeProduct({{ $productItem['id'] }})"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1 hover:bg-red-50 dark:hover:bg-red-900 rounded transition-colors duration-200">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>

<!-- Footer: Total Geral e Navegação -->
@if($showTotal || $showActions)
<div class="p-3 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
    <!-- Total Geral -->
    @if($showTotal && !empty($selectedProducts))
        <div class="mb-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-700">
            <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-green-800 dark:text-green-200">Total:</span>
                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                    @php
                        $total = collect($selectedProducts)->sum(function($item) {
                            return ($item['quantity'] ?? 1) * ($item['price'] ?? $item['salePrice'] ?? 0);
                        });
                    @endphp
                    R$ {{ number_format($total, 2, ',', '.') }}
                </span>
            </div>
        </div>
    @endif

    <!-- Navegação -->
    @if($showActions)
    <div class="flex gap-2">
        @if($cancelAction)
        <button type="button"
                wire:click="{{ $cancelAction }}"
                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
            <i class="bi bi-arrow-left mr-1"></i>
            {{ $cancelText }}
        </button>
        @endif

        @if($confirmAction)
        <button type="button"
                wire:click="{{ $confirmAction }}"
                @if(empty($selectedProducts)) disabled @endif
                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-white text-xs font-bold rounded-lg shadow-lg transition-all duration-200
                       {{ !empty($selectedProducts) ? 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700' : 'bg-gray-400 cursor-not-allowed' }}">
            <i class="bi bi-check mr-1"></i>
            {{ $confirmText }}
        </button>
        @endif
    </div>
    @endif
</div>
@endif
