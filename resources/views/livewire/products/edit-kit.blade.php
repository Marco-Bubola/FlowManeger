<!--                 <a href="{{ route('products.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors duration-200">
                    <i class="bi bi-arrow-left text-neutral-600 dark:text-neutral-300"></i>
                </a>er -->
<div class="bg-white dark:bg-neutral-800 border-b sticky top-0 z-10">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}{{ !empty($returnParams) ? '?' . http_build_query(array_filter($returnParams)) : '' }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors duration-200">
                    <i class="bi bi-arrow-left text-neutral-600 dark:text-neutral-300"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center">
                        <i class="bi bi-pencil-square text-blue-600 dark:text-blue-400 mr-3"></i>
                        Editar Kit
                    </h1>
                    <p class="text-neutral-600 dark:text-neutral-400">Altere as informações e componentes do kit</p>
                </div>
            </div>

            <!-- Badge do kit -->
            <div class="hidden sm:block">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <i class="bi bi-boxes mr-2"></i>
                    #{{ $kit->product_code }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Conteúdo Principal -->
<div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
    <form wire:submit="update" class="w-full">
        <div class="px-6 py-8 space-y-8">
            <!-- Seção: Informações do Kit -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Informações do Kit</h2>
                </div>

                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Nome do Kit -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                <i class="bi bi-boxes mr-1"></i>
                                Nome do Kit *
                            </label>
                            <input type="text"
                                   wire:model="name"
                                   id="name"
                                   class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('name') !border-red-500 @enderror"
                                   placeholder="Digite o nome do kit">
                            @error('name')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preço de Custo -->
                        <div class="space-y-2">
                            <label for="price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                <i class="bi bi-tag mr-1"></i>
                                Preço de Custo *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <span class="text-neutral-500 dark:text-neutral-400">R$</span>
                                </div>
                                <input type="text"
                                       wire:model="price"
                                       id="price"
                                       class="w-full pl-12 pr-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('price') !border-red-500 @enderror"
                                       placeholder="0,00">
                            </div>
                            @error('price')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preço de Venda -->
                        <div class="space-y-2">
                            <label for="price_sale" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                <i class="bi bi-currency-dollar mr-1"></i>
                                Preço de Venda *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <span class="text-neutral-500 dark:text-neutral-400">R$</span>
                                </div>
                                <input type="text"
                                       wire:model="price_sale"
                                       id="price_sale"
                                       class="w-full pl-12 pr-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('price_sale') !border-red-500 @enderror"
                                       placeholder="0,00">
                            </div>
                            @error('price_sale')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @error('produtos')
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mt-4">
                            <p class="text-red-700 dark:text-red-400 text-sm">{{ $message }}</p>
                        </div>
                    @enderror
                </div>
            </div>
                                </div>
                                @error('price_sale')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @error('produtos')
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                <p class="text-red-700 dark:text-red-400 text-sm">{{ $message }}</p>
                            </div>
                        @enderror

                        <!-- Botões -->
                        <div class="flex flex-col gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle mr-2"></i>
                                    Atualizar Kit
                                </span>
                                <span wire:loading>
                                    <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                                    Atualizando...
                                </span>
                            </button>

                            <a href="{{ route('products.index') }}"
                               class="w-full px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 text-center">
                                <i class="bi bi-x-circle mr-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Produtos Disponíveis -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center mb-6">
                            <i class="bi bi-collection-fill text-purple-600 dark:text-purple-400 mr-3"></i>
                            Produtos do Kit
                        </h2>

                        @if($availableProducts->isEmpty())
                            <div class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 text-gray-400">
                                    <i class="bi bi-box text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Nenhum produto disponível</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Crie produtos simples primeiro para poder montar kits.</p>
                                <a href="{{ route('products.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    <i class="bi bi-plus mr-2"></i>
                                    Criar Produto
                                </a>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($availableProducts as $product)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-xl p-4 hover:shadow-lg transition-all duration-200 @if(isset($produtos[$product->id]['selecionado']) && $produtos[$product->id]['selecionado']) bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600 @endif">
                                        <div class="flex items-start space-x-3">
                                            <!-- Checkbox -->
                                            <div class="flex-shrink-0 pt-1">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox"
                                                           wire:model.live="produtos.{{ $product->id }}.selecionado"
                                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                </label>
                                            </div>

                                            <!-- Produto Info -->
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1 truncate">
                                                    {{ $product->name }}
                                                </h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                    #{{ $product->product_code }}
                                                </p>
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="text-green-600 dark:text-green-400 font-medium">
                                                        R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                                    </span>
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        Est: {{ $product->stock_quantity }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quantidade (apenas se selecionado) -->
                                        @if(isset($produtos[$product->id]['selecionado']) && $produtos[$product->id]['selecionado'])
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Quantidade
                                                </label>
                                                <input type="number"
                                                       wire:model.live="produtos.{{ $product->id }}.quantidade"
                                                       min="1"
                                                       max="{{ $product->stock_quantity }}"
                                                       class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Resumo dos produtos selecionados -->
                            @php
                                $produtosSelecionados = collect($produtos)->filter(fn($p) => $p['selecionado'] ?? false);
                            @endphp

                            @if($produtosSelecionados->count() > 0)
                                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        Produtos selecionados ({{ $produtosSelecionados->count() }})
                                    </h3>
                                    <div class="space-y-2">
                                        @foreach($produtosSelecionados as $produtoId => $dados)
                                            @php
                                                $product = $availableProducts->find($produtoId);
                                            @endphp
                                            @if($product)
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-700 dark:text-gray-300">
                                                        {{ $product->name }} x{{ $dados['quantidade'] }}
                                                    </span>
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        R$ {{ number_format($product->price_sale * $dados['quantidade'], 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end bg-white dark:bg-neutral-800 border-t border-neutral-200 dark:border-neutral-700 px-6 py-4 sticky bottom-0">
                <a href="{{ route('products.index') }}"
                   class="px-6 py-3 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors duration-200 text-center">
                    <i class="bi bi-x-circle mr-2"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="bi bi-check-circle mr-2"></i>
                        Atualizar Kit
                    </span>
                    <span wire:loading>
                        <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                        Atualizando...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
