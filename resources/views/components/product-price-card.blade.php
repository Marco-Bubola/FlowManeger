@props(['item', 'index'])

@php
    $stockQuantity = $item['product']['stock_quantity'] ?? 0;
    $currentQuantity = $item['quantity'] ?? 1;
    $maxAvailable = $stockQuantity + $currentQuantity;
    $stockStatus = $stockQuantity > 20 ? 'high' : ($stockQuantity > 5 ? 'medium' : 'low');
@endphp

<div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-zinc-700 overflow-hidden group">

    <!-- Header do Card -->
    <div class="relative p-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 border-b border-indigo-100 dark:border-indigo-800">

        <!-- Layout: Imagem + Informações + Botão Remover -->
        <div class="flex items-center gap-4">
            <!-- Imagem do Produto -->
            <div class="w-16 h-16 bg-white dark:bg-zinc-700 rounded-xl border border-indigo-200 dark:border-indigo-700 overflow-hidden flex-shrink-0">
                @if(isset($item['product']) && !empty($item['product']['image']))
                    <img src="{{ asset('storage/products/' . $item['product']['image']) }}"
                         alt="{{ $item['product']['name'] ?? 'Produto' }}"
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
                    {{ $item['product_name'] }}
                </h3>

                <!-- Badges de Status -->
                <div class="flex items-center gap-2 mb-2">
                    <!-- Estoque -->
                    <span class="px-2 py-1 text-xs font-semibold rounded-md {{ $stockStatus === 'high' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($stockStatus === 'medium' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300') }}">
                        <i class="bi bi-boxes mr-1"></i>{{ $stockQuantity }} un.
                    </span>

                    <!-- Preço Original -->
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-semibold rounded-md">
                        <i class="bi bi-tag mr-1"></i>R$ {{ number_format($item['original_price'], 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Botão Remover Melhor Posicionado -->
            <div class="flex-shrink-0">
                <button type="button"
                        onclick="openModal('confirm-remove-{{ $index }}')"
                        class="p-3 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-xl transition-all duration-200 hover:scale-105 shadow-md hover:shadow-lg border border-red-200 dark:border-red-800"
                        title="Remover produto">
                    <i class="bi bi-trash text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="p-5">

        <!-- Grid de Controles -->
        <div class="grid grid-cols-2 gap-4 mb-5">

            <!-- Quantidade -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-hash text-indigo-500 mr-1"></i>
                    Quantidade
                </label>

                <!-- Controles de Quantidade -->
                <div class="flex items-center bg-gray-50 dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600">
                    <!-- Botão Diminuir -->
                    <button type="button"
                            onclick="let input = this.nextElementSibling; if(input.value > 1) { input.value = parseInt(input.value) - 1; input.dispatchEvent(new Event('change')); }"
                            class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-l-lg transition-colors">
                        <i class="bi bi-dash-lg text-lg font-bold"></i>
                    </button>

                    <!-- Input -->
                    <input type="number"
                           wire:model.lazy="saleItems.{{ $index }}.quantity"
                           wire:change="updateQuantity({{ $index }}, $event.target.value)"
                           class="flex-1 px-3 py-2 bg-transparent border-0 text-center text-lg font-bold text-gray-900 dark:text-white focus:ring-0 focus:outline-none"
                           min="1"
                           max="{{ $maxAvailable }}"
                           step="1">

                    <!-- Botão Aumentar -->
                    <button type="button"
                            onclick="let input = this.previousElementSibling; if(parseInt(input.value) < {{ $maxAvailable }}) { input.value = parseInt(input.value) + 1; input.dispatchEvent(new Event('change')); }"
                            class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-r-lg transition-colors">
                        <i class="bi bi-plus-lg text-lg font-bold"></i>
                    </button>
                </div>

                <!-- Info de Limite -->
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                    Máximo: {{ $maxAvailable }} unidades
                </p>

                @error("saleItems.{$index}.quantity")
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preço Unitário -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-currency-dollar text-indigo-500 mr-1"></i>
                    Preço Unitário
                </label>

                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">R$</span>
                    <input type="text"
                           id="price_input_{{ $index }}"
                           value="{{ number_format($item['price_sale'], 2, ',', '') }}"
                           class="w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white text-lg font-semibold text-right"
                           placeholder="0,00">
                    <input type="hidden"
                           wire:model.lazy="saleItems.{{ $index }}.price_sale"
                           id="price_hidden_{{ $index }}"
                           value="{{ $item['price_sale'] }}">
                </div>

                @error("saleItems.{$index}.price_sale")
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Resultados -->
        <div class="grid grid-cols-2 gap-4">

            <!-- Subtotal -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 p-4 rounded-xl border border-indigo-200 dark:border-indigo-700">
                <div class="text-center">
                    <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-1">
                        <i class="bi bi-calculator mr-1"></i>Subtotal
                    </p>
                    <p class="text-xl font-bold text-indigo-800 dark:text-indigo-200">
                        R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Diferença -->
            @php
                $difference = $item['price_sale'] - $item['original_price'];
                $percentChange = $item['original_price'] > 0 ? (($difference / $item['original_price']) * 100) : 0;
            @endphp
            <div class="p-4 rounded-xl border {{ $difference > 0 ? 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-700' : ($difference < 0 ? 'bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-red-200 dark:border-red-700' : 'bg-gradient-to-r from-gray-50 to-slate-50 dark:from-zinc-700/50 dark:to-zinc-600/50 border-gray-200 dark:border-zinc-600') }}">
                <div class="text-center">
                    <p class="text-sm font-medium mb-1 {{ $difference > 0 ? 'text-green-700 dark:text-green-300' : ($difference < 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300') }}">
                        <i class="bi {{ $difference > 0 ? 'bi-arrow-up' : ($difference < 0 ? 'bi-arrow-down' : 'bi-dash') }} mr-1"></i>
                        Diferença
                    </p>
                    <p class="text-lg font-bold {{ $difference > 0 ? 'text-green-800 dark:text-green-200' : ($difference < 0 ? 'text-red-800 dark:text-red-200' : 'text-gray-800 dark:text-gray-200') }}">
                        {{ $difference > 0 ? '+' : '' }}R$ {{ number_format($difference, 2, ',', '.') }}
                    </p>
                    <p class="text-xs font-medium {{ $difference > 0 ? 'text-green-600 dark:text-green-400' : ($difference < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }}">
                        {{ $difference > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Remoção -->
<x-confirmation-modal
    id="confirm-remove-{{ $index }}"
    title="Remover Produto"
    message="Tem certeza que deseja remover '{{ $item['product_name'] }}' desta venda? Esta ação não pode ser desfeita."
    confirm-text="Sim, Remover"
    cancel-text="Cancelar"
    confirm-action="$wire.removeSaleItem({{ $index }})"
    confirm-class="bg-red-600 hover:bg-red-700" />
