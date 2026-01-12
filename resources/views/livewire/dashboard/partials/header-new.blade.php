{{-- Header Modernizado do Dashboard --}}
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl mb-6">
    {{-- Elementos decorativos --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-16 -translate-y-16 blur-2xl"></div>
    <div class="absolute bottom-0 left-0 w-28 h-28 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-12 translate-y-12 blur-xl"></div>

    <div class="relative px-6 py-5">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            {{-- Logo e Info --}}
            <div class="flex items-center gap-5">
                {{-- Ícone Principal com animação --}}
                <div class="relative group">
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-2xl flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50 group-hover:scale-105 transition-transform duration-300">
                        <i class="fas fa-chart-line text-white text-3xl"></i>
                        <div class="absolute inset-0 bg-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    {{-- Badge Online pulsante --}}
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-3 border-white dark:border-slate-800 shadow-lg">
                        <div class="w-full h-full bg-green-400 rounded-full animate-ping opacity-75"></div>
                    </div>
                </div>

                <div class="space-y-1">
                    {{-- Breadcrumb --}}
                    <div class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">
                        <i class="fas fa-home text-indigo-600 dark:text-indigo-400"></i>
                        <span>Dashboard</span>
                        <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                        <span class="text-slate-800 dark:text-white">Visão Geral</span>
                    </div>

                    {{-- Título --}}
                    <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                        FlowManager
                    </h1>

                    {{-- Subtítulo com data --}}
                    <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-300">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-500"></i>
                            <span id="currentDate"></span>
                        </span>
                        <span class="flex items-center gap-2">
                            <i class="fas fa-clock text-purple-500"></i>
                            <span id="currentTime"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Ações e Badges --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Badge Status Sistema --}}
                <div class="inline-flex items-center space-x-2 px-4 py-2.5 bg-green-500/20 dark:bg-green-700/40 rounded-xl border border-green-500/30 dark:border-green-600/50 shadow-lg backdrop-blur-sm">
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
        </div>
    </div>
</div>

{{-- Script para atualizar data/hora --}}
<script>
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };

        document.getElementById('currentDate').textContent = now.toLocaleDateString('pt-BR', dateOptions);
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('pt-BR', timeOptions);
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>
