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
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-1">
                            <i class="fas fa-home mr-1"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Dashboard</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                            Flow Manager
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                            <span class="flex items-center">
                                <i class="fas fa-briefcase mr-1"></i>Central de Controle do Seu Negócio
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="inline-flex items-center space-x-2 px-3 py-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 shadow-sm">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-green-700 dark:text-green-400">Sistema Online</span>
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
    <!-- Tabs Navigation Moderno -->
    <div class="px-4 sm:px-6 lg:px-8 pt-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-2 mb-6">
            <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                <button onclick="showTab('overview')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-chart-bar text-blue-500"></i>
                    <span>Visão Geral</span>
                </button>
                <button onclick="showTab('financeiro')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-wallet text-green-500"></i>
                    <span>Financeiro</span>
                </button>
                <button onclick="showTab('produtos')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-box text-indigo-500"></i>
                    <span>Produtos</span>
                </button>
                <button onclick="showTab('vendas')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-shopping-cart text-purple-500"></i>
                    <span>Vendas</span>
                </button>
                <button onclick="showTab('clientes')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-users text-pink-500"></i>
                    <span>Clientes</span>
                </button>
                <button onclick="showTab('relatorios')"
                    class="tab-link flex-1 min-w-[120px] py-3 px-4 rounded-lg font-medium text-sm focus:outline-none flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fas fa-file-chart-line text-yellow-500"></i>
                    <span>Relatórios</span>
                </button>
            </nav>
        </div>
    </div>
    <!-- KPIs Principais (compactos, linha única) -->
    <div class=" px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Saldo em Caixa -->
            <div
                class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-14 h-14 bg-green-400/10 rounded-full transform translate-x-5 -translate-y-5">
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-wallet text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-green-800 dark:text-green-300 truncate font-medium">Saldo em
                                    Caixa</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-base font-bold text-green-700 dark:text-green-400 whitespace-nowrap">R$
                                {{ number_format($saldoCaixa, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contas a Pagar -->
            <div
                class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-red-200 dark:border-red-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-14 h-14 bg-red-400/10 rounded-full transform translate-x-5 -translate-y-5">
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-red-800 dark:text-red-300 truncate font-medium">Contas a Pagar
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-base font-bold text-red-700 dark:text-red-400 whitespace-nowrap">R$
                                {{ number_format($contasPagar, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contas a Receber -->
            <div
                class="group relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-14 h-14 bg-blue-400/10 rounded-full transform translate-x-5 -translate-y-5">
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-hand-holding-usd text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-blue-800 dark:text-blue-300 truncate font-medium">Contas a
                                    Receber</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-base font-bold text-blue-700 dark:text-blue-400 whitespace-nowrap">R$
                                {{ number_format($contasReceber, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturamento Total -->
            <div
                class="group relative bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-14 h-14 bg-purple-400/10 rounded-full transform translate-x-5 -translate-y-5">
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-chart-line text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-purple-800 dark:text-purple-300 truncate font-medium">Faturamento
                                    Total</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-base font-bold text-purple-700 dark:text-purple-400 whitespace-nowrap">R$
                                {{ number_format($totalFaturamento, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Ações Rápidas (modernizados) -->
    <div class=" px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total de Clientes -->
            <a href="/clients"
                class="group relative bg-gradient-to-br from-pink-50 to-rose-100 dark:from-pink-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-pink-200 dark:border-pink-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-pink-400/10 rounded-full transform translate-x-6 -translate-y-6">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-pink-800 dark:text-pink-300 font-medium truncate">Total de Clientes
                            </p>
                            <p class="text-2xl font-bold text-pink-700 dark:text-pink-400">
                                {{ number_format($totalClientes) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span
                            class="text-pink-600 dark:text-pink-400 bg-pink-100 dark:bg-pink-900/30 px-2 py-1 rounded-full font-semibold">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $clientesComSalesPendentes }} com
                            pendências
                        </span>
                        <i
                            class="fas fa-arrow-right text-pink-600 dark:text-pink-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Produtos Cadastrados -->
            <a href="/products"
                class="group relative bg-gradient-to-br from-indigo-50 to-blue-100 dark:from-indigo-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-indigo-200 dark:border-indigo-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-indigo-400/10 rounded-full transform translate-x-6 -translate-y-6">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                            <i class="fas fa-box text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-indigo-800 dark:text-indigo-300 font-medium truncate">Produtos
                                Cadastrados</p>
                            <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">
                                {{ number_format($produtosCadastrados) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span
                            class="text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 px-2 py-1 rounded-full font-semibold">
                            <i class="fas fa-exclamation-triangle mr-1"></i>{{ $produtosEstoqueBaixo }} estoque baixo
                        </span>
                        <i
                            class="fas fa-arrow-right text-indigo-600 dark:text-indigo-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Vendas no Mês -->
            <a href="/sales"
                class="group relative bg-gradient-to-br from-purple-50 to-violet-100 dark:from-purple-900/20 dark:to-violet-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-purple-400/10 rounded-full transform translate-x-6 -translate-y-6">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                            <i class="fas fa-shopping-cart text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium truncate">Vendas no Mês
                            </p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">
                                {{ number_format($salesMonth) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span
                            class="text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30 px-2 py-1 rounded-full font-semibold">
                            <i class="fas fa-ticket-alt mr-1"></i>Ticket: R$
                            {{ number_format($ticketMedio, 2, ',', '.') }}
                        </span>
                        <i
                            class="fas fa-arrow-right text-purple-600 dark:text-purple-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Novos Clientes no Mês -->
            <div
                class="group relative bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-emerald-900/20 dark:to-teal-900/30 rounded-xl shadow-lg border border-emerald-200 dark:border-emerald-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-emerald-400/10 rounded-full transform translate-x-6 -translate-y-6">
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                            <i class="fas fa-user-plus text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-emerald-800 dark:text-emerald-300 font-medium truncate">Novos
                                Clientes</p>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                                {{ number_format($clientesNovosMes) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span
                            class="text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-2 py-1 rounded-full font-semibold">
                            <i class="fas fa-calendar-alt mr-1"></i>Este mês
                        </span>
                        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo das abas (modernizado) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="tab-overview" class="tab-content">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Resumo Geral
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-600 dark:text-red-400 mb-1 font-medium">Clientes Inadimplentes</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $clientesInadimplentes }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-1 font-medium">Aniversariantes do Mês
                        </p>
                        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $aniversariantesMes }}
                        </p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1 font-medium">Produtos Vendidos no
                            Mês</p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">{{ $produtosVendidosMes }}
                        </p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1 font-medium">Produtos Cadastrados</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $produtosCadastrados }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-financeiro" class="tab-content hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-wallet text-green-500 mr-2"></i>
                    Financeiro
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1 font-medium">Saldo em Caixa</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">R$
                            {{ number_format($saldoCaixa, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-600 dark:text-red-400 mb-1 font-medium">Contas a Pagar</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-400">R$
                            {{ number_format($contasPagar, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1 font-medium">Contas a Receber</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">R$
                            {{ number_format($contasReceber, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-700 dark:to-gray-700 rounded-lg border border-slate-200 dark:border-slate-600">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-1 font-medium">Fornecedores a Pagar</p>
                        <p class="text-2xl font-bold text-slate-700 dark:text-slate-300">R$
                            {{ number_format($fornecedoresPagar, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                        <p class="text-xs text-orange-600 dark:text-orange-400 mb-1 font-medium">Despesas Fixas</p>
                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-400">R$
                            {{ number_format($despesasFixas, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 rounded-lg border border-cyan-200 dark:border-cyan-800">
                        <p class="text-xs text-cyan-600 dark:text-cyan-400 mb-1 font-medium">Clientes a Receber</p>
                        <p class="text-2xl font-bold text-cyan-700 dark:text-cyan-400">R$
                            {{ number_format($clientesReceber, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-produtos" class="tab-content hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-box text-indigo-500 mr-2"></i>
                    Produtos
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1 font-medium">Produtos Cadastrados
                        </p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">{{ $produtosCadastrados }}
                        </p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-1 font-medium">Estoque Baixo</p>
                        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $produtosEstoqueBaixo }}
                        </p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1 font-medium">Vendidos no Mês</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $produtosVendidosMes }}
                        </p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1 font-medium">Mais Vendido</p>
                        <p class="text-lg font-bold text-purple-700 dark:text-purple-400 truncate">
                            {{ $produtoMaisVendido ? $produtoMaisVendido->name : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-vendas" class="tab-content hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-shopping-cart text-purple-500 mr-2"></i>
                    Vendas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1 font-medium">Vendas no Mês</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ $salesMonth }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1 font-medium">Ticket Médio</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">R$
                            {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1 font-medium">Última Venda</p>
                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">
                            {{ $ultimaVenda ? $ultimaVenda->created_at->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1 font-medium">Valor Última Venda</p>
                        <p class="text-lg font-bold text-green-700 dark:text-green-400">
                            {{ $ultimaVenda ? 'R$ ' . number_format($ultimaVenda->total_price, 2, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-clientes" class="tab-content hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-users text-pink-500 mr-2"></i>
                    Clientes
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-lg border border-pink-200 dark:border-pink-800">
                        <p class="text-xs text-pink-600 dark:text-pink-400 mb-1 font-medium">Total de Clientes</p>
                        <p class="text-2xl font-bold text-pink-700 dark:text-pink-400">{{ $totalClientes }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1 font-medium">Novos no Mês</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $clientesNovosMes }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                        <p class="text-xs text-orange-600 dark:text-orange-400 mb-1 font-medium">Com Pendências</p>
                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-400">
                            {{ $clientesComSalesPendentes }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-600 dark:text-red-400 mb-1 font-medium">Inadimplentes</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $clientesInadimplentes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-relatorios" class="tab-content hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-file-chart-line text-yellow-500 mr-2"></i>
                    Relatórios
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1 font-medium">Faturamento Total</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">R$
                            {{ number_format($totalFaturamento, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-600 dark:text-red-400 mb-1 font-medium">Contas a Pagar</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-400">R$
                            {{ number_format($contasPagar, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1 font-medium">Contas a Receber</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">R$
                            {{ number_format($contasReceber, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1 font-medium">Ticket Médio</p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">R$
                            {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            // Esconde todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.add('hidden');
            });

            // Mostra o conteúdo selecionado
            var active = document.getElementById('tab-' + tab);
            if (active) active.classList.remove('hidden');

            // Remove estilo ativo de todos os botões
            document.querySelectorAll('.tab-link').forEach(function(el) {
                el.classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white',
                    'shadow-md');
                el.classList.add('text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-400',
                    'dark:hover:bg-slate-700');
            });

            // Adiciona estilo ativo ao botão clicado
            if (event && event.target) {
                var btn = event.target.closest('.tab-link');
                if (btn) {
                    btn.classList.remove('text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-400',
                        'dark:hover:bg-slate-700');
                    btn.classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-md');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            showTab('overview');
        });
    </script>
    // ...existing code (todo o conteúdo real do dashboard, começando pelo root <div class="min-h-screen ..."> e sem
        duplicidade)...
