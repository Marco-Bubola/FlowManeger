    <div class="min-h-screen w-full bg-gray-50 dark:bg-zinc-900 py-8">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-pencil-square text-indigo-600 dark:text-indigo-400 mr-3"></i>Editar Venda #{{ $sale->id }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Atualize as informações da venda</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="{{ route('sales.show', $sale->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="update">
            <div class="space-y-8">
                <!-- Informações Básicas -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                        <i class="bi bi-info-circle text-indigo-600 dark:text-indigo-400 mr-2"></i>
                        Informações da Venda
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Cliente -->
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="bi bi-person text-gray-400 mr-2"></i>Cliente *
                            </label>
                            <select wire:model="client_id" 
                                    id="client_id"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white @error('client_id') border-red-300 @enderror">
                                <option value="">Selecione um cliente...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo de Pagamento -->
                        <div>
                            <label for="tipo_pagamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="bi bi-credit-card text-gray-400 mr-2"></i>Tipo de Pagamento *
                            </label>
                            <select wire:model="tipo_pagamento" 
                                    id="tipo_pagamento"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white @error('tipo_pagamento') border-red-300 @enderror">
                                <option value="a_vista">À Vista</option>
                                <option value="parcelado">Parcelado</option>
                            </select>
                            @error('tipo_pagamento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Número de Parcelas -->
                        @if($tipo_pagamento === 'parcelado')
                            <div>
                                <label for="parcelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-list-ol text-gray-400 mr-2"></i>Número de Parcelas
                                </label>
                                <input type="number" 
                                       wire:model="parcelas"
                                       id="parcelas"
                                       min="2"
                                       max="12"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white @error('parcelas') border-red-300 @enderror">
                                @error('parcelas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Produtos -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            <i class="bi bi-box text-indigo-600 dark:text-indigo-400 mr-2"></i>
                            Produtos da Venda
                        </h2>
                        <button type="button" 
                                wire:click="addProduct"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="bi bi-plus mr-2"></i>
                            Adicionar Produto
                        </button>
                    </div>

                    @error('selectedProducts')
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="space-y-4">
                        @foreach($selectedProducts as $index => $product)
                            <div class="bg-gray-50 dark:bg-zinc-700 rounded-lg p-4 border border-gray-200 dark:border-zinc-600">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                                    <!-- Produto -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Produto *
                                        </label>
                                        <select wire:model="selectedProducts.{{ $index }}.product_id"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white @error('selectedProducts.'.$index.'.product_id') border-red-300 @enderror">
                                            <option value="">Selecione um produto...</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} (Estoque: {{ $prod->stock_quantity }})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedProducts.'.$index.'.product_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Quantidade -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Quantidade *
                                        </label>
                                        <input type="number" 
                                               wire:model="selectedProducts.{{ $index }}.quantity"
                                               min="1"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white @error('selectedProducts.'.$index.'.quantity') border-red-300 @enderror">
                                        @error('selectedProducts.'.$index.'.quantity')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Preço de Venda -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Preço de Venda *
                                        </label>
                                        <input type="number" 
                                               wire:model="selectedProducts.{{ $index }}.price_sale"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white @error('selectedProducts.'.$index.'.price_sale') border-red-300 @enderror">
                                        @error('selectedProducts.'.$index.'.price_sale')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Remover -->
                                    <div>
                                        <button type="button" 
                                                wire:click="removeProduct({{ $index }})"
                                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-red-300 dark:border-red-600 text-sm font-medium rounded-lg text-red-700 dark:text-red-400 bg-white dark:bg-zinc-600 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Informações do Produto Selecionado -->
                                @if($product['product_id'])
                                    @php
                                        $selectedProduct = collect($products)->firstWhere('id', $product['product_id']);
                                    @endphp
                                    @if($selectedProduct)
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-zinc-600">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-600 dark:text-gray-400">Preço Original:</span>
                                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">R$ {{ number_format($selectedProduct->price_sale, 2, ',', '.') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600 dark:text-gray-400">Estoque Disponível:</span>
                                                    <span class="ml-2 font-medium {{ $selectedProduct->stock_quantity <= 10 ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $selectedProduct->stock_quantity }} unidades
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                                    <span class="ml-2 font-medium text-indigo-600 dark:text-indigo-400">
                                                        R$ {{ number_format(($product['quantity'] ?? 0) * ($product['price_sale'] ?? 0), 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                        @if(empty($selectedProducts))
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="bi bi-box text-4xl mb-4"></i>
                                <p>Nenhum produto adicionado ainda.</p>
                                <p class="text-sm">Clique em "Adicionar Produto" para começar.</p>
                            </div>
                        @endif
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

