@props([
    'categories' => collect(),
    'search' => '',
    'category' => '',
    'tipo' => '',
    'status_filtro' => '',
    'preco_min' => '',
    'preco_max' => '',
    'perPage' => 12,
    'ordem' => '',
    'estoque_filtro' => '',
    'data_filtro' => '',
    'totalProducts' => 0
])

<!-- Painel de Filtros Moderno -->
<div x-show="showFilters"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4"
     class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 mb-6">

    <!-- Header dos Filtros -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl shadow-lg">
                <i class="bi bi-funnel-fill text-white text-xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-purple-600 to-indigo-600 dark:from-slate-200 dark:via-purple-400 dark:to-indigo-400 bg-clip-text text-transparent">
                    Filtros Avançados
                </h3>
                <p class="text-slate-600 dark:text-slate-400">
                    Refine sua busca para encontrar produtos específicos
                </p>
            </div>
        </div>

        <!-- Filtros Rápidos -->
        <div class="flex items-center gap-2">
            <!-- Busca Rápida -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-slate-400 text-sm"></i>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar produtos..."
                       class="w-56 pl-10 pr-10 py-2 border rounded-lg bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                       {{ $search ? 'border-blue-400 focus:border-blue-500 focus:ring-blue-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                       focus:ring-2 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                @if($search)
                <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500 transition-colors duration-200">
                    <i class="bi bi-x-circle text-sm"></i>
                </button>
                @endif
            </div>

            <!-- Contador de filtros ativos -->
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque_filtro || $data_filtro)
            <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-lg border border-blue-200 dark:border-blue-700">
                <i class="bi bi-filter-circle text-blue-600 dark:text-blue-400"></i>
                <span class="font-semibold text-blue-800 dark:text-blue-300 text-sm">{{ $totalProducts }} encontrados</span>
            </div>
            <button wire:click="clearFilters"
                    class="group flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="bi bi-x-circle group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="text-sm">Limpar</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Grid de Filtros Modernizado e Compacto -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Coluna Esquerda: Categoria, Tipo e Status -->
        <div class="space-y-4">

            <!-- Categoria -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-tags mr-2 text-purple-500"></i>
                    Categoria
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- Todas as categorias -->
                    <button wire:click="$set('category', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $category === '' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-list-ul text-purple-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Todas</span>
                        </div>
                    </button>

                    @foreach($categories->take(5) as $cat)
                    <button wire:click="$set('category', '{{ $cat->id_category }}')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $category == $cat->id_category ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="{{ $cat->icone ?? 'bi bi-tag' }} text-purple-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ Str::limit($cat->name, 8) }}</span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Tipo -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-box-seam mr-2 text-indigo-500"></i>
                    Tipo de Produto
                </h4>
                <div class="grid grid-cols-3 gap-2">
                    <!-- Todos os tipos -->
                    <button wire:click="$set('tipo', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 {{ $tipo === '' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-list-ul text-indigo-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todos</div>
                        </div>
                    </button>

                    <!-- Produto Simples -->
                    <button wire:click="$set('tipo', 'simples')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $tipo === 'simples' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-box text-blue-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Simples</div>
                        </div>
                    </button>

                    <!-- Kit -->
                    <button wire:click="$set('tipo', 'kit')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $tipo === 'kit' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-boxes text-green-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Kit</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-activity mr-2 text-emerald-500"></i>
                    Status
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- Todos os status -->
                    <button wire:click="$set('status_filtro', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 transition-all duration-200 {{ $status_filtro === '' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-list-ul text-emerald-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Todos</span>
                        </div>
                    </button>

                    <!-- Ativo -->
                    <button wire:click="$set('status_filtro', 'ativo')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $status_filtro === 'ativo' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-check-circle text-green-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Ativo</span>
                        </div>
                    </button>

                    <!-- Inativo -->
                    <button wire:click="$set('status_filtro', 'inativo')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all duration-200 {{ $status_filtro === 'inativo' ? 'ring-2 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-pause-circle text-yellow-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Inativo</span>
                        </div>
                    </button>

                    <!-- Descontinuado -->
                    <button wire:click="$set('status_filtro', 'descontinuado')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 {{ $status_filtro === 'descontinuado' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-x-circle text-red-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Descontinuado</span>
                        </div>
                    </button>
                </div>
            </div>

        </div>

        <!-- Coluna Direita: Preços, Ordenação e Paginação -->
        <div class="space-y-4">

            <!-- Faixa de Preços -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-currency-dollar mr-2 text-green-500"></i>
                    Faixa de Preços
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- Preço Mínimo -->
                    <div class="relative">
                        <input type="number"
                               wire:model.live="preco_min"
                               placeholder="0,00"
                               step="0.01"
                               min="0"
                               class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $preco_min ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-green-600 dark:text-green-400">
                            Mínimo
                        </label>
                    </div>

                    <!-- Preço Máximo -->
                    <div class="relative">
                        <input type="number"
                               wire:model.live="preco_max"
                               placeholder="∞"
                               step="0.01"
                               min="0"
                               class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $preco_max ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-green-600 dark:text-green-400">
                            Máximo
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ordenação -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-sort-alpha-down mr-2 text-amber-500"></i>
                    Ordenar por
                </h4>
                <div class="grid grid-cols-3 gap-1">
                    <!-- Padrão -->
                    <button wire:click="$set('ordem', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-amber-300 dark:hover:border-amber-500 transition-all duration-200 {{ $ordem === '' ? 'ring-2 ring-amber-500 bg-amber-50 dark:bg-amber-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-list-ul text-amber-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Padrão</div>
                        </div>
                    </button>

                    <!-- Recentes -->
                    <button wire:click="$set('ordem', 'recentes')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $ordem === 'recentes' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-clock text-blue-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Recentes</div>
                        </div>
                    </button>

                    <!-- A-Z -->
                    <button wire:click="$set('ordem', 'az')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $ordem === 'az' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-sort-alpha-down text-purple-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">A-Z</div>
                        </div>
                    </button>

                    <!-- Menor preço -->
                    <button wire:click="$set('ordem', 'preco_asc')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $ordem === 'preco_asc' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-arrow-up text-green-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Menor €</div>
                        </div>
                    </button>

                    <!-- Maior preço -->
                    <button wire:click="$set('ordem', 'preco_desc')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 {{ $ordem === 'preco_desc' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-arrow-down text-red-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Maior €</div>
                        </div>
                    </button>

                    <!-- Estoque -->
                    <button wire:click="$set('ordem', 'estoque_desc')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 {{ $ordem === 'estoque_desc' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : '' }}">
                        <div class="text-center">
                            <i class="bi bi-stack text-teal-500 text-sm"></i>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Estoque</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Itens por página -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                    <i class="bi bi-eye mr-2 text-pink-500"></i>
                    Itens por Página
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- 12 por página -->
                    <button wire:click="$set('perPage', '12')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage == '12' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid-3x3 text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">12</span>
                        </div>
                    </button>

                    <!-- 24 por página -->
                    <button wire:click="$set('perPage', '24')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage == '24' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">24</span>
                        </div>
                    </button>

                    <!-- 36 por página -->
                    <button wire:click="$set('perPage', '36')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage == '36' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-grid-1x2 text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">36</span>
                        </div>
                    </button>

                    <!-- 48 por página -->
                    <button wire:click="$set('perPage', '48')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage == '48' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="bi bi-list-ul text-pink-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">48</span>
                        </div>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Ações dos Filtros -->
    <div class="mt-8 flex flex-wrap gap-4 justify-between items-center pt-6 border-t border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-3">
            <button wire:click="clearFilters"
                    class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-x-circle group-hover:rotate-90 transition-transform duration-200"></i>
                Limpar Filtros
            </button>

            <button class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-star group-hover:scale-110 transition-transform duration-200"></i>
                Salvar Filtro
            </button>
        </div>

        <div class="flex gap-3">
            <button class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-file-earmark-arrow-down group-hover:scale-110 transition-transform duration-200"></i>
                Exportar Resultado
            </button>
        </div>
    </div>
</div>
