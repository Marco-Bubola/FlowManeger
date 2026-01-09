<div class="w-full">
    <!-- Incluir CSS dos produtos -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
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

        .exceeds-limit {
            border-color: #ef4444 !important;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
    </style>

<!-- Header Moderno igual às outras páginas -->
    <x-sales-header
        title="{{ $isEditing ? 'Editar' : 'Registrar' }} Produtos da Contemplação"
        :subtitle="'Contemplado: ' . $contemplation->participant->client->name . ' • Consórcio: ' . $contemplation->participant->consortium->name"
        icon="bi-box-seam"
        :backRoute="route('consortiums.show', $contemplation->participant->consortium)">
        <x-slot name="actions">
            <div class="flex items-center gap-3">
                <!-- Cards de informação -->
                <div class="px-4 py-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-wallet2 text-white/90 text-lg"></i>
                        <div>
                            <div class="text-xs text-white/70">Valor Máximo</div>
                            <div class="text-base font-black text-white">R$ {{ number_format($maxValue, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-2.5 rounded-xl {{ $this->getTotalPrice() > $maxValue ? 'bg-red-500/30 border-red-300/50' : 'bg-emerald-500/30 border-emerald-300/50' }} border backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-cart-check text-white/90 text-lg"></i>
                        <div>
                            <div class="text-xs text-white/90">Selecionado</div>
                            <div class="text-base font-black text-white">R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                @if($this->getExcessValue() > 0)
                    <div class="px-4 py-2.5 rounded-xl bg-orange-500/30 border border-orange-300/50 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle text-white/90 text-lg"></i>
                            <div>
                                <div class="text-xs text-white/90">A Pagar À Parte</div>
                                <div class="text-base font-black text-white">R$ {{ number_format($this->getExcessValue(), 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-piggy-bank text-white/90 text-lg"></i>
                            <div>
                                <div class="text-xs text-white/70">Restante</div>
                                <div class="text-base font-black text-white">R$ {{ number_format($this->getRemainingValue(), 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>
    </x-sales-header>

    <!-- Layout Split 3/4 e 1/4 -->
    <div class="w-full h-[81vh] flex">
        <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
        <div class="w-3/4 bg-white dark:bg-zinc-800 flex flex-col">
            <!-- Header com Controles -->
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-box text-purple-600 dark:text-purple-400 mr-3"></i>
                    Selecionar Produtos
                </h2>

                <!-- Controles de pesquisa e filtro -->
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Campo de pesquisa -->
                    <div class="flex-1">
                        <div class="relative">
                            <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text"
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Buscar produtos por nome, código ou categoria..."
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Filtro de categoria -->
                    <div class="flex items-center">
                        <select wire:model.live="selectedCategory"
                                class="px-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200">
                            <option value="">Todas as categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Alerta se ultrapassar o limite -->
                @if($this->getTotalPrice() > $maxValue)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 rounded-xl">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-exclamation-triangle-fill text-red-600 text-2xl"></i>
                            <div>
                                <p class="font-bold text-red-900 dark:text-red-200">Valor excedido!</p>
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    O valor total dos produtos selecionados ultrapassa o limite de R$ {{ number_format($maxValue, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Grid de Produtos com Scroll -->
            <div class="flex-1 p-6 overflow-y-auto">
                @if($filteredProducts->isEmpty())
                    <!-- Estado vazio -->
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                            <i class="bi bi-box text-8xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            @if($searchTerm)
                                Nenhum produto encontrado para "{{ $searchTerm }}"
                            @else
                                Nenhum produto disponível
                            @endif
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-center">
                            @if($searchTerm)
                                Tente buscar por outro termo ou limpe o campo de pesquisa.
                            @else
                                Adicione produtos ao sistema para poder incluí-los nas contemplações.
                            @endif
                        </p>
                    </div>
                @else
                    <!-- Grid de Cards de Produtos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($filteredProducts as $product)
                            @php
                                $isSelected = $this->isProductSelected($product->id);
                            @endphp

                            <!-- Produto com CSS customizado -->
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})"
                                 wire:key="product-{{ $product->id }}">

                                <!-- Toggle de seleção -->
                                <div class="btn-action-group flex gap-2">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                {{ $isSelected
                                                    ? 'bg-emerald-600 border-emerald-600 text-white'
                                                    : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400 dark:hover:border-emerald-500' }}">
                                        @if($isSelected)
                                        <i class="bi bi-check text-sm"></i>
                                        @endif
                                    </div>
                                </div>

                                <!-- Área da imagem -->
                                <div class="product-img-area">
                                    <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $product->name }}"
                                         class="product-img">

                                    @if($product->stock_quantity <= 5)
                                    <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>
                                        Baixo estoque
                                    </div>
                                    @endif

                                    <!-- Código do produto -->
                                    <span class="badge-product-code">
                                        <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                    </span>

                                    <!-- Quantidade em estoque -->
                                    <span class="badge-quantity">
                                        <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                    </span>

                                    <!-- Ícone da categoria -->
                                    @if($product->category)
                                    <div class="category-icon-wrapper">
                                        <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                    </div>
                                    @endif
                                </div>

                                <!-- Conteúdo do card -->
                                <div class="card-body">
                                    <div class="product-title" title="{{ $product->name }}">
                                        {{ ucwords($product->name) }}
                                    </div>

                                    <!-- Preço de venda -->
                                    <div class="price-area">
                                        <span class="badge-price-sale" title="Preço de Venda">
                                            <i class="bi bi-currency-dollar"></i>
                                            {{ number_format($product->price_sale, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Lado Direito: Produtos Selecionados (1/4 da tela) -->
        <div class="w-1/4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-l border-gray-200 dark:border-zinc-700 flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-cart-check text-purple-600"></i>
                    Produtos Selecionados
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ count(array_filter($newProducts, fn($p) => !empty($p['product_id']))) }} produto(s)
                </p>
            </div>

            <!-- Lista de produtos selecionados com scroll -->
            <div class="flex-1 p-6 overflow-y-auto space-y-3">
                @php
                    $hasProducts = false;
                @endphp

                @foreach($newProducts as $index => $item)
                    @if(!empty($item['product_id']))
                        @php
                            $hasProducts = true;
                            $product = \App\Models\Product::find($item['product_id']);
                        @endphp
                        <div class="bg-gradient-to-br from-white to-purple-50/30 dark:from-zinc-800 dark:to-purple-900/10 rounded-2xl shadow-lg border-2 border-purple-200 dark:border-purple-700/50 overflow-hidden hover:shadow-xl transition-all">
                            <!-- Imagem do Produto -->
                            <div class="relative h-32 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30">
                                <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                                <button wire:click="removeProductRow({{ $index }})"
                                        class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-lg transition-all hover:scale-110">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                                <div class="absolute bottom-2 left-2 px-2 py-1 bg-black/60 backdrop-blur-sm rounded-lg text-xs text-white font-bold">
                                    {{ $product->product_code }}
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="p-4 space-y-3">
                                <h4 class="font-black text-gray-900 dark:text-white text-sm line-clamp-2">{{ $product->name }}</h4>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs font-bold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <i class="bi bi-hash"></i> Qtd.
                                        </label>
                                        <input type="number"
                                               wire:model.live="newProducts.{{ $index }}.quantity"
                                               min="1"
                                               max="{{ $product->stock_quantity }}"
                                               class="w-full px-3 py-2 border-2 border-purple-200 dark:border-purple-700 rounded-xl text-sm font-bold bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <i class="bi bi-currency-dollar"></i> Preço
                                        </label>
                                        <input type="number"
                                               wire:model.live="newProducts.{{ $index }}.price_sale"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border-2 border-purple-200 dark:border-purple-700 rounded-xl text-sm font-bold bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                    </div>
                                </div>

                                <div class="pt-3 border-t-2 border-purple-200 dark:border-purple-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <i class="bi bi-calculator"></i> Subtotal
                                        </span>
                                        <span class="text-lg font-black text-purple-600 dark:text-purple-400">
                                            R$ {{ number_format($item['quantity'] * $item['price_sale'], 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if(!$hasProducts)
                    <div class="flex flex-col items-center justify-center h-full text-center p-6">
                        <i class="bi bi-cart-x text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Nenhum produto selecionado
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                            Clique nos produtos ao lado para selecioná-los
                        </p>
                    </div>
                @endif
            </div>

            <!-- Footer com Total e Botões -->
            <div class="p-6 bg-white dark:bg-zinc-800 border-t-2 border-gray-200 dark:border-zinc-700 space-y-4">
                <!-- Total -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Valor Máximo:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            R$ {{ number_format($maxValue, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Valor Selecionado:</span>
                        <span class="font-semibold {{ $this->getTotalPrice() > $maxValue ? 'text-red-600' : 'text-purple-600' }}">
                            R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="pt-3 border-t-2 border-gray-200 dark:border-zinc-700">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900 dark:text-white">Restante:</span>
                            <span class="text-xl font-black {{ $this->getRemainingValue() < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                R$ {{ number_format($this->getRemainingValue(), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-col gap-2">
                    <button wire:click="save"
                            @if(!$this->hasSelectedProducts() || $this->getTotalPrice() > $maxValue) disabled @endif
                            class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl disabled:shadow-none flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle-fill text-lg"></i>
                        <span>Confirmar Produtos</span>
                    </button>

                    <a href="{{ route('consortiums.show', $contemplation->participant->consortium) }}"
                       class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl transition-all text-center">
                        <i class="bi bi-x-lg mr-2"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Component -->
    <x-toast-notifications />
</div>
