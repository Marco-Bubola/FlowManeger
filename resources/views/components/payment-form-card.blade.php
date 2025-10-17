@props(['index', 'payment' => [], 'showRemove' => false])

<div class="bg-gradient-to-br from-white to-gray-50 dark:from-zinc-800 dark:to-zinc-900 p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 relative group hover-lift">
    <!-- Cabeçalho do Card -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl shadow-lg">
                <i class="bi bi-credit-card-2-front text-white text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Pagamento {{ $index + 1 }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Preencha os dados do pagamento
                </p>
            </div>
        </div>
        @if($showRemove)
            <button type="button"
                    wire:click="removePaymentRow({{ $index }})"
                    class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                <i class="bi bi-trash-fill text-lg"></i>
            </button>
        @endif
    </div>

    <!-- Formulário -->
    <div class="space-y-4">
        <!-- Campo Valor -->
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                <div class="p-1 bg-green-500/10 rounded">
                    <i class="bi bi-currency-dollar text-green-600 dark:text-green-400"></i>
                </div>
                Valor do Pagamento *
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">R$</span>
                </div>
                <input type="text"
                       id="payment-{{ $index }}"
                       x-data="{
                           displayValue: '{{ isset($payment['amount_paid']) && $payment['amount_paid'] ? number_format((float)$payment['amount_paid'], 2, ',', '.') : '0,00' }}',
                           formatCurrency(value) {
                               let numeric = value.replace(/\D/g, '');
                               if (!numeric || numeric === '0') {
                                   return '0,00';
                               }
                               let cents = parseInt(numeric);
                               return (cents / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                           }
                       }"
                       x-model="displayValue"
                       x-on:input="
                           displayValue = formatCurrency($event.target.value);
                           let numericValue = displayValue.replace(/\./g, '').replace(',', '.');
                           @this.set('payments.{{ $index }}.amount_paid', numericValue);
                       "
                       x-on:focus="if (displayValue === '0,00') $event.target.select()"
                       x-on:blur="if (!displayValue) displayValue = '0,00'"
                       wire:ignore
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-zinc-700 dark:text-white transition-all duration-200 hover:border-green-300 dark:hover:border-green-600"
                       placeholder="0,00">
            </div>
            @error("payments.{$index}.amount_paid")
                <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded-lg">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <!-- Campo Método de Pagamento -->
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                <div class="p-1 bg-blue-500/10 rounded">
                    <i class="bi bi-credit-card text-blue-600 dark:text-blue-400"></i>
                </div>
                Método de Pagamento
            </label>
            <select wire:model="payments.{{ $index }}.payment_method"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-600">
                <option value="dinheiro" class="flex items-center">💵 Dinheiro</option>
                <option value="cartao_debito">💳 Cartão de Débito</option>
                <option value="cartao_credito">💳 Cartão de Crédito</option>
                <option value="pix">⚡ PIX</option>
                <option value="transferencia">🏦 Transferência</option>
                    <option value="cheque">🧾 Cheque</option>
                    <option value="desconto">🔖 Desconto</option>
            </select>
        </div>

        <!-- Campo Data -->
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                <div class="p-1 bg-purple-500/10 rounded">
                    <i class="bi bi-calendar-event text-purple-600 dark:text-purple-400"></i>
                </div>
                Data do Pagamento
            </label>
            <input type="date"
                   wire:model="payments.{{ $index }}.payment_date"
                   class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-zinc-700 dark:text-white transition-all duration-200 hover:border-purple-300 dark:hover:border-purple-600">
        </div>

        <!-- Preview do Valor -->
        @if(isset($payment['amount_paid']) && is_numeric($payment['amount_paid']) && $payment['amount_paid'] > 0)
            <div class="mt-4 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700 dark:text-green-300">
                        Valor a ser registrado:
                    </span>
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                        R$ {{ number_format((float)$payment['amount_paid'], 2, ',', '.') }}
                    </span>
                </div>
            </div>
        @endif

        <!-- Nota para desconto -->
        @if(isset($payment['payment_method']) && $payment['payment_method'] === 'desconto')
            <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700 text-sm text-yellow-700 dark:text-yellow-300">
                <i class="bi bi-info-circle mr-2"></i>
                Ao selecionar <strong>Desconto</strong>, o valor informado será abatido do total da venda.
            </div>
        @endif
    </div>

    <!-- Indicador de Card Ativo -->
    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-green-500 to-emerald-500 rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
</div>

<style>
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
