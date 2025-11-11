@props([
    'title' => 'Livro Caixa',
    'description' => 'Controle financeiro inteligente',
    'totalTransactions' => 0,
    'totalBalance' => 0,
    'showQuickActions' => true
])

<!-- Header Moderno para Livro Caixa (estilo consistente com produtos/vendas) -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-md shadow-purple-400/20">
                    <i class="fas fa-chart-line text-white text-2xl"></i>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                        {{ $title }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $description }}</p>

                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-emerald-500/15 to-green-500/15 rounded-lg border border-emerald-200 dark:border-emerald-700">
                            <i class="fas fa-receipt text-emerald-600 dark:text-emerald-400 text-sm"></i>
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ $totalTransactions }} lançamentos</span>
                        </div>

                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-blue-500/15 to-indigo-500/15 rounded-lg border border-blue-200 dark:border-blue-700">
                            <i class="fas fa-wallet text-blue-600 dark:text-blue-400 text-sm"></i>
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">R$ {{ number_format($totalBalance, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($showQuickActions)
            <div class="flex flex-wrap gap-2 items-center">
                <a href="{{ route('cashbook.create') }}"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Nova</span>
                </a>

                <a href="{{ route('cashbook.upload') }}"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-upload mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Upload</span>
                </a>

                <button @click="showFilters = !showFilters"
                        class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                        :class="{'from-slate-700 to-slate-800': showFilters}">
                    <i class="bi bi-funnel-fill mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="hidden sm:inline text-sm">Filtros</span>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
