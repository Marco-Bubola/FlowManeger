<div x-data="{ showFilters: false, showDeleteModal: @entangle('showDeleteModal').live }" class=" w-full ">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="">

        <!-- Header Moderno -->
        <x-clients-index-header title="Clientes" :total-clients="$clients->total() ?? 0" :active-clients="$clients->where('status', 'ativo')->count() ?? 0" :premium-clients="$clients->where('type', 'premium')->count() ?? 0" :new-clients-this-month="$clients->where('created_at', '>=', now()->startOfMonth())->count() ?? 0"
            :show-quick-actions="true">

            <!-- Breadcrumb -->
            <x-slot name="breadcrumb">
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">
                        <i class="fas fa-users mr-1"></i>Clientes
                    </span>
                </div>
            </x-slot>

            <!-- Bloco de Controles Central -->
            <div class="w-full">
                <div class="flex flex-col gap-4">
                    <!-- Linha 1: Pesquisa e Contadores -->
                    <div class="flex items-center gap-4">
                        <!-- Input de Pesquisa -->
                        <div class="relative flex-1 group">
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Buscar clientes por nome, email, telefone ou cidade..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-600/50 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 dark:focus:border-purple-400 transition-all duration-300 shadow-sm backdrop-blur-sm text-sm font-medium">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-500 dark:text-slate-400 text-sm group-focus-within:text-purple-500 transition-colors duration-200"></i>
                            </div>
                            @if ($search)
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <button wire:click="$set('search', '')"
                                            class="group/clear p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-md transition-all duration-200"
                                            title="Limpar busca">
                                        <i class="bi bi-x-lg text-xs"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Contadores e Filtros -->
                        <div class="flex items-center gap-2">
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-slate-200/50 dark:border-slate-600/50 shadow-sm">
                                <i class="bi bi-people text-purple-600 dark:text-purple-400"></i>
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $clients->total() }} Clientes
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $clients->firstItem() ?? 0 }}-{{ $clients->lastItem() ?? 0 }}
                                </span>
                            </div>
                            <button @click="showFilters = !showFilters"
                                    class="p-2.5 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-slate-200/50 dark:border-slate-600/50 shadow-sm transition-all"
                                    :class="{ 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 ring-2 ring-purple-500': showFilters }"
                                    title="Filtros avançados">
                                <i class="bi bi-funnel text-base"></i>
                            </button>

                            <a href="{{ route('clients.create') }}"
                               class="flex items-center gap-2 p-2.5 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-slate-200/50 dark:border-slate-600/50 shadow-sm transition-all text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 hover:ring-2 hover:ring-purple-500"
                               title="Novo Cliente">
                                <i class="bi bi-plus-lg text-base"></i>
                                <span class="text-sm font-semibold">Cliente</span>
                            </a>
                        </div>
                    </div>

                    <!-- Linha 2: Ordenação e Paginação -->
                    <div class="flex items-center justify-between">
                        <!-- Paginação Compacta -->
                        @if ($clients->hasPages())
                            <div
                                class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 rounded-lg p-1 border border-slate-200/50 dark:border-slate-600/50">
                                @if ($clients->previousPageUrl())
                                    <a href="{{ $clients->previousPageUrl() }}"
                                       class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
                                       title="Página anterior">
                                        <i class="bi bi-chevron-left text-sm"></i>
                                    </a>
                                @endif
                                <div class="flex items-center px-3 py-1">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ $clients->currentPage() }} / {{ $clients->lastPage() }}
                                    </span>
                                </div>
                                @if ($clients->nextPageUrl())
                                    <a href="{{ $clients->nextPageUrl() }}"
                                       class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 rounded transition-all duration-200"
                                       title="Próxima página">
                                        <i class="bi bi-chevron-right text-sm"></i>
                                    </a>
                                @endif
                            </div>
                        @endif

                        <!-- Ordenação e Stats -->
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center gap-2 px-3 py-1.5 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-slate-200/50 dark:border-slate-600/50">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-300">
                                    Por
                                    {{ match ($sortBy ?? 'name') {'created_at' => 'Data','name' => 'Nome','email' => 'Email','status' => 'Status','phone' => 'Telefone','most_sales' => 'Compras',default => 'Nome'} }}
                                </span>
                                <button wire:click="toggleSort('{{ $sortBy }}')">
                                    <i class="bi bi-{{ ($sortDirection ?? 'asc') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                </button>
                            </div>
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
            </div>
</x-clients-index-header>


