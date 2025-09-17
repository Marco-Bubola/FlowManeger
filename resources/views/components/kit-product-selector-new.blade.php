@props([
    'products' => []
])

<div x-data="{
    selectedProducts: [],
    availableProducts: @js($products),

    addProduct(productId) {
        const product = this.availableProducts.find(p => p.id === productId);
        if (product && !this.selectedProducts.find(p => p.id === productId)) {
            this.selectedProducts.push({
                id: product.id,
                name: product.name,
                product_code: product.product_code,
                price: parseFloat(product.price || 0),
                salePrice: parseFloat(product.price_sale || 0),
                quantity: 1
            });
            this.updateTotals();
            this.updateLivewire();
        }
    },

    removeProduct(productId) {
        this.selectedProducts = this.selectedProducts.filter(p => p.id !== productId);
        this.updateTotals();
        this.updateLivewire();
    },

    updateQuantity(productId, quantity) {
        const product = this.selectedProducts.find(p => p.id === productId);
        if (product) {
            product.quantity = Math.max(1, parseInt(quantity) || 1);
            this.updateTotals();
            this.updateLivewire();
        }
    },

    updatePrice(productId, price) {
        const product = this.selectedProducts.find(p => p.id === productId);
        if (product) {
            product.price = parseFloat(price) || 0;
            this.updateTotals();
            this.updateLivewire();
        }
    },

    updateSalePrice(productId, salePrice) {
        const product = this.selectedProducts.find(p => p.id === productId);
        if (product) {
            product.salePrice = parseFloat(salePrice) || 0;
            this.updateTotals();
            this.updateLivewire();
        }
    },

    updateTotals() {
        const productsTotal = this.selectedProducts.reduce((total, product) => {
            return total + (product.price * product.quantity);
        }, 0);

        const productsSaleTotal = this.selectedProducts.reduce((total, product) => {
            return total + (product.salePrice * product.quantity);
        }, 0);

        // Atualizar elemento de exibição do total
        const totalElement = document.getElementById('selected-products-total');
        if (totalElement) {
            totalElement.textContent = productsTotal.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Calcular total do kit incluindo custos adicionais
        this.calculateKitTotal();
    },

    calculateKitTotal() {
        const productsTotal = this.selectedProducts.reduce((total, product) => {
            return total + (product.price * product.quantity);
        }, 0);

        const productsSaleTotal = this.selectedProducts.reduce((total, product) => {
            return total + (product.salePrice * product.quantity);
        }, 0);

        // Obter custos adicionais do input
        const additionalCostsInput = document.getElementById('additional_costs');
        let additionalCosts = 0;
        if (additionalCostsInput && additionalCostsInput.value) {
            // Remover formatação da moeda e converter para número
            const cleanValue = additionalCostsInput.value.replace(/[^\d,]/g, '').replace(',', '.');
            additionalCosts = parseFloat(cleanValue) || 0;
        }

        const kitTotalCost = productsTotal + additionalCosts;
        const kitTotalSale = productsSaleTotal + additionalCosts;

        const kitTotalElement = document.getElementById('kit-total-price');
        if (kitTotalElement) {
            kitTotalElement.textContent = kitTotalCost.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        return { cost: kitTotalCost, sale: kitTotalSale };
    },

    updateLivewire() {
        // Enviar dados para o Livewire
        $wire.set('selectedProducts', this.selectedProducts);

        const totals = this.calculateKitTotal();
        $wire.set('calculated_cost_price', totals.cost);
        $wire.set('calculated_sale_price', totals.sale);
    }
}" x-init="updateTotals()" class="space-y-6">

    @if(count($products) > 0)
        <!-- Busca de produtos -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="bi bi-search text-slate-400"></i>
            </div>
            <input type="text"
                x-on:input="
                    const searchTerm = $event.target.value.toLowerCase();
                    const cards = document.querySelectorAll('.product-card-item');
                    cards.forEach(card => {
                        const productName = card.dataset.productName.toLowerCase();
                        if (productName.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                "
                class="w-full pl-12 pr-4 py-4 border-2 border-slate-200 dark:border-slate-600 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-purple-500 focus:ring-purple-500/20 focus:ring-4 focus:outline-none transition-all duration-300"
                placeholder="Buscar produtos...">
        </div>

        <!-- Grid de produtos disponíveis -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @foreach($products as $product)
                <div class="product-card-item bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-200"
                     data-product-name="{{ $product->name }}">

                    <!-- Info do produto -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 dark:text-slate-200">{{ $product->name }}</h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $product->product_code }}</p>
                            <p class="text-sm text-emerald-600 dark:text-emerald-400">
                                Custo: R$ {{ number_format($product->price ?? 0, 2, ',', '.') }} |
                                Venda: R$ {{ number_format($product->price_sale ?? 0, 2, ',', '.') }}
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addProduct({{ $product->id }})"
                            x-show="!selectedProducts.find(p => p.id === {{ $product->id }})"
                            class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Produtos selecionados -->
        <div x-show="selectedProducts.length > 0" class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Produtos Selecionados</h4>

            <template x-for="product in selectedProducts" :key="product.id">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h5 class="font-medium text-slate-800 dark:text-slate-200" x-text="product.name"></h5>
                            <p class="text-sm text-slate-600 dark:text-slate-400" x-text="product.product_code"></p>
                        </div>
                        <button
                            type="button"
                            @click="removeProduct(product.id)"
                            class="text-red-500 hover:text-red-700 transition-colors duration-200">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <!-- Controles de quantidade e preços -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Quantidade -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantidade</label>
                            <input
                                type="number"
                                :value="product.quantity"
                                @input="updateQuantity(product.id, $event.target.value)"
                                min="1"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100"
                            />
                        </div>

                        <!-- Preço de Custo -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Preço Custo (R$)</label>
                            <input
                                type="number"
                                step="0.01"
                                :value="product.price"
                                @input="updatePrice(product.id, $event.target.value)"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100"
                            />
                        </div>

                        <!-- Preço de Venda -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Preço Venda (R$)</label>
                            <input
                                type="number"
                                step="0.01"
                                :value="product.salePrice"
                                @input="updateSalePrice(product.id, $event.target.value)"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>

    @else
        <!-- Estado vazio -->
        <div class="text-center py-12">
            <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-slate-400 to-slate-500 rounded-2xl mx-auto mb-4">
                <i class="bi bi-box text-white text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-2">Nenhum produto disponível</h3>
            <p class="text-slate-600 dark:text-slate-400">
                Você precisa ter produtos cadastrados para criar um kit.
            </p>
        </div>
    @endif

    <!-- Listener para atualizar totais quando custos adicionais mudarem -->
    <script>
        document.addEventListener('alpine:init', () => {
            const additionalCostsInput = document.getElementById('additional_costs');
            if (additionalCostsInput) {
                additionalCostsInput.addEventListener('input', () => {
                    // Trigger recalculation
                    const component = Alpine.$data(document.querySelector('[x-data]'));
                    if (component && component.calculateKitTotal) {
                        component.calculateKitTotal();
                    }
                });
            }
        });
    </script>
</div>
