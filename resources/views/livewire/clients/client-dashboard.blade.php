<div class="client-dashboard w-full">
    <!-- Incluir CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">


    <!-- Header Modernizado -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <x-client-dashboard-header
            :client="$client"
            :dias-como-cliente="$diasComoCliente"
            :back-route="route('clients.index')"
            :cliente-class="$totalVendas >= 5 && ($totalFaturado > 0 ? ($totalPago / $totalFaturado) * 100 : 0) >= 90 ? 'VIP' : 'Premium'" />
    </div>

    <!-- Filtros Modernos -->
    <x-client-filters
        :vendas="$vendas"
        :filter-year="$filterYear"
        :filter-month="$filterMonth"
        :filter-status="$filterStatus"
        :filter-payment-type="$filterPaymentType" />



    <!-- Alertas e Performance -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <x-client-alerts
            :parcelas="$parcelas"
            :total-vendas="$totalVendas"
            :total-pago="$totalPago"
            :total-faturado="$totalFaturado"
            :ultima-compra="$ultimaCompra" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-6 mb-8">
            <!-- Total de Vendas -->
            <div class="stats-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total de Vendas</p>
                        <p class="text-3xl font-bold" data-animate-number>{{ $totalVendas }}</p>
                        <p class="text-blue-200 text-xs mt-1">
                            <i class="bi bi-trending-up mr-1"></i>
                            Volume total
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-cart text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Faturado -->
            <div class="stats-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Faturado</p>
                        <p class="text-3xl font-bold">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</p>
                        <p class="text-green-200 text-xs mt-1">
                            <i class="bi bi-cash-coin mr-1"></i>
                            Receita bruta
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-currency-dollar text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pago -->
            <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Pago</p>
                        <p class="text-3xl font-bold">R$ {{ number_format($totalPago, 2, ',', '.') }}</p>
                        <p class="text-purple-200 text-xs mt-1">
                            <i class="bi bi-check-circle mr-1"></i>
                            Receita líquida
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Restante a Pagar -->
            <div class="stats-card bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
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
                    <div class="w-12 h-12 bg-amber-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-hourglass-split text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Ticket Médio -->
            <div class="stats-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Ticket Médio</p>
                        <p class="text-3xl font-bold">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                        <p class="text-orange-200 text-xs mt-1">
                            <i class="bi bi-graph-up mr-1"></i>
                            Valor médio
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-graph-up text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Última Compra -->
            <div class="stats-card bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
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
                    <div class="w-12 h-12 bg-teal-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-clock text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Parcelas Vencidas -->
            <div class="stats-card bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl hover-scale transform hover:scale-105 transition-all duration-300">
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
                    <div class="w-12 h-12 bg-red-400/30 rounded-xl flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegação por Abas -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Tabs Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                <nav class="flex flex-wrap px-6" aria-label="Tabs">
                    <button wire:click="setActiveTab('vendas')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'vendas' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-cart mr-2"></i>
                        <span class="hidden sm:inline">Vendas</span>
                        <span class="ml-1 px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 rounded-full text-xs">{{ count($vendas) }}</span>
                    </button>

                    <button wire:click="setActiveTab('graficos')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'graficos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-graph-up mr-2"></i>
                        <span class="hidden sm:inline">Gráficos</span>
                    </button>

                    <button wire:click="setActiveTab('parcelas')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'parcelas' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-calendar-check mr-2"></i>
                        <span class="hidden sm:inline">Parcelas</span>
                        <span class="ml-1 px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 rounded-full text-xs">{{ count($parcelas) }}</span>
                    </button>

                    <button wire:click="setActiveTab('pagamentos')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'pagamentos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-credit-card mr-2"></i>
                        <span class="hidden sm:inline">Pagamentos</span>
                        <span class="ml-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full text-xs">{{ count($pagamentos) }}</span>
                    </button>

                    <button wire:click="setActiveTab('produtos')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'produtos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-box mr-2"></i>
                        <span class="hidden sm:inline">Produtos</span>
                    </button>

                    <button wire:click="setActiveTab('insights')"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-all duration-200 flex items-center {{ $activeTab === 'insights' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <i class="bi bi-lightbulb mr-2"></i>
                        <span class="hidden sm:inline">Insights</span>
                        <span class="ml-1 px-1.5 py-0.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-full text-xs">AI</span>
                    </button>
                </nav>
            </div>

            <!-- Tabs Content -->
            <div class="p-6">
                <!-- Aba Vendas -->
                @if($activeTab === 'vendas')
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        @if(count($vendas) > 0)
                            @foreach($vendas as $venda)
                                <div class="bg-gradient-to-br from-white via-gray-50 to-blue-50 dark:from-gray-700 dark:via-gray-800 dark:to-blue-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 group">
                                    <!-- Header da Venda -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                                #{{ $venda['id'] }}
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    Venda #{{ $venda['id'] }}
                                                </h3>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    <i class="bi bi-calendar3 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($venda['created_at'])->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>

                                        <span class="px-2 py-1 rounded-lg text-xs font-medium
                                            @if($venda['status'] === 'pago') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                            @elseif($venda['status'] === 'pendente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                            @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                                            @if($venda['status'] === 'pago') ✅ @elseif($venda['status'] === 'pendente') ⏳ @else ❌ @endif
                                            {{ ucfirst($venda['status']) }}
                                        </span>
                                    </div>

                                    <!-- Valor Principal -->
                                    <div class="text-center mb-4 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg">
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            R$ {{ number_format($venda['total_price'], 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 flex items-center justify-center mt-1">
                                            @if(ucfirst($venda['tipo_pagamento']) === 'A_vista')
                                                <i class="bi bi-cash-coin mr-1"></i>
                                                À Vista
                                            @else
                                                <i class="bi bi-credit-card mr-1"></i>
                                                {{ $venda['parcelas'] }}x de R$ {{ number_format($venda['total_price'] / $venda['parcelas'], 2, ',', '.') }}
                                            @endif
                                        </p>

                                        <!-- Informações de Pagamento -->
                                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200 dark:border-gray-600 text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">
                                                <i class="bi bi-check-circle mr-1"></i>
                                                Pago: R$ {{ number_format($venda['amount_paid'] ?? 0, 2, ',', '.') }}
                                            </span>
                                            @if(($venda['total_price'] - ($venda['amount_paid'] ?? 0)) > 0)
                                                <span class="text-amber-600 dark:text-amber-400">
                                                    <i class="bi bi-hourglass-split mr-1"></i>
                                                    Restante: R$ {{ number_format($venda['total_price'] - ($venda['amount_paid'] ?? 0), 2, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Produtos da Venda -->
                                    @if(isset($venda['sale_items']) && count($venda['sale_items']) > 0)
                                        <div class="mb-4">
                                            <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                                <i class="bi bi-box mr-1"></i>
                                                Produtos ({{ count($venda['sale_items']) }}):
                                            </h4>
                                            <div class="space-y-1">
                                                @foreach(array_slice($venda['sale_items'], 0, 2) as $item)
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="text-gray-600 dark:text-gray-400 truncate mr-2">
                                                            {{ $item['product']['name'] ?? 'Produto' }}
                                                        </span>
                                                        <span class="bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 px-2 py-0.5 rounded-full font-medium">
                                                            {{ $item['quantity'] }}x
                                                        </span>
                                                    </div>
                                                @endforeach
                                                @if(count($venda['sale_items']) > 2)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                                        <i class="bi bi-three-dots"></i>
                                                        +{{ count($venda['sale_items']) - 2 }} mais
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Ações -->
                                    <div class="flex space-x-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <a href="{{ route('sales.show', $venda['id']) }}"
                                           class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs transition-colors group-hover:bg-indigo-700">
                                            <i class="bi bi-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs transition-colors">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-cart text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhuma venda encontrada</h3>
                                <p class="text-gray-600 dark:text-gray-400">Ajuste os filtros ou faça uma nova venda para este cliente.</p>
                                <a href="{{ route('sales.create') }}?client_id={{ $client->id }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl mt-4">
                                    <i class="bi bi-plus mr-2"></i>
                                    Criar Nova Venda
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Aba Gráficos -->
                @if($activeTab === 'graficos')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Vendas por Mês -->
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="bi bi-calendar3 text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                Vendas por Mês (Últimos 12 meses)
                            </h3>
                            <div class="h-64">
                                @if(count($vendasPorMes) > 0)
                                    <canvas id="vendasPorMesChart"></canvas>
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                                        <i class="bi bi-graph-up text-4xl"></i>
                                        <span class="ml-2">Dados insuficientes</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Vendas por Status -->
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="bi bi-pie-chart text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                Vendas por Status
                            </h3>
                            <div class="h-64">
                                @if(count($vendasPorStatus) > 0)
                                    <canvas id="vendasPorStatusChart"></canvas>
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                                        <i class="bi bi-pie-chart text-4xl"></i>
                                        <span class="ml-2">Dados insuficientes</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Produtos Mais Comprados -->
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="bi bi-star text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                Produtos Mais Comprados
                            </h3>
                            <div class="space-y-3">
                                @forelse($produtosMaisComprados as $produto)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $produto['produto'] }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $produto['quantidade'] }} unidades</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhum produto encontrado</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Categorias Mais Compradas -->
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="bi bi-tags text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                Categorias Preferidas
                            </h3>
                            <div class="space-y-3">
                                @forelse($categoriasMaisCompradas as $categoria)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $categoria['categoria'] }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $categoria['quantidade'] }} unidades</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhuma categoria encontrada</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Aba Parcelas -->
                @if($activeTab === 'parcelas')
                    <div class="mb-6">
                        <!-- Header com informações e paginação -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Parcelas do Cliente</h3>
                                <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 rounded-full text-sm font-medium">
                                    {{ $totalParcelas }} parcelas
                                </span>
                            </div>

                            <!-- Paginação -->
                            @if($totalParcelas > $parcelasPerPage)
                                <nav class="flex items-center justify-center gap-2 select-none">
                                    <button wire:click="prevParcelasPage"
                                            @if($parcelasPage <= 1) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-purple-100 hover:text-purple-700 dark:hover:bg-purple-900/30 dark:hover:text-purple-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span class="px-4 py-2 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 font-semibold text-base shadow-sm border border-purple-200 dark:border-purple-700">
                                        {{ $parcelasPage }} / {{ ceil($totalParcelas / $parcelasPerPage) }}
                                    </span>
                                    <button wire:click="nextParcelasPage"
                                            @if($parcelasPage >= ceil($totalParcelas / $parcelasPerPage)) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-purple-100 hover:text-purple-700 dark:hover:bg-purple-900/30 dark:hover:text-purple-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </nav>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(count($parcelas) > 0)
                            @foreach($parcelas as $parcela)
                                <div class="bg-gradient-to-br from-green-50/80 via-white to-emerald-100 dark:from-green-900/40 dark:via-gray-900 dark:to-emerald-900 rounded-2xl border-2 border-green-100 dark:border-green-900/40 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.03] group overflow-hidden relative">
                                    <div class="absolute top-0 right-0 m-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-900 dark:bg-green-900/60 dark:text-green-200 shadow">
                                            <i class="bi bi-check2-circle mr-1"></i> Pago
                                        </span>
                                    </div>
                                    <!-- Header com Status, Número e ícones -->
                                    <div class="relative p-6 bg-transparent">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-16 h-16 flex items-center justify-center rounded-xl text-white font-bold text-2xl shadow-lg ring-1 ring-white/30"
                                                     style="background: linear-gradient(135deg, rgba(16,185,129,0.95), rgba(6,95,70,0.95));">
                                                    <i class="bi bi-credit-card-fill text-xl"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Parcela {{ $parcela['numero_parcela'] }}</h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                        <i class="bi bi-receipt text-base"></i>
                                                        <span>Venda #{{ $parcela['sale']['id'] }}</span>
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Status e métodos -->
                                            <div class="flex items-center space-x-3">
                                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300 bg-white/60 dark:bg-gray-800/60 px-3 py-1 rounded-full shadow-sm">
                                                    <i class="bi bi-wallet2 mr-2"></i>
                                                    <span>{{ $parcela['metodo_pagamento'] ?? 'Indefinido' }}</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                        @if($parcela['status'] === 'pago') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                                        @elseif($parcela['status'] === 'pendente') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                                                        @if($parcela['status'] === 'pago')
                                                            <i class="bi bi-check-circle-fill mr-1"></i>Pago
                                                        @elseif($parcela['status'] === 'pendente')
                                                            <i class="bi bi-clock-fill mr-1"></i>Pendente
                                                        @else
                                                            <i class="bi bi-x-circle-fill mr-1"></i>{{ ucfirst($parcela['status']) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Corpo do Card -->
                                    <div class="p-6">
                                        <!-- Valor da Parcela com ícone grande -->
                                        <div class="text-center py-4 mb-6 bg-white/60 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-white/30 dark:border-gray-700 p-4">
                                            <div class="flex items-center justify-center gap-3">
                                                <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200 shadow-md">
                                                    <i class="bi bi-cash-stack text-2xl"></i>
                                                </div>
                                                <div>
                                                    <p class="text-3xl font-extrabold text-gray-900 dark:text-white mb-0">
                                                        R$ {{ number_format($parcela['valor'], 2, ',', '.') }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-300">Valor da parcela</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Informações de Data e Payer -->
                                        <div class="grid grid-cols-1 gap-4 mb-6">
                                            <div class="flex items-center justify-between p-3 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-white/20">
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    <i class="bi bi-calendar-event mr-2 text-lg"></i>
                                                    <div class="text-sm">
                                                        <div class="font-medium">Vencimento</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($parcela['data_vencimento'])->format('d/m/Y') }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                        <i class="bi bi-person-circle mr-2 text-lg"></i>
                                                        <div class="text-sm">{{ $parcela['cliente_nome'] ?? ($parcela['sale']['client']['nome'] ?? 'Cliente') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Indicador de Situação -->
                                        @if(\Carbon\Carbon::parse($parcela['data_vencimento'])->isPast() && $parcela['status'] === 'pendente')
                                            <div class="flex items-center justify-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400">
                                                <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                                                <span class="text-sm font-medium">
                                                    Vencida há {{ \Carbon\Carbon::parse($parcela['data_vencimento'])->diffInDays(now()) }} dias
                                                </span>
                                            </div>
                                        @elseif($parcela['status'] === 'pendente')
                                            @php
                                                $diasRestantes = \Carbon\Carbon::parse($parcela['data_vencimento'])->diffInDays(now());
                                                $isUrgente = $diasRestantes <= 3;
                                            @endphp
                                            <div class="flex items-center justify-center p-3
                                                @if($isUrgente) bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800 text-orange-600 dark:text-orange-400
                                                @else bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 @endif
                                                rounded-lg border">
                                                <i class="bi bi-{{ $isUrgente ? 'exclamation-diamond' : 'clock' }} mr-2"></i>
                                                <span class="text-sm font-medium">
                                                    @if($diasRestantes == 0)
                                                        Vence hoje!
                                                    @elseif($diasRestantes == 1)
                                                        Vence amanhã
                                                    @else
                                                        Vence em {{ $diasRestantes }} dias
                                                    @endif
                                                </span>
                                            </div>
                                        @elseif($parcela['status'] === 'pago')
                                            <div class="flex items-center justify-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400">
                                                <i class="bi bi-check-circle-fill mr-2"></i>
                                                <span class="text-sm font-medium">Pagamento confirmado</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-16">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center mx-auto mb-6">
                                    <i class="bi bi-calendar-check text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhuma parcela encontrada</h3>
                                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">Este cliente não possui vendas parceladas no período selecionado. As parcelas aparecerão aqui quando houver vendas a prazo.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Aba Pagamentos -->
                @if($activeTab === 'pagamentos')
                    <div class="mb-6">
                        <!-- Header com informações e paginação -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Histórico de Pagamentos</h3>
                                <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full text-sm font-medium">
                                    {{ $totalPagamentos }} pagamentos
                                </span>
                            </div>

                            <!-- Paginação -->
                            @if($totalPagamentos > $pagamentosPerPage)
                                <nav class="flex items-center justify-center gap-2 select-none">
                                    <button wire:click="prevPagamentosPage"
                                            @if($pagamentosPage <= 1) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-green-100 hover:text-green-700 dark:hover:bg-green-900/30 dark:hover:text-green-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span class="px-4 py-2 rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-semibold text-base shadow-sm border border-green-200 dark:border-green-700">
                                        {{ $pagamentosPage }} / {{ ceil($totalPagamentos / $pagamentosPerPage) }}
                                    </span>
                                    <button wire:click="nextPagamentosPage"
                                            @if($pagamentosPage >= ceil($totalPagamentos / $pagamentosPerPage)) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-green-100 hover:text-green-700 dark:hover:bg-green-900/30 dark:hover:text-green-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </nav>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(count($pagamentos) > 0)
                            @foreach($pagamentos as $pagamento)
                                <div class="bg-gradient-to-br from-green-50/80 via-white to-emerald-100 dark:from-green-900/40 dark:via-gray-900 dark:to-emerald-900 rounded-2xl border-2 border-green-100 dark:border-green-900/40 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.03] group overflow-hidden relative">
                                    <div class="absolute top-0 right-0 m-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-900 dark:bg-green-900/60 dark:text-green-200 shadow">
                                            <i class="bi bi-cash-stack mr-1"></i> Pago
                                        </span>
                                    </div>

                                    <!-- Header do Pagamento -->
                                    <div class="relative p-6 bg-transparent">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-16 h-16 flex items-center justify-center rounded-xl text-white font-bold text-2xl shadow-lg ring-1 ring-white/30" style="background: linear-gradient(135deg, rgba(16,185,129,0.95), rgba(6,95,70,0.95));">
                                                    @if($pagamento['payment_method'] === 'dinheiro')
                                                        <i class="bi bi-cash-stack text-2xl"></i>
                                                    @elseif($pagamento['payment_method'] === 'cartao_credito')
                                                        <i class="bi bi-credit-card-2-front text-2xl"></i>
                                                    @elseif($pagamento['payment_method'] === 'cartao_debito')
                                                        <i class="bi bi-credit-card text-2xl"></i>
                                                    @elseif($pagamento['payment_method'] === 'pix')
                                                        <i class="bi bi-qr-code text-2xl"></i>
                                                    @else
                                                        <i class="bi bi-wallet text-2xl"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pagamento</h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="bi bi-receipt"></i> Venda #{{ $pagamento['sale']['id'] }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center space-x-3">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/70 dark:bg-gray-800/60 shadow-sm">
                                                    <i class="bi bi-clock-history mr-2"></i>
                                                    <span class="text-xs">{{ \Carbon\Carbon::parse($pagamento['payment_date'])->format('d/m/Y') }}</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                        @if($pagamento['payment_method'] === 'dinheiro')
                                                            <i class="bi bi-cash mr-1"></i>Dinheiro
                                                        @elseif($pagamento['payment_method'] === 'cartao_credito')
                                                            <i class="bi bi-credit-card mr-1"></i>Crédito
                                                        @elseif($pagamento['payment_method'] === 'cartao_debito')
                                                            <i class="bi bi-credit-card-2-front mr-1"></i>Débito
                                                        @elseif($pagamento['payment_method'] === 'pix')
                                                            <i class="bi bi-qr-code mr-1"></i>PIX
                                                        @else
                                                            <i class="bi bi-wallet mr-1"></i>{{ ucfirst(str_replace('_', ' ', $pagamento['payment_method'])) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Corpo do Card -->
                                    <div class="p-6">
                                        <div class="text-center py-4 mb-6 bg-white/60 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-white/30 dark:border-gray-700 p-4">
                                            <div class="flex items-center justify-center gap-3">
                                                <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200 shadow-md">
                                                    <i class="bi bi-cash-coin text-2xl"></i>
                                                </div>
                                                <div>
                                                    <p class="text-3xl font-extrabold text-gray-900 dark:text-white mb-0">R$ {{ number_format($pagamento['amount_paid'], 2, ',', '.') }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-300">Valor pago</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 mb-6">
                                            <div class="flex items-center justify-between p-3 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-white/10">
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    <i class="bi bi-person-circle mr-2 text-lg"></i>
                                                    <div class="text-sm">
                                                        <div class="font-medium">Recebido de</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $pagamento['sale']['client']['nome'] ?? 'Cliente' }}</div>
                                                    </div>
                                                </div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($pagamento['payment_date'])->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>

                                        <div class="flex gap-3 mt-4">
                                            <a href="{{ route('sales.show', $pagamento['sale']['id']) }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white text-sm font-semibold rounded-2xl shadow-md transition-all duration-200 transform hover:-translate-y-0.5" title="Ver Venda">
                                                <i class="bi bi-eye-fill text-lg"></i>
                                                <span>Ver Venda</span>
                                            </a>

                                            <button class="inline-flex items-center gap-2 px-4 py-2 bg-white/70 dark:bg-gray-800/60 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-2xl shadow-sm border border-white/20 hover:shadow-md transition" title="Ver Comprovante">
                                                <i class="bi bi-receipt text-lg"></i>
                                                <span>Comprovante</span>
                                            </button>

                                            <button wire:click="baixarComprovante({{ $pagamento['id'] }})" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-2xl shadow transition" title="Baixar Comprovante">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-16">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center mx-auto mb-6">
                                    <i class="bi bi-credit-card text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhum pagamento encontrado</h3>
                                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">Este cliente ainda não realizou pagamentos no período selecionado. Os pagamentos aparecerão aqui conforme forem sendo processados.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Aba Produtos Favoritos -->
                @if($activeTab === 'produtos')
                    <div class="mb-6">
                        <!-- Header com informações e paginação -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produtos Mais Comprados</h3>
                                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 rounded-full text-sm font-medium">
                                    {{ $totalProdutos }} produtos
                                </span>
                            </div>

                            <!-- Paginação -->
                            @if($totalProdutos > $produtosPerPage)
                                <nav class="flex items-center justify-center gap-2 select-none">
                                    <button wire:click="prevProdutosPage"
                                            @if($produtosPage <= 1) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-indigo-100 hover:text-indigo-700 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span class="px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 font-semibold text-base shadow-sm border border-indigo-200 dark:border-indigo-700">
                                        {{ $produtosPage }} / {{ ceil($totalProdutos / $produtosPerPage) }}
                                    </span>
                                    <button wire:click="nextProdutosPage"
                                            @if($produtosPage >= ceil($totalProdutos / $produtosPerPage)) disabled @endif
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-indigo-100 hover:text-indigo-700 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </nav>
                            @endif
                        </div>

                    </div>

                    <!-- Grid de Produtos Estilo Products Index -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                        @forelse($produtosMaisComprados as $produto)
                            <!-- Produto Simples com CSS customizado mantido -->
                            <div class="product-card-modern">
                                <!-- Área da imagem com badges -->
                                <div class="product-img-area">
                                    <img src="{{ asset('storage/products/product-placeholder.png') }}"
                                         class="product-img"
                                         alt="{{ $produto['produto'] }}">

                                    <!-- Badge de Quantidade -->
                                    <span class="badge-quantity" title="Quantidade Total Comprada">
                                        <i class="bi bi-stack"></i> {{ $produto['quantidade'] }}
                                    </span>

                                    <!-- Ícone da categoria -->
                                    <div class="category-icon-wrapper">
                                        <i class="bi bi-box category-icon"></i>
                                    </div>
                                </div>

                                <!-- Conteúdo -->
                                <div class="card-body">
                                    <div class="product-title" title="{{ $produto['produto'] }}">
                                        {{ ucwords($produto['produto']) }}
                                    </div>
                                </div>

                                <!-- Badge de preço -->
                                <span class="badge-price" title="Compras realizadas">
                                    <i class="bi bi-bag-check"></i>
                                    {{ $produto['vendas'] }}x comprado
                                </span>
                            </div>
                        @empty
                            <!-- Estado vazio aprimorado -->
                            <div class="col-span-full empty-state flex flex-col items-center justify-center py-20 bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-2xl border-2 border-dashed border-neutral-300 dark:border-neutral-600">
                                <div class="relative">
                                    <!-- Ícone animado -->
                                    <div class="w-32 h-32 mx-auto mb-6 text-neutral-400 relative">
                                        <div class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 dark:from-purple-800 dark:to-blue-800 rounded-full opacity-20 animate-pulse"></div>
                                        <i class="bi bi-box absolute inset-0 flex items-center justify-center text-5xl"></i>
                                    </div>

                                    <!-- Elementos decorativos -->
                                    <div class="absolute top-0 left-0 w-4 h-4 bg-purple-300 rounded-full opacity-50 animate-bounce"></div>
                                    <div class="absolute top-4 right-0 w-3 h-3 bg-blue-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 0.5s;"></div>
                                    <div class="absolute bottom-0 left-4 w-2 h-2 bg-pink-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 1s;"></div>
                                </div>

                                <h3 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 mb-3">📦 Nenhum produto encontrado</h3>
                                <p class="text-neutral-600 dark:text-neutral-400 text-center mb-8 max-w-md text-lg">
                                    Este cliente ainda não comprou produtos no período selecionado. Os produtos aparecerão aqui conforme as compras forem realizadas.
                                </p>
                            </div>
                        @endforelse
                    </div>
                @endif

                <!-- Aba Insights Avançados -->
                @if($activeTab === 'insights')
                    @livewire('clients.client-metrics', ['clienteId' => $client->id])
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Removido Alpine.js daqui pois já está no layout principal -->

<script>
// Listener para eventos de debug do Livewire
document.addEventListener('livewire:init', () => {
    Livewire.on('debug-info', (message) => {
        console.log('Debug Event:', message);
        alert('Debug: ' + message);
    });

    Livewire.on('teste-realizado', (message) => {
        console.log('Teste Event:', message);
        alert('Teste: ' + message);
    });
});

// Debug de cliques nos botões
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.hasAttribute('wire:click')) {
            console.log('Botão clicado:', e.target.getAttribute('wire:click'));
        }
    });
});
</script>

<!-- Scripts para Gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurações globais dos gráficos
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--tw-text-opacity') ? '#6b7280' : '#9ca3af';

    // Variáveis para os gráficos
    let vendasChart, statusChart;

    function initCharts() {
        @if(count($vendasPorMes) > 0)
            // Gráfico de Vendas por Mês
            const ctx1 = document.getElementById('vendasPorMesChart');
            if (ctx1) {
                if (vendasChart) {
                    vendasChart.destroy();
                }

                const gradient = ctx1.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

                vendasChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: @json(array_column($vendasPorMes, 'mes')),
                        datasets: [{
                            label: 'Faturamento (R$)',
                            data: @json(array_column($vendasPorMes, 'faturamento')),
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }, {
                            label: 'Número de Vendas',
                            data: @json(array_column($vendasPorMes, 'vendas')),
                            borderColor: 'rgb(139, 92, 246)',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: 'rgb(99, 102, 241)',
                                borderWidth: 1,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 0) {
                                            return 'Faturamento: R$ ' + new Intl.NumberFormat('pt-BR').format(context.parsed.y);
                                        } else {
                                            return 'Vendas: ' + context.parsed.y;
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#9ca3af'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)'
                                },
                                ticks: {
                                    color: '#9ca3af',
                                    callback: function(value) {
                                        return 'R$ ' + new Intl.NumberFormat('pt-BR').format(value);
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    color: '#9ca3af'
                                }
                            }
                        }
                    }
                });
            }
        @endif

        @if(count($vendasPorStatus) > 0)
            // Gráfico de Vendas por Status
            const ctx2 = document.getElementById('vendasPorStatusChart');
            if (ctx2) {
                if (statusChart) {
                    statusChart.destroy();
                }

                const statusColors = {
                    'pago': '#10b981',
                    'pendente': '#f59e0b',
                    'cancelado': '#ef4444'
                };

                const labels = @json(array_keys($vendasPorStatus));
                const colors = labels.map(status => statusColors[status] || '#6b7280');

                statusChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: labels.map(status => status.charAt(0).toUpperCase() + status.slice(1)),
                        datasets: [{
                            data: @json(array_values($vendasPorStatus)),
                            backgroundColor: colors,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif
    }

    // Inicializar gráficos
    initCharts();

    // Reinicializar gráficos quando a aba mudar
    document.addEventListener('livewire:component.updated', function() {
        if (document.querySelector('.tab-content') && @this.activeTab === 'graficos') {
            setTimeout(initCharts, 100);
        }
    });

    // Responsividade para dispositivos móveis
    window.addEventListener('resize', function() {
        if (vendasChart) vendasChart.resize();
        if (statusChart) statusChart.resize();
    });

    // Detectar mudança de tema (claro/escuro)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const isDark = document.documentElement.classList.contains('dark');
                Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';

                if (vendasChart) {
                    vendasChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                    vendasChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                    vendasChart.options.scales.y1.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                    vendasChart.update();
                }

                if (statusChart) {
                    statusChart.update();
                }
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});

// Função para animar números
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString('pt-BR');
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animar números nos cards quando carregarem
document.addEventListener('DOMContentLoaded', function() {
    const numberElements = document.querySelectorAll('[data-animate-number]');
    numberElements.forEach(element => {
        const finalValue = parseInt(element.textContent.replace(/[^\d]/g, ''));
        element.textContent = '0';
        setTimeout(() => {
            animateValue(element, 0, finalValue, 1000);
        }, 300);
    });
});
</script>
