{{-- Grid de 6 KPIs Principais --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    {{-- 1. Saldo em Caixa --}}
    <div class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-2xl shadow-lg border border-green-200 dark:border-green-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        {{-- Elemento decorativo --}}
        <div class="absolute top-0 right-0 w-24 h-24 bg-green-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-wallet text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-green-800 dark:text-green-300 uppercase tracking-wide">Saldo em Caixa</p>
                </div>
            </div>
            <p class="text-3xl font-bold text-green-700 dark:text-green-400 mb-1">
                R$ {{ number_format($saldoCaixa, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
                <i class="fas fa-chart-line"></i>
                <span>Disponível agora</span>
            </div>
        </div>
    </div>

    {{-- 2. Receitas do Período --}}
    <div class="group relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-2xl shadow-lg border border-blue-200 dark:border-blue-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-arrow-up text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase tracking-wide">Receitas do Mês</p>
                </div>
            </div>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-400 mb-1">
                R$ {{ number_format($receitasPeriodo, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                <i class="fas fa-trending-up"></i>
                <span>Entradas</span>
            </div>
        </div>
    </div>

    {{-- 3. Despesas do Período --}}
    <div class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-2xl shadow-lg border border-red-200 dark:border-red-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        <div class="absolute top-0 right-0 w-24 h-24 bg-red-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-arrow-down text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-red-800 dark:text-red-300 uppercase tracking-wide">Despesas do Mês</p>
                </div>
            </div>
            <p class="text-3xl font-bold text-red-700 dark:text-red-400 mb-1">
                R$ {{ number_format($despesasPeriodo, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                <i class="fas fa-trending-down"></i>
                <span>Saídas</span>
            </div>
        </div>
    </div>

    {{-- 4. Lucro Líquido --}}
    <div class="group relative bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/30 rounded-2xl shadow-lg border border-purple-200 dark:border-purple-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        <div class="absolute top-0 right-0 w-24 h-24 bg-purple-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-chart-line text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-purple-800 dark:text-purple-300 uppercase tracking-wide">Lucro Líquido</p>
                </div>
            </div>
            <p class="text-3xl font-bold {{ $lucroLiquido >= 0 ? 'text-purple-700 dark:text-purple-400' : 'text-red-700 dark:text-red-400' }} mb-1">
                R$ {{ number_format(abs($lucroLiquido), 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs {{ $lucroLiquido >= 0 ? 'text-purple-600 dark:text-purple-400' : 'text-red-600 dark:text-red-400' }}">
                <i class="fas fa-{{ $lucroLiquido >= 0 ? 'smile' : 'frown' }}"></i>
                <span>{{ number_format($margemLucro, 1) }}% margem</span>
            </div>
        </div>
    </div>

    {{-- 5. Contas a Pagar --}}
    <div class="group relative bg-gradient-to-br from-orange-50 to-amber-100 dark:from-orange-900/20 dark:to-amber-900/30 rounded-2xl shadow-lg border border-orange-200 dark:border-orange-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        <div class="absolute top-0 right-0 w-24 h-24 bg-orange-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-orange-800 dark:text-orange-300 uppercase tracking-wide">Contas a Pagar</p>
                </div>
            </div>
            <p class="text-3xl font-bold text-orange-700 dark:text-orange-400 mb-1">
                R$ {{ number_format($contasPagar, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs text-orange-600 dark:text-orange-400">
                <i class="fas fa-calendar-alt"></i>
                <span>Pendente</span>
            </div>
        </div>
    </div>

    {{-- 6. Contas a Receber --}}
    <div class="group relative bg-gradient-to-br from-teal-50 to-cyan-100 dark:from-teal-900/20 dark:to-cyan-900/30 rounded-2xl shadow-lg border border-teal-200 dark:border-teal-800 p-5 hover:shadow-2xl transition-all duration-300 overflow-hidden hover:scale-105">
        <div class="absolute top-0 right-0 w-24 h-24 bg-teal-400/10 rounded-full transform translate-x-12 -translate-y-12 group-hover:scale-150 transition-transform duration-500"></div>

        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <i class="fas fa-hand-holding-usd text-white text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wide">Contas a Receber</p>
                </div>
            </div>
            <p class="text-3xl font-bold text-teal-700 dark:text-teal-400 mb-1">
                R$ {{ number_format($contasReceber, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-2 text-xs text-teal-600 dark:text-teal-400">
                <i class="fas fa-clock"></i>
                <span>A receber</span>
            </div>
        </div>
    </div>

</div>
