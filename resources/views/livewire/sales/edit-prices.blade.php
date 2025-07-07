<div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
    <!-- Header fixo -->
    <div class="w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="bi bi-currency-dollar text-indigo-600 dark:text-indigo-400 mr-3"></i>
                    Editar Preços - Venda #{{ $sale->id }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Cliente: {{ $sale->client->name ?? 'Cliente não informado' }}
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('sales.show', $sale->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Voltar
                </a>
                <button type="button" 
                        wire:click="savePrices" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-check-circle mr-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="w-full p-6">
        <!-- Resumo da venda -->
        <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Resumo da Venda
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ count($saleItems) }} item(s)
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Atual</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        R$ {{ number_format($this->total, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Alertas de erro -->
        @if($errors->has('general'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 mr-2"></i>
                    <span class="text-red-600 dark:text-red-400">{{ $errors->first('general') }}</span>
                </div>
            </div>
        @endif

        <!-- Formulário de edição -->
        <form wire:submit.prevent="savePrices">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($saleItems as $index => $item)
                    <div class="bg-white dark:bg-zinc-800 p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-md hover:shadow-xl transition-all duration-200 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                    <i class="bi bi-box text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $item['product_name'] }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Preço original: <span class="font-semibold">R$ {{ number_format($item['original_price'], 2, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" 
                                    wire:click="removeSaleItem({{ $index }})"
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                                    title="Remover produto">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="flex-1 flex flex-col gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Quantidade
                                </label>
                                <input type="number" 
                                       wire:model.lazy="saleItems.{{ $index }}.quantity"
                                       wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white text-base"
                                       min="1" 
                                       step="1">
                                @error("saleItems.{$index}.quantity")
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Preço Unitário
                                </label>
                                <input type="number" 
                                       wire:model.lazy="saleItems.{{ $index }}.price_sale"
                                       wire:change="updatePrice({{ $index }}, $event.target.value)"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white text-base"
                                       min="0.01" 
                                       step="0.01">
                                @error("saleItems.{$index}.price_sale")
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Subtotal
                                </label>
                                <div class="w-full px-3 py-2 bg-gray-100 dark:bg-zinc-600 border border-gray-300 dark:border-zinc-600 rounded-lg text-gray-900 dark:text-white font-semibold text-base">
                                    R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Diferença
                                </label>
                                @php
                                    $difference = $item['price_sale'] - $item['original_price'];
                                    $percentChange = $item['original_price'] > 0 ? (($difference / $item['original_price']) * 100) : 0;
                                @endphp
                                <div class="w-full px-3 py-2 rounded-lg border text-sm
                                            {{ $difference > 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300' : 
                                               ($difference < 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300' : 
                                                'bg-gray-100 dark:bg-zinc-600 border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300') }}">
                                    <div class="font-semibold">
                                        {{ $difference > 0 ? '+' : '' }}R$ {{ number_format($difference, 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs">
                                        {{ $difference > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </form>
    </div>

    <!-- Scripts para notificações -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('success', (message) => {
                alert(message);
            });
            
            Livewire.on('error', (message) => {
                alert(message);
            });
        });
    </script>
</div>
