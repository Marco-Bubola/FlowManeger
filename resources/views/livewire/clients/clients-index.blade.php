<div class="min-h-screen w-full py-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Header com estatísticas -->
        <div class="mb-8">
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="bi bi-people text-white text-xl"></i>
                        </div>
                        Clientes
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Gerencie seus clientes de forma simples e eficiente</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button wire:click="exportClients" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 transition-all duration-200 shadow-sm">
                        <i class="bi bi-download mr-2"></i>
                        Exportar
                    </button>
                    <button onclick="document.getElementById('importModal').style.display='block'" 
                            class="inline-flex items-center px-4 py-2 border border-green-300 dark:border-green-600 text-sm font-medium rounded-lg text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-900 transition-all duration-200 shadow-sm">
                        <i class="bi bi-upload mr-2"></i>
                        Importar
                    </button>
                    <a href="{{ route('clients.create') }}" 
                       class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 transition-all duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Novo Cliente
                    </a>
                </div>
            </div>

            <!-- Cards de estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="bi bi-people text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Clientes</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clients->total() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="bi bi-graph-up text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Clientes Ativos</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clients->where('created_at', '>=', now()->subDays(30))->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <i class="bi bi-star text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Clientes Premium</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clients->filter(fn($client) => $client->sales->count() >= 5)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar-plus text-orange-600 dark:text-orange-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Novos este Mês</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clients->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner da Nova Funcionalidade -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-6 text-white shadow-lg border border-gray-200 dark:border-gray-700 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/30">
                            <i class="bi bi-speedometer2 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold flex items-center">
                                🚀 Nova Funcionalidade: Dashboard do Cliente
                                <span class="ml-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
                                    NOVO
                                </span>
                            </h3>
                            <p class="text-blue-100 mt-2 text-lg">
                                Visualize análises avançadas, gráficos interativos, insights de compra e muito mais!
                            </p>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <div class="grid grid-cols-2 gap-4 text-sm text-blue-100">
                            <div class="flex items-center space-x-2 bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                                <i class="bi bi-graph-up text-lg"></i>
                                <span>Gráficos Avançados</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                                <i class="bi bi-lightbulb text-lg"></i>
                                <span>Insights IA</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                                <i class="bi bi-heart text-lg"></i>
                                <span>Score Fidelidade</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                                <i class="bi bi-trophy text-lg"></i>
                                <span>Rankings</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-white/20">
                    <p class="text-sm text-blue-100 flex items-center">
                        <i class="bi bi-info-circle mr-2"></i>
                        Clique no botão <strong class="mx-1 bg-white/20 px-2 py-1 rounded border border-white/30">Dashboard</strong> em qualquer cliente para experimentar esta nova experiência!
                    </p>
                </div>
            </div>
        </div>

            <!-- Filtros e Ações -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8" x-data="{ showAdvancedFilters: @entangle('showAdvancedFilters') }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="bi bi-funnel text-gray-600 dark:text-gray-400 mr-2"></i>
                        Busca e Filtros
                    </h2>
                    <div class="flex items-center space-x-3">
                        <button @click="showAdvancedFilters = !showAdvancedFilters" 
                                wire:click="$toggle('showAdvancedFilters')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 border border-blue-200 dark:border-blue-700 transition-colors duration-200">
                            <i class="bi bi-sliders mr-2"></i>
                            Filtros Avançados
                            <i class="bi bi-chevron-down ml-1 transition-transform duration-200" :class="showAdvancedFilters ? 'rotate-180' : ''"></i>
                        </button>
                        <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">{{ $clients->total() }} resultados</span>
                    </div>
                </div>
                
                <!-- Busca Rápida -->
                <div class="mb-6">
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="search"
                               placeholder="🔍 Pesquisar por nome, email, telefone ou cidade..."
                               class="w-full pl-12 pr-16 py-4 text-lg border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-search text-gray-400 text-xl"></i>
                        </div>
                        @if($search)
                            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <i class="bi bi-x-circle text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filtros Avançados (Escondidos por padrão) -->
                <div x-show="showAdvancedFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="bi bi-gear text-gray-500 mr-2"></i>
                            Configurações Avançadas
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <!-- Ordenar -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-sort-down text-gray-400 mr-1"></i>Ordenar por
                                </label>
                                <select wire:model.live="filter" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="">Padrão</option>
                                    <option value="created_at">Últimos Adicionados</option>
                                    <option value="updated_at">Últimos Atualizados</option>
                                    <option value="name_asc">Nome A-Z</option>
                                    <option value="name_desc">Nome Z-A</option>
                                    <option value="most_sales">Mais Compras</option>
                                    <option value="best_customers">Maiores Gastadores</option>
                                    <option value="recent_activity">Atividade Recente</option>
                                </select>
                            </div>

                            <!-- Status do Cliente -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-person-check text-gray-400 mr-1"></i>Status
                                </label>
                                <select wire:model.live="statusFilter" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="">Todos</option>
                                    <option value="vip">VIP (10+ compras)</option>
                                    <option value="premium">Premium (5+ compras)</option>
                                    <option value="standard">Padrão</option>
                                    <option value="new">Novos (último mês)</option>
                                    <option value="inactive">Inativos (6+ meses)</option>
                                </select>
                            </div>

                            <!-- Período -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-calendar-range text-gray-400 mr-1"></i>Cadastrado em
                                </label>
                                <select wire:model.live="periodFilter" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="">Qualquer período</option>
                                    <option value="today">Hoje</option>
                                    <option value="week">Esta semana</option>
                                    <option value="month">Este mês</option>
                                    <option value="quarter">Últimos 3 meses</option>
                                    <option value="year">Este ano</option>
                                </select>
                            </div>

                            <!-- Itens por página -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-grid text-gray-400 mr-1"></i>Por página
                                </label>
                                <select wire:model.live="perPage" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="12">12 clientes</option>
                                    <option value="16">16 clientes</option>
                                    <option value="20">20 clientes</option>
                                    <option value="24">24 clientes</option>
                                    <option value="32">32 clientes</option>
                                    <option value="48">48 clientes</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filtros Adicionais -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Valor de Compras -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-currency-dollar text-gray-400 mr-1"></i>Valor Total de Compras
                                </label>
                                <div class="flex gap-2">
                                    <input type="number" wire:model.live="minValue" placeholder="Min" 
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <input type="number" wire:model.live="maxValue" placeholder="Max" 
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>

                            <!-- Número de Compras -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-cart text-gray-400 mr-1"></i>Número de Compras
                                </label>
                                <div class="flex gap-2">
                                    <input type="number" wire:model.live="minSales" placeholder="Min" 
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <input type="number" wire:model.live="maxSales" placeholder="Max" 
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="flex items-end gap-2">
                                <button wire:click="clearAllFilters" 
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <i class="bi bi-arrow-clockwise mr-1"></i>Limpar
                                </button>
                                <button wire:click="exportClients" 
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-colors duration-200">
                                    <i class="bi bi-download mr-1"></i>Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Painel de Ações em Massa -->
            @if(count($selectedClients) > 0)
                <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-xl p-4 mb-6 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-blue-700 dark:text-blue-300">
                            <i class="bi bi-check-square text-lg mr-2"></i>
                            <span class="font-medium">{{ count($selectedClients) }} cliente(s) selecionado(s)</span>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="bulkExport" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                <i class="bi bi-download mr-1"></i>Exportar
                            </button>
                            <button wire:click="bulkDelete" 
                                    onclick="return confirm('Tem certeza que deseja deletar os clientes selecionados?')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                                <i class="bi bi-trash mr-1"></i>Deletar
                            </button>
                            <button wire:click="$set('selectedClients', [])" 
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                                <i class="bi bi-x mr-1"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        <!-- Lista de Clientes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($clients->count() > 0)
                <!-- Header da tabela -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <i class="bi bi-table text-gray-600 dark:text-gray-400 mr-2"></i>
                            Lista de Clientes
                        </h3>
                        <div class="flex items-center space-x-3">
                            <!-- Checkbox Selecionar Todos -->
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="selectAll" 
                                       class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Selecionar todos</span>
                            </label>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $clients->count() }} de {{ $clients->total() }}</span>
                            <div class="flex items-center space-x-1">
                                <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" title="Visualização em Grid">
                                    <i class="bi bi-grid"></i>
                                </button>
                                <button class="p-2 text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg" title="Visualização em Tabela">
                                    <i class="bi bi-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de clientes -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($clients as $client)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 group relative transform hover:-translate-y-1">
                                <!-- Checkbox de seleção -->
                                <div class="absolute top-3 left-3 z-10">
                                    <input type="checkbox" wire:model.live="selectedClients" value="{{ $client->id }}" 
                                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-gray-700">
                                </div>
                                <!-- Header do card com gradiente -->
                                <div class="h-20 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-black opacity-10"></div>
                                    <div class="absolute top-3 right-3">
                                        @if($client->sales->count() >= 10)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg">
                                                <i class="bi bi-crown mr-1"></i>VIP
                                            </span>
                                        @elseif($client->sales->count() >= 5)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
                                                <i class="bi bi-star mr-1"></i>Premium
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg">
                                                <i class="bi bi-person mr-1"></i>Padrão
                                            </span>
                                        @endif
                                    </div>
                                    <!-- Pattern decorativo -->
                                    <div class="absolute -top-4 -right-4 w-16 h-16 bg-white opacity-10 rounded-full"></div>
                                    <div class="absolute -bottom-2 -left-2 w-12 h-12 bg-white opacity-10 rounded-full"></div>
                                    
                                    <!-- ID do Cliente -->
                                    <div class="absolute top-3 left-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-mono bg-white/20 text-white border border-white/30">
                                            ID: {{ $client->id }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Avatar centralizado -->
                                <div class="flex justify-center -mt-10 mb-4 relative z-10">
                                    <div class="relative">
                                        <img src="{{ $client->caminho_foto }}" 
                                             alt="Avatar de {{ $client->name }}"
                                             class="w-20 h-20 rounded-full border-4 border-white dark:border-gray-800 shadow-xl group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-r from-green-400 to-green-600 rounded-full border-2 border-white dark:border-gray-800 shadow-lg"></div>
                                    </div>
                                </div>

                                <!-- Informações do cliente -->
                                <div class="px-6 pb-6">
                                    <!-- Nome e data -->
                                    <div class="text-center mb-4">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $client->name }}</h3>
                                        <div class="flex items-center justify-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <i class="bi bi-calendar-plus mr-1"></i>
                                                {{ $client->created_at->format('d/m/Y') }}
                                            </span>
                                            <span class="flex items-center">
                                                <i class="bi bi-clock mr-1"></i>
                                                {{ $client->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Informações de contato expandidas -->
                                    <div class="space-y-2 mb-4">
                                        @if($client->email)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="bi bi-envelope text-blue-600 dark:text-blue-400 text-xs"></i>
                                                </div>
                                                <span class="truncate">{{ $client->email }}</span>
                                                <button class="ml-auto p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400" title="Copiar email">
                                                    <i class="bi bi-copy text-xs"></i>
                                                </button>
                                            </div>
                                        @endif
                                        @if($client->phone)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="bi bi-telephone text-green-600 dark:text-green-400 text-xs"></i>
                                                </div>
                                                <span>{{ $client->phone }}</span>
                                                <a href="tel:{{ $client->phone }}" class="ml-auto p-1 text-gray-400 hover:text-green-600 dark:hover:text-green-400" title="Ligar">
                                                    <i class="bi bi-telephone-fill text-xs"></i>
                                                </a>
                                            </div>
                                        @endif
                                        @if($client->address)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="bi bi-geo-alt text-purple-600 dark:text-purple-400 text-xs"></i>
                                                </div>
                                                <span class="truncate">{{ Str::limit($client->address, 30) }}</span>
                                                <button class="ml-auto p-1 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400" title="Ver no mapa">
                                                    <i class="bi bi-map text-xs"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Estatísticas melhoradas -->
                                    <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700/50 dark:to-blue-900/20 rounded-xl p-4 mb-5 border border-gray-100 dark:border-gray-600">
                                        <div class="grid grid-cols-2 gap-4 mb-3">
                                            <div class="text-center">
                                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-lg">
                                                    <i class="bi bi-cart text-white"></i>
                                                </div>
                                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $client->sales->count() }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                    {{ $client->sales->count() === 1 ? 'Compra' : 'Compras' }}
                                                </p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-lg">
                                                    <i class="bi bi-currency-dollar text-white"></i>
                                                </div>
                                                <p class="text-lg font-bold text-green-600 dark:text-green-400">R$ {{ number_format($client->sales->sum('total_price'), 0, ',', '.') }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Gasto</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Métricas adicionais -->
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div class="bg-white dark:bg-gray-800/50 rounded-lg p-2 text-center">
                                                <p class="font-semibold text-orange-600 dark:text-orange-400">
                                                    R$ {{ $client->sales->count() > 0 ? number_format($client->sales->sum('total_price') / $client->sales->count(), 0, ',', '.') : '0' }}
                                                </p>
                                                <p class="text-gray-500 dark:text-gray-400">Ticket Médio</p>
                                            </div>
                                            <div class="bg-white dark:bg-gray-800/50 rounded-lg p-2 text-center">
                                                <p class="font-semibold text-purple-600 dark:text-purple-400">
                                                    {{ $client->created_at->diffInDays(now()) }}d
                                                </p>
                                                <p class="text-gray-500 dark:text-gray-400">Como Cliente</p>
                                            </div>
                                        </div>
                                        
                                        @if($client->sales->count() > 0)
                                            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                                        <i class="bi bi-clock mr-1"></i>
                                                        Última compra:
                                                    </span>
                                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $client->sales->sortByDesc('created_at')->first()?->created_at?->diffForHumans() ?? 'N/A' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-xs mt-1">
                                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                                        <i class="bi bi-activity mr-1"></i>
                                                        Status:
                                                    </span>
                                                    <span class="font-semibold {{ $client->sales->where('created_at', '>=', now()->subDays(30))->count() > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        {{ $client->sales->where('created_at', '>=', now()->subDays(30))->count() > 0 ? 'Ativo' : 'Inativo' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Ações redesenhadas -->
                                    <div class="space-y-3">
                                        <a href="{{ route('clients.dashboard', $client->id) }}" 
                                           class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 relative">
                                            <i class="bi bi-speedometer2 mr-2"></i>
                                            Ver Dashboard Completo
                                            <span class="absolute -top-1 -right-1 bg-gradient-to-r from-orange-400 to-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full shadow-lg animate-bounce">
                                                NOVO
                                            </span>
                                        </a>
                                        
                                        <div class="grid grid-cols-4 gap-2">
                                            <a href="{{ route('clients.resumo', $client->id) }}" 
                                               class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 transition-all duration-200"
                                               title="Ver Resumo Detalhado">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                            <a href="{{ route('clients.edit', $client->id) }}" 
                                               class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 transition-all duration-200"
                                               title="Editar Informações">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="mailto:{{ $client->email }}" 
                                               class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 border border-blue-200 dark:border-blue-700 transition-all duration-200"
                                               title="Enviar E-mail">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                            <button wire:click="confirmDelete({{ $client->id }})" 
                                                    class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-700 transition-all duration-200"
                                                    title="Excluir Cliente">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Ações rápidas -->
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <button class="flex items-center justify-center px-3 py-1.5 text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                                    <i class="bi bi-whatsapp mr-1 text-green-600"></i>
                                                    WhatsApp
                                                </button>
                                                <button class="flex items-center justify-center px-3 py-1.5 text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                                    <i class="bi bi-plus-circle mr-1 text-blue-600"></i>
                                                    Nova Venda
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Paginação -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <span>Mostrando {{ $clients->firstItem() ?? 0 }} até {{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} resultados</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{ $clients->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- Estado vazio -->
                <div class="text-center py-20">
                    <div class="mx-auto w-32 h-32 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-full flex items-center justify-center mb-6 border-4 border-blue-100 dark:border-blue-800">
                        <i class="bi bi-people text-5xl text-blue-400 dark:text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                        @if($search)
                            Nenhum cliente encontrado
                        @else
                            Sua lista de clientes está vazia
                        @endif
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                        @if($search)
                            Não encontramos clientes com o termo "<strong>{{ $search }}</strong>". Tente refinar sua pesquisa ou limpar os filtros.
                        @else
                            Comece sua jornada empresarial adicionando seu primeiro cliente e construa relacionamentos duradouros.
                        @endif
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                        @if($search)
                            <button wire:click="$set('search', '')" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 transition-all duration-200">
                                <i class="bi bi-arrow-clockwise mr-2"></i>
                                Limpar Pesquisa
                            </button>
                        @endif
                        
                        <a href="{{ route('clients.create') }}" 
                           class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 transition-all duration-200">
                            <i class="bi bi-plus mr-2"></i>
                            Adicionar Primeiro Cliente
                        </a>
                    </div>
                    
                    @if(!$search)
                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Dicas para começar:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
                                    <i class="bi bi-person-plus text-blue-600 dark:text-blue-400 text-2xl mb-2"></i>
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-1">Adicione Dados Completos</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Inclua nome, email, telefone e outras informações relevantes</p>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-100 dark:border-green-800">
                                    <i class="bi bi-graph-up text-green-600 dark:text-green-400 text-2xl mb-2"></i>
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-1">Acompanhe o Progresso</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Use o dashboard para análises detalhadas de cada cliente</p>
                                </div>
                                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-100 dark:border-purple-800">
                                    <i class="bi bi-heart text-purple-600 dark:text-purple-400 text-2xl mb-2"></i>
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-1">Construa Relacionamentos</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Mantenha histórico de interações e preferências</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    
        <!-- Modal de Confirmação de Exclusão -->
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black bg-opacity-50 backdrop-blur-sm" wire:click="cancelDelete">
            <div class="relative p-4 w-full max-w-md max-h-full transform transition-all duration-300 scale-100" wire:click.stop>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
                    <!-- Header do modal -->
                    <div class="flex items-center justify-between p-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-3">
                                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                            </div>
                            Confirmar Exclusão
                        </h3>
                        <button type="button" 
                                wire:click="cancelDelete"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors duration-200">
                            <i class="bi bi-x text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Conteúdo do modal -->
                    <div class="px-6 pb-6">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                            <p class="text-sm text-red-800 dark:text-red-400">
                                <i class="bi bi-info-circle mr-2"></i>
                                Esta ação não pode ser desfeita. O cliente e todos os dados relacionados serão permanentemente removidos.
                            </p>
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Tem certeza de que deseja excluir o cliente <strong class="text-gray-900 dark:text-white">{{ $deletingClient?->name }}</strong>?
                        </p>
                        
                        <!-- Botões de ação -->
                        <div class="flex gap-3">
                            <button type="button" 
                                    wire:click="deleteClient"
                                    class="flex-1 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm py-2.5 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="bi bi-trash mr-2"></i>
                                Sim, Excluir
                            </button>
                            <button type="button" 
                                    wire:click="cancelDelete"
                                    class="flex-1 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-600 font-medium rounded-lg text-sm py-2.5 border border-gray-300 dark:border-gray-600 transition-colors duration-200">
                                <i class="bi bi-x mr-2"></i>
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notificações Toast Elegantes -->
        @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show" x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-green-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-check-circle-fill text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('message') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false" class="text-green-200 hover:text-white transition-colors duration-200 p-1 rounded-lg hover:bg-white/10">
                            <i class="bi bi-x text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show" x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-red-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false" class="text-red-200 hover:text-white transition-colors duration-200 p-1 rounded-lg hover:bg-white/10">
                            <i class="bi bi-x text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal de Importação -->
        <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="bi bi-upload text-green-500 mr-2"></i>
                        Importar Clientes
                    </h3>
                    <button onclick="document.getElementById('importModal').style.display='none'" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selecione o arquivo CSV
                        </label>
                        <input type="file" accept=".csv" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Formato do arquivo:</h4>
                        <p class="text-xs text-blue-700 dark:text-blue-400">
                            O arquivo deve conter as colunas: nome, email, telefone, endereco, cidade
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button onclick="document.getElementById('importModal').style.display='none'" 
                            class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Importar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications after 5 seconds
    setTimeout(function() {
        const notifications = document.querySelectorAll('[x-data*="show"]');
        notifications.forEach(function(notification) {
            if (window.Alpine && notification._x_dataStack) {
                notification._x_dataStack[0].show = false;
            }
        });
    }, 5000);
    
    // Smooth scroll enhancement for filter toggles
    const filterToggle = document.querySelector('[x-on\\:click*="showAdvancedFilters"]');
    if (filterToggle) {
        filterToggle.addEventListener('click', function() {
            setTimeout(function() {
                const filtersContainer = document.querySelector('[x-show="showAdvancedFilters"]');
                if (filtersContainer) {
                    filtersContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest',
                        inline: 'start'
                    });
                }
            }, 150);
        });
    }
    
    // Enhanced loading states with Livewire
    if (typeof Livewire !== 'undefined') {
        // Loading overlay
        Livewire.hook('message.sent', () => {
            document.body.style.cursor = 'wait';
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'livewire-loading';
            loadingOverlay.className = 'fixed inset-0 bg-black/10 backdrop-blur-[1px] z-40 flex items-center justify-center';
            loadingOverlay.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Carregando...</span>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
        });
        
        Livewire.hook('message.processed', () => {
            document.body.style.cursor = '';
            const loadingOverlay = document.getElementById('livewire-loading');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        });
    }
    
    // Search input enhancement with debounce
    const searchInput = document.querySelector('input[wire\\:model\\.debounce="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Add visual feedback for search
            this.classList.add('ring-2', 'ring-blue-500/50');
            setTimeout(() => {
                this.classList.remove('ring-2', 'ring-blue-500/50');
            }, 300);
        });
    }
    
    // Card hover animations
    const clientCards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800.rounded-xl');
    clientCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});

// Utility function for smooth animations
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString('pt-BR');
    }, 16);
}
</script>
@endpush

<!-- Estilos CSS customizados para animações -->
@push('styles')
<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeInUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-in-right {
    animation: slideInRight 0.3s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

/* Smooth transitions for all interactive elements */
button, input, select, .cursor-pointer {
    transition: all 0.2s ease-in-out;
}

/* Enhanced focus states */
button:focus, input:focus, select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
    .bg-gradient-to-r {
        background-image: linear-gradient(to right, var(--tw-gradient-stops));
    }
}

/* Loading animation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endpush

