<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-900 dark:to-gray-800">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard de Vendas</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Análise completa de vendas e clientes</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Principal
                    </a>
                    <a href="{{ route('sales.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerenciar Vendas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Faturamento Total</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</p>
                        <p class="text-xs text-red-500 dark:text-red-400">R$ {{ number_format($totalFaltante, 2, ',', '.') }} pendente</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Clients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Clientes</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalClientes) }}</p>
                        <p class="text-xs text-orange-500 dark:text-orange-400">{{ $clientesComSalesPendentes }} com pendências</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Recurrent Ticket -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ticket Recorrente</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($ticketMedioRecorrente, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">clientes recorrentes</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Last Sale -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Última Venda</p>
                        @if($ultimaVenda)
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">R$ {{ number_format($ultimaVenda->total_price, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ultimaVenda->created_at->format('d/m/Y H:i') }}</p>
                        @else
                        <p class="text-2xl font-bold text-gray-400">--</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma venda</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-full">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Comparison -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Comparativo de Períodos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Mês Atual</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($vendasMesAtual, 2, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Mês Anterior</p>
                    <p class="text-xl font-bold text-gray-600 dark:text-gray-400">R$ {{ number_format($vendasMesAnterior, 2, ',', '.') }}</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Ano Atual</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($vendasAnoAtual, 2, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Ano Anterior</p>
                    <p class="text-xl font-bold text-gray-600 dark:text-gray-400">R$ {{ number_format($vendasAnoAnterior, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales Evolution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evolução das Vendas (12 meses)</h3>
                <div class="h-80">
                    <canvas id="salesEvolutionChart"></canvas>
                </div>
            </div>

            <!-- Sales by Category Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Vendas por Categoria</h3>
                <div class="h-80">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Client Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Clients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top 10 Clientes</h3>
                <div class="space-y-3">
                    @forelse($vendasPorCliente as $index => $venda)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                                <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $venda['client']['name'] ?? 'Cliente não encontrado' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $venda['qtd_vendas'] }} vendas</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($venda['total_vendas'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhuma venda registrada</p>
                    @endforelse
                </div>
            </div>

            <!-- Pending Clients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Clientes com Pendências</h3>
                <div class="space-y-3">
                    @forelse($clientesPendentes as $cliente)
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $cliente['name'] }}</p>
                            <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-2 py-1 rounded">Pendente</span>
                        </div>
                        @foreach($cliente['sales'] as $sale)
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                            <span>Venda #{{ $sale['id'] }}</span>
                            <span class="font-medium text-red-600 dark:text-red-400">R$ {{ number_format($sale['valor_restante'], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhuma pendência</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status das Vendas</h3>
                <div class="space-y-3">
                    @forelse($vendasPorStatus as $status)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $status['status'] }}</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $status['total'] }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhuma venda registrada</p>
                    @endforelse
                </div>
            </div>

            <!-- Recurrent Clients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Clientes Recorrentes</h3>
                <div class="space-y-3">
                    @forelse($clientesRecorrentes as $cliente)
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $cliente['client']['name'] ?? 'Cliente não encontrado' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $cliente['qtd_vendas'] }} compras</p>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($cliente['total'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum cliente recorrente</p>
                    @endforelse
                </div>
            </div>

            <!-- Inactive Clients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Clientes Inativos (6 meses)</h3>
                <div class="space-y-3">
                    @forelse($clientesInativos as $cliente)
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $cliente['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $cliente['email'] ?? 'Sem email' }}</p>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum cliente inativo</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Evolution Chart
    const evolutionCtx = document.getElementById('salesEvolutionChart').getContext('2d');
    const evolutionData = @json($vendasPorMesEvolucao);
    
    const evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: evolutionData.map(item => item.periodo),
            datasets: [{
                label: 'Vendas',
                data: evolutionData.map(item => item.total),
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Vendas: R$ ' + context.parsed.y.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($dadosGraficoPizza);
    
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category_name),
            datasets: [{
                data: categoryData.map(item => item.total_sold),
                backgroundColor: [
                    '#8B5CF6',
                    '#EC4899',
                    '#F59E0B',
                    '#10B981',
                    '#3B82F6',
                    '#EF4444',
                    '#06B6D4',
                    '#84CC16'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' unidades';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
