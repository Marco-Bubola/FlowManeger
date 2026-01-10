<div class="w-full">

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-cyan-50/90 to-blue-50/80 dark:from-slate-800/90 dark:via-cyan-900/30 dark:to-blue-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
        <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-cyan-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-cyan-400/10 via-blue-400/10 to-indigo-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 via-blue-500 to-indigo-500 rounded-xl shadow-lg flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                            <i class="fas fa-wallet text-white text-2xl"></i>
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                            <div class="w-full h-full bg-green-500 rounded-full animate-ping opacity-75"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <!-- Breadcrumb dentro do header -->
                        <div class="flex items-center gap-2 text-sm text-white/70 mb-1">
                            <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">
                                <i class="fas fa-chart-line mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-white font-medium">
                                <i class="fas fa-book mr-1"></i>Livro Caixa
                            </span>
                        </div>

                        <h1 class="text-2xl lg:text-3xl font-bold text-white">
                            Fluxo de Caixa
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-200">
                            <span class="flex items-center">
                                <i class="fas fa-coins mr-1"></i>
                                <span class="text-slate-200">Controle Completo das Suas Finanças</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center space-x-2 px-3 py-2 bg-green-600/20 dark:bg-green-700/80 rounded-lg border border-green-700/30 dark:border-green-800 shadow-sm">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-white">Dados em Tempo Real</span>
                    </div>

                    <a href="{{ route('dashboard.index') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-home mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('cashbook.index') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-cog mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Gerenciar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="">
        <!-- KPIs Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Saldo Total -->
            <div class="group relative bg-gradient-to-br from-cyan-50 to-blue-100 dark:from-cyan-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-cyan-200 dark:border-cyan-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-cyan-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-wallet text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-cyan-800 dark:text-cyan-300 font-medium">Saldo Total</p>
                            <p class="text-2xl font-bold text-cyan-700 dark:text-cyan-400">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Receitas -->
            <div class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-arrow-up text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-green-800 dark:text-green-300 font-medium">Total Receitas</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Despesas -->
            <div class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-red-200 dark:border-red-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-arrow-down text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-red-800 dark:text-red-300 font-medium">Total Despesas</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-400">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultado Líquido -->
            <div class="group relative bg-gradient-to-br from-purple-50 to-indigo-100 dark:from-purple-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium">Resultado Líquido</p>
                            <p class="text-2xl font-bold {{ $saldoTotal >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                R$ {{ number_format($saldoTotal, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico Fluxo Mensal -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-area text-cyan-500 mr-2"></i>
                        Fluxo de Caixa Mensal ({{ $ano }})
                    </h3>
                </div>
                <div id="cashFlowChart" class="h-80"></div>
            </div>

            <!-- Gráfico Receitas vs Despesas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Receitas vs Despesas Mensais
                    </h3>
                </div>
                <div id="revenueExpenseChart" class="h-80"></div>
            </div>
        </div>

        <!-- Segunda linha de gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gastos Mensais por Categoria e Banco -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-layer-group text-indigo-500 mr-2"></i>
                        Gastos Mensais de Invoices (Top Categorias × Bancos)
                    </h3>
                </div>
                <div id="gastosMensaisChart" class="h-80"></div>
            </div>

            <!-- Gastos Diários de Invoices -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-2"></i>
                        Gastos Diários - Invoices ({{ \Carbon\Carbon::create($anoInvoices, $mesInvoices)->locale('pt_BR')->isoFormat('MMMM/YYYY') }})
                    </h3>
                </div>
                <div id="dailyInvoicesChart" class="h-80"></div>
            </div>
        </div>

        <!-- Gráfico de Categorias -->
        @if(count($categorias) > 0)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fas fa-tags text-purple-500 mr-2"></i>
                    Top 10 Categorias de Invoices (Total Geral)
                </h3>
            </div>
            <div id="categoriasChart" class="h-96"></div>
        </div>
        @endif

        <!-- Cards de Resumo Financeiro -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Estatísticas do Último Mês -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                    Último Mês com Movimento ({{ $nomeUltimoMes }})
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-arrow-up text-green-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Receitas</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">R$ {{ number_format($receitaUltimoMes, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-arrow-down text-red-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Despesas</span>
                        </div>
                        <span class="text-lg font-bold text-red-600">R$ {{ number_format($despesaUltimoMes, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 {{ $saldoUltimoMes >= 0 ? 'bg-cyan-50 dark:bg-cyan-900/20' : 'bg-orange-50 dark:bg-orange-900/20' }} rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-balance-scale {{ $saldoUltimoMes >= 0 ? 'text-cyan-600' : 'text-orange-600' }}"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Saldo</span>
                        </div>
                        <span class="text-lg font-bold {{ $saldoUltimoMes >= 0 ? 'text-cyan-600' : 'text-orange-600' }}">R$ {{ number_format($saldoUltimoMes, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Informações dos Bancos -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-university text-indigo-500 mr-2"></i>
                    Resumo dos Bancos
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-building text-indigo-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Total de Bancos</span>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">{{ $totalBancos }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-invoice text-red-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Total Invoices</span>
                        </div>
                        <span class="text-lg font-bold text-red-600">R$ {{ number_format($totalInvoicesBancos, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-cyan-50 dark:bg-cyan-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-coins text-cyan-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Saldo Total Bancos</span>
                        </div>
                        <span class="text-lg font-bold text-cyan-600">R$ {{ number_format($saldoTotalBancos, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Indicadores de Performance -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
                    Indicadores de Performance
                </h3>
                <div class="space-y-3">
                    @php
                        $mediaReceitas = count(array_filter($dadosReceita)) > 0 ? array_sum($dadosReceita) / count(array_filter($dadosReceita)) : 0;
                        $mediaDespesas = count(array_filter($dadosDespesa)) > 0 ? array_sum($dadosDespesa) / count(array_filter($dadosDespesa)) : 0;
                        $taxaSucesso = $totalDespesas > 0 ? (($totalReceitas / $totalDespesas) * 100) : 0;
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chart-line text-blue-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Média Receitas/Mês</span>
                        </div>
                        <span class="text-lg font-bold text-blue-600">R$ {{ number_format($mediaReceitas, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chart-line text-orange-600"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Média Despesas/Mês</span>
                        </div>
                        <span class="text-lg font-bold text-orange-600">R$ {{ number_format($mediaDespesas, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 {{ $taxaSucesso >= 100 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-percentage {{ $taxaSucesso >= 100 ? 'text-green-600' : 'text-red-600' }}"></i>
                            <span class="text-sm text-slate-300 dark:text-slate-200">Taxa Rec/Desp</span>
                        </div>
                        <span class="text-lg font-bold {{ $taxaSucesso >= 100 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($taxaSucesso, 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Bancos Detalhada -->
        @if(count($bancosInfo) > 0)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                <i class="fas fa-list text-indigo-500 mr-2"></i>
                Detalhamento por Banco
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($bancosInfo as $banco)
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700/50 dark:to-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-university text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ $banco['nome'] }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $banco['descricao'] ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Total Invoices:</span>
                            <span class="font-semibold text-red-600">R$ {{ number_format($banco['total_invoices'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Quantidade:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $banco['qtd_invoices'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Média:</span>
                            <span class="font-semibold text-blue-600">R$ {{ number_format($banco['media_invoices'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- ApexCharts CDN e Scripts -->
    <style>
        .apexcharts-legend-text { color: #0f172a !important; }
        .dark .apexcharts-legend-text, .apexcharts-theme-dark .apexcharts-legend-text { color: #E5E7EB !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dadosReceita = @json($dadosReceita);
            const dadosDespesa = @json($dadosDespesa);
            const saldosMes = @json($saldosMes);
            const diasInvoices = @json($diasInvoices);
            const valoresInvoices = @json($valoresInvoices);

            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

            const cashFlowOptions = {
                series: [{ name: 'Saldo Mensal', data: saldosMes }],
                chart: { type: 'area', height: 320, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                colors: ['#06b6d4'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
                xaxis: { categories: meses, labels: { style: { colors: '#64748b' } } },
                yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#64748b' } } },
                grid: { borderColor: '#e2e8f0' },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                tooltip: { theme: 'dark', style: { fontSize: '13px', colors: ['#ffffff'] }, y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
            };
            new ApexCharts(document.querySelector("#cashFlowChart"), cashFlowOptions).render();

            const revenueExpenseOptions = {
                series: [{ name: 'Receitas', data: dadosReceita }, { name: 'Despesas', data: dadosDespesa }],
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                colors: ['#10b981', '#ef4444'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%', dataLabels: { position: 'top' } } },
                dataLabels: { enabled: false },
                xaxis: { categories: meses, labels: { style: { colors: '#64748b' } } },
                yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#64748b' } } },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                grid: { borderColor: '#e2e8f0' },
                tooltip: { theme: 'dark', style: { fontSize: '13px', colors: ['#ffffff'] }, y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
            };
            new ApexCharts(document.querySelector("#revenueExpenseChart"), revenueExpenseOptions).render();

            // Gráfico de Gastos Mensais por Categoria e Banco
            const gastosMensaisMeses = @json($gastosMensaisMeses ?? []);
            const gastosPorCategoria = @json($gastosMensaisPorCategoria ?? []);
            const gastosPorCatBank = @json($gastosMensaisPorCategoriaBanco ?? []);

            if (gastosMensaisMeses.length > 0 && document.querySelector("#gastosMensaisChart")) {
                const seriesData = [];
                const palette = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#f97316', '#ef4444', '#0ea5a4', '#a78bfa'];

                if (Object.keys(gastosPorCatBank).length > 0) {
                    // Cada série é uma combinação Categoria — Banco (limitada pelo backend aos top categories x top banks)
                    for (const [label, valores] of Object.entries(gastosPorCatBank)) {
                        seriesData.push({ name: label, data: valores });
                    }
                } else if (Object.keys(gastosPorCategoria).length > 0) {
                    // Fallback: apenas categorias
                    for (const [categoria, valores] of Object.entries(gastosPorCategoria)) {
                        seriesData.push({ name: categoria, data: valores });
                    }
                }

                const gastosMensaisOptions = {
                    series: seriesData,
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, stacked: true },
                    colors: palette,
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '70%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: gastosMensaisMeses, labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#64748b' } } },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    tooltip: { theme: 'dark', style: { fontSize: '13px', colors: ['#ffffff'] }, y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
                };

                new ApexCharts(document.querySelector("#gastosMensaisChart"), gastosMensaisOptions).render();
            }

            const dailyInvoicesOptions = {
                series: [{ name: 'Gastos', data: valoresInvoices }],
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                colors: ['#f97316'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '70%' } },
                dataLabels: { enabled: false },
                xaxis: { categories: diasInvoices, labels: { style: { colors: '#64748b' }, rotate: -45, rotateAlways: false } },
                yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#64748b' } } },
                grid: { borderColor: '#e2e8f0' },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                tooltip: { theme: 'dark', style: { fontSize: '13px', colors: ['#ffffff'] }, y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
            };
            new ApexCharts(document.querySelector("#dailyInvoicesChart"), dailyInvoicesOptions).render();

            // Gráfico de Categorias (Barras Horizontais)
            const categorias = @json($categorias ?? []);
            const valoresCategorias = @json($valoresCategorias ?? []);

            if (categorias.length > 0 && document.querySelector("#categoriasChart")) {
                const categoriasOptions = {
                    series: [{ name: 'Total Gastos', data: valoresCategorias }],
                    chart: { type: 'bar', height: 384, toolbar: { show: false } },
                    colors: ['#8b5cf6'],
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 8,
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        },
                        offsetX: 0,
                        style: { fontSize: '12px', colors: ['#fff'] }
                    },
                    xaxis: {
                        categories: categorias,
                        labels: {
                            formatter: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            },
                            style: { colors: '#64748b' }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#64748b', fontSize: '13px' },
                            maxWidth: 200
                        }
                    },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    tooltip: {
                        theme: 'dark',
                        style: { fontSize: '13px', colors: ['#ffffff'] },
                        y: {
                            formatter: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#categoriasChart"), categoriasOptions).render();
            }
        });
    </script>
</div>
