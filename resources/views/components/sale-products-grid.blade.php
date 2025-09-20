@props(['sale'])

<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl shadow-lg">
                <i class="bi bi-box text-white text-2xl"></i>
            </div>
            Produtos da Venda
            <span class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-400 rounded-full text-sm font-semibold">
                {{ $sale->saleItems->count() }} {{ $sale->saleItems->count() === 1 ? 'item' : 'itens' }}
            </span>
        </h2>

        <div class="flex gap-3">
            <a href="{{ route('sales.edit-prices', $sale->id) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
                <i class="bi bi-pencil mr-2"></i>
                Editar Preços
            </a>
            <a href="{{ route('sales.add-products', $sale->id) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
                <i class="bi bi-plus mr-2"></i>
                Adicionar Produto
            </a>
        </div>
    </div>

    @if($sale->saleItems->count() > 0)
    <!-- Container de produtos com scroll natural -->
    <div class="w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-8">
        @foreach($sale->saleItems as $item)
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-zinc-700 overflow-hidden group">

            <!-- Header do Card -->
            <div class="relative p-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 border-b border-indigo-100 dark:border-indigo-800">

                <!-- Layout: Imagem + Informações + Botão Remover -->
                <div class="flex items-center gap-4">
                    <!-- Imagem do Produto -->
                    <div class="w-16 h-16 bg-white dark:bg-zinc-700 rounded-xl border border-indigo-200 dark:border-indigo-700 overflow-hidden flex-shrink-0">
                        @if($item->product->image)
                            <img src="{{ asset('storage/products/' . $item->product->image) }}"
                                 alt="{{ $item->product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-zinc-600 dark:to-zinc-700 flex items-center justify-center">
                                <i class="bi bi-image text-2xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Informações Principais -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 truncate">
                            {{ $item->product->name }}
                        </h3>

                        <!-- Badges de Status -->
                        <div class="flex items-center gap-2 mb-2">
                            <!-- Quantidade -->
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-semibold rounded-md">
                                <i class="bi bi-stack mr-1"></i>{{ $item->quantity }}x
                            </span>

                            <!-- Categoria -->
                            @if($item->product->category)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 text-xs font-semibold rounded-md">
                                <i class="bi bi-{{ $item->product->category->icon ?? 'box' }} mr-1"></i>{{ $item->product->category->name ?? 'Categoria' }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Botão Remover -->
                    <div class="flex-shrink-0">
                        <button type="button"
                                @click="$dispatch('show-modal-{{ $item->id }}')"
                                class="p-3 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-xl transition-all duration-200 hover:scale-105 shadow-md hover:shadow-lg border border-red-200 dark:border-red-800"
                                title="Remover produto">
                            <i class="bi bi-trash text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="p-5">
                <!-- Grid de Preços -->
                <div class="grid grid-cols-2 gap-4">

                    <!-- Preço Original -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-700 dark:to-zinc-600 p-4 rounded-xl border border-gray-200 dark:border-zinc-600">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                <i class="bi bi-tag mr-1"></i>Preço Original
                            </p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-200">
                                R$ {{ number_format($item->price, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Preço de Venda -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-xl border border-green-200 dark:border-green-700">
                        <div class="text-center">
                            <p class="text-sm font-medium text-green-700 dark:text-green-400 mb-1">
                                <i class="bi bi-currency-dollar mr-1"></i>Preço Venda
                            </p>
                            <p class="text-lg font-bold text-green-800 dark:text-green-400">
                                R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subtotal -->
                <div class="mt-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 rounded-xl border border-indigo-200 dark:border-indigo-700">
                    <div class="text-center">
                        <p class="text-sm font-medium text-indigo-700 dark:text-indigo-400 mb-1">
                            <i class="bi bi-calculator mr-1"></i>Subtotal
                        </p>
                        <p class="text-xl font-bold text-indigo-800 dark:text-indigo-400">
                            R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Diferença de Preço (se houver) -->
                @php
                    $difference = $item->price_sale - $item->price;
                @endphp
                @if($difference != 0)
                <div class="mt-3 p-3 rounded-xl border text-center {{ $difference > 0 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border-yellow-200 dark:border-yellow-700' : 'bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-blue-200 dark:border-blue-700' }}">
                    <p class="text-xs font-medium {{ $difference > 0 ? 'text-yellow-700 dark:text-yellow-400' : 'text-blue-700 dark:text-blue-400' }} mb-1">
                        {{ $difference > 0 ? 'Aumento' : 'Desconto' }}
                    </p>
                    <p class="text-sm font-bold {{ $difference > 0 ? 'text-yellow-800 dark:text-yellow-400' : 'text-blue-800 dark:text-blue-400' }}">
                        {{ $difference > 0 ? '+' : '' }}R$ {{ number_format($difference, 2, ',', '.') }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-16 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-zinc-800/50 dark:via-zinc-700/50 dark:to-zinc-800/50 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-600">
        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="bi bi-box text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nenhum produto adicionado</h3>
        <p class="text-lg text-gray-500 dark:text-gray-400 mb-6">Adicione produtos para começar a visualizar os itens da venda</p>
        <a href="{{ route('sales.add-products', $sale->id) }}"
           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl font-semibold text-lg">
            <i class="bi bi-plus-circle mr-3 text-xl"></i>
            Adicionar Primeiro Produto
        </a>
    </div>
    @endif
</div>
