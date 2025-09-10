<div class="min-h-screen w-full bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-zinc-900 dark:via-slate-900 dark:to-indigo-950 py-8">
    <div class="w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with enhanced styling -->
        <div class="mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-pencil-square text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Editar Venda #{{ $sale->id }}
                            </h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Atualize as informações da venda • Criada em {{ $sale->created_at->format('d/m/Y H:i') }}
                            </p>
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    <i class="bi bi-calendar mr-1"></i>
                                    {{ $sale->created_at->diffForHumans() }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <i class="bi bi-currency-dollar mr-1"></i>
                                    R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0 flex gap-3">
                        <button type="button"
                                onclick="window.print()"
                                class="inline-flex items-center px-4 py-2 border border-purple-300 dark:border-purple-600 text-sm font-medium rounded-lg text-purple-700 dark:text-purple-300 bg-white dark:bg-zinc-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                            <i class="bi bi-printer mr-2"></i>
                            Imprimir
                        </button>
                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="bi bi-arrow-left mr-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="update">
            @csrf
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Coluna Principal - Informações da Venda -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Informações Básicas -->
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <i class="bi bi-info-circle text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                Informações da Venda
                            </h2>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Editando</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Cliente -->
                            <div class="space-y-2">
                                <label for="client_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <i class="bi bi-person text-indigo-500 mr-2"></i>Cliente *
                                </label>
                                <select wire:model="client_id"
                                        id="client_id"
                                        class="w-full px-4 py-4 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-lg hover:shadow-xl @error('client_id') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                    <option value="">Selecione um cliente...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="flex items-center mt-2 text-sm text-red-600">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Tipo de Pagamento -->
                            <div class="space-y-2">
                                <label for="tipo_pagamento" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <i class="bi bi-credit-card text-indigo-500 mr-2"></i>Tipo de Pagamento *
                                </label>
                                <select wire:model="tipo_pagamento"
                                        wire:change="$refresh"
                                        id="tipo_pagamento"
                                        class="w-full px-4 py-4 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-lg hover:shadow-xl @error('tipo_pagamento') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                    <option value="a_vista">💳 À Vista</option>
                                    <option value="parcelado">📅 Parcelado</option>
                                </select>
                                @error('tipo_pagamento')
                                    <div class="flex items-center mt-2 text-sm text-red-600">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Número de Parcelas - Aparecer apenas quando parcelado -->
                        <div class="mt-6 transition-all duration-500 ease-in-out {{ $tipo_pagamento === 'parcelado' ? 'opacity-100 max-h-96' : 'opacity-0 max-h-0 overflow-hidden' }}">
                            @if($tipo_pagamento === 'parcelado')
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border-2 border-blue-200 dark:border-blue-800">
                                    <label for="parcelas" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                                        <i class="bi bi-list-ol text-blue-500 mr-2"></i>Número de Parcelas
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                        @for($i = 1; $i <= 12; $i++)
                                            <label class="relative cursor-pointer">
                                                <input type="radio"
                                                       wire:model="parcelas"
                                                       value="{{ $i }}"
                                                       class="sr-only peer">
                                                <div class="flex items-center justify-center h-12 w-full border-2 border-gray-200 dark:border-zinc-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-105 hover:border-blue-300 hover:shadow-md">
                                                    {{ $i }}x
                                                </div>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('parcelas')
                                        <div class="flex items-center mt-3 text-sm text-red-600">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    @if($parcelas > 1)
                                        <div class="mt-4 p-4 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <div class="flex items-center text-sm text-blue-800 dark:text-blue-300">
                                                <i class="bi bi-info-circle mr-2"></i>
                                                <span>Valor por parcela: <strong>R$ {{ number_format($this->getTotalPrice() / $parcelas, 2, ',', '.') }}</strong></span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Informações Adicionais da Venda -->
                        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 dark:bg-zinc-700 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Ativa</p>
                                    </div>
                                    <i class="bi bi-check-circle text-2xl text-green-500"></i>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-zinc-700 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Última Atualização</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $sale->updated_at->format('d/m/Y') }}</p>
                                    </div>
                                    <i class="bi bi-clock text-2xl text-blue-500"></i>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-zinc-700 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Total de Itens</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ count($selectedProducts) }}</p>
                                    </div>
                                    <i class="bi bi-box text-2xl text-purple-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produtos -->
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <i class="bi bi-box text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                Produtos da Venda
                            </h2>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ count($selectedProducts) }} {{ count($selectedProducts) === 1 ? 'produto' : 'produtos' }}
                                </span>
                                <button type="button"
                                        wire:click="addProduct"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-plus-lg mr-2"></i>
                                    Adicionar Produto
                                </button>
                            </div>
                        </div>

                        @error('selectedProducts')
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 rounded-lg">
                                <div class="flex items-center">
                                    <i class="bi bi-exclamation-triangle text-red-500 mr-2"></i>
                                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                            </div>
                        @enderror

                        <div class="space-y-6">
                            @foreach($selectedProducts as $index => $product)
                                <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-zinc-700 dark:to-zinc-600 rounded-2xl p-6 border-2 border-gray-200 dark:border-zinc-600 hover:shadow-lg transition-all duration-300">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <i class="bi bi-tag text-indigo-500 mr-2"></i>
                                            Produto #{{ $index + 1 }}
                                        </h3>
                                        <button type="button"
                                                wire:click="removeProduct({{ $index }})"
                                                class="inline-flex items-center justify-center w-10 h-10 border-2 border-red-300 dark:border-red-600 text-sm font-medium rounded-xl text-red-700 dark:text-red-400 bg-white dark:bg-zinc-600 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-4 focus:ring-red-500/30 transition-all duration-300 hover:scale-110">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Produto -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-box-seam text-blue-500 mr-2"></i>Produto *
                                            </label>
                                            <select wire:model="selectedProducts.{{ $index }}.product_id"
                                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.product_id') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <option value="">Selecione um produto...</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}">{{ $prod->name }} (Estoque: {{ $prod->stock_quantity }})</option>
                                                @endforeach
                                            </select>
                                            @error('selectedProducts.'.$index.'.product_id')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Quantidade -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-plus-slash-minus text-green-500 mr-2"></i>Quantidade *
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model="selectedProducts.{{ $index }}.quantity"
                                                       min="1"
                                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.quantity') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <i class="bi bi-hash text-gray-400"></i>
                                                </div>
                                            </div>
                                            @error('selectedProducts.'.$index.'.quantity')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Preço de Venda -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-currency-dollar text-yellow-500 mr-2"></i>Preço de Venda *
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model="selectedProducts.{{ $index }}.price_sale"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full px-4 py-3 pl-12 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.price_sale') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <div class="absolute inset-y-0 left-8 flex items-center">
                                                    <div class="h-6 border-l border-gray-300 dark:border-zinc-500"></div>
                                                </div>
                                            </div>
                                            @error('selectedProducts.'.$index.'.price_sale')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Informações do Produto Selecionado -->
                                    @if($product['product_id'])
                                        @php
                                            $selectedProduct = collect($products)->firstWhere('id', $product['product_id']);
                                        @endphp
                                        @if($selectedProduct)
                                            <div class="mt-6 pt-6 border-t-2 border-gray-200 dark:border-zinc-600">
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Preço Original</p>
                                                                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($selectedProduct->price_sale, 2, ',', '.') }}</p>
                                                            </div>
                                                            <i class="bi bi-tag text-2xl text-blue-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Estoque</p>
                                                                <p class="text-lg font-bold {{ $selectedProduct->stock_quantity <= 10 ? 'text-red-600' : 'text-green-600' }}">
                                                                    {{ $selectedProduct->stock_quantity }}
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-boxes text-2xl {{ $selectedProduct->stock_quantity <= 10 ? 'text-red-500' : 'text-green-500' }}"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Subtotal</p>
                                                                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                                                    R$ {{ number_format(($product['quantity'] ?? 0) * ($product['price_sale'] ?? 0), 2, ',', '.') }}
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-calculator text-2xl text-indigo-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Margem</p>
                                                                @php
                                                                    $margin = $product['price_sale'] ? (($product['price_sale'] - $selectedProduct->price) / $product['price_sale']) * 100 : 0;
                                                                @endphp
                                                                <p class="text-lg font-bold {{ $margin > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                    {{ number_format($margin, 1) }}%
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-graph-up text-2xl {{ $margin > 0 ? 'text-green-500' : 'text-red-500' }}"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach

                            @if(empty($selectedProducts))
                                <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                                    <div class="bg-gray-100 dark:bg-zinc-700 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                                        <i class="bi bi-box text-5xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold mb-2">Nenhum produto adicionado</h3>
                                    <p class="text-sm mb-6">Clique em "Adicionar Produto" para começar a editar os itens da venda.</p>
                                    <button type="button"
                                            wire:click="addProduct"
                                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <i class="bi bi-plus-lg mr-2"></i>
                                        Adicionar Primeiro Produto
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Resumo -->
                @if(!empty($selectedProducts))
                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            <i class="bi bi-calculator text-indigo-600 dark:text-indigo-400 mr-2"></i>
                            Resumo da Venda
                        </h2>

                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg p-6">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Valor Total</p>
                                <p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                                    R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {{ count($selectedProducts) }} {{ count($selectedProducts) === 1 ? 'item' : 'itens' }} selecionado{{ count($selectedProducts) === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ações -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                    <div class="flex flex-col sm:flex-row gap-4 justify-end">
                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="bi bi-x-lg mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ empty($selectedProducts) || !$client_id ? 'disabled' : '' }}>
                            <i class="bi bi-check-lg mr-2"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
