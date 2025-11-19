@props([
    'showQuickActions' => false
])

<!-- Ações Rápidas Expandidas -->
<div x-show="showQuickActions"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4"
     class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-6 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 mb-6">

    <!-- Header das Ações -->
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl shadow-lg">
            <i class="bi bi-lightning-fill text-white text-xl"></i>
        </div>
        <div>
            <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-green-600 to-emerald-600 dark:from-green-300 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                Ações Rápidas
            </h3>
            <p class="text-slate-600 dark:text-slate-400">
                Execute ações em lote e operações especiais
            </p>
        </div>
    </div>

    <!-- Grid de Ações -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Exportar Excel -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 hover:from-emerald-100 hover:to-green-100 dark:hover:from-emerald-900/30 dark:hover:to-green-900/30 rounded-2xl border border-emerald-200 dark:border-emerald-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-file-earmark-excel text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Exportar Excel</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Planilha completa</div>
            </div>
        </button>

        <!-- Importar CSV -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/30 dark:hover:to-indigo-900/30 rounded-2xl border border-blue-200 dark:border-blue-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-file-earmark-arrow-up text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Importar CSV</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Upload em lote</div>
            </div>
        </button>

        <!-- Relatórios -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 hover:from-purple-100 hover:to-violet-100 dark:hover:from-purple-900/30 dark:hover:to-violet-900/30 rounded-2xl border border-purple-200 dark:border-purple-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-graph-up-arrow text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Relatórios</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Análises e métricas</div>
            </div>
        </button>

        <!-- Duplicar Produto -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 hover:from-amber-100 hover:to-yellow-100 dark:hover:from-amber-900/30 dark:hover:to-yellow-900/30 rounded-2xl border border-amber-200 dark:border-amber-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-files text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Duplicar</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Copiar produtos</div>
            </div>
        </button>

        <!-- Edição em Massa -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 hover:from-orange-100 hover:to-red-100 dark:hover:from-orange-900/30 dark:hover:to-red-900/30 rounded-2xl border border-orange-200 dark:border-orange-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-pencil-square text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Editar Massa</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Múltiplos produtos</div>
            </div>
        </button>

        <!-- Análise de Estoque -->
        <button class="group flex flex-col items-center gap-3 p-4 bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 hover:from-teal-100 hover:to-cyan-100 dark:hover:from-teal-900/30 dark:hover:to-cyan-900/30 rounded-2xl border border-teal-200 dark:border-teal-700/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="bi bi-boxes text-white text-xl"></i>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Estoque</div>
                <div class="text-xs text-slate-600 dark:text-slate-400">Análise e controle</div>
            </div>
        </button>
    </div>

    <!-- Ações em Massa -->
    <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
        <h4 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-check2-square text-purple-600"></i>
            Ações em Massa
        </h4>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <button class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 dark:from-red-600 dark:to-pink-700 dark:hover:from-red-500 dark:hover:to-pink-600 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-trash3"></i>
                <span class="text-sm">Excluir Selecionados</span>
            </button>

            <button class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 dark:from-green-600 dark:to-emerald-700 dark:hover:from-green-500 dark:hover:to-emerald-600 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-check-circle"></i>
                <span class="text-sm">Ativar Selecionados</span>
            </button>

            <button class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 dark:from-slate-600 dark:to-slate-700 dark:hover:from-slate-500 dark:hover:to-slate-600 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-slash-circle"></i>
                <span class="text-sm">Inativar Selecionados</span>
            </button>

            <button class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 dark:from-blue-600 dark:to-indigo-700 dark:hover:from-blue-500 dark:hover:to-indigo-600 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-pencil-square"></i>
                <span class="text-sm">Editar Selecionados</span>
            </button>
        </div>
    </div>
</div>
