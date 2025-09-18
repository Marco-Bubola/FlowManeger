@props([
    'products' => [],
    'showBackButton' => true
])

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
                        {{ count($products) }} produtos encontrados
                    </span>
                </div>

                @if($showBackButton)
                    <div class="flex gap-3">
                        <button wire:click="$set('showProductsTable', false)"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-neutral-500 to-neutral-600 hover:from-neutral-600 hover:to-neutral-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-arrow-left mr-2"></i>
                            Voltar
                        </button>
                        <button wire:click="store"
                                class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-emerald-600 to-blue-600 hover:from-emerald-700 hover:to-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-save mr-2"></i>
                            Salvar Produtos
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Conteúdo -->
        @if(count($products) > 0)
            <!-- Grid de Produtos -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $index => $product)
                        <div class="bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-700 dark:to-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-600 p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <!-- Cabeçalho do Card -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-600 px-2 py-1 rounded-full">
                                    Produto
                                </div>
                            </div>

                            <!-- Nome do Produto -->
                            <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-200 mb-2 line-clamp-2">
                                {{ $product['name'] ?? 'Produto sem nome' }}
                            </h3>

                            <!-- Preço -->
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="bi bi-currency-dollar text-emerald-600 dark:text-emerald-400"></i>
                                <span class="text-xl font-bold text-emerald-700 dark:text-emerald-300">
                                    R$ {{ number_format($product['price'] ?? 0, 2, ',', '.') }}
                                </span>
                            </div>

                            <!-- Descrição -->
                            @if(!empty($product['description']))
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4 line-clamp-3">
                                    {{ $product['description'] }}
                                </p>
                            @endif

                            <!-- Categoria -->
                            @if(!empty($product['category']))
                                <div class="flex items-center space-x-2">
                                    <i class="bi bi-tag text-blue-600 dark:text-blue-400"></i>
                                    <span class="text-sm text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-full">
                                        {{ $product['category'] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-neutral-300 to-neutral-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="bi bi-inbox text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                    Nenhum produto encontrado
                </h3>
                <p class="text-neutral-500 dark:text-neutral-400 max-w-md mx-auto">
                    Verifique se o arquivo contém produtos válidos e tente novamente.
                </p>
            </div>
        @endif
    </div>
</div>
