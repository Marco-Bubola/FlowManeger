@props([
    'newProducts' => [],
    'filteredProducts' => collect(),
    'hasSelectedProducts' => false,
    'totalPrice' => 0,
    'sale'
])

<!-- Header do painel direito -->
<div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-gradient-to-r from-white to-emerald-50/50 dark:from-zinc-800 dark:to-emerald-900/10">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-sm">
                <i class="bi bi-cart-plus text-white text-xs"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Carrinho</h3>
                @php $selectedCount = count(array_filter($newProducts, fn($p) => !empty($p['product_id']))); @endphp
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight">
                    {{ $selectedCount }} {{ $selectedCount === 1 ? 'item' : 'itens' }} selecionados
                </p>
            </div>
        </div>
        @if($hasSelectedProducts)
        <button type="button"
                wire:click="clearAllProducts"
                wire:confirm="Remover todos os produtos selecionados?"
                class="flex items-center gap-1 text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 px-2 py-1 rounded-lg transition-colors border border-red-200 dark:border-red-800/40">
            <i class="bi bi-x-circle text-xs"></i>
            Limpar
        </button>
        @endif
    </div>
</div>

<!-- Lista de produtos selecionados com scroll -->
<div class="flex-1 overflow-y-auto">
    @if(!$hasSelectedProducts)
        <div class="flex flex-col items-center justify-center h-full p-4 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30 rounded-2xl flex items-center justify-center shadow-inner">
                <i class="bi bi-cart text-2xl text-emerald-400 dark:text-emerald-500"></i>
            </div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                Carrinho vazio
            </h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                Clique nos produtos à esquerda para adicioná-los ao carrinho
            </p>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg px-3 py-2 border border-emerald-200 dark:border-emerald-800">
                <i class="bi bi-hand-index"></i>
                Toque no card para selecionar
            </div>
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
                                <div class="flex-shrink-0 mr-3 relative">
                                    <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $selectedProduct->name }}"
                                         class="w-10 h-10 rounded-lg object-cover ring-2 ring-emerald-200 dark:ring-emerald-700">
                                    <!-- Número do item -->
                                    <span class="absolute -top-1.5 -left-1.5 w-4 h-4 bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $selectedProduct->name }}">
                                        {{ $selectedProduct->name }}
                                    </h4>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                        #{{ $selectedProduct->product_code }}
                                    </p>
                                </div>
                                <button type="button"
                                        wire:click="removeProductRow({{ $index }})"
                                        class="flex-shrink-0 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded-lg transition-colors ml-1">
                                    <i class="bi bi-x-lg text-xs"></i>
                                </button>
                            </div>

                            <!-- Controles em grid -->
                            @php $exceedsStock = $productItem['quantity'] > $selectedProduct->stock_quantity; @endphp
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Quantidade -->
                                <div>
                                    <label class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mb-0.5 block uppercase tracking-wide">
                                        Qtd
                                        <span class="normal-case font-normal text-gray-400">/ {{ $selectedProduct->stock_quantity }} disp.</span>
                                    </label>
                                    <input type="number"
                                           wire:model.live="newProducts.{{ $index }}.quantity"
                                           min="1"
                                           max="{{ $selectedProduct->stock_quantity }}"
                                           class="w-full h-7 text-center text-xs border rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors {{ $exceedsStock ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-zinc-600' }}">
                                    @if($exceedsStock)
                                    <p class="text-[10px] text-red-500 mt-0.5 flex items-center gap-0.5">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Excede estoque
                                    </p>
                                    @endif
                                </div>

                                <!-- Preço Unitário -->
                                <div>
                                    <label class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mb-0.5 block uppercase tracking-wide">Preço Unit.</label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-[10px] text-gray-500 dark:text-gray-400">R$</span>
                                        <input type="number"
                                               wire:model.live="newProducts.{{ $index }}.price_sale"
                                               step="0.01"
                                               min="0"
                                               class="w-full h-7 text-xs border border-gray-300 dark:border-zinc-600 rounded-lg pl-6 pr-1 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Total do item destacado -->
                            <div class="mt-2 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-lg p-2 border border-emerald-200 dark:border-emerald-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-medium flex items-center gap-1">
                                        <i class="bi bi-calculator"></i> Subtotal
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
    <!-- Resumo total -->
    @if($hasSelectedProducts)
        @php
            $selectedCount = count(array_filter($newProducts, fn($p) => !empty($p['product_id'])));
            $totalQty = array_sum(array_map(fn($p) => !empty($p['product_id']) ? (int)$p['quantity'] : 0, $newProducts));
        @endphp
        <div class="mb-3 p-2.5 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">
                    <i class="bi bi-bag-check mr-1"></i>{{ $selectedCount }} {{ $selectedCount === 1 ? 'item' : 'itens' }} · {{ $totalQty }} un.
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Total a adicionar:</span>
                <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                    R$ {{ number_format($totalPrice, 2, ',', '.') }}
                </span>
            </div>
        </div>
    @endif

    <!-- Botão Confirmar centralizado + link Cancelar -->
    <button type="button"
            wire:click="addProducts"
            wire:loading.attr="disabled"
            wire:target="addProducts"
            @if(!$hasSelectedProducts) disabled @endif
            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white text-sm font-bold transition-all duration-300 shadow-md mb-2
            {{ $hasSelectedProducts
                ? 'bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 hover:shadow-lg hover:shadow-emerald-500/25 active:scale-95'
                : 'bg-gray-300 dark:bg-zinc-600 cursor-not-allowed text-gray-500 dark:text-zinc-400' }}">
        <span wire:loading.remove wire:target="addProducts" class="flex items-center gap-2">
            <i class="bi bi-check2-circle text-base"></i>
            Confirmar Adição
        </span>
        <span wire:loading wire:target="addProducts" class="flex items-center gap-2">
            <i class="bi bi-hourglass-split animate-spin"></i>
            Adicionando...
        </span>
    </button>

    <a href="{{ route('sales.show', $sale->id) }}"
       class="w-full flex items-center justify-center gap-1 px-4 py-2 rounded-xl text-gray-600 dark:text-gray-400 text-xs font-medium hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
        <i class="bi bi-arrow-left text-xs"></i>
        Cancelar e voltar
    </a>
</div>