<!-- Filtros Avançados (usando showFilters do Alpine.js) -->
<div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-4"
    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50 mb-6">

    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center justify-between">
        <div class="flex items-center">
            <i class="bi bi-sliders text-purple-600 dark:text-purple-400 mr-2"></i>
            Filtros Avançados
        </div>
        <button wire:click="clearFilters"
            class="flex items-center gap-2 px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-all duration-200">
            <i class="bi bi-arrow-clockwise"></i>
            Limpar
        </button>
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Coluna Esquerda: Status e Período -->


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
        <!-- Ordenação -->
        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-arrow-up-down mr-2 text-emerald-500"></i>
                Ordenar por
            </h4>
            <div class="grid grid-cols-3 gap-1">
                <!-- Por Nome -->
                <button wire:click="toggleSort('name')"
                    class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 transition-all duration-200 {{ $sortBy === 'name' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                    <div class="text-center">
                        <i class="bi bi-person text-emerald-500 text-sm"></i>
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Nome</div>
                    </div>
                </button>

                <!-- Por Data -->
                <button wire:click="toggleSort('created_at')"
                    class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $sortBy === 'created_at' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                    <div class="text-center">
                        <i class="bi bi-calendar text-blue-500 text-sm"></i>
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Data</div>
                    </div>
                </button>

                <!-- Por Email -->
                <button wire:click="toggleSort('email')"
                    class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $sortBy === 'email' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                    <div class="text-center">
                        <i class="bi bi-envelope text-purple-500 text-sm"></i>
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Email</div>
                    </div>
                </button>

                <!-- Por Status -->
                <button wire:click="toggleSort('status')"
                    class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $sortBy === 'status' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                    <div class="text-center">
                        <i class="bi bi-flag text-orange-500 text-sm"></i>
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Status</div>
                    </div>
                </button>

                <!-- Por Telefone -->
                <button wire:click="toggleSort('phone')"
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


<!-- Grid de clientes -->
<div class="p-3">
    @if($clients->isEmpty())
        <div
            class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-8 text-center border border-dashed border-gray-300 dark:border-gray-700">
            <div class="mx-auto w-16 h-16 text-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-16 h-16">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhum cliente encontrado</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tente ajustar sua busca ou adicione um novo cliente para começar.
            </p>
            <div class="mt-6">
                <a href="{{ route('clients.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">
                    <i class="bi bi-plus-circle"></i>
                    <span>Adicionar Novo Cliente</span>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-6">
            @foreach ($clients as $client)
                <div x-data="{ expanded: false }"
                    class="bg-slate-800/90 backdrop-blur-sm border border-slate-700 hover:border-purple-500 rounded-xl overflow-hidden hover:shadow-2xl transition-all duration-300 group relative">
                    <!-- Checkbox de seleção -->
                    <div class="absolute top-2 left-2 z-10">
                        <input type="checkbox" wire:model.live="selectedClients" value="{{ $client->id }}"
                            class="rounded border-gray-600 text-purple-600 focus:ring-purple-500 bg-slate-700">
                    </div>
                    <!-- Header do card com gradiente - REDUZIDO -->
                    <div
                        class="h-16 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 relative overflow-hidden">
                        <div class="absolute inset-0 bg-black opacity-10"></div>
                        <div class="absolute top-2 right-2">
                            @if ($client->sales->count() >= 10)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow">
                                    <i class="bi bi-crown mr-1"></i>VIP
                                </span>
                            @elseif($client->sales->count() >= 5)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow">
                                    <i class="bi bi-star mr-1"></i>Premium
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow">
                                    <i class="bi bi-person mr-1"></i>Padrão
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Avatar centralizado - REDUZIDO -->
                    <div class="flex justify-center -mt-8 mb-2 relative z-10">
                        <div class="relative">
                            <img src="{{ $client->caminho_foto }}" alt="Avatar de {{ $client->name }}"
                                class="w-16 h-16 rounded-full border-4 border-slate-800 shadow-xl group-hover:scale-105 transition-transform duration-200">
                            <div
                                class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-r from-green-400 to-green-600 rounded-full border-2 border-slate-800 shadow">
                            </div>
                        </div>
                    </div>

                    <!-- Informações do cliente - COMPACTO -->
                    <div class="px-4 pb-4">
                        <!-- Nome - REDUZIDO -->
                        <div class="text-center mb-2">
                            <h3 class="text-base font-bold text-white truncate">{{ $client->name }}</h3>
                            <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
                                <span class="flex items-center">
                                    <i class="bi bi-envelope text-xs mr-1"></i>
                                    <span class="truncate max-w-[120px]">{{ $client->email ?: 'N/A' }}</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-center text-xs text-gray-400 mt-1">
                                <i class="bi bi-telephone text-xs mr-1"></i>
                                {{ $client->phone ?: 'N/A' }}
                            </div>
                        </div>

                        <!-- Estatísticas COMPACTAS - NOVO DESIGN MELHORADO -->
                        <div class="grid grid-cols-2 gap-3 py-3 mb-3">
                            <!-- Vendas -->
                            <div class="relative bg-gradient-to-br from-blue-500/10 to-purple-500/10 border border-blue-500/20 rounded-xl p-3 hover:border-blue-400 transition-all duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                                        <i class="bi bi-cart3 text-white text-sm"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-blue-400 uppercase tracking-wide">Vendas</span>
                                </div>
                                <p class="text-xl font-bold text-white mb-0.5">
                                    {{ $client->sales->count() }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-medium">
                                    R$ {{ number_format($client->sales->sum('total_price'), 0, ',', '.') }}
                                </p>
                            </div>

                            <!-- Consórcios -->
                            <div class="relative bg-gradient-to-br from-green-500/10 to-teal-500/10 border border-green-500/20 rounded-xl p-3 hover:border-green-400 transition-all duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                                        <i class="bi bi-building text-white text-sm"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-green-400 uppercase tracking-wide">Consórcios</span>
                                </div>
                                <p class="text-xl font-bold text-white mb-0.5">
                                    {{ $client->consortiumParticipants ? $client->consortiumParticipants->count() : 0 }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-medium">
                                    {{ $client->consortiumParticipants ? $client->consortiumParticipants->where('status', 'active')->count() : 0 }} ativos
                                </p>
                            </div>
                        </div>

                        <!-- 3 BOTÕES DE DASHBOARD NA MESMA LINHA - NOVO -->
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <a href="{{ route('clients.dashboard', $client->id) }}"
                                class="flex flex-col items-center justify-center px-2 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-br from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="bi bi-speedometer2 mb-1"></i>
                                <span class="text-[10px]">Dashboard</span>
                            </a>
                            <a href="{{ route('clients.resumo', $client->id) }}"
                                class="flex flex-col items-center justify-center px-2 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-br from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="bi bi-graph-up mb-1"></i>
                                <span class="text-[10px]">Resumo</span>
                            </a>
                            @if($client->consortiumParticipants && $client->consortiumParticipants->count() > 0)
                            <a href="{{ route('clients.consortiums', $client->id) }}"
                                class="flex flex-col items-center justify-center px-2 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-br from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="bi bi-building mb-1"></i>
                                <span class="text-[10px]">Consórcio</span>
                            </a>
                            @else
                            <button disabled
                                class="flex flex-col items-center justify-center px-2 py-2 text-xs font-semibold rounded-lg text-gray-500 bg-slate-700/50 cursor-not-allowed opacity-60">
                                <i class="bi bi-building mb-1"></i>
                                <span class="text-[10px]">Consórcio</span>
                            </button>
                            @endif
                        </div>

                        <!-- Ações Secundárias - COMPACTO -->
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <a href="{{ route('clients.edit', $client->id) }}"
                                class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-gray-300 bg-slate-700 hover:bg-slate-600 border border-slate-600 transition-all duration-200"
                                title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="mailto:{{ $client->email }}"
                                class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-blue-400 bg-blue-900/20 hover:bg-blue-900/30 border border-blue-700 transition-all duration-200"
                                title="Email">
                                <i class="bi bi-envelope"></i>
                            </a>
                            @if ($client->sales->count() == 0)
                            <button wire:click="confirmDelete({{ $client->id }})"
                                class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-red-400 bg-red-900/20 hover:bg-red-900/30 border border-red-700 transition-all duration-200"
                                title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                            @else
                            <button disabled
                                class="flex items-center justify-center px-2 py-2 text-xs font-medium rounded-lg text-gray-500 bg-slate-700/50 cursor-not-allowed opacity-50"
                                title="Cliente com vendas não pode ser excluído">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>

                        <!-- Ações Rápidas - LINHA 2 -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button wire:click="openExportModal({{ $client->id }})"
                                class="flex items-center justify-center px-3 py-2 text-purple-400 bg-purple-900/20 hover:bg-purple-900/30 rounded-lg border border-purple-700 transition-all duration-200">
                                <i class="bi bi-download mr-1.5"></i>
                                Export
                            </button>
                            @if($client->phone)
                            <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $client->phone) }}?text={{ urlencode('Olá ' . $client->name . ', tudo bem?') }}"
                                target="_blank"
                                class="flex items-center justify-center px-3 py-2 text-green-400 bg-green-900/20 hover:bg-green-900/30 rounded-lg border border-green-700 transition-all duration-200">
                                <i class="bi bi-whatsapp mr-1.5"></i>
                                WhatsApp
                            </a>
                            @else
                            <button disabled
                                class="flex items-center justify-center px-3 py-2 text-gray-500 bg-slate-700/50 rounded-lg cursor-not-allowed opacity-50">
                                <i class="bi bi-whatsapp mr-1.5"></i>
                                WhatsApp
                            </button>
                            @endif
                        </div>

                        </div>
                    </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Incluir Modais de Export (um para cada cliente) --}}
@include('livewire.clients._export-modal')

<!-- Modal de Confirmação de Exclusão -->
<div class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
    x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" wire:click="cancelDelete">
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
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-pink-500 to-purple-600">
            </div>

            <!-- Header do modal -->
            <div class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-slate-700/50">
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


</div>

