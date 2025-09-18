<div x-data="{ showFilters: false }" class="min-h-screen w-full py-8">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">

        <!-- Header Moderno -->
        <x-sales-header
            title="Vendas"
            :total-sales="$totalSales ?? 0"
            :pending-sales="$pendingSales ?? 0"
            :today-sales="$todaySales ?? 0"
            :total-revenue="$totalRevenue ?? 0"
            :show-quick-actions="true" />

        <!-- Barra de Controle Superior com Pesquisa e Paginação -->
        <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-4 shadow-lg border border-white/20 dark:border-slate-700/50 mb-6">

            <!-- Linha Principal: Pesquisa (50%) + Controles e Paginação (50%) -->
            <div class="flex items-center gap-6">

                <!-- Lado Esquerdo: Campo de Pesquisa (50%) -->
                <div class="flex-1">
                    <div class="relative group">
                        <!-- Input principal -->
                        <div class="relative">
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Buscar vendas por ID, cliente, produto ou status..."
                                   class="w-full pl-12 pr-16 py-3 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900
                                          border border-slate-200/50 dark:border-slate-600/50 rounded-xl
                                          text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                          focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-400
                                          transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                          text-base font-medium">

                            <!-- Ícone de busca -->
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-purple-500 transition-colors duration-200"></i>
                            </div>

                            <!-- Botão limpar -->
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <button wire:click="$set('search', '')"
                                        x-show="$wire.search && $wire.search.length > 0"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-50"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-50"
                                        class="group/clear p-1.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500
                                               text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white
                                               rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                        title="Limpar busca">
                                    <i class="bi bi-x-lg text-xs group-hover/clear:rotate-90 transition-transform duration-200"></i>
                                </button>
                            </div>

                            <!-- Indicador de carregamento -->
                            <div wire:loading.delay wire:target="search"
                                 class="absolute right-12 top-1/2 transform -translate-y-1/2">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                            </div>

                            <!-- Efeito de brilho -->
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 via-transparent to-blue-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        </div>
                    </div>
                </div>

                <!-- Lado Direito: Informações + Paginação + Controles (50%) -->
                <div class="flex items-center gap-4">

                    <!-- Contador de Resultados -->
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg text-white">
                            <i class="bi bi-cart text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                @if($sales->total())
                                    {{ $sales->total() }} {{ $sales->total() === 1 ? 'Venda' : 'Vendas' }}
                                @else
                                    Nenhuma venda
                                @endif
                            </h3>
                            @if($sales->total() > 0)
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }}
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Paginação Compacta -->
                    @if($sales->hasPages())
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                        <!-- Primeira/Anterior -->
                        @if($sales->currentPage() > 1)
                        <a href="{{ $sales->url(1) }}"
                           class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                           title="Primeira página">
                            <i class="bi bi-chevron-double-left text-sm"></i>
                        </a>
                        @endif

                        @if($sales->previousPageUrl())
                        <a href="{{ $sales->previousPageUrl() }}"
                           class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                           title="Página anterior">
                            <i class="bi bi-chevron-left text-sm"></i>
                        </a>
                        @endif

                        <!-- Páginas -->
                        <div class="flex items-center px-3 py-1">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ $sales->currentPage() }} / {{ $sales->lastPage() }}
                            </span>
                        </div>

                        <!-- Próxima/Última -->
                        @if($sales->nextPageUrl())
                        <a href="{{ $sales->nextPageUrl() }}"
                           class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                           title="Próxima página">
                            <i class="bi bi-chevron-right text-sm"></i>
                        </a>
                        @endif

                        @if($sales->currentPage() < $sales->lastPage())
                        <a href="{{ $sales->url($sales->lastPage()) }}"
                           class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition-all duration-200"
                           title="Última página">
                            <i class="bi bi-chevron-double-right text-sm"></i>
                        </a>
                        @endif
                    </div>
                    @endif

                    <!-- Botão de Filtros -->
                    <button @click="showFilters = !showFilters"
                            class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-all duration-200"
                            :class="{ 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300': showFilters }"
                            title="Filtros avançados">
                        <i class="bi bi-funnel text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Linha Secundária: Pesquisas rápidas e indicadores -->
            <div class="mt-4 flex items-center justify-between">
                <!-- Pesquisas Rápidas -->
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">Pesquisas Rápidas:</span>

                    <button wire:click="setQuickSearch('pendente')"
                            class="group px-3 py-1 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50
                                   text-yellow-700 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-yellow-200 dark:border-yellow-700">
                        <i class="bi bi-clock mr-1"></i>
                        Pendentes
                    </button>

                    <button wire:click="setQuickSearch('pago')"
                            class="group px-3 py-1 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50
                                   text-green-700 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-green-200 dark:border-green-700">
                        <i class="bi bi-check-circle mr-1"></i>
                        Pagas
                    </button>

                    <button wire:click="setQuickSearch('pix')"
                            class="group px-3 py-1 bg-cyan-100 hover:bg-cyan-200 dark:bg-cyan-900/30 dark:hover:bg-cyan-900/50
                                   text-cyan-700 hover:text-cyan-800 dark:text-cyan-400 dark:hover:text-cyan-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-cyan-200 dark:border-cyan-700">
                        <i class="bi bi-qr-code mr-1"></i>
                        PIX
                    </button>

                    <button wire:click="setQuickSearch('cartão')"
                            class="group px-3 py-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50
                                   text-blue-700 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-blue-200 dark:border-blue-700">
                        <i class="bi bi-credit-card mr-1"></i>
                        Cartão
                    </button>

                    <button wire:click="setQuickSearch('dinheiro')"
                            class="group px-3 py-1 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50
                                   text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-emerald-200 dark:border-emerald-700">
                        <i class="bi bi-cash mr-1"></i>
                        Dinheiro
                    </button>

                    <button wire:click="setQuickSearch('hoje')"
                            class="group px-3 py-1 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50
                                   text-indigo-700 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-indigo-200 dark:border-indigo-700">
                        <i class="bi bi-calendar-day mr-1"></i>
                        Hoje
                    </button>
                </div>

                <!-- Indicadores de Status e Ordenação -->
                <div class="flex items-center gap-3">
                    <!-- Indicador de Ordenação Atual -->
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 rounded-xl border border-indigo-200 dark:border-indigo-700">
                        <i class="bi bi-arrow-up-down text-indigo-600 dark:text-indigo-400 text-sm"></i>
                        <span class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">
                            {{
                                match($sortBy) {
                                    'created_at' => 'Por Data',
                                    'total_price' => 'Por Valor',
                                    'client_name' => 'Por Cliente',
                                    'status' => 'Por Status',
                                    'id' => 'Por ID',
                                    'updated_at' => 'Por Atualização',
                                    default => 'Por Data'
                                }
                            }}
                            <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
                        </span>
                    </div>

                    @if($search || $statusFilter || $clientFilter || $startDate || $endDate || $minValue || $maxValue)
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                        <i class="bi bi-funnel-fill text-blue-600 dark:text-blue-400 text-sm"></i>
                        <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Filtros Ativos</span>
                    </div>
                    @endif

                    <!-- Quick Stats -->
                    <div class="hidden lg:flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                            <i class="bi bi-check-circle"></i>
                            <span>{{ $sales->where('status', 'pago')->count() }} Pagas</span>
                        </div>
                        <div class="flex items-center gap-1 text-yellow-600 dark:text-yellow-400">
                            <i class="bi bi-clock"></i>
                            <span>{{ $sales->where('status', 'pendente')->count() }} Pendentes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <x-sales-filters
            :show-filters="false"
            :clients="$clients ?? collect()"
            :sellers="$sellers ?? collect()"
            :status-filter="$statusFilter"
            :client-filter="$clientFilter"
            :start-date="$startDate"
            :end-date="$endDate"
            :min-value="$minValue"
            :max-value="$maxValue"
            :payment-method-filter="$paymentMethodFilter"
            :seller-filter="$sellerFilter"
            :quick-filter="$quickFilter"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection" />

        <!-- Grid de Cards de Vendas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 mb-8">
            @forelse($sales as $sale)
                <x-sale-card :sale="$sale" />
            @empty
                <!-- Estado Vazio -->
                <div class="col-span-full">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div class="w-20 h-20 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-cart-x text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhuma venda encontrada
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if($search ?? false)
                                Não encontramos vendas com o termo "{{ $search }}".
                            @else
                                Comece registrando sua primeira venda.
                            @endif
                        </p>
                        <a href="{{ route('sales.create') }}"
                           class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar Nova Venda
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginação Moderna -->
        @if($sales->hasPages())
            <div class="flex justify-center mt-8">
                <div class="bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-600/50 p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-center">
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal ?? false)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="cancelDelete"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white dark:bg-zinc-800 rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-gray-200 dark:border-zinc-700">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 sm:mx-0 sm:h-10 sm:w-10 shadow-lg">
                        <i class="bi bi-exclamation-triangle text-white"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white" id="modal-title">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Tem certeza que deseja excluir a venda <strong>#{{ $deletingSale?->id }}</strong>?
                                Esta ação não pode ser desfeita e os produtos serão devolvidos ao estoque.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button"
                        wire:click="deleteSale"
                        class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-base font-medium text-white hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition-all duration-200 transform hover:scale-105">
                        <i class="bi bi-trash mr-2"></i>
                        Excluir Venda
                    </button>
                    <button type="button"
                        wire:click="cancelDelete"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-zinc-600 shadow-lg px-4 py-2 bg-white dark:bg-zinc-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-all duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
