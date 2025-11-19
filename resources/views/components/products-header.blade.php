@props([
    'title' => 'Catálogo de Produtos',
    'description' => '',
    'totalProducts' => 0,
    'totalCategories' => 0,
    'showQuickActions' => true
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Título e Estatísticas -->
            <div class="flex items-center gap-6">
                <!-- Ícone principal -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="bi bi-boxes text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>

                    <!-- Estatísticas modernas -->
                    <div class="flex items-center gap-6 text-lg">
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg">
                                <i class="bi bi-box-seam text-white text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $totalProducts }}</span>
                            <span class="text-slate-600 dark:text-slate-400">produtos</span>
                        </div>

                        <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-lg">
                                <i class="bi bi-tags text-white text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $totalCategories }}</span>
                            <span class="text-slate-600 dark:text-slate-400">categorias</span>
                        </div>
                    </div>

                    @if($description)
                    <p class="text-slate-600 dark:text-slate-400 font-medium">
                        {{ $description }}
                    </p>
                    @endif
                </div>
            </div>

            @if($showQuickActions)
            <!-- Ações Rápidas Principais -->
            <div class="flex flex-wrap gap-3 items-center">
                <a href="{{ route('products.create') }}"
                   class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="bi bi-plus-lg mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Novo Produto
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-xl bg-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>

                <a href="{{ route('products.kit.create') }}"
                   class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="bi bi-boxes mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Novo Kit
                </a>

                <a href="{{ route('products.upload') }}"
                   class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="bi bi-file-earmark-arrow-up mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Upload
                </a>

                <!-- Botão de filtros -->
                <button @click="showFilters = !showFilters"
                    class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 dark:from-slate-600 dark:to-slate-700 dark:hover:from-slate-500 dark:hover:to-slate-600 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    :class="{'from-purple-600 to-purple-700 dark:from-purple-500 dark:to-purple-600': showFilters}">
                    <i class="bi bi-funnel-fill mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="hidden sm:inline">Filtros</span>
                    <!-- Indicador de filtros ativos -->
                    <span x-show="hasActiveFilters" class="ml-2 w-2 h-2 bg-red-400 rounded-full animate-pulse"></span>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
