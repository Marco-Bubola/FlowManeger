@props(['sale'])

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <div class="p-2 bg-indigo-500/10 rounded-xl">
                <i class="bi bi-credit-card text-indigo-600 dark:text-indigo-400 text-xl"></i>
            </div>
            Pagamentos
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('sales.add-payments', $sale->id) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg text-sm font-semibold">
                <i class="bi bi-plus-circle mr-2"></i>
                <span class="hidden md:inline">Adicionar</span>
            </a>
            @if($sale->payments->count() > 0)
            <a href="{{ route('sales.edit-payments', $sale->id) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg text-sm font-semibold">
                <i class="bi bi-pencil mr-2"></i>
                <span class="hidden md:inline">Editar</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Lista de Pagamentos -->
    @if($sale->payments->count() > 0)
    <div class="space-y-4 max-h-96 overflow-y-auto">
        @foreach($sale->payments as $payment)
        <div class="p-4 bg-gradient-to-r from-white via-indigo-50/30 to-green-50/30 dark:from-zinc-800 dark:via-indigo-900/10 dark:to-green-900/10 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center gap-4">
                <!-- Ícone do método de pagamento -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                        @switch($payment->payment_method)
                            @case('dinheiro')
                                <i class="bi bi-cash text-white text-xl"></i>
                                @break
                            @case('cartao_debito')
                            @case('cartao_credito')
                                <i class="bi bi-credit-card text-white text-xl"></i>
                                @break
                            @case('pix')
                                <i class="bi bi-lightning-charge text-white text-xl"></i>
                                @break
                            @case('transferencia')
                                <i class="bi bi-bank text-white text-xl"></i>
                                @break
                            @case('cheque')
                                <i class="bi bi-check2-square text-white text-xl"></i>
                                @break
                            @default
                                <i class="bi bi-currency-dollar text-white text-xl"></i>
                        @endswitch
                    </div>
                </div>

                <!-- Informações do pagamento -->
                <div class="flex-1 flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}
                        </p>
                        <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 text-green-800 dark:text-green-400 rounded-full text-xs font-semibold border border-green-200 dark:border-green-700 shadow-sm">
                            <i class="bi bi-check-circle-fill mr-1"></i>Confirmado
                        </span>
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <i class="bi bi-credit-card-2-front"></i>
                            {{
                                match($payment->payment_method) {
                                    'dinheiro' => '💵 Dinheiro',
                                    'cartao_debito' => '💳 Cartão de Débito',
                                    'cartao_credito' => '💳 Cartão de Crédito',
                                    'pix' => '⚡ PIX',
                                    'transferencia' => '🏦 Transferência',
                                    'cheque' => '🧾 Cheque',
                                    default => '💰 ' . ucfirst(str_replace('_', ' ', $payment->payment_method))
                                }
                            }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="bi bi-calendar-event"></i>
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-zinc-800/50 dark:via-zinc-700/50 dark:to-zinc-800/50 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600">
        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="bi bi-credit-card text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nenhum pagamento registrado</h3>
        <p class="text-base text-gray-500 dark:text-gray-400 mb-4">
            Adicione um pagamento para começar o controle financeiro desta venda.
        </p>
        <a href="{{ route('sales.add-payments', $sale->id) }}"
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
            <i class="bi bi-plus-circle mr-2"></i>
            Adicionar Primeiro Pagamento
        </a>
    </div>
    @endif
</div>
