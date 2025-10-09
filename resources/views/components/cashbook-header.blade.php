@props([
    'title' => 'Livro Caixa',
    'description' => 'Controle financeiro inteligente',
    'totalTransactions' => 0,
    'totalBalance' => 0,
    'showQuickActions' => true
])

<!-- Header Moderno para Livro Caixa (estilo consistente com produtos/vendas) -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-6">
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="fas fa-chart-line text-white text-3xl"></i>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400">{{ $description }}</p>

                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                            <i class="fas fa-receipt text-emerald-600 dark:text-emerald-400"></i>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $totalTransactions }} lançamentos</span>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                            <i class="fas fa-wallet text-blue-600 dark:text-blue-400"></i>
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">R$ {{ number_format($totalBalance, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($showQuickActions)
            <div class="flex flex-wrap gap-3 items-center">
                <a href="{{ route('cashbook.create') }}"
                   class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Nova Transação
                </a>

                <a href="{{ route('cashbook.upload') }}"
                   class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-upload mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Upload
                </a>

                <button @click="showFilters = !showFilters"
                        class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        :class="{'from-slate-700 to-slate-800': showFilters}">
                    <i class="bi bi-funnel-fill mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="hidden sm:inline">Filtros</span>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
