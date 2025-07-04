<div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
    <!-- Header fixo -->
    <div class="w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="bi bi-credit-card-fill text-purple-600 dark:text-purple-400 mr-3"></i>
                    Editar Pagamentos - Venda #{{ $sale->id }}
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
                @if(count($payments) > 0)
                    <button type="button" 
                            wire:click="updatePayments" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                        <i class="bi bi-check-circle mr-2"></i>
                        Salvar Alterações
                    </button>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="w-full p-6">
        <!-- Cabeçalho com informações da venda -->
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Venda #{{ $sale->id }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Data: {{ $sale->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total da Venda</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Atual dos Pagamentos</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        R$ {{ number_format($this->totalPayments, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        @if(count($payments) > 0)
            <!-- Formulário para editar pagamentos -->
            <form wire:submit.prevent="updatePayments">
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Editar Pagamentos Existentes
                    </h3>

                    @foreach($payments as $index => $payment)
                        <div class="bg-white dark:bg-zinc-800 p-6 border border-gray-200 dark:border-zinc-700 rounded-lg">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-white">
                                    Pagamento {{ $index + 1 }}
                                </h4>
                                <button type="button" 
                                        wire:click="removePayment({{ $index }})"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Valor -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Valor *
                                    </label>
                                    <input type="number" 
                                           wire:model="payments.{{ $index }}.amount_paid"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white"
                                           min="0.01" 
                                           step="0.01">
                                    @error("payments.{$index}.amount_paid")
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Método de Pagamento -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Método de Pagamento
                                    </label>
                                    <select wire:model="payments.{{ $index }}.payment_method"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="cartao_debito">Cartão de Débito</option>
                                        <option value="cartao_credito">Cartão de Crédito</option>
                                        <option value="pix">PIX</option>
                                        <option value="transferencia">Transferência</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>

                                <!-- Data -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Data do Pagamento
                                    </label>
                                    <input type="date" 
                                           wire:model="payments.{{ $index }}.payment_date"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
                                </div>

                                <!-- Observações -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Observações
                                    </label>
                                    <input type="text" 
                                           wire:model="payments.{{ $index }}.notes"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white"
                                           placeholder="Observações opcionais">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Resumo dos pagamentos -->
                <div class="mt-8 p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total dos Pagamentos
                            </h4>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                R$ {{ number_format($this->totalPayments, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total da Venda
                            </h4>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Valor Restante
                            </h4>
                            <p class="text-2xl font-bold {{ $this->remainingAmount <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                R$ {{ number_format($this->remainingAmount, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botões de ação -->
                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('sales.show', $sale->id) }}" 
                       class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('sales.add-payments', $sale->id) }}" 
                           class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <i class="bi bi-plus mr-2"></i>
                            Adicionar Novo
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <i class="bi bi-check-circle mr-2"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        @else
            <!-- Caso não tenha pagamentos -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-credit-card text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhum pagamento registrado</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Adicione pagamentos para esta venda</p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('sales.add-payments', $sale->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Adicionar Pagamento
                    </a>
                    <a href="{{ route('sales.show', $sale->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Voltar
                    </a>
                </div>
            </div>
        @endif
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
