<div x-data="{ showFilters: false, showDeleteModal: @entangle('showDeleteModal').live }" class=" w-full ">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="">

        <!-- Header Moderno -->
        <x-clients-index-header title="Clientes" :total-clients="$clients->total() ?? 0" :active-clients="$clients->where('status', 'ativo')->count() ?? 0" :premium-clients="$clients->where('type', 'premium')->count() ?? 0" :new-clients-this-month="$clients->where('created_at', '>=', now()->startOfMonth())->count() ?? 0"
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
                                   placeholder="Buscar clientes por nome, email, telefone ou cidade..."
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

                            <!-- Botão limpar (visibilidade controlada por Blade para evitar expressão Alpine problemática) -->
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                @if($search)
                                    <button wire:click="$set('search', '')"
                                        class="group/clear p-1.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500
                                                   text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white
                                                   rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                        title="Limpar busca">
                                        <i class="bi bi-x-lg text-xs group-hover/clear:rotate-90 transition-transform duration-200"></i>
                                    </button>
                                @endif
                            </div>

    <!-- Indicador de carregamento -->
    <div wire:loading.delay wire:target="search" class="absolute right-12 top-1/2 transform -translate-y-1/2">
        <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
    </div>

    <!-- Efeito de brilho -->
    <div
        class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 via-transparent to-blue-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none">
    </div>
</div>
</div>
</div>

</div>
<!-- Lado Direito: Informações + Paginação + Controles (50%) -->
<div class="flex items-center gap-4">

    <!-- Contador de Resultados -->
    <div class="flex items-center gap-2">
        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg text-white">
            <i class="bi bi-people text-base"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                @if ($clients->total())
                    {{ $clients->total() }} {{ $clients->total() === 1 ? 'Cliente' : 'Clientes' }}
                @else
                    Nenhum cliente
                @endif
            </h3>
            @if ($clients->total() > 0)
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    {{ $clients->firstItem() ?? 0 }} - {{ $clients->lastItem() ?? 0 }}
                </p>
            @endif
        </div>
    </div>

    <!-- Paginação Compacta -->
    @if ($clients->hasPages())
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
            <!-- Primeira/Anterior -->
            @if ($clients->currentPage() > 1)
                <a href="{{ $clients->url(1) }}"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
                    title="Primeira página">
                    <i class="bi bi-chevron-double-left text-sm"></i>
                </a>
            @endif

            @if ($clients->previousPageUrl())
                <a href="{{ $clients->previousPageUrl() }}"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
                    title="Página anterior">
                    <i class="bi bi-chevron-left text-sm"></i>
                </a>
            @endif

            <!-- Páginas -->
            <div class="flex items-center px-3 py-1">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ $clients->currentPage() }} / {{ $clients->lastPage() }}
                </span>
            </div>

            <!-- Próxima/Última -->
            @if ($clients->nextPageUrl())
                <a href="{{ $clients->nextPageUrl() }}"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
                    title="Próxima página">
                    <i class="bi bi-chevron-right text-sm"></i>
                </a>
            @endif

            @if ($clients->currentPage() < $clients->lastPage())
                <a href="{{ $clients->url($clients->lastPage()) }}"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
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

        <button wire:click="setQuickSearch('ativo')"
            class="group px-3 py-1 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50
                                   text-green-700 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-green-200 dark:border-green-700">
            <i class="bi bi-person-check mr-1"></i>
            Ativos
        </button>

        <button wire:click="setQuickSearch('premium')"
            class="group px-3 py-1 bg-purple-100 hover:bg-purple-200 dark:bg-purple-900/30 dark:hover:bg-purple-900/50
                                   text-purple-700 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-purple-200 dark:border-purple-700">
            <i class="bi bi-star mr-1"></i>
            Premium
        </button>

        <button wire:click="setQuickSearch('recente')"
            class="group px-3 py-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50
                                   text-blue-700 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-blue-200 dark:border-blue-700">
            <i class="bi bi-clock mr-1"></i>
            Recentes
        </button>

        <button wire:click="setQuickSearch('mais_compras')"
            class="group px-3 py-1 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50
                                   text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-emerald-200 dark:border-emerald-700">
            <i class="bi bi-graph-up mr-1"></i>
            Mais Compras
        </button>

        <button wire:click="setQuickSearch('inativos')"
            class="group px-3 py-1 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50
                                   text-red-700 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300
                                   text-xs font-medium rounded-lg transition-all duration-200 border border-red-200 dark:border-red-700">
            <i class="bi bi-person-x mr-1"></i>
            Inativos
        </button>
    </div>

    <!-- Indicadores de Status e Ordenação -->
    <div class="flex items-center gap-3">
        <!-- Indicador de Ordenação Atual -->
        <div
            class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 rounded-xl border border-indigo-200 dark:border-indigo-700">
            <i class="bi bi-arrow-up-down text-indigo-600 dark:text-indigo-400 text-sm"></i>
            <span class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">
                {{ match ($sortBy ?? 'name') {
                    'created_at' => 'Por Data',
                    'name' => 'Por Nome',
                    'email' => 'Por Email',
                    'status' => 'Por Status',
                    'phone' => 'Por Telefone',
                    default => 'Por Nome',
                } }}
                <i class="bi bi-{{ ($sortDirection ?? 'asc') === 'asc' ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
            </span>
        </div>

        @if ($search || ($statusFilter ?? false) || ($clientFilter ?? false) || ($startDate ?? false) || ($endDate ?? false))
            <div
                class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                <i class="bi bi-funnel-fill text-blue-600 dark:text-blue-400 text-sm"></i>
                <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Filtros Ativos</span>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="hidden lg:flex items-center gap-4 text-sm">
            <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                <i class="bi bi-person-check"></i>
                <span>{{ $clients->where('status', 'ativo')->count() }} Ativos</span>
            </div>
            <div class="flex items-center gap-1 text-purple-600 dark:text-purple-400">
                <i class="bi bi-star"></i>
                <span>{{ $clients->where('type', 'premium')->count() }} Premium</span>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Filtros Avançados (usando showFilters do Alpine.js) -->
