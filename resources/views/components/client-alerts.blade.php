@props([
    'parcelas' => [],
    'totalVendas' => 0,
    'totalPago' => 0,
    'totalFaturado' => 0,
    'ultimaCompra' => null
])

<!-- Seção de Alertas e Informações Importantes -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Alertas de Parcelas -->
    <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 group">
        <h3 class="text-lg font-bold text-red-800 dark:text-red-400 mb-4 flex items-center">
            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-exclamation-triangle-fill text-white text-lg"></i>
            </div>
            Alertas Importantes
        </h3>

        @php
            $parcelasVencidas = collect($parcelas)->filter(function($parcela) {
                return $parcela['status'] === 'pendente' && \Carbon\Carbon::parse($parcela['data_vencimento'])->isPast();
            });
            $parcelasVencendoSemana = collect($parcelas)->filter(function($parcela) {
                return $parcela['status'] === 'pendente' &&
                       \Carbon\Carbon::parse($parcela['data_vencimento'])->isBetween(now(), now()->addDays(7));
            });
        @endphp

        <div class="space-y-4">
            @if($parcelasVencidas->count() > 0)
                <div class="flex items-start space-x-3 p-4 bg-red-100 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-700/50 group/alert hover:bg-red-200 dark:hover:bg-red-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/alert:scale-110 transition-transform duration-200">
                        <i class="bi bi-exclamation-circle-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                            {{ $parcelasVencidas->count() }} parcela{{ $parcelasVencidas->count() > 1 ? 's' : '' }} vencida{{ $parcelasVencidas->count() > 1 ? 's' : '' }}
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                            Valor total: R$ {{ number_format($parcelasVencidas->sum('valor'), 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            @endif

            @if($parcelasVencendoSemana->count() > 0)
                <div class="flex items-start space-x-3 p-4 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl border border-yellow-200 dark:border-yellow-700/50 group/alert hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/alert:scale-110 transition-transform duration-200">
                        <i class="bi bi-clock-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">
                            {{ $parcelasVencendoSemana->count() }} parcela{{ $parcelasVencendoSemana->count() > 1 ? 's' : '' }} vence{{ $parcelasVencendoSemana->count() > 1 ? 'm' : '' }} esta semana
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                            Valor total: R$ {{ number_format($parcelasVencendoSemana->sum('valor'), 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            @endif

            @if($parcelasVencidas->count() == 0 && $parcelasVencendoSemana->count() == 0)
                <div class="flex items-center justify-center space-x-3 p-4 bg-green-100 dark:bg-green-900/30 rounded-xl border border-green-200 dark:border-green-700/50">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold text-green-800 dark:text-green-300">Todas as parcelas em dia!</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Performance do Cliente -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 group">
        <h3 class="text-lg font-bold text-blue-800 dark:text-blue-400 mb-4 flex items-center">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-graph-up-arrow text-white text-lg"></i>
            </div>
            Performance
        </h3>

        <div class="space-y-6">
            <!-- Frequência de Compras -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 flex items-center">
                        <i class="bi bi-arrow-repeat text-blue-500 mr-2"></i>
                        Frequência de Compras
                    </span>
                    @php
                        $diasComoCliente = $ultimaCompra ? \Carbon\Carbon::parse($ultimaCompra)->diffInDays(now()) : 0;
                        $frequencia = $diasComoCliente > 0 && $totalVendas > 1 ?
                            round($diasComoCliente / $totalVendas) : 0;
                    @endphp
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        @if($frequencia > 0)
                            A cada {{ $frequencia }} dias
                        @else
                            Cliente novo
                        @endif
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 shadow-inner">
                    @php
                        $frequenciaScore = $frequencia > 0 ? min(100, (30 / max(1, $frequencia)) * 100) : 0;
                    @endphp
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-1000 shadow-lg"
                         style="width: {{ $frequenciaScore }}%"></div>
                </div>
            </div>

            <!-- Taxa de Pagamento -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 flex items-center">
                        <i class="bi bi-check-circle text-green-500 mr-2"></i>
                        Taxa de Pagamento
                    </span>
                    @php
                        $taxaPagamento = $totalFaturado > 0 ? ($totalPago / $totalFaturado) * 100 : 0;
                    @endphp
                    <span class="text-sm font-bold text-green-600 dark:text-green-400 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        {{ number_format($taxaPagamento, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 shadow-inner">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-3 rounded-full transition-all duration-1000 shadow-lg"
                         style="width: {{ $taxaPagamento }}%"></div>
                </div>
            </div>

            <!-- Valor Médio Score -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 flex items-center">
                        <i class="bi bi-graph-up text-purple-500 mr-2"></i>
                        Valor Médio
                    </span>
                    @php
                        $ticketMedio = $totalVendas > 0 ? $totalFaturado / $totalVendas : 0;
                        $ticketScore = min(100, ($ticketMedio / 1000) * 100); // Considera R$ 1000 como 100%
                    @endphp
                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        @if($ticketScore >= 80)
                            Excelente
                        @elseif($ticketScore >= 60)
                            Bom
                        @elseif($ticketScore >= 40)
                            Médio
                        @else
                            Baixo
                        @endif
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 shadow-inner">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-1000 shadow-lg"
                         style="width: {{ $ticketScore }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Ações -->
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 group">
        <h3 class="text-lg font-bold text-green-800 dark:text-green-400 mb-4 flex items-center">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-list-check text-white text-lg"></i>
            </div>
            Próximas Ações
        </h3>

        <div class="space-y-4">
            @if($parcelasVencidas->count() > 0)
                <div class="flex items-start space-x-3 p-4 bg-orange-100 dark:bg-orange-900/30 rounded-xl border border-orange-200 dark:border-orange-700/50 group/action hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/action:scale-110 transition-transform duration-200">
                        <i class="bi bi-telephone-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-orange-800 dark:text-orange-300">
                            Entrar em contato para cobrança
                        </p>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                            Parcelas em atraso precisam de atenção
                        </p>
                    </div>
                </div>
            @endif

            @if($ultimaCompra && \Carbon\Carbon::parse($ultimaCompra)->diffInDays(now()) > 30)
                <div class="flex items-start space-x-3 p-4 bg-blue-100 dark:bg-blue-900/30 rounded-xl border border-blue-200 dark:border-blue-700/50 group/action hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/action:scale-110 transition-transform duration-200">
                        <i class="bi bi-gift-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                            Oferecer promoção especial
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            Cliente inativo há mais de 30 dias
                        </p>
                    </div>
                </div>
            @endif

            @php
                $taxaPagamento = $totalFaturado > 0 ? ($totalPago / $totalFaturado) * 100 : 0;
            @endphp
            @if($totalVendas >= 5 && $taxaPagamento >= 90)
                <div class="flex items-start space-x-3 p-4 bg-purple-100 dark:bg-purple-900/30 rounded-xl border border-purple-200 dark:border-purple-700/50 group/action hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/action:scale-110 transition-transform duration-200">
                        <i class="bi bi-star-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-purple-800 dark:text-purple-300">
                            Considerar cliente VIP
                        </p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                            Histórico excelente de pagamentos
                        </p>
                    </div>
                </div>
            @endif

            @if($parcelasVencidas->count() == 0 && $totalVendas > 0)
                <div class="flex items-start space-x-3 p-4 bg-green-100 dark:bg-green-900/30 rounded-xl border border-green-200 dark:border-green-700/50 group/action hover:bg-green-200 dark:hover:bg-green-900/50 transition-all duration-200">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0 group-hover/action:scale-110 transition-transform duration-200">
                        <i class="bi bi-cart-plus-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                            Oportunidade de nova venda
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Cliente com bom histórico financeiro
                        </p>
                    </div>
                </div>
            @endif

            @if($parcelasVencidas->count() == 0 && $totalVendas == 0)
                <div class="flex items-center justify-center p-6 bg-gray-100 dark:bg-gray-900/30 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-gray-400 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="bi bi-plus-circle text-white text-xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Aguardando primeira venda
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
