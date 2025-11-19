@props([
    'availableProducts' => [],
    'selectedProducts' => [],
    'wireModel' => 'selectedProducts',
    'emptyStateTitle' => 'Nenhum produto disponível',
    'emptyStateDescription' => 'Você precisa ter produtos cadastrados para criar um kit.',
    'emptyStateButtonRoute' => null,
    'emptyStateButtonText' => 'Cadastrar Produto'
])

<div class="space-y-6">
    @if(count($availableProducts) > 0)
        <!-- Search Bar -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="bi bi-search text-slate-400"></i>
            </div>
            <input type="text"
                x-data
                x-on:input="
                    const searchTerm = $event.target.value.toLowerCase();
                    const products = document.querySelectorAll('.product-card');
                    products.forEach(product => {
                        const productName = product.dataset.productName.toLowerCase();
                        if (productName.includes(searchTerm)) {
                            product.style.display = 'block';
                        } else {
                            product.style.display = 'none';
                        }
                    });
                "
                class="w-full pl-12 pr-4 py-4 border-2 border-slate-200 dark:border-slate-600 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-purple-500 focus:ring-purple-500/20 focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                placeholder="Buscar produtos...">
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($availableProducts as $product)
                <div class="product-card group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl p-6 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 hover:scale-[1.02] transition-all duration-300"
                     data-product-name="{{ $product->name }}"
                     x-data="{
                        selected: @entangle($wireModel).{{ $product->id }}.selected,
                        quantity: @entangle($wireModel).{{ $product->id }}.quantity,
                        price: @entangle($wireModel).{{ $product->id }}.price,
                        salePrice: @entangle($wireModel).{{ $product->id }}.salePrice
                     }">

                    <!-- Product Image -->
                    <div class="relative mb-4">
                        @if($product->image && $product->image !== 'product-placeholder.png')
                            <img src="{{ asset('storage/products/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-32 object-cover rounded-xl shadow-lg">
                        @else
                            <div class="w-full h-32 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 rounded-xl flex items-center justify-center">
                                <i class="bi bi-image text-4xl text-slate-400"></i>
                            </div>
                        @endif

                        <!-- Selection Indicator -->
                        <div class="absolute top-2 right-2">
                            <input type="checkbox"
                                   x-model="selected"
                                   class="w-6 h-6 text-purple-600 bg-white/90 border-2 border-purple-300 rounded-lg focus:ring-purple-500 focus:ring-2 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-3">
                        <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg leading-tight group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                            {{ $product->name }}
                        </h3>

                        <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-400">
                            <span class="flex items-center">
                                <i class="bi bi-upc-scan mr-2"></i>
                                {{ $product->product_code }}
                            </span>
                            <span class="flex items-center">
                                <i class="bi bi-boxes mr-2"></i>
                                {{ $product->stock_quantity }} unid.
                            </span>
                        </div>

                        <div class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-3 border border-emerald-200 dark:border-emerald-800">
                            <div class="text-sm text-emerald-700 dark:text-emerald-300 font-medium">Preço de Venda</div>
                            <div class="text-xl font-bold text-emerald-800 dark:text-emerald-200">
                                R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                            </div>
                        </div>

                        <!-- Quantity and Price Selector (shown when selected) -->
                        <div x-show="selected"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="space-y-4">

                            <!-- Quantidade -->
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                                <label class="block text-sm font-medium text-purple-700 dark:text-purple-300 mb-2">
                                    Quantidade no Kit
                                </label>
                                <div class="flex items-center space-x-3">
                                    <button type="button"
                                            @click="quantity = Math.max(1, quantity - 1)"
                                            class="w-8 h-8 bg-purple-500 hover:bg-purple-600 text-white rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number"
                                           x-model="quantity"
                                           min="1"
                                           :max="{{ $product->stock_quantity }}"
                                           class="w-16 text-center py-2 border border-purple-300 dark:border-purple-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-purple-500 focus:border-purple-500">
                                    <button type="button"
                                            @click="quantity = Math.min({{ $product->stock_quantity }}, quantity + 1)"
                                            class="w-8 h-8 bg-purple-500 hover:bg-purple-600 text-white rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                <div class="text-xs text-purple-600 dark:text-purple-400 mt-2">
                                    Estoque disponível: {{ $product->stock_quantity }} unidades
                                </div>
                            </div>

                            <!-- Preços Personalizados -->
                            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                                <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-3">Preços para o Kit</h4>

                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Preço de Custo -->
                                    <div>
                                        <label class="block text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">
                                            Preço de Custo
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-500 text-sm">R$</span>
                                            <input type="text"
                                                   x-model="price"
                                                   placeholder="{{ number_format($product->price, 2, ',', '.') }}"
                                                   class="w-full pl-8 pr-3 py-2 text-sm border border-blue-300 dark:border-blue-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>

                                    <!-- Preço de Venda -->
                                    <div>
                                        <label class="block text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">
                                            Preço de Venda
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-500 text-sm">R$</span>
                                            <input type="text"
                                                   x-model="salePrice"
                                                   wire:model="selectedProducts.{{ $product->id }}.salePrice"
                                                   placeholder="{{ number_format($product->price_sale, 2, ',', '.') }}"
                                                   class="w-full pl-8 pr-3 py-2 text-sm border border-blue-300 dark:border-blue-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                                    💡 Deixe em branco para usar os preços padrão do produto
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Selected Products Summary -->
        <div x-data="{ selectedCount: 0 }"
             x-init="
                $watch('$wire.{{ $wireModel }}', (value) => {
                    selectedCount = Object.values(value || {}).filter(item => item.selected).length;
                });
             "
             x-show="selectedCount > 0"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-gradient-to-r from-purple-500 via-purple-600 to-indigo-600 rounded-2xl p-6 text-white shadow-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                        <i class="bi bi-check-circle-fill text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Produtos Selecionados</h3>
                        <p class="text-purple-100" x-text="selectedCount + ' produto(s) adicionado(s) ao kit'"></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-100">Total estimado</div>
                    <div class="text-2xl font-bold" x-text="'R$ ' + calculateTotal()"></div>
                </div>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="relative inline-block">
                <i class="bi bi-box-seam text-8xl text-slate-300 dark:text-slate-600"></i>
                <div class="absolute -top-2 -right-2 w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                    <i class="bi bi-exclamation text-white text-lg"></i>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-200 mt-6 mb-3">
                {{ $emptyStateTitle }}
            </h3>
            <p class="text-lg text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto">
                {{ $emptyStateDescription }}
            </p>

            @if($emptyStateButtonRoute)
                <a href="{{ route($emptyStateButtonRoute) }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="bi bi-plus-circle mr-2"></i>
                    {{ $emptyStateButtonText }}
                </a>
            @endif
        </div>
    @endif
</div>

<script>
function calculateTotal() {
    const selectedProducts = @js($selectedProducts) || {};
    let total = 0;

    Object.entries(selectedProducts).forEach(([productId, data]) => {
        if (data.selected && data.price && data.quantity) {
            total += parseFloat(data.price) * parseInt(data.quantity);
        }
    });

    return total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
