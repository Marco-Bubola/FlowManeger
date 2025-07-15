<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard de Fluxo de Caixa</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Análise completa do seu fluxo financeiro</p>
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
                    <a href="{{ route('cashbook.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerenciar Cashbook
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Year Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros de Período</h2>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ano:</label>
                        <select wire:model.live="ano" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            @foreach(range(date('Y')-5, date('Y')+2) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Mês (Invoices):</label>
                        <select wire:model.live="mesInvoices" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->locale('pt_BR')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Income -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Receitas</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Expenses -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Despesas</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Net Balance -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Total</p>
                        <p class="text-2xl font-bold {{ $saldoTotal >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format(abs($saldoTotal), 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 {{ $saldoTotal >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-full">
                        <svg class="w-6 h-6 {{ $saldoTotal >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Data -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Monthly Evolution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evolução Mensal {{ $ano }}</h3>
                <div class="h-80">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Last Month Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumo do Último Mês</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mês</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $nomeUltimoMes }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Receitas</span>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($receitaUltimoMes, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Despesas</span>
                        </div>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400">R$ {{ number_format($despesaUltimoMes, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 {{ $saldoUltimoMes >= 0 ? 'bg-blue-500' : 'bg-orange-500' }} rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Saldo</span>
                        </div>
                        <span class="text-sm font-bold {{ $saldoUltimoMes >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                            R$ {{ number_format(abs($saldoUltimoMes), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banks Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informações dos Bancos</h3>
                <a href="{{ route('banks.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Gerenciar Bancos
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total de Bancos</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalBancos }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total de Faturas</p>
                    <p class="text-xl font-bold text-orange-600 dark:text-orange-400">R$ {{ number_format($totalInvoicesBancos, 2, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Total</p>
                    <p class="text-xl font-bold {{ $saldoTotalBancos >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        R$ {{ number_format(abs($saldoTotalBancos), 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total de Saídas</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400">R$ {{ number_format($totalSaidasBancos, 2, ',', '.') }}</p>
                </div>
            </div>

            @if(count($bancosInfo) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Banco</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Qtd Faturas</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Total</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Média</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bancosInfo as $banco)
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-3 px-4">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $banco['nome'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $banco['descricao'] }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right text-gray-900 dark:text-white">{{ $banco['qtd_invoices'] }}</td>
                            <td class="py-3 px-4 text-right text-red-600 dark:text-red-400">R$ {{ number_format($banco['total_invoices'], 2, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-gray-900 dark:text-white">R$ {{ number_format($banco['media_invoices'], 2, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right {{ $banco['saldo'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                R$ {{ number_format(abs($banco['saldo']), 2, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Nenhum banco cadastrado</p>
            </div>
            @endif
        </div>

        <!-- Daily Invoices Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Faturas Diárias - {{ \Carbon\Carbon::create($anoInvoices, $mesInvoices, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}
            </h3>
            <div class="h-80">
                <canvas id="dailyInvoicesChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                label: 'Receitas',
                data: @json($dadosReceita),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            }, {
                label: 'Despesas',
                data: @json($dadosDespesa),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1
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
                            return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Daily Invoices Chart
    const dailyCtx = document.getElementById('dailyInvoicesChart').getContext('2d');
    const dailyChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: @json($diasInvoices),
            datasets: [{
                label: 'Faturas',
                data: @json($valoresInvoices),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
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
                            return 'Faturas: R$ ' + context.parsed.y.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Update charts when Livewire updates
    Livewire.on('updated', function() {
        monthlyChart.data.datasets[0].data = @json($dadosReceita);
        monthlyChart.data.datasets[1].data = @json($dadosDespesa);
        monthlyChart.update();
        
        dailyChart.data.labels = @json($diasInvoices);
        dailyChart.data.datasets[0].data = @json($valoresInvoices);
        dailyChart.update();
    });
});
</script>
@endpush
