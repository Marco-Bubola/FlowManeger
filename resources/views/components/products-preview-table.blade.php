@props([
    'products' => [],
    'showBackButton' => true
])

<!-- Tabela de Preview dos Produtos -->
<div class="space-y-8">
    <!-- Cabeçalho da Seção -->
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="bi bi-check-circle-fill text-white text-xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-emerald-700 to-blue-700 dark:from-emerald-300 dark:to-blue-300 bg-clip-text text-transparent">
                Produtos Extraídos
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400">
                Revise e edite os dados antes de salvar no sistema
            </p>
        </div>
    </div>

    <!-- Container Principal -->
    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-xl rounded-2xl border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl overflow-hidden">
        <!-- Header do Container -->
        <div class="bg-gradient-to-r from-neutral-50 to-neutral-100 dark:from-neutral-800 dark:to-neutral-700 px-8 py-6 border-b border-neutral-200 dark:border-neutral-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                        {{ count($products) }} {{ count($products) === 1 ? 'produto encontrado' : 'produtos encontrados' }}
                    </span>
                </div>

                <!-- Stats -->
                <div class="hidden sm:flex items-center space-x-6 text-sm">
                    <div class="flex items-center space-x-2">
                        <i class="bi bi-box-seam text-blue-500"></i>
                        <span class="text-neutral-600 dark:text-neutral-400">Produtos</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ count($products) }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="bi bi-currency-dollar text-emerald-500"></i>
                        <span class="text-neutral-600 dark:text-neutral-400">Com preço</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                            {{ count(array_filter($products, fn($p) => isset($p['price_sale']) && $p['price_sale'] > 0)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if(count($products) > 0)
            <!-- Grid de Cards dos Produtos -->
            <div class="p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                    @foreach($products as $index => $product)
                        <div class="group bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden hover:scale-105">
                            <!-- Header do Card -->
                            <div class="relative">
                                <!-- Botões de Ação -->
                                <div class="absolute top-3 right-3 z-10 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button type="button"
                                            onclick="copyProductName({{ $index }}, '{{ $product['name'] ?? '' }}')"
                                            class="w-8 h-8 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg flex items-center justify-center text-xs transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110"
                                            title="Copiar nome">
                                        <i class="bi bi-tag"></i>
                                    </button>
                                    <button type="button"
                                            onclick="copyProductCode({{ $index }}, '{{ $product['product_code'] ?? '' }}')"
                                            class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center text-xs transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110"
                                            title="Copiar código">
                                        <i class="bi bi-upc-scan"></i>
                                    </button>
                                    <button type="button"
                                            wire:click="removeProduct({{ $index }})"
                                            class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center text-xs transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110"
                                            title="Remover">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>

                                <!-- Área da Imagem -->
                                <div class="relative h-40 bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600 cursor-pointer"
                                     onclick="document.getElementById('image-input-{{ $index }}').click();">

                                    @if(isset($product['temp_image']))
                                        <img src="{{ $product['temp_image'] }}"
                                             class="w-full h-full object-cover"
                                             alt="{{ $product['name'] ?? 'Produto' }}"
                                             id="product-image-{{ $index }}">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <div class="text-center space-y-2">
                                                <i class="bi bi-image text-3xl text-neutral-400"></i>
                                                <p class="text-xs text-neutral-500">Clique para adicionar</p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center opacity-0 hover:opacity-100">
                                        <div class="bg-white bg-opacity-90 rounded-full p-3">
                                            <i class="bi bi-camera text-neutral-700 text-xl"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Input de arquivo oculto -->
                                <input type="file"
                                       id="image-input-{{ $index }}"
                                       class="hidden"
                                       accept="image/*">

                                <!-- Badges sobre a imagem -->
                                <div class="absolute bottom-3 left-3 flex space-x-2">
                                    <!-- Código do produto -->
                                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-2 py-1 rounded-lg text-xs font-medium shadow-lg">
                                        <input type="text"
                                               wire:model.lazy="productsUpload.{{ $index }}.product_code"
                                               class="bg-transparent border-none text-white placeholder-purple-200 w-16 text-center focus:outline-none"
                                               placeholder="Código"
                                               maxlength="15">
                                    </div>

                                    <!-- Quantidade -->
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded-lg text-xs font-medium shadow-lg">
                                        <input type="number"
                                               wire:model.lazy="productsUpload.{{ $index }}.stock_quantity"
                                               min="0"
                                               class="bg-transparent border-none text-white placeholder-blue-200 w-12 text-center focus:outline-none"
                                               placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Conteúdo do Card -->
                            <div class="p-4 space-y-4">
                                <!-- Nome do produto -->
                                <div>
                                    <input type="text"
                                           wire:model.lazy="productsUpload.{{ $index }}.name"
                                           class="w-full text-center font-bold text-neutral-800 dark:text-neutral-200 bg-transparent border-none focus:outline-none focus:ring-2 focus:ring-purple-300 dark:focus:ring-purple-600 rounded px-2 py-1 transition-all duration-200"
                                           placeholder="Nome do produto">
                                </div>

                                <!-- Status -->
                                <div class="flex justify-center">
                                    <select wire:model.lazy="productsUpload.{{ $index }}.status"
                                            class="bg-gradient-to-r from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600 border border-neutral-300 dark:border-neutral-600 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-300 dark:focus:ring-purple-600 transition-all duration-200">
                                        <option value="ativo">✅ Ativo</option>
                                        <option value="inativo">⏸️ Inativo</option>
                                        <option value="descontinuado">❌ Descontinuado</option>
                                    </select>
                                </div>

                                <!-- Preços -->
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- Preço de Custo -->
                                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-2 border border-orange-200 dark:border-orange-700">
                                        <label class="text-xs text-orange-700 dark:text-orange-300 font-medium">Custo</label>
                                        <div class="flex items-center">
                                            <span class="text-xs text-orange-600 mr-1">R$</span>
                                            <input type="number"
                                                   wire:model.lazy="productsUpload.{{ $index }}.price"
                                                   step="0.01"
                                                   class="bg-transparent border-none text-orange-700 dark:text-orange-300 placeholder-orange-400 text-sm w-full focus:outline-none"
                                                   placeholder="0,00">
                                        </div>
                                    </div>

                                    <!-- Preço de Venda -->
                                    <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-lg p-2 border border-emerald-200 dark:border-emerald-700">
                                        <label class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">Venda</label>
                                        <div class="flex items-center">
                                            <span class="text-xs text-emerald-600 mr-1">R$</span>
                                            <input type="number"
                                                   wire:model.lazy="productsUpload.{{ $index }}.price_sale"
                                                   step="0.01"
                                                   class="bg-transparent border-none text-emerald-700 dark:text-emerald-300 placeholder-emerald-400 text-sm w-full focus:outline-none"
                                                   placeholder="0,00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="bg-gradient-to-r from-neutral-50 to-neutral-100 dark:from-neutral-800 dark:to-neutral-700 px-8 py-6 border-t border-neutral-200 dark:border-neutral-600">
                <div class="flex flex-col sm:flex-row gap-4">
                    @if($showBackButton)
                    <button wire:click="$set('showProductsTable', false)"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-neutral-500 to-neutral-600 hover:from-neutral-600 hover:to-neutral-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Voltar
                    </button>
                    @endif

                    <button wire:click="store"
                            class="flex-1 inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-600 to-blue-600 hover:from-emerald-700 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-check-circle mr-2"></i>
                            Salvar Produtos ({{ count($products) }})
                        </span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Salvando...
                        </span>
                    </button>
                </div>
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-neutral-300 to-neutral-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-inbox text-2xl text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-neutral-600 dark:text-neutral-400 mb-2">
                    Nenhum produto encontrado
                </h3>
                <p class="text-neutral-500 dark:text-neutral-500">
                    Verifique o formato do arquivo e tente novamente
                </p>
            </div>
        @endif
    </div>
</div>
