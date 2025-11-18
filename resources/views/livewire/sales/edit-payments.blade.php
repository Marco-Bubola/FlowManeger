<div class=" w-full ">
    <!-- Header fixo -->
    <div class="w-full px-6 py-4 sticky top-0 z-20 shadow-lg rounded-b-2xl backdrop-blur border-b ">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-purple-100 dark:bg-purple-900 shadow">
                    <i class="bi bi-credit-card-fill text-3xl text-purple-600 dark:text-purple-400"></i>
                </span>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-pencil-square text-purple-600 dark:text-purple-400"></i>
                        Editar Pagamentos
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
                @if(count($payments) > 0)
                    <button type="button"
                            wire:click="updatePayments"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200 shadow">
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
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg shadow">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($payments as $index => $payment)
                            <div class="bg-white dark:bg-zinc-800 p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-lg relative flex flex-col gap-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="inline-flex items-center gap-2 text-md font-semibold text-gray-900 dark:text-white">
                                        <i class="bi bi-credit-card-2-front text-indigo-500 dark:text-indigo-300"></i>
                                        Pagamento {{ $index + 1 }}
                                    </span>
                                    <button type="button"
                                            wire:click="removePayment({{ $index }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
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
                                               step="0.01">
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
                                    <!-- Observações removido -->
                                </div>
                            </div>
                        @endforeach
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
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-slate-600 text-sm font-medium rounded-lg text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-purple-500 transition-colors duration-200">
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
