<div class="w-full bg-gradient-to-br from-indigo-50 via-white to-green-50 min-h-screen dark:from-zinc-900 dark:via-zinc-800 dark:to-green-900">
    <!-- Header fixo -->
    <div class="w-full px-6 py-4 sticky top-0 z-20 bg-white/80 dark:bg-zinc-900/80 shadow-lg rounded-b-2xl backdrop-blur">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 dark:bg-green-900 shadow">
                    <i class="bi bi-cash-coin text-3xl text-green-600 dark:text-green-400"></i>
                </span>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-plus-circle text-green-600 dark:text-green-400"></i>
                        Adicionar Pagamentos
                        <span class="ml-2 text-base font-medium text-indigo-600 dark:text-indigo-400">Venda #{{ $sale->id }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="bi bi-person-circle text-indigo-500 dark:text-indigo-300"></i>
                        Cliente: {{ $sale->client->name ?? 'Cliente não informado' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('sales.show', $sale->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 shadow">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Voltar
                </a>
                <button type="button" 
                        wire:click="addPayments" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 shadow">
                    <i class="bi bi-check-circle mr-2"></i>
                    Salvar Pagamentos
                </button>
            </div>
        </div>
    </div>
    <div class="w-full p-6">
        <!-- Cabeçalho com informações da venda -->
        <div class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Venda #{{ $sale->id }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Cliente: {{ $sale->client->name ?? 'Cliente não informado' }}
                    </p>
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
                    <p class="text-sm text-gray-600 dark:text-gray-400">Valor Pendente</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        R$ {{ number_format($this->remainingAmount, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form wire:submit.prevent="addPayments">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-wallet2 text-indigo-600 dark:text-indigo-400"></i>
                        Adicionar Pagamentos
                    </h3>
                    <button type="button" 
                            wire:click="addPaymentRow" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center gap-2 shadow">
                        <i class="bi bi-plus-lg"></i>
                        Adicionar Mais
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($payments as $index => $payment)
                        <div class="bg-white dark:bg-zinc-800 p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-lg relative flex flex-col gap-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-2 text-md font-semibold text-gray-900 dark:text-white">
                                    <i class="bi bi-credit-card-2-front text-indigo-500 dark:text-indigo-300"></i>
                                    Pagamento {{ $index + 1 }}
                                </span>
                                @if(count($payments) > 1)
                                    <button type="button" 
                                            wire:click="removePaymentRow({{ $index }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="flex flex-col gap-3">
                                <!-- Valor -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                        <i class="bi bi-currency-dollar text-green-600"></i> Valor *
                                    </label>
                                    <input type="number" 
                                           wire:model="payments.{{ $index }}.amount_paid"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white"
                                           min="0.01" 
                                           step="0.01"
                                           placeholder="0,00">
                                    @error("payments.{$index}.amount_paid")
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Método de Pagamento -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                        <i class="bi bi-credit-card"></i> Método de Pagamento
                                    </label>
                                    <select wire:model="payments.{{ $index }}.payment_method"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
                                        <option value="dinheiro">💵 Dinheiro</option>
                                        <option value="cartao_debito">💳 Cartão de Débito</option>
                                        <option value="cartao_credito">💳 Cartão de Crédito</option>
                                        <option value="pix">⚡ PIX</option>
                                        <option value="transferencia">🏦 Transferência</option>
                                        <option value="cheque">🧾 Cheque</option>
                                    </select>
                                </div>
                                <!-- Data -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                        <i class="bi bi-calendar-event"></i> Data do Pagamento
                                    </label>
                                    <input type="date" 
                                           wire:model="payments.{{ $index }}.payment_date"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Resumo dos pagamentos -->
            <div class="mt-8 p-6 bg-gradient-to-r from-green-50 via-white to-indigo-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-green-200 dark:border-green-800 rounded-2xl shadow flex flex-col md:flex-row gap-6 items-center justify-center">
                <div class="flex-1 text-center flex flex-col items-center gap-2">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <i class="bi bi-cash-stack text-green-600"></i> Total dos Pagamentos
                    </h4>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                        <i class="bi bi-coin"></i> R$ {{ number_format($this->totalPayments, 2, ',', '.') }}
                    </p>
                </div>
                <div class="flex-1 text-center flex flex-col items-center gap-2">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle text-red-600"></i> Valor Pendente
                    </h4>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 flex items-center gap-1">
                        <i class="bi bi-exclamation-diamond"></i> R$ {{ number_format($this->remainingAmount, 2, ',', '.') }}
                    </p>
                </div>
                <div class="flex-1 text-center flex flex-col items-center gap-2">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <i class="bi bi-calculator text-indigo-600"></i> Valor Final Restante
                    </h4>
                    @php
                        $remaining = $this->remainingAmount - $this->totalPayments;
                    @endphp
                    <p class="text-2xl font-bold {{ $remaining <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} flex items-center gap-1">
                        <i class="bi bi-cash"></i> R$ {{ number_format($remaining, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Botões de ação -->
            <div class="mt-8 flex flex-wrap items-center gap-4 justify-center">
                <a href="{{ route('sales.show', $sale->id) }}" 
                   class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 shadow">
                    <i class="bi bi-arrow-left"></i>
                    Voltar
                </a>
                <button type="button" 
                        wire:click="addPaymentRow" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 shadow">
                    <i class="bi bi-plus-lg"></i>
                    Adicionar Mais
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 shadow">
                    <i class="bi bi-check-circle"></i>
                    Salvar Pagamentos
                </button>
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
