<div class="w-full">
    <!-- Tabs Navigation Moderno -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('overview')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3m-4 4h4m-2 0v4m0-4V5" />
                    </svg>
                    Visão Geral
                </button>
                <button onclick="showTab('financeiro')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
                    </svg>
                    Financeiro
                </button>
                <button onclick="showTab('produtos')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Produtos
                </button>
                <button onclick="showTab('vendas')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Vendas
                </button>
                <button onclick="showTab('clientes')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 10-8 0 4 4 0 008 0z" />
                    </svg>
                    Clientes
                </button>
                <button onclick="showTab('relatorios')"
                    class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                    <svg class="w-5 h-5 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                    Relatórios
                </button>
            </nav>
        </div>
    </div>
    <!-- Enhanced Header -->
    <div class="bg-white dark:bg-gray-800 shadow-lg border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-6">
                    <div
                        class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 via-purple-600 to-indigo-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Flow Manager
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Central de Controle do Seu Negócio</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500" id="currentDateTime"></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 px-3 py-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-green-700 dark:text-green-400">Sistema Online</span>
                    </div>
                    <a href="{{ route('dashboard.cashbook') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
                        </svg>
                        Fluxo de Caixa
                    </a>
                    <a href="{{ route('dashboard.products') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Produtos
                    </a>
                    <a href="{{ route('dashboard.sales') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Vendas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs e Cards principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 my-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-start">
            <span class="text-gray-500 text-xs mb-1">Saldo em Caixa</span>
            <span class="text-2xl font-bold text-green-600">R$ {{ number_format($saldoCaixa, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-start">
            <span class="text-gray-500 text-xs mb-1">Contas a Pagar</span>
            <span class="text-2xl font-bold text-red-600">R$ {{ number_format($contasPagar, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-start">
            <span class="text-gray-500 text-xs mb-1">Contas a Receber</span>
            <span class="text-2xl font-bold text-blue-600">R$ {{ number_format($contasReceber, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col items-start">
            <span class="text-gray-500 text-xs mb-1">Faturamento Total</span>
            <span class="text-2xl font-bold text-purple-600">R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</span>
        </div>
    </div>

    <!-- Cards de ações rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="/clients" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col hover:shadow-xl transition">
            <span class="text-gray-500 text-xs mb-1">Total de Clientes</span>
            <span class="text-2xl font-bold text-pink-600">{{ number_format($totalClientes) }}</span>
            <span class="text-xs text-gray-400 mt-1">{{ $clientesComSalesPendentes }} com pendências</span>
        </a>
        <a href="/products" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col hover:shadow-xl transition">
            <span class="text-gray-500 text-xs mb-1">Produtos Cadastrados</span>
            <span class="text-2xl font-bold text-indigo-600">{{ number_format($produtosCadastrados) }}</span>
            <span class="text-xs text-gray-400 mt-1">{{ $produtosEstoqueBaixo }} com estoque baixo</span>
        </a>
        <a href="/sales" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col hover:shadow-xl transition">
            <span class="text-gray-500 text-xs mb-1">Vendas no Mês</span>
            <span class="text-2xl font-bold text-purple-600">{{ number_format($salesMonth) }}</span>
            <span class="text-xs text-gray-400 mt-1">Ticket Médio: R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span>
        </a>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex flex-col">
            <span class="text-gray-500 text-xs mb-1">Novos Clientes no Mês</span>
            <span class="text-2xl font-bold text-green-600">{{ number_format($clientesNovosMes) }}</span>
        </div>
    </div>

    <!-- Conteúdo das abas -->
    <div id="tab-overview" class="tab-content">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Resumo Geral</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Clientes inadimplentes: <span class="font-bold text-red-600">{{ $clientesInadimplentes }}</span></p>
                    <p class="text-gray-500">Aniversariantes do mês: <span class="font-bold text-yellow-600">{{ $aniversariantesMes }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Produtos vendidos no mês: <span class="font-bold text-indigo-600">{{ $produtosVendidosMes }}</span></p>
                    <p class="text-gray-500">Produtos cadastrados: <span class="font-bold text-green-600">{{ $produtosCadastrados }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-financeiro" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Financeiro</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Saldo em Caixa: <span class="font-bold text-green-600">R$ {{ number_format($saldoCaixa, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Contas a Pagar: <span class="font-bold text-red-600">R$ {{ number_format($contasPagar, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Contas a Receber: <span class="font-bold text-blue-600">R$ {{ number_format($contasReceber, 2, ',', '.') }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Fornecedores a Pagar: <span class="font-bold">R$ {{ number_format($fornecedoresPagar, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Despesas Fixas: <span class="font-bold">R$ {{ number_format($despesasFixas, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Clientes a Receber: <span class="font-bold">R$ {{ number_format($clientesReceber, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Outros a Receber: <span class="font-bold">R$ {{ number_format($outrosReceber, 2, ',', '.') }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-produtos" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Produtos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Produtos Cadastrados: <span class="font-bold text-indigo-600">{{ $produtosCadastrados }}</span></p>
                    <p class="text-gray-500">Produtos com Estoque Baixo: <span class="font-bold text-yellow-600">{{ $produtosEstoqueBaixo }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Produtos Vendidos no Mês: <span class="font-bold text-green-600">{{ $produtosVendidosMes }}</span></p>
                    <p class="text-gray-500">Mais Vendido: <span class="font-bold">{{ $produtoMaisVendido ? $produtoMaisVendido->name : '-' }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-vendas" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Vendas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Vendas no Mês: <span class="font-bold text-purple-600">{{ $salesMonth }}</span></p>
                    <p class="text-gray-500">Ticket Médio: <span class="font-bold">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Última Venda: <span class="font-bold">{{ $ultimaVenda ? $ultimaVenda->created_at->format('d/m/Y H:i') : '-' }}</span></p>
                    <p class="text-gray-500">Valor: <span class="font-bold">{{ $ultimaVenda ? 'R$ ' . number_format($ultimaVenda->total_price, 2, ',', '.') : '-' }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-clientes" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Clientes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Total de Clientes: <span class="font-bold text-pink-600">{{ $totalClientes }}</span></p>
                    <p class="text-gray-500">Novos Clientes no Mês: <span class="font-bold text-green-600">{{ $clientesNovosMes }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Clientes com Pendências: <span class="font-bold text-red-600">{{ $clientesComSalesPendentes }}</span></p>
                    <p class="text-gray-500">Clientes Inadimplentes: <span class="font-bold text-red-600">{{ $clientesInadimplentes }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-relatorios" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold mb-2">Relatórios</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500">Faturamento Total: <span class="font-bold text-purple-600">R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Contas a Pagar: <span class="font-bold text-red-600">R$ {{ number_format($contasPagar, 2, ',', '.') }}</span></p>
                </div>
                <div>
                    <p class="text-gray-500">Contas a Receber: <span class="font-bold text-blue-600">R$ {{ number_format($contasReceber, 2, ',', '.') }}</span></p>
                    <p class="text-gray-500">Ticket Médio: <span class="font-bold">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.add('hidden');
            });
            var active = document.getElementById('tab-' + tab);
            if (active) active.classList.remove('hidden');
            document.querySelectorAll('.tab-link').forEach(function(el) {
                el.classList.remove('text-blue-600', 'dark:text-blue-400', 'border-blue-600', 'dark:border-blue-400');
                el.classList.add('text-gray-500', 'dark:text-gray-300', 'border-transparent');
            });
            if (event && event.target) {
                event.target.classList.add('text-blue-600', 'dark:text-blue-400', 'border-blue-600', 'dark:border-blue-400');
                event.target.classList.remove('text-gray-500', 'dark:text-gray-300', 'border-transparent');
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            showTab('overview');
        });
    </script>
// ...existing code (todo o conteúdo real do dashboard, começando pelo root <div class="min-h-screen ..."> e sem duplicidade)...
