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

        <!-- Indicador de filtros ativos e ações -->
        <div class="flex items-center gap-3">
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque_filtro || $data_filtro)
            <div class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                <i class="bi bi-filter-circle text-blue-600 dark:text-blue-400"></i>
                <span class="font-semibold text-blue-800 dark:text-blue-300">{{ $totalProducts }} encontrados</span>
            </div>
            <button wire:click="clearFilters"
                    class="group flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-x-circle group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="hidden sm:inline">Limpar</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Grid de Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Busca Rápida -->
        <div class="md:col-span-2 lg:col-span-4">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg mr-3 shadow-md">
                    <i class="bi bi-search text-white text-sm"></i>
                </div>
                Busca Rápida
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="bi bi-search text-slate-400"></i>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="🔍 Pesquisar produtos, códigos ou descrições..."
                       class="w-full pl-12 pr-12 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                       {{ $search ? 'border-blue-400 focus:border-blue-500 focus:ring-blue-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                       focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                @if($search)
                <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-red-500 transition-colors duration-200">
                    <i class="bi bi-x-circle text-lg"></i>
                </button>
                @endif
            </div>
        </div>

        <!-- Categoria -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-tags text-white text-sm"></i>
                </div>
                Categoria
            </label>
            <select wire:model.live="category"
                    class="w-full p-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                    {{ $category ? 'border-purple-400 focus:border-purple-500 focus:ring-purple-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                    focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                <option value="">🏷️ Todas as categorias</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tipo -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-box-seam text-white text-sm"></i>
                </div>
                Tipo
            </label>
            <select wire:model.live="tipo"
                    class="w-full p-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                    {{ $tipo ? 'border-indigo-400 focus:border-indigo-500 focus:ring-indigo-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                    focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                <option value="">🔄 Todos os tipos</option>
                <option value="simples">📦 Produto Simples</option>
                <option value="kit">📦📦 Kit de Produtos</option>
            </select>
        </div>

        <!-- Status -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-activity text-white text-sm"></i>
                </div>
                Status
            </label>
            <select wire:model.live="status_filtro"
                    class="w-full p-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                    {{ $status_filtro ? 'border-emerald-400 focus:border-emerald-500 focus:ring-emerald-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-emerald-300' }}
                    focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                <option value="">📊 Todos os status</option>
                <option value="ativo">✅ Ativo</option>
                <option value="inativo">⏸️ Inativo</option>
                <option value="descontinuado">❌ Descontinuado</option>
            </select>
        </div>

        <!-- Ordenação -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-400 to-amber-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-sort-alpha-down text-white text-sm"></i>
                </div>
                Ordenação
            </label>
            <select wire:model.live="ordem"
                    class="w-full p-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                    {{ $ordem ? 'border-amber-400 focus:border-amber-500 focus:ring-amber-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-amber-500 focus:ring-amber-500/20 hover:border-amber-300' }}
                    focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                <option value="">📅 Ordem padrão</option>
                <option value="recentes">🆕 Mais recentes</option>
                <option value="antigas">📜 Mais antigas</option>
                <option value="az">🔤 A-Z</option>
                <option value="za">🔤 Z-A</option>
                <option value="preco_asc">💰 Menor preço</option>
                <option value="preco_desc">💎 Maior preço</option>
                <option value="estoque_asc">📦 Menor estoque</option>
                <option value="estoque_desc">📈 Maior estoque</option>
            </select>
        </div>

        <!-- Preço Mínimo -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-currency-dollar text-white text-sm"></i>
                </div>
                Preço Mínimo
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500 font-medium">R$</span>
                <input type="number"
                       wire:model.live="preco_min"
                       placeholder="0,00"
                       step="0.01"
                       min="0"
                       class="w-full pl-12 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                       {{ $preco_min ? 'border-green-400 focus:border-green-500 focus:ring-green-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-green-500 focus:ring-green-500/20 hover:border-green-300' }}
                       focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
            </div>
        </div>

        <!-- Preço Máximo -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-red-400 to-red-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-currency-dollar text-white text-sm"></i>
                </div>
                Preço Máximo
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500 font-medium">R$</span>
                <input type="number"
                       wire:model.live="preco_max"
                       placeholder="∞"
                       step="0.01"
                       min="0"
                       class="w-full pl-12 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                       {{ $preco_max ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-red-500 focus:ring-red-500/20 hover:border-red-300' }}
                       focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
            </div>
        </div>

        <!-- Itens por Página -->
        <div class="group">
            <label class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3 group-hover:text-slate-600 dark:group-hover:text-slate-400 transition-colors duration-300">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-slate-400 to-slate-600 rounded-lg mr-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-eye text-white text-sm"></i>
                </div>
                Por Página
            </label>
            <select wire:model.live="perPage"
                    class="w-full p-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                    border-slate-200 dark:border-slate-600 focus:border-slate-500 focus:ring-slate-500/20 hover:border-slate-300
                    focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                <option value="12">12 itens</option>
                <option value="24">24 itens</option>
                <option value="36">36 itens</option>
                <option value="48">48 itens</option>
            </select>
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
