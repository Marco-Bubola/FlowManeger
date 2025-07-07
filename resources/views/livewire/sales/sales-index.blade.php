<div class="min-h-screen w-full py-8">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Header + Filtros/Pesquisa -->
        <div class="mb-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- Header principal -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:gap-6 flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="bi bi-cart text-indigo-600 dark:text-indigo-400 mr-3"></i>Vendas
                    </h1>
                    <p class="mt-2 lg:mt-0 text-sm text-gray-600 dark:text-gray-400">Gerencie suas vendas de forma simples e eficiente</p>
                </div>
                <!-- Filtros, pesquisa e nova venda -->
                <div class="flex flex-col gap-2 w-full lg:w-auto lg:flex-row lg:items-center lg:justify-end">
                    <!-- Campo de Pesquisa -->
                    <div class="w-full lg:w-64">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-search text-gray-400"></i>
                            </div>
                            <input type="text"
                                wire:model.live.debounce.300ms="search"
                                id="search"
                                placeholder="Pesquisar cliente..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">
                        </div>
                    </div>
                    <!-- Dropdown de Filtros -->
                    <div x-data="{ open: false }" class="relative w-full lg:w-auto">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center justify-center w-full lg:w-auto px-4 py-2 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-lg shadow-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="bi bi-funnel mr-2"></i>Filtros
                            <i class="bi bi-chevron-down ml-2" :class="{'rotate-180': open}"></i>
                        </button>
                        
                        <!-- Dropdown Panel -->
                        <div x-show="open" 
                             @click.away="open = false" 
                             x-cloak
                             class="absolute right-0 z-50 mt-2 w-96 lg:w-[600px] bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-xl p-4">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200 dark:border-zinc-600">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <i class="bi bi-funnel mr-2"></i>Filtros
                                </h3>
                                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <i class="bi bi-x text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Filtros -->
                            <div class="space-y-4">
                                <!-- Primeira linha -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Status -->
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-flag text-gray-400 mr-1"></i>Status
                                        </label>
                                        <select wire:model.live="status"
                                            id="status"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                            <option value="">Todos</option>
                                            <option value="pendente">Pendente</option>
                                            <option value="pago">Pago</option>
                                            <option value="cancelado">Cancelado</option>
                                        </select>
                                    </div>
                                    <!-- Tipo de Pagamento -->
                                    <div>
                                        <label for="payment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-credit-card text-gray-400 mr-1"></i>Pagamento
                                        </label>
                                        <select wire:model.live="payment_type"
                                            id="payment_type"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                            <option value="">Todos</option>
                                            <option value="a_vista">À Vista</option>
                                            <option value="parcelado">Parcelado</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Segunda linha -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Ordenar -->
                                    <div>
                                        <label for="filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-sort-down text-gray-400 mr-1"></i>Ordenar
                                        </label>
                                        <select wire:model.live="filter"
                                            id="filter"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                            <option value="">Padrão</option>
                                            <option value="created_at">Mais Recentes</option>
                                            <option value="updated_at">Últimas Atualizações</option>
                                            <option value="name_asc">Cliente A-Z</option>
                                            <option value="name_desc">Cliente Z-A</option>
                                            <option value="price_asc">Menor Valor</option>
                                            <option value="price_desc">Maior Valor</option>
                                        </select>
                                    </div>
                                    <!-- Itens por página -->
                                    <div>
                                        <label for="perPage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-list text-gray-400 mr-1"></i>Por página
                                        </label>
                                        <select wire:model.live="perPage"
                                            id="perPage"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                            <option value="12">12</option>
                                            <option value="18">18</option>
                                            <option value="24">24</option>
                                            <option value="36">36</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Terceira linha - Datas -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Data Início -->
                                    <div>
                                        <label for="date_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-calendar text-gray-400 mr-1"></i>Data Início
                                        </label>
                                        <input type="date"
                                            wire:model.live="date_start"
                                            id="date_start"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                    <!-- Data Fim -->
                                    <div>
                                        <label for="date_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-calendar text-gray-400 mr-1"></i>Data Fim
                                        </label>
                                        <input type="date"
                                            wire:model.live="date_end"
                                            id="date_end"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                </div>
                                
                                <!-- Quarta linha - Valores -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Valor Mínimo -->
                                    <div>
                                        <label for="min_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-currency-dollar text-gray-400 mr-1"></i>Valor Mínimo
                                        </label>
                                        <input type="number"
                                            wire:model.live="min_value"
                                            id="min_value"
                                            step="0.01"
                                            placeholder="0,00"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">
                                    </div>
                                    <!-- Valor Máximo -->
                                    <div>
                                        <label for="max_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="bi bi-currency-dollar text-gray-400 mr-1"></i>Valor Máximo
                                        </label>
                                        <input type="number"
                                            wire:model.live="max_value"
                                            id="max_value"
                                            step="0.01"
                                            placeholder="999.999,99"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">
                                    </div>
                                </div>
                                
                                <!-- Botões de Ação -->
                                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-zinc-600">
                                    <button @click="
                                        $wire.set('status', '');
                                        $wire.set('payment_type', '');
                                        $wire.set('filter', '');
                                        $wire.set('date_start', '');
                                        $wire.set('date_end', '');
                                        $wire.set('min_value', '');
                                        $wire.set('max_value', '');
                                        $wire.set('perPage', 12);
                                    " 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors duration-200">
                                        <i class="bi bi-arrow-clockwise mr-2"></i>Limpar Filtros
                                    </button>
                                    <button @click="open = false" 
                                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                        <i class="bi bi-check mr-2"></i>Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botão Nova Venda -->
                    <a href="{{ route('sales.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Nova Venda
                    </a>
                </div>
            </div>
        </div>
        <!-- Fim Dropdown de Filtros -->

        <!-- Lista de Vendas -->
        <div class="overflow-hidden">
            @if($sales->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @foreach($sales as $sale)
                <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl overflow-hidden hover:shadow-2xl hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-300 group relative">
                    <!-- Header com gradiente -->
                    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="bi bi-receipt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">
                                        Venda #{{ $sale->id }}
                                    </h3>
                                    <p class="text-white/80 text-sm">
                                        {{ $sale->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            <!-- Status Badge -->
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm
                                    @if($sale->status === 'pago') bg-green-500/30 text-green-100 border border-green-400/50
                                    @elseif($sale->status === 'pendente') bg-yellow-500/30 text-yellow-100 border border-yellow-400/50
                                    @else bg-red-500/30 text-red-100 border border-red-400/50 @endif">
                                    @if($sale->status === 'pago')
                                    <i class="bi bi-check-circle mr-1"></i>Pago
                                    @elseif($sale->status === 'pendente')
                                    <i class="bi bi-clock mr-1"></i>Pendente
                                    @else
                                    <i class="bi bi-x-circle mr-1"></i>Cancelado
                                    @endif
                                </span>
                                <!-- Dropdown de Ações -->
                                <div x-data="{ actionsOpen: false }" class="relative">
                                    <button @click="actionsOpen = !actionsOpen" 
                                            class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center text-white hover:bg-white/30 transition-colors duration-200">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div x-show="actionsOpen" 
                                         @click.away="actionsOpen = false" 
                                         x-cloak
                                         class="absolute right-0 top-10 z-50 w-56 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-xl py-2">
                                        <a href="{{ route('sales.show', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-eye text-blue-500 mr-3"></i>Ver Detalhes
                                        </a>
                                        <a href="{{ route('sales.edit', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-pencil text-green-500 mr-3"></i>Editar Venda
                                        </a>
                                        <a href="{{ route('sales.add-products', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-plus-circle text-indigo-500 mr-3"></i>Adicionar Produtos
                                        </a>
                                        <a href="{{ route('sales.edit-prices', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-currency-dollar text-purple-500 mr-3"></i>Editar Preços
                                        </a>
                                        <a href="{{ route('sales.add-payments', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-credit-card text-teal-500 mr-3"></i>Adicionar Pagamento
                                        </a>
                                        <a href="{{ route('sales.edit-payments', $sale->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-pencil-square text-orange-500 mr-3"></i>Editar Pagamentos
                                        </a>
                                        <div class="border-t border-gray-200 dark:border-zinc-600 my-2"></div>
                                        <button wire:click="exportPdf({{ $sale->id }})" 
                                                class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-file-earmark-pdf text-red-500 mr-3"></i>Exportar PDF
                                        </button>
                                        <button wire:click="confirmDelete({{ $sale->id }})" 
                                                class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                            <i class="bi bi-trash text-red-500 mr-3"></i>Excluir Venda
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="p-6">
                        <!-- Cliente -->
                        <div class="mb-4">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                    <i class="bi bi-person text-white text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $sale->client->name }}</h4>
                                    @if($sale->client->email)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sale->client->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Produtos da Venda -->
                        <div class="mb-4">
                            <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                                <i class="bi bi-box text-gray-400 mr-2"></i>Produtos ({{ $sale->saleItems->count() }})
                            </h5>
                            <div class="space-y-2 max-h-24 overflow-y-auto">
                                @foreach($sale->saleItems->take(3) as $item)
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        @if($item->product->image)
                                        <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="w-6 h-6 rounded object-cover">
                                        @else
                                        <div class="w-6 h-6 bg-gray-300 dark:bg-zinc-600 rounded flex items-center justify-center">
                                            <i class="bi bi-image text-xs text-gray-500"></i>
                                        </div>
                                        @endif
                                        <span class="text-xs font-medium text-gray-900 dark:text-white truncate max-w-[120px]">
                                            {{ $item->product->name }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $item->quantity }}x</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">R$ {{ number_format($item->price, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                                @endforeach
                                @if($sale->saleItems->count() > 3)
                                <div class="text-center">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        +{{ $sale->saleItems->count() - 3 }} item(s) a mais
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Estatísticas financeiras -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-700 dark:to-zinc-600 rounded-xl p-4 mb-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                        R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Valor Total</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                        {{ $sale->saleItems->sum('quantity') }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                        {{ $sale->saleItems->sum('quantity') === 1 ? 'Unidade' : 'Unidades' }}
                                    </p>
                                </div>
                            </div>

                            @if($sale->amount_paid > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-zinc-500">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Pago:</span>
                                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                        R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}
                                    </span>
                                </div>
                                @if($sale->amount_paid < $sale->total_price)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Restante:</span>
                                    <span class="text-sm font-bold text-red-600 dark:text-red-400">
                                        R$ {{ number_format($sale->total_price - $sale->amount_paid, 2, ',', '.') }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Tipo de Pagamento -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-credit-card text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $sale->tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}
                                </span>
                            </div>
                            @if($sale->tipo_pagamento === 'parcelado')
                            <span class="px-3 py-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-bold rounded-full">
                                {{ $sale->parcelas }}x parcelas
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Efeito hover -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-2xl"></div>
                </div>
                @endforeach
            </div>

            <!-- Paginação -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-zinc-600">
                {{ $sales->links() }}
            </div>
            @else
            <!-- Estado vazio -->
            <div class="text-center py-16">
                <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-6">
                    <i class="bi bi-cart text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhuma venda encontrada</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    @if($search)
                    Não encontramos vendas com o termo "{{ $search }}".
                    @else
                    Comece registrando sua primeira venda.
                    @endif
                </p>
                <a href="{{ route('sales.create') }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <i class="bi bi-plus mr-2"></i>
                    Registrar Venda
                </a>
            </div>
            @endif
        </div>
    </div>
  </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tem certeza de que deseja excluir a venda <strong>#{{ $deletingSale?->id }}</strong> do cliente <strong>{{ $deletingSale?->client->name }}</strong>?
                                Esta ação não pode ser desfeita e o estoque dos produtos será restaurado.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        wire:click="deleteSale"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="bi bi-trash mr-2"></i>
                        Excluir
                    </button>
                    <button type="button"
                        wire:click="cancelDelete"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif