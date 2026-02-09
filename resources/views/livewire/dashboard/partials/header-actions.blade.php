{{-- Ações do Header do Dashboard --}}
<div class="flex flex-wrap items-center gap-3">
    {{-- Badge Status Sistema --}}
    <div
        class="inline-flex items-center space-x-2 px-4 py-2.5 bg-green-500/20 dark:bg-green-700/40 rounded-xl border border-green-500/30 dark:border-green-600/50 shadow-lg backdrop-blur-sm">
        <div class="w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse shadow-lg shadow-green-500/50"></div>
        <span class="text-sm font-semibold text-green-700 dark:text-green-300">Sistema Online</span>
    </div>
    {{-- Botão Refresh --}}
    <button wire:click="refreshData"
        class="group inline-flex items-center px-4 py-2.5 bg-white/80 dark:bg-slate-700/80 hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl border border-slate-200 dark:border-slate-600 backdrop-blur-sm">
        <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
        <span>Atualizar</span>
    </button>
    {{-- Botão Fluxo de Caixa --}}
    <a href="{{ route('dashboard.cashbook') }}"
        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
        <i class="fas fa-wallet mr-2 group-hover:scale-110 transition-transform duration-200"></i>
        <span>Fluxo de Caixa</span>
    </a>
    {{-- Botão Produtos --}}
    <a href="{{ route('dashboard.products') }}"
        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
        <i class="fas fa-box mr-2 group-hover:scale-110 transition-transform duration-200"></i>
        <span>Produtos</span>
    </a>
    {{-- Botão Vendas --}}
    <a href="{{ route('dashboard.sales') }}"
        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
        <i class="fas fa-shopping-cart mr-2 group-hover:scale-110 transition-transform duration-200"></i>
        <span>Vendas</span>
    </a>
</div>
