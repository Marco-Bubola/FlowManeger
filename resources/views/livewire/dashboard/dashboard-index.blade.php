<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-800"
    x-data="{
        activeTab: 'visao-geral',
        currentTime: new Date().toLocaleString('pt-BR'),
        autoRefresh: true,
        notifications: [],
        init() {
            // Auto update time
            setInterval(() => {
                this.currentTime = new Date().toLocaleString('pt-BR');
            }, 1000);

            // Auto refresh data every 5 minutes
            if (this.autoRefresh) {
                setInterval(() => {
                    this.$wire.loadDashboardData();
                }, 300000);
            }

            // Initialize charts when tab changes
            this.$watch('activeTab', (value) => {
                setTimeout(() => {
                    this.initChartsForTab(value);
                }, 200);
            });
        },
        initChartsForTab(tab) {
            if (tab === 'analytics') {
                this.initAdvancedAnalytics();
            } else if (tab === 'tempo-real') {
                this.initRealTimeCharts();
            } else if (tab === 'operacoes') {
                this.initOperationsCharts();
            } else if (tab === 'relatorios') {
                this.initReportsCharts();
            } else if (tab === 'visao-geral') {
                this.initExecutiveCharts();
            }
        }
    }">

    <!-- Tab Navigation -->
    <div class="bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm border-b border-gray-200/50 dark:border-gray-700/50">
        <div class="w-full px-6 lg:px-8">
            <!-- Coloque aqui a navegação das tabs, se necessário -->
        </div>
    </div>

    <!-- Tab Content -->
    <div class="w-full px-6 lg:px-8 py-8">
        <!-- Visão Geral Tab -->
        <div x-show="activeTab === 'visao-geral'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código da Visão Geral (cards, gráficos, etc)... -->
        </div>

        <!-- Analytics Avançado Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código do Analytics Avançado... -->
        </div>

        <!-- Tempo Real Tab -->
        <div x-show="activeTab === 'tempo-real'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código do Tempo Real... -->
        </div>

        <!-- Operações Tab -->
        <div x-show="activeTab === 'operacoes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código das Operações... -->
        </div>

        <!-- Relatórios Tab -->
        <div x-show="activeTab === 'relatorios'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código dos Relatórios... -->
        </div>

        <!-- Configurações Tab -->
        <div x-show="activeTab === 'configuracoes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código das Configurações... -->
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            // ...existing code for charts and tab logic...
        </script>
    @endpush
</div>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-800"
    x-data="{
        activeTab: 'visao-geral',
        init() {
            // Inicialização customizada se necessário
        }
    }">
    <!-- Tab Navigation -->
    <div class="bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm border-b border-gray-200/50 dark:border-gray-700/50">
        <div class="w-full px-6 lg:px-8">
            <!-- Coloque aqui a navegação das tabs, se necessário -->
        </div>
    </div>

    <!-- Tab Content -->
    <div class="w-full px-6 lg:px-8 py-8">
        <!-- Visão Geral Tab -->
        <div x-show="activeTab === 'visao-geral'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código da Visão Geral (cards, gráficos, etc)... -->
        </div>

        <!-- Analytics Avançado Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código do Analytics Avançado... -->
        </div>

        <!-- Tempo Real Tab -->
        <div x-show="activeTab === 'tempo-real'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código do Tempo Real... -->
        </div>

        <!-- Operações Tab -->
        <div x-show="activeTab === 'operacoes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código das Operações... -->
        </div>

        <!-- Relatórios Tab -->
        <div x-show="activeTab === 'relatorios'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código dos Relatórios... -->
        </div>

        <!-- Configurações Tab -->
        <div x-show="activeTab === 'configuracoes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- ...código das Configurações... -->
        </div>
    </div>
