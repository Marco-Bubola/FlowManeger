@props([
    'totalVendas' => 0,
    'totalFaturado' => 0,
    'totalPago' => 0,
    'totalPendente' => 0,
    'ticketMedio' => 0,
    'ultimaCompra' => null,
    'vendas' => [],
    'parcelas' => []
])

<!-- Cards de Resumo Financeiro Modernos -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-6 mb-8">
    <!-- Total de Vendas -->
    <div class="stats-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Total de Vendas</p>
                <p class="text-3xl font-bold" data-animate-number>{{ $totalVendas }}</p>
                <p class="text-blue-200 text-xs mt-1">
                    <i class="bi bi-trending-up mr-1"></i>
                    Volume total
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-cart text-2xl"></i>
            </div>
        </div>
        <!-- Efeito de brilho no hover -->
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Total Faturado -->
    <div class="stats-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Total Faturado</p>
                <p class="text-3xl font-bold">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</p>
                <p class="text-green-200 text-xs mt-1">
                    <i class="bi bi-cash-coin mr-1"></i>
                    Receita bruta
                </p>
            </div>
            <div class="w-12 h-12 bg-green-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-currency-dollar text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Total Pago -->
    <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-medium">Total Pago</p>
                <p class="text-3xl font-bold">R$ {{ number_format($totalPago, 2, ',', '.') }}</p>
                <p class="text-purple-200 text-xs mt-1">
                    <i class="bi bi-check-circle mr-1"></i>
                    Receita líquida
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-check-circle text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Restante a Pagar -->
    <div class="stats-card bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-amber-100 text-sm font-medium">Restante a Pagar</p>
                <p class="text-3xl font-bold">R$ {{ number_format($totalPendente, 2, ',', '.') }}</p>
                <p class="text-amber-200 text-xs mt-1">
                    <i class="bi bi-hourglass-split mr-1"></i>
                    @if($totalFaturado > 0)
                        {{ number_format(($totalPendente / $totalFaturado) * 100, 1) }}% pendente
                    @else
                        0% pendente
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 bg-amber-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-hourglass-split text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Ticket Médio -->
    <div class="stats-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm font-medium">Ticket Médio</p>
                <p class="text-3xl font-bold">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                <p class="text-orange-200 text-xs mt-1">
                    <i class="bi bi-graph-up mr-1"></i>
                    Valor médio
                </p>
            </div>
            <div class="w-12 h-12 bg-orange-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-graph-up text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Última Compra -->
    <div class="stats-card bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm font-medium">Última Compra</p>
                <p class="text-2xl font-bold">
                    {{ count($vendas) > 0 ? \Carbon\Carbon::parse($vendas[0]['created_at'])->diffForHumans() : 'Nunca' }}
                </p>
                <p class="text-teal-200 text-xs mt-1">
                    <i class="bi bi-clock-history mr-1"></i>
                    Atividade recente
                </p>
            </div>
            <div class="w-12 h-12 bg-teal-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-clock text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>

    <!-- Parcelas Vencidas -->
    <div class="stats-card bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-medium">Parcelas Vencidas</p>
                <p class="text-3xl font-bold">
                    {{ collect($parcelas)->filter(function($parcela) {
                        return $parcela['status'] === 'pendente' && \Carbon\Carbon::parse($parcela['data_vencimento'])->isPast();
                    })->count() }}
                </p>
                <p class="text-red-200 text-xs mt-1">
                    <i class="bi bi-exclamation-triangle mr-1"></i>
                    @php
                        $valorVencido = collect($parcelas)->filter(function($parcela) {
                            return $parcela['status'] === 'pendente' && \Carbon\Carbon::parse($parcela['data_vencimento'])->isPast();
                        })->sum('valor');
                    @endphp
                    R$ {{ number_format($valorVencido, 2, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-red-400/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-exclamation-triangle text-2xl"></i>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
    </div>
</div>

<style>
.stats-card {
    position: relative;
    overflow: hidden;
}

.hover-scale:hover {
    transform: translateY(-4px) scale(1.02);
}

/* Animação para números */
@keyframes countUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

[data-animate-number] {
    animation: countUp 0.6s ease-out;
}
</style>
