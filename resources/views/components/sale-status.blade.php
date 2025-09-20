@props(['sale'])

<div class="bg-gradient-to-r from-green-50 via-white to-indigo-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <div class="p-2 bg-green-500/10 rounded-xl">
            <i class="bi bi-flag text-green-500 text-xl"></i>
        </div>
        Status e Pagamento
    </h3>

    <div class="space-y-4">
        <!-- Status da Venda -->
        <div class="flex items-center justify-between p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <i class="bi bi-activity text-indigo-600 dark:text-indigo-400 text-lg"></i>
                </div>
                <span class="text-base font-medium text-gray-700 dark:text-gray-300">Status</span>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2 shadow-sm
                @if($sale->status === 'pago')
                    bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 dark:from-green-900/30 dark:to-emerald-900/30 dark:text-green-400 border border-green-200 dark:border-green-700
                @elseif($sale->status === 'pendente')
                    bg-gradient-to-r from-yellow-100 to-orange-100 text-yellow-800 dark:from-yellow-900/30 dark:to-orange-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-700
                @else
                    bg-gradient-to-r from-red-100 to-rose-100 text-red-800 dark:from-red-900/30 dark:to-rose-900/30 dark:text-red-400 border border-red-200 dark:border-red-700
                @endif">
                @if($sale->status === 'pago')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($sale->status === 'pendente')
                    <i class="bi bi-clock-fill"></i>
                @else
                    <i class="bi bi-x-circle-fill"></i>
                @endif
                {{ ucfirst($sale->status) }}
            </span>
        </div>

        <!-- Tipo de Pagamento -->
        <div class="flex items-center justify-between p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <i class="bi bi-wallet2 text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <span class="text-base font-medium text-gray-700 dark:text-gray-300">Tipo</span>
            </div>
            <div class="flex items-center gap-2">
                @if($sale->tipo_pagamento === 'a_vista')
                    <div class="px-4 py-2 bg-gradient-to-r from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 text-blue-800 dark:text-blue-400 rounded-full text-sm font-semibold border border-blue-200 dark:border-blue-700 shadow-sm">
                        <i class="bi bi-cash-coin mr-1"></i>À Vista
                    </div>
                @else
                    <div class="px-4 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-400 rounded-full text-sm font-semibold border border-indigo-200 dark:border-indigo-700 shadow-sm">
                        <i class="bi bi-credit-card mr-1"></i>Parcelado ({{ $sale->parcelas }}x)
                    </div>
                @endif
            </div>
        </div>

        <!-- Data da Venda -->
        <div class="flex items-center justify-between p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                    <i class="bi bi-calendar-event text-amber-600 dark:text-amber-400 text-lg"></i>
                </div>
                <span class="text-base font-medium text-gray-700 dark:text-gray-300">Data da Venda</span>
            </div>
            <span class="text-base font-semibold text-gray-900 dark:text-white">
                {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
</div>
