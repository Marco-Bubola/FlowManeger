<div class="min-h-screen w-full">
    <!-- Header fixo -->
    <div class="w-full  px-6 py-4 sticky top-0 z-10">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-8">
                @foreach($saleItems as $index => $item)
                    <div class="bg-white dark:bg-zinc-800 p-7 border-2 border-indigo-100 dark:border-indigo-900/40 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-200 flex flex-col gap-4 relative overflow-hidden group">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center shadow-md overflow-hidden border-2 border-indigo-200 dark:border-indigo-800">
                                @if(isset($item['product']) && !empty($item['product']['image']))
                                    <img src="{{ asset('storage/products/' . $item['product']['image']) }}"
                                         alt="{{ $item['product']['name'] ?? 'Produto' }}"
                                         class="w-full h-full object-cover rounded-lg">
                                @else
                                    <div class="w-full h-full bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-image text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-box-seam text-indigo-600 dark:text-indigo-400"></i>
                                    {{ $item['product_name'] }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                    <i class="bi bi-currency-dollar"></i>
                                    Preço original: <span class="font-semibold">R$ {{ number_format($item['original_price'], 2, ',', '.') }}</span>
                                </p>
                            </div>
                            <button type="button" 
                                    wire:click="removeSaleItem({{ $index }})"
                                    class="ml-2 p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                                    title="Remover produto">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>

                        <div class="flex-1 grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                    <i class="bi bi-hash"></i>
                                    Quantidade
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"><i class="bi bi-123"></i></span>
                                    <input type="number" 
                                           wire:model.lazy="saleItems.{{ $index }}.quantity"
                                           wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                           class="w-full pl-9 px-3 py-2 border border-indigo-200 dark:border-indigo-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white text-base shadow-sm"
                                           min="1" 
                                           step="1">
                                </div>
                                @error("saleItems.{$index}.quantity")
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                    <i class="bi bi-cash-coin"></i>
                                    Preço Unitário
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" 
                                           wire:model.lazy="saleItems.{{ $index }}.price_sale"
                                           wire:change="updatePrice({{ $index }}, $event.target.value)"
                                           class="w-full pl-9 px-3 py-2 border border-indigo-200 dark:border-indigo-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white text-base shadow-sm"
                                           min="0.01" 
                                           step="0.01">
                                </div>
                                @error("saleItems.{$index}.price_sale")
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                    <i class="bi bi-calculator"></i>
                                    Subtotal
                                </label>
                                <div class="w-full px-3 py-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg text-gray-900 dark:text-white font-semibold text-base flex items-center gap-2 shadow-sm">
                                    <i class="bi bi-cash-stack text-indigo-500"></i>
                                    R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                    <i class="bi bi-graph-up-arrow"></i>
                                    Diferença
                                </label>
                                @php
                                    $difference = $item['price_sale'] - $item['original_price'];
                                    $percentChange = $item['original_price'] > 0 ? (($difference / $item['original_price']) * 100) : 0;
                                @endphp
                                <div class="w-full px-3 py-2 rounded-lg border-2 text-sm flex items-center gap-2 shadow-sm
                                            {{ $difference > 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-700 dark:text-green-300' : 
                                               ($difference < 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 text-red-700 dark:text-red-300' : 
                                                'bg-gray-100 dark:bg-zinc-600 border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300') }}">
                                    <i class="bi {{ $difference > 0 ? 'bi-arrow-up-right-circle-fill' : ($difference < 0 ? 'bi-arrow-down-right-circle-fill' : 'bi-dash-circle') }} text-lg"></i>
                                    <div>
                                        <div class="font-semibold flex items-center gap-1">
                                            <i class="bi bi-currency-exchange"></i>
                                            {{ $difference > 0 ? '+' : '' }}R$ {{ number_format($difference, 2, ',', '.') }}
                                        </div>
                                        <div class="text-xs flex items-center gap-1">
                                            <i class="bi bi-percent"></i>
                                            {{ $difference > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%
                                        </div>
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