<div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-4"
    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50 mb-6">

    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center">
        <i class="bi bi-sliders text-purple-600 dark:text-purple-400 mr-2"></i>
        Filtros Avançados
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Coluna Esquerda: Status e Período -->
        <div class="space-y-4">

            <!-- Status do Cliente -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-person-check mr-2 text-purple-500"></i>
                    Status do Cliente
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- Todos os Status -->
                    <button wire:click="$set('statusFilter', '')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $statusFilter === '' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-list-ul text-purple-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Todos</span>
                        </div>
                    </button>

                    <!-- Ativo -->
                    <button wire:click="$set('statusFilter', 'ativo')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $statusFilter === 'ativo' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-person-check text-green-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Ativo</span>
                        </div>
                    </button>

                    <!-- Inativo -->
                    <button wire:click="$set('statusFilter', 'inativo')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 {{ $statusFilter === 'inativo' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-person-x text-red-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Inativo</span>
                        </div>
                    </button>

                    <!-- Premium -->
                    <button wire:click="$set('statusFilter', 'premium')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all duration-200 {{ $statusFilter === 'premium' ? 'ring-2 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-star text-yellow-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Premium</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Período -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-calendar-range mr-2 text-indigo-500"></i>
                    Período de Cadastro
                </h4>
                <div class="grid grid-cols-3 gap-1">
                    <!-- Qualquer período -->
                    <button wire:click="$set('dateFilter', '')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 {{ $dateFilter === '' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-list-ul text-indigo-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todos</div>
                        </div>
                    </button>

                    <!-- Hoje -->
                    <button wire:click="$set('dateFilter', 'hoje')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $dateFilter === 'hoje' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-calendar-day text-blue-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Hoje</div>
                        </div>
                    </button>

                    <!-- Esta semana -->
                    <button wire:click="$set('dateFilter', 'semana')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $dateFilter === 'semana' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-calendar-week text-green-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Semana</div>
                        </div>
                    </button>

                    <!-- Este mês -->
                    <button wire:click="$set('dateFilter', 'mes')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $dateFilter === 'mes' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-calendar-month text-purple-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Mês</div>
                        </div>
                    </button>

                    <!-- Este ano -->
                    <button wire:click="$set('dateFilter', 'ano')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $dateFilter === 'ano' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-calendar text-orange-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Ano</div>
                        </div>
                    </button>
                </div>
            </div>

        </div>

        <!-- Coluna Direita: Ordenação e Paginação -->
        <div class="space-y-4">

            <!-- Ordenação -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-arrow-up-down mr-2 text-emerald-500"></i>
                    Ordenar por
                </h4>
                <div class="grid grid-cols-3 gap-1">
                    <!-- Por Nome -->
                    <button wire:click="$set('sortBy', 'name')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 transition-all duration-200 {{ $sortBy === 'name' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-person text-emerald-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Nome</div>
                        </div>
                    </button>

                    <!-- Por Data -->
                    <button wire:click="$set('sortBy', 'created_at')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $sortBy === 'created_at' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-calendar text-blue-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Data</div>
                        </div>
                    </button>

                    <!-- Por Email -->
                    <button wire:click="$set('sortBy', 'email')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $sortBy === 'email' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-envelope text-purple-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Email</div>
                        </div>
                    </button>

                    <!-- Por Status -->
                    <button wire:click="$set('sortBy', 'status')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $sortBy === 'status' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-flag text-orange-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Status</div>
                        </div>
                    </button>

                    <!-- Por Telefone -->
                    <button wire:click="$set('sortBy', 'phone')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 {{ $sortBy === 'phone' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-telephone text-teal-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Telefone</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Itens por página -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-grid mr-2 text-pink-500"></i>
                    Itens por Página
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- 12 por página -->
                    <button wire:click="$set('perPage', '12')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '12' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid-3x3 text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">12</span>
                        </div>
                    </button>

                    <!-- 24 por página -->
                    <button wire:click="$set('perPage', '24')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '24' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">24</span>
                        </div>
                    </button>

                    <!-- 48 por página -->
                    <button wire:click="$set('perPage', '48')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '48' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid-1x2 text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">48</span>
                        </div>
                    </button>

                    <!-- 96 por página -->
                    <button wire:click="$set('perPage', '96')"
                        class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '96' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-list-ul text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">96</span>
                        </div>
                    </button>
                </div>
            </div>

        </div>

    </div> <!-- Botões de Ação dos Filtros -->
    <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
        <button wire:click="clearFilters"
            class="flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200
                               bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-all duration-200">
            <i class="bi bi-arrow-clockwise"></i>
            Limpar Filtros
        </button>

        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
            <i class="bi bi-info-circle"></i>
            <span>{{ $clients->total() }} cliente(s) encontrado(s)</span>
        </div>
    </div>
