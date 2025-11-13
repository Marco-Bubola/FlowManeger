<div class="w-full">

    <!-- Header Moderno -->
    <div
        class="relative overflow-hidden bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5">
        </div>
        <div
            class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-12 -translate-y-12">
        </div>
        <div
            class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8">
        </div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <!-- Ícone Principal -->
                    <div class="relative">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-lg flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                            <div
                                class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40">
                            </div>
                        </div>
                        <!-- Badge Online -->
                        <div
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                            <div class="w-full h-full bg-green-500 rounded-full animate-ping opacity-75"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-sm text-slate-300 dark:text-slate-200 mb-1">
                            <i class="fas fa-home mr-1"></i>
                            <span class="text-white font-medium">Dashboard</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">
                            Flow Manager
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-200">
                            <span class="flex items-center">
                                <i class="fas fa-briefcase mr-1"></i>
                                <span class="text-slate-200">Central de Controle do Seu Negócio</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="inline-flex items-center space-x-2 px-3 py-2 bg-green-600/20 dark:bg-green-700/80 rounded-lg border border-green-700/30 dark:border-green-800 shadow-sm">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-white">Sistema Online</span>
                    </div>

                    <a href="{{ route('dashboard.cashbook') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-wallet mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Fluxo de Caixa</span>
                    </a>

                    <a href="{{ route('dashboard.products') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-box mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Produtos</span>
                    </a>

                    <a href="{{ route('dashboard.sales') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i
                            class="fas fa-shopping-cart mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Vendas</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 pb-8">
        <!-- KPIs Principais (Linha 1) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Saldo em Caixa -->
            <div
                class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full transform translate-x-8 -translate-y-8">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-wallet text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-green-800 dark:text-green-300 font-medium">Saldo em Caixa</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">R$
                                {{ number_format($saldoCaixa, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contas a Pagar -->
            <div
                class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-red-200 dark:border-red-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full transform translate-x-8 -translate-y-8">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-red-800 dark:text-red-300 font-medium">Contas a Pagar</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-400">R$
                                {{ number_format($contasPagar, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contas a Receber -->
            <div
                class="group relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-20 h-20 bg-blue-400/10 rounded-full transform translate-x-8 -translate-y-8">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-hand-holding-usd text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-blue-800 dark:text-blue-300 font-medium">Contas a Receber</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">R$
                                {{ number_format($contasReceber, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturamento Total -->
            <div
                class="group relative bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full transform translate-x-8 -translate-y-8">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium">Faturamento Total</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">R$
                                {{ number_format($totalFaturamento, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico de Receitas vs Despesas -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-area text-indigo-500 mr-2"></i>
                        Receitas vs Despesas
                    </h3>
                </div>
                <div id="revenueExpenseChart" class="h-80"></div>
            </div>

            <!-- Gráfico de Valor de Vendas vs Custo dos Produtos -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-bar text-purple-500 mr-2"></i>
                        Valor de Vendas vs Custo dos Produtos
                    </h3>
                </div>
                <div id="salesCostChart" class="h-80"></div>
                <div class="mt-4 pt-3 border-t border-slate-700/40">
                    <div class="flex items-center justify-between text-sm">
                        <div class="text-slate-300">Total Valor de Vendas</div>
                        <div class="font-semibold text-white">R$ {{ number_format($valorVendas, 2, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <div class="text-slate-300">Total Custos (Estoque + Vendidos)</div>
                        <div class="font-semibold text-white">R$ {{ number_format($custoEstoque + $custoProdutosVendidos, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Informações Detalhadas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Clientes -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-users text-pink-500 mr-2"></i>
                    Clientes
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-pink-50 dark:bg-pink-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-friends text-pink-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Total</span>
                        </div>
                        <span class="text-lg font-bold text-pink-600">{{ $totalClientes }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-plus text-green-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Novos no Mês</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">{{ $clientesNovosMes }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-orange-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Com Pendências</span>
                        </div>
                        <span class="text-lg font-bold text-orange-600">{{ $clientesComSalesPendentes }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Inadimplentes</span>
                        </div>
                        <span class="text-lg font-bold text-red-600">{{ $clientesInadimplentes }}</span>
                    </div>
                </div>
            </div>

            <!-- Produtos -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-box text-indigo-500 mr-2"></i>
                    Produtos
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-boxes text-indigo-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Cadastrados</span>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">{{ $produtosCadastrados }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Estoque Baixo</span>
                        </div>
                        <span class="text-lg font-bold text-yellow-600">{{ $produtosEstoqueBaixo }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-green-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Vendidos no Mês</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">{{ $produtosVendidosMes }}</span>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-star text-purple-600"></i>
                            <span class="text-xs text-slate-600 dark:text-slate-400">Mais Vendido</span>
                        </div>
                        <span
                            class="text-sm font-bold text-purple-600 truncate block">{{ $produtoMaisVendido ? $produtoMaisVendido->name : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Vendas -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-shopping-cart text-purple-500 mr-2"></i>
                    Vendas
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-purple-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Vendas no Mês</span>
                        </div>
                        <span class="text-lg font-bold text-purple-600">{{ $salesMonth }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-ticket-alt text-blue-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Ticket Médio</span>
                        </div>
                        <span class="text-lg font-bold text-blue-600">R$
                            {{ number_format($ticketMedio, 2, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-clock text-emerald-600"></i>
                            <span class="text-xs text-slate-600 dark:text-slate-400">Última Venda</span>
                        </div>
                        <span class="text-sm font-bold text-emerald-600">
                            {{ $ultimaVenda ? $ultimaVenda->created_at->format('d/m/Y H:i') : '-' }}
                        </span>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                            <span class="text-xs text-slate-600 dark:text-slate-400">Valor Última Venda</span>
                        </div>
                        <span class="text-sm font-bold text-green-600">
                            {{ $ultimaVenda ? 'R$ ' . number_format($ultimaVenda->total_price, 2, ',', '.') : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Pizza - Distribuição de Produtos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-green-500 mr-2"></i>
                    Gastos Mensais de Invoices
                </h3>
                <div id="invoiceExpensesChart" class="h-80"></div>
            </div>

            <!-- Indicadores de Performance -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                    Indicadores de Performance
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-percentage text-green-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Margem de Lucro</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">{{ number_format($margemLucro, 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between p-3 {{ $taxaCrescimento >= 0 ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-arrow-{{ $taxaCrescimento >= 0 ? 'up' : 'down' }} text-{{ $taxaCrescimento >= 0 ? 'blue' : 'red' }}-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Taxa de Crescimento</span>
                        </div>
                        <span class="text-lg font-bold text-{{ $taxaCrescimento >= 0 ? 'blue' : 'red' }}-600">{{ number_format($taxaCrescimento, 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-box-open text-indigo-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Produtos Ativos</span>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">{{ $produtosAtivos }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-dollar-sign text-purple-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Valor em Estoque</span>
                        </div>
                        <span class="text-lg font-bold text-purple-600">R$ {{ number_format($custoEstoque, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-shopping-cart text-orange-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Custo Prod. Vendidos</span>
                        </div>
                        <span class="text-lg font-bold text-orange-600">R$ {{ number_format($custoProdutosVendidos, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ApexCharts CDN -->
    <style>
        /* Força cores legíveis nas legendas do ApexCharts em light/dark mode */
        .apexcharts-legend-text { color: #0f172a !important; }
        .dark .apexcharts-legend-text, .apexcharts-theme-dark .apexcharts-legend-text { color: #E5E7EB !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Receitas vs Despesas (Área)
            const revenueExpenseOptions = {
                series: [{
                    name: 'Receitas',
                    data: [{{ $contasReceber }}, {{ $totalFaturamento }}, {{ $clientesReceber }}]
                }, {
                    name: 'Despesas',
                    data: [{{ $contasPagar }}, {{ $fornecedoresPagar }}, {{ $despesasFixas }}]
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 800
                    }
                },
                colors: ['#10b981', '#ef4444'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                    }
                },
                xaxis: {
                    categories: ['A Receber', 'Faturamento', 'Clientes'],
                    labels: {
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        },
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                    // Legend color adapts to dark/light mode
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        labels: {
                            colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a']
                        }
                    },
                grid: {
                    borderColor: '#e2e8f0'
                },
                tooltip: {
                    theme: 'dark',
                    style: {
                        fontSize: '13px',
                        colors: ['#ffffff']
                    },
                    y: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            });
                        }
                    }
                }
            };

            const revenueExpenseChart = new ApexCharts(document.querySelector("#revenueExpenseChart"),
                revenueExpenseOptions);
            revenueExpenseChart.render();

            // Gráfico de Valor de Vendas vs Custo dos Produtos (Barra)
            const salesCostOptions = {
                series: [{
                    name: 'Valor de Vendas',
                    data: [{{ $valorVendas }}, null]
                }, {
                    name: 'Custo Estoque',
                    data: [null, {{ $custoEstoque }}]
                }, {
                    name: 'Custo Vendidos',
                    data: [null, {{ $custoProdutosVendidos }}]
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: true,
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#8b5cf6', '#3b82f6', '#f59e0b'],
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        columnWidth: '60%',
                        distributed: false,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ['#64748b']
                    },
                    formatter: function(value) {
                        if (value === null || typeof value === 'undefined') return '';
                        return 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                    }
                },
                xaxis: {
                    categories: ['Valor', 'Custo'],
                    labels: {
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        },
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                },
                grid: {
                    borderColor: '#e2e8f0'
                },
                tooltip: {
                    theme: 'dark',
                    style: {
                        fontSize: '13px',
                        colors: ['#ffffff']
                    },
                    y: {
                        formatter: function(value) {
                            if (value === null || typeof value === 'undefined') return '';
                            return 'R$ ' + Number(value).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            });
                        }
                    }
                }
            };

            const salesCostChart = new ApexCharts(document.querySelector("#salesCostChart"),
                salesCostOptions);
            salesCostChart.render();

            // Gráfico de Gastos Mensais de Invoices (Linha) por banco
            const invoiceExpensesOptions = {
                series: @json($gastosInvoicePorBanco),
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 800
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: 5,
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 7
                    }
                },
                xaxis: {
                    categories: [
                        '{{ now()->subMonths(5)->format("M") }}',
                        '{{ now()->subMonths(4)->format("M") }}',
                        '{{ now()->subMonths(3)->format("M") }}',
                        '{{ now()->subMonths(2)->format("M") }}',
                        '{{ now()->subMonths(1)->format("M") }}',
                        '{{ now()->format("M") }}'
                    ],
                    labels: {
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        },
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                grid: {
                    borderColor: '#e2e8f0'
                },
                // Legend color adapts to dark/light mode
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: {
                        colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a']
                    }
                },
                tooltip: {
                    theme: 'dark',
                    style: {
                        fontSize: '13px',
                        colors: ['#ffffff']
                    },
                    y: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            });
                        }
                    }
                }
            };

            const invoiceExpensesChart = new ApexCharts(document.querySelector("#invoiceExpensesChart"),
                invoiceExpensesOptions);
            invoiceExpensesChart.render();
        });
    </script>
</div>
