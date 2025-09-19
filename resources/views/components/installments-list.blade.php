@props(['parcelas'])

@if($parcelas && $parcelas->count() > 0)
<div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 dark:from-indigo-900/20 dark:via-zinc-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <div class="p-2 bg-indigo-500/10 rounded-xl">
            <i class="bi bi-credit-card text-indigo-500 text-xl"></i>
        </div>
        Parcelas ({{ $parcelas->count() }})
    </h3>

    <div class="space-y-3 max-h-96 overflow-y-auto">
        @foreach($parcelas as $parcela)
        <div class="flex items-center justify-between p-4 bg-white/60 dark:bg-zinc-800/60 rounded-xl border border-gray-200/50 dark:border-zinc-700/50 hover:bg-white/80 dark:hover:bg-zinc-800/80 transition-all duration-200">
            <!-- Número da Parcela -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
                    {{ $parcela->numero_parcela }}
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Parcela {{ $parcela->numero_parcela }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">R$ {{ number_format($parcela->valor, 2, ',', '.') }}</p>
                </div>
            </div>

            <!-- Status e Data -->
            <div class="flex flex-col items-end gap-2">
                <div class="flex items-center gap-2">
                    @if($parcela->status === 'pago')
                        <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 text-green-800 dark:text-green-400 rounded-full text-xs font-semibold border border-green-200 dark:border-green-700 shadow-sm">
                            <i class="bi bi-check-circle-fill mr-1"></i>Pago
                        </span>
                    @else
                        @if(\Carbon\Carbon::parse($parcela->data_vencimento)->isPast())
                            <span class="px-3 py-1 bg-gradient-to-r from-red-100 to-rose-100 dark:from-red-900/30 dark:to-rose-900/30 text-red-800 dark:text-red-400 rounded-full text-xs font-semibold border border-red-200 dark:border-red-700 shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>Vencido
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gradient-to-r from-yellow-100 to-orange-100 dark:from-yellow-900/30 dark:to-orange-900/30 text-yellow-800 dark:text-yellow-400 rounded-full text-xs font-semibold border border-yellow-200 dark:border-yellow-700 shadow-sm">
                                <i class="bi bi-clock-fill mr-1"></i>Pendente
                            </span>
                        @endif
                    @endif
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Vencimento</p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}
                    </p>
                </div>

                @if($parcela->status === 'pendente')
                <button type="button"
                        wire:click="openPaymentModal({{ $parcela->id }})"
                        class="px-3 py-1 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="bi bi-cash mr-1"></i>Pagar
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
