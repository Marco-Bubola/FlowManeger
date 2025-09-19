@props([
    'client' => null,
    'diasComoCliente' => 0,
    'backRoute' => null,
    'clienteClass' => 'Premium'
])

<!-- Header Modernizado do Dashboard de Cliente -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                @if($backRoute)
                <!-- Botão voltar melhorado -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <!-- Avatar do Cliente com efeitos modernos -->
                <div class="relative">
                    @if($client && $client->caminho_foto)
                        <div class="w-20 h-20 rounded-2xl overflow-hidden shadow-xl ring-4 ring-white/50 dark:ring-slate-700/50">
                            <img src="{{ $client->caminho_foto }}" 
                                 alt="{{ $client->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25 flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                            <span class="text-white font-bold text-2xl">
                                @if($client)
                                    {{ strtoupper(substr($client->name, 0, 1) . (strpos($client->name, ' ') !== false ? substr($client->name, strpos($client->name, ' ') + 1, 1) : '')) }}
                                @else
                                    CL
                                @endif
                            </span>
                            <!-- Efeito de brilho -->
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                        </div>
                    @endif
                    
                    <!-- Indicador de status online -->
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-3 border-white dark:border-slate-800 shadow-lg">
                        <div class="w-full h-full bg-green-500 rounded-full animate-ping opacity-75"></div>
                    </div>
                </div>

                <!-- Informações do Cliente -->
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                            {{ $client ? $client->name : 'Cliente' }}
                        </h1>
                        
                        <!-- Badge da classe do cliente -->
                        <span class="px-3 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold rounded-full shadow-lg flex items-center">
                            <i class="bi bi-star-fill mr-1"></i>
                            {{ $clienteClass }}
                        </span>
                    </div>
                    
                    <!-- Informações de contato e dados -->
                    <div class="flex items-center space-x-6 text-sm text-slate-600 dark:text-slate-400">
                        @if($client && $client->email)
                            <span class="flex items-center bg-white/50 dark:bg-slate-800/50 px-3 py-1 rounded-full">
                                <i class="bi bi-envelope text-blue-500 mr-2"></i>
                                {{ $client->email }}
                            </span>
                        @endif
                        
                        @if($client && $client->phone)
                            <span class="flex items-center bg-white/50 dark:bg-slate-800/50 px-3 py-1 rounded-full">
                                <i class="bi bi-telephone text-green-500 mr-2"></i>
                                {{ $client->phone }}
                            </span>
                        @endif
                        
                        @if($diasComoCliente > 0)
                            <span class="flex items-center bg-white/50 dark:bg-slate-800/50 px-3 py-1 rounded-full">
                                <i class="bi bi-calendar-heart text-purple-500 mr-2"></i>
                                {{ $diasComoCliente }} dias como cliente
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center space-x-3">
                <!-- Dropdown de Exportação -->
                <div class="relative" x-data="{ exportOpen: false }">
                    <button @click="exportOpen = !exportOpen" 
                            class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-download mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        Exportar Dados
                        <i class="bi bi-chevron-down ml-2 transform transition-transform duration-200" :class="{ 'rotate-180': exportOpen }"></i>
                        <!-- Efeito hover ring -->
                        <div class="absolute inset-0 rounded-2xl bg-green-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                    
                    <div x-show="exportOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.away="exportOpen = false"
                         class="absolute right-0 mt-2 w-64 bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl z-50 backdrop-blur-lg">
                        
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                <i class="bi bi-file-earmark-pdf text-red-500 mr-2"></i>
                                Opções de Exportação
                            </h3>
                            
                            <div class="space-y-2">
                                <button wire:click="exportClientPDF('complete')" 
                                        class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                                    <i class="bi bi-file-earmark-text text-blue-500 mr-3"></i>
                                    Relatório Completo
                                </button>
                                
                                <button wire:click="exportClientPDF('vendas')" 
                                        class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                                    <i class="bi bi-cart text-green-500 mr-3"></i>
                                    Apenas Vendas
                                </button>
                                
                                <button wire:click="exportClientPDF('financeiro')" 
                                        class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                                    <i class="bi bi-cash-coin text-purple-500 mr-3"></i>
                                    Dados Financeiros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botão Nova Venda -->
                @if($client)
                <a href="{{ route('sales.create') }}?client_id={{ $client->id }}" 
                   class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="bi bi-plus-circle mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                    Nova Venda
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-indigo-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>