</div>

<!-- Painel de Ações em Massa -->
@if (count($selectedClients ?? []) > 0)
    <div
        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-2xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center text-blue-700 dark:text-blue-300">
                <i class="bi bi-check-square text-lg mr-2"></i>
                <span class="font-medium">{{ count($selectedClients) }} cliente(s) selecionado(s)</span>
            </div>
            <div class="flex gap-2">
                <button wire:click="bulkExport"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-download mr-1"></i>
                    Exportar
                </button>
                <button wire:click="bulkDelete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-trash mr-1"></i>
                    Excluir
                </button>
                <button wire:click="$set('selectedClients', [])"
                    class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-x mr-1"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Grid de clientes -->
<div class="p-3">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($clients as $client)
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 group relative transform hover:-translate-y-1">
                <!-- Checkbox de seleção -->
                <div class="absolute top-3 left-3 z-10">
                    <input type="checkbox" wire:model.live="selectedClients" value="{{ $client->id }}"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-gray-700">
                </div>
                <!-- Header do card com gradiente -->
                <div
                    class="h-20 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="absolute top-3 right-3">
                        @if ($client->sales->count() >= 10)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg">
                                <i class="bi bi-crown mr-1"></i>VIP
                            </span>
                        @elseif($client->sales->count() >= 5)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
                                <i class="bi bi-star mr-1"></i>Premium
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg">
                                <i class="bi bi-person mr-1"></i>Padrão
                            </span>
                        @endif
                    </div>
                    <!-- Pattern decorativo -->
                    <div class="absolute -top-4 -right-4 w-16 h-16 bg-white opacity-10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-12 h-12 bg-white opacity-10 rounded-full"></div>

                    <!-- ID do Cliente -->
                    <div class="absolute top-3 left-3">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-mono bg-white/20 text-white border border-white/30">
                            ID: {{ $client->id }}
                        </span>
                    </div>
                </div>

                <!-- Avatar centralizado -->
                <div class="flex justify-center -mt-10 mb-4 relative z-10">
                    <div class="relative">
                        <img src="{{ $client->caminho_foto }}" alt="Avatar de {{ $client->name }}"
                            class="w-20 h-20 rounded-full border-4 border-white dark:border-gray-800 shadow-xl group-hover:scale-110 transition-transform duration-300">
                        <div
                            class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-r from-green-400 to-green-600 rounded-full border-2 border-white dark:border-gray-800 shadow-lg">
                        </div>
                    </div>
                </div>

                <!-- Informações do cliente -->
                <div class="px-6 pb-6">
                    <!-- Nome e data -->
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $client->name }}</h3>
                        <div
                            class="flex items-center justify-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
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
                        @if ($client->email)
                            <div
                                class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                <div
                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="bi bi-envelope text-blue-600 dark:text-blue-400 text-xs"></i>
                                </div>
                                <span class="truncate">{{ $client->email }}</span>
                                <button class="ml-auto p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"
                                    title="Copiar email">
                                    <i class="bi bi-copy text-xs"></i>
                                </button>
                            </div>
                        @endif
                        @if ($client->phone)
                            <div
                                class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                <div
                                    class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="bi bi-telephone text-green-600 dark:text-green-400 text-xs"></i>
                                </div>
                                <span>{{ $client->phone }}</span>
                                <a href="tel:{{ $client->phone }}"
                                    class="ml-auto p-1 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                                    title="Ligar">
                                    <i class="bi bi-telephone-fill text-xs"></i>
                                </a>
                            </div>
                        @endif
                        @if ($client->address)
                            <div
                                class="flex items-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                <div
                                    class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="bi bi-geo-alt text-purple-600 dark:text-purple-400 text-xs"></i>
                                </div>
                                <span class="truncate">{{ Str::limit($client->address, 30) }}</span>
                                <button
                                    class="ml-auto p-1 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400"
                                    title="Ver no mapa">
                                    <i class="bi bi-map text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Estatísticas melhoradas -->
                    <div
                        class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700/50 dark:to-blue-900/20 rounded-xl p-4 mb-5 border border-gray-100 dark:border-gray-600">
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div class="text-center">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-lg">
                                    <i class="bi bi-cart text-white"></i>
                                </div>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $client->sales->count() }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $client->sales->count() === 1 ? 'Compra' : 'Compras' }}
                                </p>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-lg">
                                    <i class="bi bi-currency-dollar text-white"></i>
                                </div>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">R$
                                    {{ number_format($client->sales->sum('total_price'), 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Gasto</p>
                            </div>
                        </div>

                        <!-- Métricas adicionais -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-white dark:bg-gray-800/50 rounded-lg p-2 text-center">
                                <p class="font-semibold text-orange-600 dark:text-orange-400">
                                    R$
                                    {{ $client->sales->count() > 0 ? number_format($client->sales->sum('total_price') / $client->sales->count(), 0, ',', '.') : '0' }}
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

                        @if ($client->sales->count() > 0)
                            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <i class="bi bi-clock mr-1"></i>
                                        Última compra:
                                    </span>
                                    <span
                                        class="font-semibold text-gray-700 dark:text-gray-300">{{ $client->sales->sortByDesc('created_at')->first()?->created_at?->diffForHumans() ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs mt-1">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <i class="bi bi-activity mr-1"></i>
                                        Status:
                                    </span>
                                    <span
                                        class="font-semibold {{ $client->sales->where('created_at', '>=', now()->subDays(30))->count() > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
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
                            <span
                                class="absolute -top-1 -right-1 bg-gradient-to-r from-orange-400 to-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full shadow-lg animate-bounce">
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
                            @if($client->sales->count() == 0)
                            <button wire:click="confirmDelete({{ $client->id }})"
                                class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-700 transition-all duration-200"
                                title="Excluir Cliente">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>

                        <!-- Ações rápidas -->
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <button
                                    class="flex items-center justify-center px-3 py-1.5 text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <i class="bi bi-whatsapp mr-1 text-green-600"></i>
                                    WhatsApp
                                </button>
                                <a href="{{ route('sales.create') }}?client_id={{ $client->id }}"
                                    class="flex items-center justify-center px-3 py-1.5 text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
                                    title="Nova Venda para {{ $client->name }}">
                                    <i class="bi bi-plus-circle mr-1 text-blue-600"></i>
                                    Nova Venda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
    <div class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" wire:click="cancelDelete">
        <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100"
            x-transition:enter="transition ease-out duration-300 delay-75"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4" wire:click.stop>
            <div
                class="relative bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/30 dark:border-slate-700/50 overflow-hidden">
                <!-- Gradiente decorativo no topo -->
                <div
                    class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-pink-500 to-purple-600">
                </div>

                <!-- Header do modal -->
                <div
                    class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-slate-700/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-shield-exclamation text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            Confirmar Exclusão
                        </h3>
                    </div>
                    <button type="button" wire:click="cancelDelete"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100/50 hover:bg-slate-200/70 dark:bg-slate-700/50 dark:hover:bg-slate-600/70 rounded-xl text-sm w-10 h-10 flex justify-center items-center transition-all duration-200"
                        title="Fechar">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <!-- Conteúdo do modal -->
                <div class="p-6 text-center">
                    <!-- Ícone central com animação -->
                    <div class="relative mx-auto mb-6">
                        <div
                            class="w-20 h-20 mx-auto bg-gradient-to-br from-red-100 to-pink-100 dark:from-red-900/40 dark:to-pink-900/40 rounded-full flex items-center justify-center shadow-xl ring-4 ring-red-100 dark:ring-red-900/30">
                            <i class="bi bi-person-x text-red-600 dark:text-red-400 text-3xl animate-pulse"></i>
                        </div>
                        <!-- Ícones decorativos orbitando -->
                        <div
                            class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                            <i class="bi bi-exclamation text-white text-sm font-bold"></i>
                        </div>
                        <div
                            class="absolute -bottom-2 -left-2 w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                            <i class="bi bi-trash text-white text-sm"></i>
                        </div>
                    </div>

                    <!-- Título e descrição -->
                    <h3 class="mb-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                        Excluir Cliente?
                    </h3>
                    <p class="mb-6 text-slate-600 dark:text-slate-400 leading-relaxed">
                        Esta ação não pode ser desfeita. Todas as informações e histórico deste cliente serão <span
                            class="font-semibold text-red-600 dark:text-red-400">permanentemente removidos</span>.
                    </p>

                    <!-- Alertas adicionais -->
                    <div
                        class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-200 dark:border-amber-700/50">
                        <div class="flex items-center justify-center gap-2 text-amber-700 dark:text-amber-400">
                            <i class="bi bi-info-circle text-lg"></i>
                            <span class="text-sm font-medium">Dados que serão perdidos:</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs text-amber-600 dark:text-amber-500">
                            <div class="flex items-center gap-1">
                                <i class="bi bi-person-circle"></i>
                                <span>Informações pessoais</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="bi bi-receipt"></i>
                                <span>Histórico de vendas</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="bi bi-cash-coin"></i>
                                <span>Dados financeiros</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="bi bi-graph-up"></i>
                                <span>Relatórios e métricas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="flex gap-3">
                        <button wire:click="cancelDelete" type="button"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-slate-700 dark:text-slate-300 bg-slate-100/70 dark:bg-slate-700/70 hover:bg-slate-200/80 dark:hover:bg-slate-600/80 rounded-xl font-semibold transition-all duration-200 backdrop-blur-sm border border-slate-200/50 dark:border-slate-600/50 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-shield-check text-lg"></i>
                            <span>Manter Cliente</span>
                        </button>
                        <button wire:click="deleteClient" type="button"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 ring-2 ring-red-500/20 focus:ring-4 focus:ring-red-500/40">
                            <i class="bi bi-trash-fill text-lg"></i>
                            <span>Confirmar Exclusão</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificações Toast -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div
                class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-green-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('message') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false"
                            class="text-green-200 hover:text-white transition-colors duration-200">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div
                class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-red-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false"
                            class="text-red-200 hover:text-white transition-colors duration-200">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Importação -->
    <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center">
                    <i class="bi bi-upload text-green-500 mr-2"></i>
                    Importar Clientes
                </h3>
                <button onclick="document.getElementById('importModal').style.display='none'"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Selecione o arquivo CSV
                    </label>
                    <input type="file" accept=".csv"
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Formato do arquivo:</h4>
                    <p class="text-xs text-blue-700 dark:text-blue-400">
                        O arquivo deve conter as colunas: nome, email, telefone, endereço, cidade
                    </p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button onclick="document.getElementById('importModal').style.display='none'"
                    class="flex-1 px-4 py-2 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Cancelar
                </button>
                <button class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Importar
                </button>
            </div>
        </div>
    </div>

    <!-- Navegação de Paginação no Final da Página -->
    @if ($clients->hasPages())
        <div
            class="mt-8 bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Informações da Paginação -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg text-white">
                        <i class="bi bi-people text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">
                            Página {{ $clients->currentPage() }} de {{ $clients->lastPage() }}
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Mostrando {{ $clients->firstItem() ?? 0 }} - {{ $clients->lastItem() ?? 0 }} de
                            {{ $clients->total() }} {{ $clients->total() === 1 ? 'cliente' : 'clientes' }}
                        </p>
                    </div>
                </div>

                <!-- Controles de Navegação -->
                <div class="flex items-center gap-2">
                    <!-- Primeira Página -->
                    @if ($clients->currentPage() > 1)
                        <a href="{{ $clients->url(1) }}"
                            class="group p-3 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-indigo-500 hover:to-purple-600 dark:from-slate-700 dark:to-slate-600 dark:hover:from-indigo-500 dark:hover:to-purple-600 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Primeira página">
                            <i
                                class="bi bi-chevron-double-left text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 rounded-xl">
                            <i class="bi bi-chevron-double-left text-lg"></i>
                        </div>
                    @endif

                    <!-- Página Anterior -->
                    @if ($clients->previousPageUrl())
                        <a href="{{ $clients->previousPageUrl() }}"
                            class="group p-3 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-blue-500 hover:to-indigo-600 dark:from-slate-700 dark:to-slate-600 dark:hover:from-blue-500 dark:hover:to-indigo-600 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Página anterior">
                            <i
                                class="bi bi-chevron-left text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 rounded-xl">
                            <i class="bi bi-chevron-left text-lg"></i>
                        </div>
                    @endif

                    <!-- Páginas Numeradas -->
                    <div
                        class="hidden sm:flex items-center gap-1 px-4 py-2 bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-xl border border-purple-200 dark:border-purple-700">
                        @php
                            $start = max(1, $clients->currentPage() - 2);
                            $end = min($clients->lastPage(), $clients->currentPage() + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $clients->currentPage())
                                <div
                                    class="px-3 py-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold rounded-lg shadow-lg">
                                    {{ $i }}
                                </div>
                            @else
                                <a href="{{ $clients->url($i) }}"
                                    class="px-3 py-1 text-slate-600 hover:text-purple-600 dark:text-slate-300 dark:hover:text-purple-400 hover:bg-white/50 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor
                    </div>

                    <!-- Próxima Página -->
                    @if ($clients->nextPageUrl())
                        <a href="{{ $clients->nextPageUrl() }}"
                            class="group p-3 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-blue-500 hover:to-indigo-600 dark:from-slate-700 dark:to-slate-600 dark:hover:from-blue-500 dark:hover:to-indigo-600 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Próxima página">
                            <i
                                class="bi bi-chevron-right text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 rounded-xl">
                            <i class="bi bi-chevron-right text-lg"></i>
                        </div>
                    @endif

                    <!-- Última Página -->
                    @if ($clients->currentPage() < $clients->lastPage())
                        <a href="{{ $clients->url($clients->lastPage()) }}"
                            class="group p-3 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-indigo-500 hover:to-purple-600 dark:from-slate-700 dark:to-slate-600 dark:hover:from-indigo-500 dark:hover:to-purple-600 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Última página">
                            <i
                                class="bi bi-chevron-double-right text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 rounded-xl">
                            <i class="bi bi-chevron-double-right text-lg"></i>
                        </div>
                    @endif
                </div>

                <!-- Seletor de Itens por Página -->
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Por página:</label>
                    <select wire:model.live="perPage"
                        class="px-3 py-2 bg-gradient-to-r from-white to-slate-50 dark:from-slate-700 dark:to-slate-600 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 font-medium focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="36">36</option>
                        <option value="48">48</option>
                    </select>
                </div>
            </div>
        </div>
    @endif
</div>