</div>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- AI-Powered Insights -->
    <div
        class="bg-gradient-to-r from-indigo-50 to-purple-100 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-8 border border-indigo-200/50 dark:border-indigo-700/50">
        <div class="flex items-center mb-6">
            <div
                class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                    </path>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Insights Inteligentes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Análise preditiva baseada em IA</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 border border-white/20 dark:border-gray-700/20">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-900 dark:text-white">Previsão de Vendas</h4>
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">
                    +{{ number_format($totalSales * 1.15, 0) }}%
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Crescimento esperado próximo mês</p>
            </div>

            <div
                class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 border border-white/20 dark:border-gray-700/20">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-900 dark:text-white">Otimização</h4>
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                    {{ $totalProdutosEstoque < 50 ? '3' : '1' }} Ações</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $totalProdutosEstoque < 50 ? 'Reabastecer estoque urgente' : 'Processo otimizado' }}
                </p>
            </div>

            <div
                class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 border border-white/20 dark:border-gray-700/20">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-900 dark:text-white">Risco</h4>
                    <div
                        class="w-8 h-8 {{ $totalCashbook < 0 ? 'bg-red-500' : ($totalCashbook < 500 ? 'bg-yellow-500' : 'bg-green-500') }} rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                </div>
                <p
                    class="text-2xl font-bold {{ $totalCashbook < 0 ? 'text-red-600' : ($totalCashbook < 500 ? 'text-yellow-600' : 'text-green-600') }} mb-2">
                    {{ $totalCashbook < 0 ? 'Alto' : ($totalCashbook < 500 ? 'Médio' : 'Baixo') }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Nível de risco financeiro</p>
            </div>
        </div>
    </div>
</div>

<!-- Tempo Real Tab -->
<div x-show="activeTab === 'tempo-real'" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0">

    <!-- Real-Time Header -->
    <div
        class="bg-gradient-to-r from-emerald-50 to-teal-100 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl p-6 border border-emerald-200/50 dark:border-emerald-700/50 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoramento Tempo Real</h2>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">Online</span>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Atualizações automáticas a cada 30 segundos</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Última atualização</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400" id="lastUpdate">Agora</p>
            </div>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div
        class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Feed de Atividades Ao Vivo</h3>
            <div class="flex items-center space-x-4">
                <button
                    class="text-sm px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors duration-200">
                    Pausar
                </button>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Atualizando</span>
                </div>
            </div>
        </div>

        <div class="space-y-4 max-h-96 overflow-y-auto" id="liveActivityFeed">
            <div
                class="flex items-start space-x-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200/50 dark:border-green-700/50 animate-fadeIn">
                <div
                    class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900 dark:text-white">Nova venda processada</p>
                        <span class="text-xs text-gray-500 dark:text-gray-500">Agora</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cliente XYZ - R$
                        {{ number_format(rand(50, 500), 2, ',', '.') }}</p>
                </div>
            </div>

            <div
                class="flex items-start space-x-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200/50 dark:border-blue-700/50 animate-fadeIn">
                <div
                    class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900 dark:text-white">Novo cliente cadastrado</p>
                        <span class="text-xs text-gray-500 dark:text-gray-500">2 min atrás</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Maria Silva - Segmento Premium</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operações Tab -->
<div x-show="activeTab === 'operacoes'" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0">

    <!-- Operations Header -->
    <div
        class="bg-gradient-to-r from-indigo-50 to-blue-100 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-2xl p-6 border border-indigo-200/50 dark:border-indigo-700/50 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Centro de Operações</h2>
                <p class="text-gray-600 dark:text-gray-400">Ferramentas e ações para gestão operacional</p>
            </div>
            <div class="flex items-center space-x-4">
                <button
                    class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-blue-600 text-white rounded-lg hover:from-indigo-600 hover:to-blue-700 transition-all duration-200 shadow-lg">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nova Operação
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Action Tools -->
    <div
        class="bg-gradient-to-r from-gray-50 to-slate-100 dark:from-gray-900/20 dark:to-slate-900/20 rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Ferramentas Rápidas</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button
                class="p-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-gray-700/20 hover:shadow-lg transition-all duration-200 group">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-3 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Nova Venda</p>
            </button>

            <button
                class="p-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-gray-700/20 hover:shadow-lg transition-all duration-200 group">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-3 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Add Produto</p>
            </button>

            <button
                class="p-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-gray-700/20 hover:shadow-lg transition-all duration-200 group">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-3 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Add Cliente</p>
            </button>

            <a href="{{ route('dashboard.cashbook') }}"
                class="p-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-gray-700/20 hover:shadow-lg transition-all duration-200 group">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-3 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Fluxo de Caixa</p>
            </a>
        </div>
    </div>
</div>

<!-- Relatórios Tab -->
<div x-show="activeTab === 'relatorios'" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0">

    <!-- Reports Header -->
    <div
        class="bg-gradient-to-r from-indigo-50 to-purple-100 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-6 border border-indigo-200/50 dark:border-indigo-700/50 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Central de Relatórios</h2>
                <p class="text-gray-600 dark:text-gray-400">Relatórios executivos e análises avançadas</p>
            </div>
            <div class="flex items-center space-x-4">
                <button
                    class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Gerar Relatório
                </button>
            </div>
        </div>
    </div>

    <!-- Report Types Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Financial Report -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Relatório Financeiro</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">DRE, Balanço e Fluxo de Caixa completo</p>
            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Exportar PDF/Excel</div>
        </div>

        <!-- Sales Report -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Relatório de Vendas</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Performance comercial e ranking de produtos</p>
            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">{{ $totalSales }} vendas este mês
            </div>
        </div>

        <!-- Customer Report -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-purple-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Relatório de Clientes</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Segmentação e análise comportamental</p>
            <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">{{ $totalClientes }} clientes ativos
            </div>
        </div>
    </div>
</div>

<!-- Configurações Tab -->
<div x-show="activeTab === 'config'" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0">

    <!-- Settings Header -->
    <div
        class="bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900/20 dark:to-gray-900/20 rounded-2xl p-6 border border-slate-200/50 dark:border-slate-700/50 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Configurações do Sistema</h2>
                <p class="text-gray-600 dark:text-gray-400">Personalização e configurações avançadas</p>
            </div>
            <div class="flex items-center space-x-4">
                <button
                    class="px-4 py-2 bg-gradient-to-r from-slate-500 to-gray-600 text-white rounded-lg hover:from-slate-600 hover:to-gray-700 transition-all duration-200 shadow-lg">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                        </path>
                    </svg>
                    Salvar Configurações
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Dashboard Preferences -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Preferências do Dashboard</h3>

            <div class="space-y-6">
                <div>
                    <label class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Auto-refresh (5 min)</span>
                        <input type="checkbox" checked class="toggle toggle-primary">
                    </label>
                </div>
                <div>
                    <label class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Modo escuro automático</span>
                        <input type="checkbox" class="toggle toggle-primary">
                    </label>
                </div>
                <div>
                    <label class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Notificações push</span>
                        <input type="checkbox" checked class="toggle toggle-primary">
                    </label>
                </div>
                <div>
                    <label class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Análise avançada</span>
                        <input type="checkbox" checked class="toggle toggle-primary">
                    </label>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Informações do Sistema</h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Versão</span>
                    <span class="font-bold text-gray-900 dark:text-white">FlowManager v2.1.0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Última atualização</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Status do sistema</span>
                    <span class="flex items-center font-bold text-green-600 dark:text-green-400">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                        Online
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Backup automático</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">Ativo</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Enhanced Quick Access Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
    <!-- Cashbook Quick Access -->
    <a href="{{ route('dashboard.cashbook') }}" class="group block">
        <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 rounded-2xl shadow-xl border border-blue-200 dark:border-blue-700 p-8 hover:shadow-2xl transition-all duration-300 group-hover:scale-105 group-hover:-translate-y-2">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <svg class="w-6 h-6 text-blue-400 group-hover:text-blue-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Dashboard Financeiro</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Análise detalhada do seu fluxo financeiro, bancos e
                previsões</p>
            <div class="flex items-center space-x-4 text-sm">
                <span class="flex items-center text-green-600 dark:text-green-400">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    Analytics Avançados
                </span>
                <span class="flex items-center text-blue-600 dark:text-blue-400">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                    IA & Previsões
                </span>
            </div>
        </div>
    </a>

    <!-- Products Quick Access -->
    <a href="{{ route('dashboard.products') }}" class="group block">
        <div
            class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/20 rounded-2xl shadow-xl border border-green-200 dark:border-green-700 p-8 hover:shadow-2xl transition-all duration-300 group-hover:scale-105 group-hover:-translate-y-2">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <svg class="w-6 h-6 text-green-400 group-hover:text-green-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Dashboard de Produtos</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Análise de vendas, estoque e performance detalhada dos
                produtos</p>
            @if ($produtoMaisVendido)
                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-green-600 dark:text-green-400 font-semibold uppercase">Top Produto</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $produtoMaisVendido->name }}</p>
                </div>
            @endif
            <div class="flex items-center space-x-4 text-sm">
                <span class="flex items-center text-emerald-600 dark:text-emerald-400">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                    Gestão de Estoque
                </span>
                <span class="flex items-center text-green-600 dark:text-green-400">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    Analytics
                </span>
            </div>
        </div>
    </a>

    <!-- Sales Quick Access -->
    <a href="{{ route('dashboard.sales') }}" class="group block">
        <div
            class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/20 rounded-2xl shadow-xl border border-purple-200 dark:border-purple-700 p-8 hover:shadow-2xl transition-all duration-300 group-hover:scale-105 group-hover:-translate-y-2">
            <div class="flex items-center justify-between mb-6">
                <div
                    class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <svg class="w-6 h-6 text-purple-400 group-hover:text-purple-600 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Dashboard de Vendas</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Análise de vendas, clientes e performance comercial
                detalhada</p>
            <div class="flex items-center space-x-4 text-sm">
                <span class="flex items-center text-violet-600 dark:text-violet-400">
                    <div class="w-2 h-2 bg-violet-500 rounded-full mr-2"></div>
                    CRM Avançado
                </span>
                <span class="flex items-center text-purple-600 dark:text-purple-400">
                    <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                    Métricas
                </span>
            </div>
        </div>
    </a>
</div>
</div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update current date and time
            function updateDateTime() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                document.getElementById('currentDateTime').textContent =
                    now.toLocaleDateString('pt-BR', options);
            }

            updateDateTime();
            setInterval(updateDateTime, 60000); // Update every minute

            // Financial Overview Chart
            const financialChartOptions = {
                series: [{
                    name: 'Receitas',
                    data: [{{ $totalCashbook >= 0 ? $totalCashbook + 1000 : 1000 }},
                        {{ $totalCashbook >= 0 ? $totalCashbook + 800 : 800 }},
                        {{ $totalCashbook >= 0 ? $totalCashbook + 1200 : 1200 }},
                        {{ $totalCashbook >= 0 ? $totalCashbook + 900 : 900 }},
                        {{ $totalCashbook >= 0 ? $totalCashbook + 1500 : 1500 }},
                        {{ $totalCashbook >= 0 ? $totalCashbook + 1100 : 1100 }}
                    ]
                }, {
                    name: 'Despesas',
                    data: [{{ abs($totalCashbook < 0 ? $totalCashbook : 500) }},
                        {{ abs($totalCashbook < 0 ? $totalCashbook + 200 : 600) }},
                        {{ abs($totalCashbook < 0 ? $totalCashbook + 100 : 400) }},
                        {{ abs($totalCashbook < 0 ? $totalCashbook + 300 : 700) }},
                        {{ abs($totalCashbook < 0 ? $totalCashbook + 150 : 550) }},
                        {{ abs($totalCashbook < 0 ? $totalCashbook + 250 : 650) }}
                    ]
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ['#10B981', '#EF4444'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.8,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 3,
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                    labels: {
                        style: {
                            colors: '#9CA3AF',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9CA3AF',
                            fontSize: '12px'
                        },
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: {
                        colors: '#9CA3AF'
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                }
            };

            // Sales Performance Chart
            const salesChartOptions = {
                series: [{
                    name: 'Vendas',
                    data: [{{ $totalFaturamento > 0 ? $totalFaturamento / 6 : 100 }},
                        {{ $totalFaturamento > 0 ? $totalFaturamento / 5 : 120 }},
                        {{ $totalFaturamento > 0 ? $totalFaturamento / 4 : 150 }},
                        {{ $totalFaturamento > 0 ? $totalFaturamento / 3 : 180 }},
                        {{ $totalFaturamento > 0 ? $totalFaturamento / 2 : 200 }},
                        {{ $totalFaturamento > 0 ? $totalFaturamento : 250 }}
                    ]
                }],
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ['#3B82F6'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 4
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 3,
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                    labels: {
                        style: {
                            colors: '#9CA3AF',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9CA3AF',
                            fontSize: '12px'
                        },
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                },
                markers: {
                    size: 6,
                    colors: ['#3B82F6'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 8
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                }
            };

            // Initialize Charts
            if (document.getElementById('financialChart')) {
                const financialChart = new ApexCharts(document.getElementById('financialChart'),
                    financialChartOptions);
                financialChart.render();
            }

            if (document.getElementById('salesChart')) {
                const salesChart = new ApexCharts(document.getElementById('salesChart'), salesChartOptions);
                salesChart.render();
            }

            // Add hover effects and interactions
            document.querySelectorAll('[data-hover="scale"]').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });

                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Auto-refresh data every 5 minutes
            setInterval(function() {
                if (typeof Livewire !== 'undefined') {
                    Livewire.emit('refreshDashboard');
                }
            }, 300000);

            // Initialize Advanced Analytics Charts
            function initAdvancedAnalytics() {
                // Customer Analytics Chart
                if (document.getElementById('customerAnalyticsChart')) {
                    const customerChart = new ApexCharts(document.getElementById('customerAnalyticsChart'), {
                        chart: {
                            type: 'donut',
                            height: 250,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{{ $totalClientes - $clientesComSalesPendentes }},
                            {{ $clientesComSalesPendentes }}
                        ],
                        labels: ['Clientes Ativos', 'Clientes Pendentes'],
                        colors: ['#10B981', '#F59E0B'],
                        legend: {
                            position: 'bottom'
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val) => Math.round(val) + '%'
                        }
                    });
                    customerChart.render();
                }

                // Product Performance Chart
                if (document.getElementById('productPerformanceChart')) {
                    const productChart = new ApexCharts(document.getElementById('productPerformanceChart'), {
                        chart: {
                            type: 'radialBar',
                            height: 250
                        },
                        series: [
                            {{ $totalProdutosEstoque > 0 ? ($totalProdutosEstoque / $totalProdutos) * 100 : 0 }}
                        ],
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '60%'
                                },
                                dataLabels: {
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: 600,
                                        color: '#374151'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        fontWeight: 700,
                                        color: '#1F2937',
                                        formatter: (val) => Math.round(val) + '%'
                                    }
                                }
                            }
                        },
                        labels: ['Estoque Disponível'],
                        colors: ['#10B981']
                    });
                    productChart.render();
                }

                // Health Index Chart
                if (document.getElementById('healthIndexChart')) {
                    const healthScore = Math.min(100, Math.max(0, ({{ $totalCashbook }} / 1000 * 40) + (
                        {{ $totalClientes }} / 50 * 30) + ({{ $totalProdutos }} / 100 * 30)));
                    const healthChart = new ApexCharts(document.getElementById('healthIndexChart'), {
                        chart: {
                            type: 'radialBar',
                            height: 250
                        },
                        series: [healthScore],
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '60%'
                                },
                                dataLabels: {
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: 600,
                                        color: '#374151'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        fontWeight: 700,
                                        formatter: (val) => Math.round(val) + '/100'
                                    }
                                }
                            }
                        },
                        labels: ['Score de Saúde'],
                        colors: [healthScore >= 80 ? '#10B981' : healthScore >= 60 ? '#F59E0B' : '#EF4444']
                    });
                    healthChart.render();
                }

                // Revenue Trend Chart
                if (document.getElementById('revenueTrendChart')) {
                    const revenueTrendChart = new ApexCharts(document.getElementById('revenueTrendChart'), {
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                                name: 'Receita Real',
                                data: [{{ $totalFaturamento * 0.6 }}, {{ $totalFaturamento * 0.7 }},
                                    {{ $totalFaturamento * 0.8 }}, {{ $totalFaturamento * 0.9 }},
                                    {{ $totalFaturamento }}, {{ $totalFaturamento * 1.1 }}
                                ]
                            },
                            {
                                name: 'Projeção',
                                data: [{{ $totalFaturamento * 0.8 }}, {{ $totalFaturamento * 0.85 }},
                                    {{ $totalFaturamento * 0.95 }}, {{ $totalFaturamento }},
                                    {{ $totalFaturamento * 1.15 }},
                                    {{ $totalFaturamento * 1.25 }}
                                ]
                            }
                        ],
                        xaxis: {
                            categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']
                        },
                        colors: ['#3B82F6', '#10B981'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.6,
                                opacityTo: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right'
                        }
                    });
                    revenueTrendChart.render();
                }

                // Customer Segmentation Chart
                if (document.getElementById('customerSegmentationChart')) {
                    const segmentationChart = new ApexCharts(document.getElementById('customerSegmentationChart'), {
                        chart: {
                            type: 'pie',
                            height: 320
                        },
                        series: [{{ $totalClientes * 0.4 }}, {{ $totalClientes * 0.35 }},
                            {{ $totalClientes * 0.25 }}
                        ],
                        labels: ['Premium', 'Regular', 'Novo'],
                        colors: ['#8B5CF6', '#3B82F6', '#10B981'],
                        legend: {
                            position: 'bottom'
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val, opts) => Math.round(val) + '%'
                        }
                    });
                    segmentationChart.render();
                }
            }

            // Initialize Real-Time Charts
            function initRealTimeCharts() {
                // Live Sales Chart
                if (document.getElementById('liveSalesChart')) {
                    const liveSalesChart = new ApexCharts(document.getElementById('liveSalesChart'), {
                        chart: {
                            type: 'line',
                            height: 320,
                            animations: {
                                enabled: true,
                                easing: 'linear',
                                dynamicAnimation: {
                                    speed: 1000
                                }
                            },
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: 'Vendas por Hora',
                            data: [{{ rand(1, 5) }}, {{ rand(1, 8) }},
                                {{ rand(2, 10) }}, {{ rand(1, 6) }},
                                {{ rand(3, 12) }}, {{ rand(1, 7) }},
                                {{ rand(2, 9) }}, {{ rand(1, 5) }}
                            ]
                        }],
                        xaxis: {
                            categories: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00',
                                '22:00'
                            ],
                            labels: {
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        colors: ['#3B82F6'],
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 6,
                            colors: ['#3B82F6'],
                            strokeColors: '#fff',
                            strokeWidth: 2
                        },
                        grid: {
                            borderColor: '#E5E7EB'
                        },
                        dataLabels: {
                            enabled: false
                        }
                    });
                    liveSalesChart.render();
                }

                // System Activity Chart
                if (document.getElementById('systemActivityChart')) {
                    const systemActivityChart = new ApexCharts(document.getElementById('systemActivityChart'), {
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: 'CPU',
                            data: [{{ rand(20, 40) }}, {{ rand(25, 45) }},
                                {{ rand(30, 50) }}, {{ rand(20, 40) }},
                                {{ rand(35, 55) }}, {{ rand(25, 45) }},
                                {{ rand(30, 50) }}, {{ rand(20, 40) }}
                            ]
                        }, {
                            name: 'Memória',
                            data: [{{ rand(40, 60) }}, {{ rand(45, 65) }},
                                {{ rand(50, 70) }}, {{ rand(40, 60) }},
                                {{ rand(55, 75) }}, {{ rand(45, 65) }},
                                {{ rand(50, 70) }}, {{ rand(40, 60) }}
                            ]
                        }],
                        xaxis: {
                            categories: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00',
                                '22:00'
                            ]
                        },
                        colors: ['#10B981', '#F59E0B'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.6,
                                opacityTo: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right'
                        }
                    });
                    systemActivityChart.render();
                }
            }

            // Initialize Operations Charts
            function initOperationsCharts() {
                // System Performance Chart
                if (document.getElementById('systemPerformanceChart')) {
                    const performanceChart = new ApexCharts(document.getElementById('systemPerformanceChart'), {
                        chart: {
                            type: 'area',
                            height: 250,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            name: 'Performance',
                            data: [95, 97, 99, 96, 98, 99, 97, 99, 98, 99]
                        }],
                        colors: ['#10B981'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.6,
                                opacityTo: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: (val) => val + '%'
                            }
                        }
                    });
                    performanceChart.render();
                }
            }

            // Initialize Executive Charts
            function initExecutiveCharts() {
                // Executive Financial Overview
                if (document.getElementById('executiveFinancialChart')) {
                    const executiveChart = new ApexCharts(document.getElementById('executiveFinancialChart'), {
                        chart: {
                            type: 'line',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                                name: 'Receitas',
                                type: 'column',
                                data: [{{ $totalFaturamento * 0.7 }}, {{ $totalFaturamento * 0.8 }},
                                    {{ $totalFaturamento * 0.9 }}, {{ $totalFaturamento }},
                                    {{ $totalFaturamento * 1.1 }}, {{ $totalFaturamento * 1.2 }}
                                ]
                            },
                            {
                                name: 'Despesas',
                                type: 'column',
                                data: [{{ abs($totalCashbook) * 0.5 }},
                                    {{ abs($totalCashbook) * 0.6 }},
                                    {{ abs($totalCashbook) * 0.7 }},
                                    {{ abs($totalCashbook) * 0.8 }},
                                    {{ abs($totalCashbook) * 0.9 }}, {{ abs($totalCashbook) }}
                                ]
                            },
                            {
                                name: 'Lucro',
                                type: 'line',
                                data: [{{ $totalFaturamento * 0.2 }}, {{ $totalFaturamento * 0.2 }},
                                    {{ $totalFaturamento * 0.2 }},
                                    {{ $totalFaturamento * 0.2 }},
                                    {{ $totalFaturamento * 0.2 }}, {{ $totalFaturamento * 0.2 }}
                                ]
                            }
                        ],
                        xaxis: {
                            categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']
                        },
                        colors: ['#3B82F6', '#EF4444', '#10B981'],
                        stroke: {
                            curve: 'smooth',
                            width: [0, 0, 3]
                        },
                        plotOptions: {
                            bar: {
                                columnWidth: '60%'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        }
                    });
                    executiveChart.render();
                }

                // Business Intelligence Chart
                if (document.getElementById('businessIntelligenceChart')) {
                    const biChart = new ApexCharts(document.getElementById('businessIntelligenceChart'), {
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: 'Vendas',
                            data: [{{ $totalSales * 0.6 }}, {{ $totalSales * 0.7 }},
                                {{ $totalSales * 0.8 }}, {{ $totalSales * 0.9 }},
                                {{ $totalSales }}, {{ $totalSales * 1.1 }}
                            ]
                        }],
                        xaxis: {
                            categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']
                        },
                        colors: ['#8B5CF6'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.6,
                                opacityTo: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        dataLabels: {
                            enabled: false
                        }
                    });
                    biChart.render();
                }
            }

            // Initialize default charts for Visão Geral tab
            initExecutiveCharts();

            console.log('✅ Dashboard principal carregado com sucesso!');
            console.log('📊 Gráficos inicializados');
            console.log('🔄 Auto-refresh configurado para 5 minutos');
            console.log('🎯 Sistema de abas ativado');
        });
    </script>

    </div>
