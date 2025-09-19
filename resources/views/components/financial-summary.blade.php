@props(['sale'])

<div class="space-y-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <div class="p-2 bg-indigo-500/10 rounded-xl">
            <i class="bi bi-calculator text-indigo-600 dark:text-indigo-400 text-xl"></i>
        </div>
        Resumo Financeiro
    </h2>

    <!-- Total da Venda -->
    <div class="bg-gradient-to-r from-indigo-50 via-white to-blue-50 dark:from-indigo-900/20 dark:via-zinc-900/20 dark:to-blue-900/20 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-indigo-200 dark:border-indigo-700">
        <div class="text-center">
            <div class="flex items-center justify-center mb-3">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-2xl shadow-lg">
                    <i class="bi bi-currency-dollar text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">Total da Venda</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                R$ {{ number_format($sale->total_price, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Valor Pago -->
    @if($sale->total_paid > 0)
    <div class="bg-gradient-to-r from-green-50 via-white to-emerald-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-emerald-900/20 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-green-200 dark:border-green-700">
        <div class="text-center">
            <div class="flex items-center justify-center mb-3">
                <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl shadow-lg">
                    <i class="bi bi-check-circle text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">Valor Pago</p>
            <p class="text-3xl font-bold text-green-800 dark:text-green-400">
                R$ {{ number_format($sale->total_paid, 2, ',', '.') }}
            </p>
        </div>
    </div>
    @endif

    <!-- Valor em Aberto -->
    @if($sale->remaining_amount > 0)
    <div class="bg-gradient-to-r from-red-50 via-white to-rose-50 dark:from-red-900/20 dark:via-zinc-900/20 dark:to-rose-900/20 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-red-200 dark:border-red-700">
        <div class="text-center">
            <div class="flex items-center justify-center mb-3">
                <div class="p-3 bg-gradient-to-br from-red-500 to-rose-500 rounded-2xl shadow-lg">
                    <i class="bi bi-exclamation-triangle text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">Valor em Aberto</p>
            <p class="text-3xl font-bold text-red-800 dark:text-red-400">
                R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}
            </p>
        </div>
    </div>
    @endif

    <!-- Porcentagem Paga -->
    @if($sale->total_price > 0)
    @php
        $percentagePaid = ($sale->total_paid / $sale->total_price) * 100;
    @endphp
    <div class="bg-gradient-to-r from-purple-50 via-white to-pink-50 dark:from-purple-900/20 dark:via-zinc-900/20 dark:to-pink-900/20 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-purple-200 dark:border-purple-700">
        <div class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-3">Progresso do Pagamento</p>

            <!-- Barra de Progresso -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-3 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500 shadow-inner"
                     style="width: {{ $percentagePaid }}%"></div>
            </div>

            <p class="text-xl font-bold text-gray-900 dark:text-white">
                {{ number_format($percentagePaid, 1) }}% concluído
            </p>
        </div>
    </div>
    @endif
</div>
