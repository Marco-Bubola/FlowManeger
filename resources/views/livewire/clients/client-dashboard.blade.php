<div class="client-dashboard min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-blue-900/20 dark:to-indigo-900/20">
    <!-- Incluir CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('resources/css/client-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('resources/css/client-dashboard-enhanced.css') }}">
    <!-- Header do Cliente -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('clients.index') }}" 
                       class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="bi bi-arrow-left text-xl"></i>
                    </a>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Avatar do Cliente -->
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-xl">
                                {{ strtoupper(substr($client->name, 0, 1) . (strpos($client->name, ' ') !== false ? substr($client->name, strpos($client->name, ' ') + 1, 1) : '')) }}
                            </span>
                        </div>
                        
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $client->name }}</h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                @if($client->email)
                                    <span class="flex items-center">
                                        <i class="bi bi-envelope mr-1"></i>
                                        {{ $client->email }}
                                    </span>
                                @endif
                                @if($client->phone)
                                    <span class="flex items-center">
                                        <i class="bi bi-telephone mr-1"></i>
                                        {{ $client->phone }}
                                    </span>
                                @endif
                                @if($diasComoCliente > 0)
                                    <span class="flex items-center">
                                        <i class="bi bi-calendar-check mr-1"></i>
                                        Cliente há {{ $diasComoCliente }} dias
                                    </span>
                                @endif
                                <span class="flex items-center">
                                    <i class="bi bi-star mr-1"></i>
                                    <span class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-full text-xs font-medium">
                                        @if($totalVendas >= 20) VIP
                                        @elseif($totalVendas >= 10) Premium
                                        @elseif($totalVendas >= 5) Regular
                                        @else Novo @endif
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="flex items-center space-x-3">
                    <!-- Dropdown de Exportação -->
                    <div class="relative" x-data="{ exportOpen: false }">
                        <button @click="exportOpen = !exportOpen" 
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="bi bi-download mr-2"></i>
                            Exportar Dados
                            <i class="bi bi-chevron-down ml-2 transform transition-transform duration-200" :class="{ 'rotate-180': exportOpen }"></i>
                        </button>
                        
                        <div x-show="exportOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.away="exportOpen = false"
                             class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl z-50 backdrop-blur-lg">
                            
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <i class="bi bi-file-earmark-pdf text-red-500 mr-2"></i>
                                    Opções de Exportação
                                </h3>
                                
                                <div class="space-y-2">
                                    <button wire:click="exportClientPDF('complete')" 
                                            @click="exportOpen = false"
                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <i class="bi bi-file-earmark-pdf text-red-500 mr-3"></i>
                                        <div class="text-left">
                                            <div class="font-medium">Relatório Completo</div>
                                            <div class="text-xs text-gray-500">Todas as vendas + analytics</div>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="exportClientPDF('vendas')" 
                                            @click="exportOpen = false"
                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <i class="bi bi-cart text-blue-500 mr-3"></i>
                                        <div class="text-left">
                                            <div class="font-medium">Apenas Vendas</div>
                                            <div class="text-xs text-gray-500">Lista detalhada de vendas</div>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="exportClientPDF('financeiro')" 
                                            @click="exportOpen = false"
                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <i class="bi bi-graph-up text-green-500 mr-3"></i>
                                        <div class="text-left">
                                            <div class="font-medium">Relatório Financeiro</div>
                                            <div class="text-xs text-gray-500">Resumo + parcelas pendentes</div>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="exportClientExcel" 
                                            @click="exportOpen = false"
                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <i class="bi bi-file-earmark-excel text-green-600 mr-3"></i>
                                        <div class="text-left">
                                            <div class="font-medium">Planilha Excel</div>
                                            <div class="text-xs text-gray-500">Dados para análise</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('sales.create') }}?client_id={{ $client->id }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="bi bi-plus mr-2"></i>
                        Nova Venda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header com Filtros em Dropdown -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="bi bi-speedometer2 text-indigo-600 dark:text-indigo-400 mr-3"></i>
                    Dashboard do Cliente
                </h2>
                <div class="flex items-center space-x-2 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">
                    <i class="bi bi-clock text-indigo-600 dark:text-indigo-400 text-sm"></i>
                    <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Atualizado em tempo real</span>
                </div>
            </div>
            
            <!-- Dropdown de Filtros -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-lg hover:shadow-xl text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                    <i class="bi bi-funnel-fill mr-2 text-indigo-600 dark:text-indigo-400"></i>
                    <span class="font-medium">Filtros Avançados</span>
                    <i class="bi bi-chevron-down ml-2 transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl z-50 backdrop-blur-lg">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="bi bi-sliders text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                Configurar Filtros
                            </h3>
                            <button wire:click="clearFilters" 
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium flex items-center">
                                <i class="bi bi-arrow-clockwise mr-1"></i>
                                Limpar
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Ano -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <i class="bi bi-calendar3 text-indigo-500 mr-1"></i>
                                    Ano
                                </label>
                                <select wire:model.live="filterYear" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    @for($year = now()->year; $year >= now()->year - 5; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <!-- Mês -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <i class="bi bi-calendar-month text-indigo-500 mr-1"></i>
                                    Mês
                                </label>
                                <select wire:model.live="filterMonth" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="all">Todos os meses</option>
                                    @for($month = 1; $month <= 12; $month++)
                                        <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <i class="bi bi-check-circle text-indigo-500 mr-1"></i>
                                    Status
                                </label>
                                <select wire:model.live="filterStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="all">Todos</option>
                                    <option value="pago">✅ Pago</option>
                                    <option value="pendente">⏳ Pendente</option>
                                    <option value="cancelado">❌ Cancelado</option>
                                </select>
                            </div>
                            
                            <!-- Tipo de Pagamento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <i class="bi bi-credit-card text-indigo-500 mr-1"></i>
                                    Pagamento
                                </label>
                                <select wire:model.live="filterPaymentType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="all">Todos</option>
                                    <option value="a_vista">💰 À Vista</option>
                                    <option value="parcelado">📊 Parcelado</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Resultados encontrados:</span>
                                <span class="font-medium">{{ count($vendas) }} vendas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Alertas e Informações Importantes -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Alertas de Parcelas -->
            <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-400 mb-4 flex items-center">
                    <i class="bi bi-exclamation-triangle-fill mr-2"></i>
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
                
                <div class="space-y-3">
                    @if($parcelasVencidas->count() > 0)
                        <div class="flex items-start space-x-3 p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <i class="bi bi-exclamation-circle-fill text-red-600 dark:text-red-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">
                                    {{ $parcelasVencidas->count() }} parcela(s) vencida(s)
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400">
                                    Valor: R$ {{ number_format($parcelasVencidas->sum('valor'), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($parcelasVencendoSemana->count() > 0)
                        <div class="flex items-start space-x-3 p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                            <i class="bi bi-clock-fill text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                    {{ $parcelasVencendoSemana->count() }} parcela(s) vencem esta semana
                                </p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400">
                                    Valor: R$ {{ number_format($parcelasVencendoSemana->sum('valor'), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($parcelasVencidas->count() == 0 && $parcelasVencendoSemana->count() == 0)
                        <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                            <i class="bi bi-check-circle-fill"></i>
                            <span class="text-sm">Todas as parcelas em dia!</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Performance do Cliente -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-400 mb-4 flex items-center">
                    <i class="bi bi-graph-up-arrow mr-2"></i>
                    Performance
                </h3>
                
                <div class="space-y-4">
                    <!-- Frequência de Compras -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Frequência de Compras</span>
                            @php
                                $frequencia = $diasComoCliente > 0 && $totalVendas > 1 ? 
                                    round($diasComoCliente / $totalVendas) : 0;
                            @endphp
                            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                @if($frequencia > 0)
                                    A cada {{ $frequencia }} dias
                                @else
                                    Cliente novo
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            @php
                                $frequenciaScore = $frequencia > 0 ? min(100, (30 / max(1, $frequencia)) * 100) : 0;
                            @endphp
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $frequenciaScore }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Taxa de Pagamento -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Taxa de Pagamento</span>
                            @php
                                $taxaPagamento = $totalFaturado > 0 ? ($totalPago / $totalFaturado) * 100 : 0;
                            @endphp
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ number_format($taxaPagamento, 1) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $taxaPagamento }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Evolução do Ticket Médio -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Valor Médio</span>
                            @php
                                $ticketScore = min(100, ($ticketMedio / 1000) * 100); // Considera R$ 1000 como 100%
                            @endphp
                            <span class="text-sm font-medium text-purple-600 dark:text-purple-400">
                                Excelente
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $ticketScore }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Próximas Ações -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-green-800 dark:text-green-400 mb-4 flex items-center">
                    <i class="bi bi-list-check mr-2"></i>
                    Próximas Ações
                </h3>
                
                <div class="space-y-3">
                    @if($parcelasVencidas->count() > 0)
                        <div class="flex items-start space-x-3 p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                            <i class="bi bi-telephone-fill text-orange-600 dark:text-orange-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-orange-800 dark:text-orange-300">
                                    Entrar em contato
                                </p>
                                <p class="text-xs text-orange-600 dark:text-orange-400">
                                    Cobrar parcelas vencidas
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if(\Carbon\Carbon::parse($ultimaCompra ?? now())->diffInDays(now()) > 30)
                        <div class="flex items-start space-x-3 p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <i class="bi bi-gift-fill text-blue-600 dark:text-blue-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                    Oferecer promoção
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-400">
                                    Cliente inativo há mais de 30 dias
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($totalVendas >= 5 && $taxaPagamento >= 90)
                        <div class="flex items-start space-x-3 p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <i class="bi bi-star-fill text-purple-600 dark:text-purple-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-purple-800 dark:text-purple-300">
                                    Cliente VIP
                                </p>
                                <p class="text-xs text-purple-600 dark:text-purple-400">
                                    Oferecer condições especiais
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($parcelasVencidas->count() == 0 && $totalVendas > 0)
                        <div class="flex items-start space-x-3 p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <i class="bi bi-cart-plus-fill text-green-600 dark:text-green-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    Oportunidade de venda
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    Cliente em dia e ativo
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cards de Resumo Financeiro Expandidos -->
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
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(count($parcelas) > 0)
                            @foreach($parcelas as $parcela)
                                <div class="bg-gradient-to-br from-white via-yellow-50 to-orange-50 dark:from-gray-700 dark:via-yellow-900/20 dark:to-orange-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center text-white font-bold">
                                                {{ $parcela['numero_parcela'] }}
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    Parcela {{ $parcela['numero_parcela'] }}
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                                    <i class="bi bi-calendar3 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($parcela['data_vencimento'])->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            @if($parcela['status'] === 'pago') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                            @elseif($parcela['status'] === 'pendente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                            @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                                            @if($parcela['status'] === 'pago') ✅ @elseif($parcela['status'] === 'pendente') ⏳ @else ❌ @endif
                                            {{ ucfirst($parcela['status']) }}
                                        </span>
                                    </div>
                                    
                                    <div class="text-center py-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg mb-4">
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            R$ {{ number_format($parcela['valor'], 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            <i class="bi bi-receipt mr-1"></i>
                                            Venda #{{ $parcela['sale']['id'] }}
                                        </p>
                                    </div>
                                    
                                    @if(\Carbon\Carbon::parse($parcela['data_vencimento'])->isPast() && $parcela['status'] === 'pendente')
                                        <div class="flex items-center justify-center text-red-600 dark:text-red-400 text-sm">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Vencida há {{ \Carbon\Carbon::parse($parcela['data_vencimento'])->diffInDays(now()) }} dias
                                        </div>
                                    @elseif($parcela['status'] === 'pendente')
                                        <div class="flex items-center justify-center text-blue-600 dark:text-blue-400 text-sm">
                                            <i class="bi bi-clock mr-1"></i>
                                            Vence em {{ \Carbon\Carbon::parse($parcela['data_vencimento'])->diffInDays(now()) }} dias
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-calendar-check text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhuma parcela encontrada</h3>
                                <p class="text-gray-600 dark:text-gray-400">Este cliente não possui vendas parceladas no período selecionado.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Aba Pagamentos -->
                @if($activeTab === 'pagamentos')
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(count($pagamentos) > 0)
                            @foreach($pagamentos as $pagamento)
                                <div class="bg-gradient-to-br from-white via-green-50 to-emerald-50 dark:from-gray-700 dark:via-green-900/20 dark:to-emerald-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white">
                                                @if($pagamento['payment_method'] === 'dinheiro')
                                                    <i class="bi bi-cash text-xl"></i>
                                                @elseif($pagamento['payment_method'] === 'cartao_credito')
                                                    <i class="bi bi-credit-card text-xl"></i>
                                                @elseif($pagamento['payment_method'] === 'cartao_debito')
                                                    <i class="bi bi-credit-card-2-front text-xl"></i>
                                                @elseif($pagamento['payment_method'] === 'pix')
                                                    <i class="bi bi-qr-code text-xl"></i>
                                                @else
                                                    <i class="bi bi-wallet text-xl"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $pagamento['payment_method'])) }}
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                                    <i class="bi bi-calendar3 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($pagamento['payment_date'])->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center py-4 bg-green-50 dark:bg-green-900/20 rounded-lg mb-4">
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                            R$ {{ number_format($pagamento['amount_paid'], 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            <i class="bi bi-receipt mr-1"></i>
                                            Venda #{{ $pagamento['sale']['id'] }}
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center justify-center text-green-600 dark:text-green-400 text-sm">
                                        <i class="bi bi-check-circle-fill mr-1"></i>
                                        Pagamento confirmado
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-credit-card text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhum pagamento encontrado</h3>
                                <p class="text-gray-600 dark:text-gray-400">Este cliente ainda não realizou pagamentos no período selecionado.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Aba Produtos Favoritos -->
                @if($activeTab === 'produtos')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse($produtosMaisComprados as $produto)
                            <div class="bg-gradient-to-br from-white via-purple-50 to-pink-50 dark:from-gray-700 dark:via-purple-900/20 dark:to-pink-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 group">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4 group-hover:scale-110 transition-transform duration-200">
                                        {{ $produto['quantidade'] }}
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">{{ $produto['produto'] }}</h3>
                                    
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Quantidade:</span>
                                            <span class="font-medium text-purple-600 dark:text-purple-400">{{ $produto['quantidade'] }} und</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Compras:</span>
                                            <span class="font-medium text-pink-600 dark:text-pink-400">{{ $produto['vendas'] }}x</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                                            @if($produto['vendas'] >= 5)
                                                <i class="bi bi-star-fill text-yellow-500 mr-1"></i>
                                                Produto Favorito
                                            @elseif($produto['vendas'] >= 3)
                                                <i class="bi bi-heart-fill text-red-500 mr-1"></i>
                                                Muito Comprado
                                            @else
                                                <i class="bi bi-bag-check-fill text-green-500 mr-1"></i>
                                                Produto Regular
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-box text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhum produto encontrado</h3>
                                <p class="text-gray-600 dark:text-gray-400">Este cliente ainda não comprou produtos no período selecionado.</p>
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

<!-- Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
