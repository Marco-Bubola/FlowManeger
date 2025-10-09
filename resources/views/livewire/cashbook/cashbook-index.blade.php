<div x-data="{ showFilters: false }" class="">
    <!-- Header moderno consistente -->
    <x-cashbook-header :total-transactions="$transactionsCount ?? 0" :total-balance="$totalBalance ?? 0" :show-quick-actions="true" />

    <!-- Main Content Layout -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            <!-- Left Column - Calendar & Chart (20% - 1 col) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Calendar -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl p-6 rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            📅 Calendário
                        </h3>
                    </div>

                    <!-- Navegação do Calendário com selects e botões -->
                    <div class="flex items-center justify-between mb-6">
                        <button wire:click="changeMonth('previous')"
                            class="p-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div class="flex items-center space-x-2">
                            <select wire:model.live="month"
                                class="rounded-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 dark:text-white text-sm font-medium shadow-inner focus:ring-2 focus:ring-purple-500">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                            <select wire:model.live="year"
                                class="rounded-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 dark:text-white text-sm font-medium shadow-inner focus:ring-2 focus:ring-purple-500">
                                @foreach (range(now()->year - 5, now()->year + 2) as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="changeMonth('next')"
                            class="p-2 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Exibir mês/ano atual -->
                    <div class="mb-4 text-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Exibindo: {{ $monthName ?? 'Carregando...' }}
                        </span>
                    </div>

                    <!-- Feedback temporário -->
                    @if(session('message'))
                        <div class="mb-4 p-2 bg-green-100 border border-green-300 rounded text-xs text-green-800">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if(session('debug_info'))
                        <div class="mb-4 p-2 bg-blue-100 border border-blue-300 rounded text-xs text-blue-800">
                            <strong>Debug:</strong> {{ session('debug_info') }}
                        </div>
                    @endif

                    @if(session('calendar_debug'))
                        <div class="mb-4 p-2 bg-yellow-100 border border-yellow-300 rounded text-xs text-yellow-800">
                            <strong>Calendar:</strong> {{ session('calendar_debug') }}
                        </div>
                    @endif                    <!-- Cabeçalho do calendário -->
                    <div class="grid grid-cols-7 gap-1 mb-3">
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">D</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">S</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">T</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">Q</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">Q</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">S</div>
                        <div class="text-center text-xs font-bold text-gray-600 dark:text-gray-300 py-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">S</div>
                    </div>

                    <!-- Dias do calendário -->
                    <div class="grid grid-cols-7 gap-2">
                        @if(isset($calendarData) && is_array($calendarData))
                            @foreach ($calendarData as $week)
                                @foreach ($week as $day)
                                    <div wire:click="selectDay('{{ $day['date'] }}')"
                                        class="relative min-h-[40px] p-2 border-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 {{ $day['is_current_month'] ? 'bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 hover:from-blue-50 hover:to-purple-50 dark:hover:from-gray-700 dark:hover:to-gray-600 border-gray-200 dark:border-gray-600' : 'bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 border-gray-300 dark:border-gray-500' }} {{ $day['is_today'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/25' : '' }} {{ isset($selectedDate) && $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg shadow-green-500/25' : '' }}">
                                        <div class="text-sm font-bold {{ $day['is_current_month'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['is_today'] ? 'text-blue-600 dark:text-blue-400' : '' }} {{ isset($selectedDate) && $selectedDate === $day['date'] ? 'text-green-600 dark:text-green-400' : '' }}">
                                            {{ $day['day'] }}
                                        </div>
                                        @if ($day['transaction_count'] > 0)
                                            <div class="absolute bottom-1 right-1">
                                                <div class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        @else
                            <!-- Placeholder enquanto carrega -->
                            @for($i = 0; $i < 42; $i++)
                                <div class="relative min-h-[40px] p-2 border-2 rounded-xl bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-500">
                                    <div class="text-sm font-bold text-gray-400 dark:text-gray-500">-</div>
                                </div>
                            @endfor
                        @endif
                    </div>

                    @if (isset($selectedDate) && $selectedDate)
                        <div class="mt-4">
                            <button wire:click="clearDateFilter"
                                class="w-full text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 rounded-xl p-3 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Limpar Filtro
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Chart Section -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl p-6 rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            📊 Por Categoria
                        </h3>
                    </div>

                    <!-- Categories Chart -->
                    <div class="space-y-4">
                        @foreach($categories as $category)
                            @php
                                $categoryTransactions = collect($transactionsByCategory)->filter(function($group) use ($category) {
                                    return $group['category_id'] == $category->id_category;
                                });
                                $categoryTotal = $categoryTransactions->sum(function($group) {
                                    return collect($group['transactions'])->sum(function($transaction) {
                                        return abs($transaction['value'] ?? 0);
                                    });
                                });
                                $totalExpenses = collect($transactionsByCategory)->sum(function($group) {
                                    return collect($group['transactions'])->sum(function($transaction) {
                                        return abs($transaction['value'] ?? 0);
                                    });
                                });
                                $percentage = $totalExpenses > 0 ? ($categoryTotal / $totalExpenses) * 100 : 0;
                            @endphp
                            @if($categoryTotal > 0)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            @if($category->icone)
                                                <i class="{{ $category->icone }} text-lg text-gray-700 dark:text-gray-300"></i>
                                            @else
                                                <div class="w-5 h-5 bg-gray-300 dark:bg-gray-600 rounded"></div>
                                            @endif
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            R$ {{ number_format($categoryTotal, 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-red-500 to-pink-600 h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                        <span>{{ number_format($percentage, 1) }}% do total</span>
                                        <span>{{ $categoryTransactions->sum(function($group) { return count($group['transactions']); }) }} transações</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if(collect($categories)->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nenhuma transação por categoria</p>
                            </div>
                        @endif
                    </div>
                </div>


            </div>

            <!-- Right Column - Transactions (80% - 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Income Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-arrow-up text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Receitas</p>
                                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                            R$ {{ number_format($totals['income'] ?? 0, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-900 px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-green-700 dark:text-green-300">Total do mês</span>
                                <i class="fas fa-arrow-up text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-rose-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-arrow-down text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Despesas</p>
                                        <p class="text-xl font-bold text-red-600 dark:text-red-400">
                                            R$ {{ number_format($totals['expense'] ?? 0, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900 dark:to-rose-900 px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-red-700 dark:text-red-300">Total do mês</span>
                                <i class="fas fa-arrow-down text-red-600 dark:text-red-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br {{ ($totals['balance'] ?? 0) >= 0 ? 'from-blue-400 to-indigo-500' : 'from-orange-400 to-amber-500' }} rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-balance-scale text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo</p>
                                        <p class="text-xl font-bold {{ ($totals['balance'] ?? 0) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                            @php $bal = $totals['balance'] ?? 0; @endphp
                                            @if($bal >= 0)
                                                R$ {{ number_format($bal, 2, ',', '.') }}
                                            @else
                                                -R$ {{ number_format(abs($bal), 2, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r {{ ($totals['balance'] ?? 0) >= 0 ? 'from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900' : 'from-orange-50 to-amber-50 dark:from-orange-900 dark:to-amber-900' }} px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs {{ ($totals['balance'] ?? 0) >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }}">Resultado</span>
                                <i class="fas fa-calculator {{ ($totals['balance'] ?? 0) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Transactions List -->
                <div class="space-y-4">
                    <!-- Controls -->
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <i class="fas fa-list mr-2"></i>
                            Transações por Categoria
                        </h2>
                        <div class="flex items-center space-x-2" x-data="{ expandAll() { document.querySelectorAll('[x-data]')
    .forEach(el => { if (el.__x && el.__x.$data && typeof el.__x.$data.expanded !== 'undefined') { el.__x.$data.expanded = true; } }); },
    collapseAll() { document.querySelectorAll('[x-data]')
    .forEach(el => { if (el.__x && el.__x.$data && typeof el.__x.$data.expanded !== 'undefined') { el.__x.$data.expanded = false; } }); }
}">
                            <button @click="expandAll()"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900 hover:bg-blue-100 dark:hover:bg-blue-800 rounded-lg transition-colors duration-200">
                                <i class="fas fa-expand-arrows-alt mr-1"></i>
                                Expandir
                            </button>
                            <button @click="collapseAll()"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200">
                                <i class="fas fa-compress-arrows-alt mr-1"></i>
                                Recolher
                            </button>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                    @forelse($transactionsByCategory as $index => $categoryGroup)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300"
                             x-data="{ expanded: false }">
                            <!-- Category Header -->
                            <div class="cursor-pointer select-none" @click="expanded = !expanded">
                                <div class="px-6 py-5 border-l-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                     style="border-left-color: {{ $categoryGroup['category_hexcolor_category'] }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg"
                                                 style="background: linear-gradient(135deg, {{ $categoryGroup['category_hexcolor_category'] }}20, {{ $categoryGroup['category_hexcolor_category'] }}40)">
                                                <i class="{{ $categoryGroup['category_icone'] }} text-xl"
                                                   style="color: {{ $categoryGroup['category_hexcolor_category'] }}"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-lg font-bold text-gray-900 dark:text-white truncate">
                                                    {{ $categoryGroup['category_name'] }}
                                                </h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                                    <i class="fas fa-coins mr-1"></i>
                                                    {{ count($categoryGroup['transactions']) }} transação(ões)
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right space-y-2">
                                                @if($categoryGroup['total_receita'] > 0)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                        <i class="fas fa-arrow-up mr-1"></i>
                                                        R$ {{ number_format($categoryGroup['total_receita'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                                @if($categoryGroup['total_despesa'] > 0)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                        <i class="fas fa-arrow-down mr-1"></i>
                                                        R$ {{ number_format($categoryGroup['total_despesa'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-chevron-down text-gray-400 dark:text-gray-300 transition-transform duration-200"
                                                       :class="{ 'rotate-180': expanded }"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transactions List -->
                            <div x-show="expanded" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                                <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-600 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-semibold text-gray-700 dark:text-gray-200 flex items-center">
                                            <i class="fas fa-list mr-2"></i>
                                            Detalhes das Transações
                                        </span>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-gray-600 dark:text-gray-300 flex items-center">
                                                <i class="fas fa-calculator mr-1"></i>
                                                {{ count($categoryGroup['transactions']) }} item(s)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grid com 2 colunas para as transações -->
                                <div class="p-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($categoryGroup['transactions'] as $transaction)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-md {{ $transaction['type_id'] == 1 ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                                                            <i class="fas fa-{{ $transaction['type_id'] == 1 ? 'plus' : 'minus' }} text-sm {{ $transaction['type_id'] == 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                                {{ $transaction['description'] }}
                                                            </p>
                                                            <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                <span>{{ $transaction['time'] }}</span>
                                                                @if($transaction['is_pending'])
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                                        <i class="fas fa-clock mr-1"></i>
                                                                        Pendente
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-between">
                                                    <div class="text-right">
                                                        <span class="text-lg font-bold {{ $transaction['type_id'] == 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                            {{ $transaction['type_id'] == 1 ? '+' : '-' }}R$ {{ number_format($transaction['value'], 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('cashbook.edit', $transaction['id']) }}"
                                                           class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 flex items-center justify-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200 shadow-md hover:shadow-lg">
                                                            <i class="fas fa-edit text-xs"></i>
                                                        </a>
                                                        <button wire:click="confirmDelete({{ $transaction['id'] }})"
                                                                class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 flex items-center justify-center text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors duration-200 shadow-md hover:shadow-lg">
                                                            <i class="fas fa-trash text-xs"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                @if($transaction['note'] || $transaction['client_name'])
                                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                            @if($transaction['note'])
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-sticky-note mr-1"></i>
                                                                    <span class="truncate">{{ $transaction['note'] }}</span>
                                                                </div>
                                                            @endif
                                                            @if($transaction['client_name'])
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-user mr-1"></i>
                                                                    <span>{{ $transaction['client_name'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            </div>
                        @empty
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center">
                                <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-inbox text-gray-400 dark:text-gray-500 text-3xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nenhuma transação encontrada</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-8">Comece criando uma nova transação ou ajuste os filtros para ver seus dados.</p>
                                <a href="{{ route('cashbook.create') }}"
                                   class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <i class="fas fa-plus mr-2"></i>
                                    Criar Primeira Transação
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão Ultra Moderno -->
    @if($showDeleteModal)
    <div x-data="{ modalOpen: true }"
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] overflow-y-auto">
        <!-- Backdrop com blur e gradiente -->
        <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md"></div>

        <!-- Container do Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <!-- Modal -->
            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
                 class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                <!-- Efeitos visuais de fundo -->
                <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-br from-pink-400/20 to-red-600/20 rounded-full blur-3xl"></div>

                <!-- Conteúdo do Modal -->
                <div class="relative z-10">
                    <!-- Header com ícone animado -->
                    <div class="text-center pt-8 pb-4">
                        <div class="relative inline-flex items-center justify-center">
                            <!-- Círculos de fundo animados -->
                            <div class="absolute w-24 h-24 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-full animate-pulse"></div>
                            <div class="absolute w-20 h-20 bg-gradient-to-r from-red-500/40 to-pink-600/40 rounded-full animate-ping"></div>

                            <!-- Ícone principal -->
                            <div class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="bi bi-exclamation-triangle text-2xl text-white animate-bounce"></i>
                            </div>
                        </div>

                        <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">Excluir Transação?</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Tem certeza que deseja excluir a transação selecionada? Esta ação é permanente e não pode ser desfeita.</p>
                        @if($deletingTransaction)
                            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300"><strong>#{{ $deletingTransaction->id }}</strong> — {{ $deletingTransaction->description ?? 'Sem descrição' }}</p>
                        @endif
                    </div>

                    <!-- Botões de ação -->
                    <div class="px-6 pb-8 sm:px-10 sm:pb-10 flex items-center justify-center gap-4">
                        <button wire:click="deleteTransaction" type="button" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-2xl shadow-md">
                            <i class="fas fa-trash"></i>
                            Confirmar Exclusão
                        </button>
                        <button x-on:click="modalOpen = false; $wire.call('cancelDelete')" type="button" class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-2xl shadow-sm">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .calendar-day {
        @apply w-10 h-10 flex items-center justify-center rounded-xl cursor-pointer transition-all duration-200 text-sm font-medium;
    }

    .calendar-day:hover {
        @apply bg-gray-100 dark:bg-gray-700 transform scale-105;
    }

    .calendar-day.today {
        @apply bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg;
    }

    .calendar-day.selected {
        @apply bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg transform scale-105;
    }

    .calendar-day.has-transactions {
        position: relative;
    }

    .calendar-day.has-transactions::after {
        content: '';
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        @apply bg-red-500 shadow-lg;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.1); }
    }

    .floating-animation {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .pulse-glow {
        animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
        50% { box-shadow: 0 0 40px rgba(139, 92, 246, 0.6); }
    }

    .gradient-border {
        position: relative;
        background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        border-radius: 1rem;
        padding: 2px;
    }

    .gradient-border > div {
        border-radius: calc(1rem - 2px);
    }

    /* Transaction Card Interactions */
    .transaction-card:hover .card-actions {
        opacity: 1 !important;
        transform: translateY(0) !important;
        max-height: 80px !important;
    }

    .card-actions {
        max-height: 0 !important;
        overflow: hidden;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease-out;
        margin: 0 !important;
        padding: 0 !important;
    }

    .transaction-card:hover .card-actions {
        margin-top: 0.75rem !important;
        padding-top: 0.75rem !important;
    }

    .transaction-card.expanded .expand-icon {
        transform: rotate(180deg);
    }

    .card-details {
        max-height: 0;
        overflow: hidden;
        margin-top: 0 !important;
        padding-top: 0 !important;
        border: none;
        transition: all 0.3s ease-out;
    }

    .transaction-card.expanded .card-details {
        max-height: 200px;
        margin-top: 1rem !important;
        padding-top: 1rem !important;
        border-top: 1px solid rgba(209, 213, 219, 0.5);
    }

    .dark .transaction-card.expanded .card-details {
        border-top-color: rgba(75, 85, 99, 0.5);
    }
</style>
@endpush

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script>
    // Disponibilizar categoryManager no escopo global para Alpine
    window.categoryManager = function() {
        return {
            expandAll() {
                document.querySelectorAll('[x-data]').forEach(el => {
                    if (el.__x && el.__x.$data && typeof el.__x.$data.expanded !== 'undefined') {
                        el.__x.$data.expanded = true;
                    }
                });
            },
            collapseAll() {
                document.querySelectorAll('[x-data]').forEach(el => {
                    if (el.__x && el.__x.$data && typeof el.__x.$data.expanded !== 'undefined') {
                        el.__x.$data.expanded = false;
                    }
                });
            }
        }
    }

    // Enhanced calendar functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add keyboard navigation for calendar
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = '{{ route("cashbook.create") }}';
            }

            // ESC para limpar filtro
            if (e.key === 'Escape' && typeof @this !== 'undefined' && @this.selectedDate) {
                @this.clearDateFilter();
            }

            // Ctrl+R para recarregar dados
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                if (typeof @this !== 'undefined' && @this.loadData) {
                    @this.loadData();
                    showNotification('🔄 Dados atualizados!', 'success');
                }
            }
        });

        // Add tooltips to calendar days
        const calendarDays = document.querySelectorAll('.calendar-day');
        calendarDays.forEach(day => {
            if (day.classList.contains('has-transactions')) {
                day.setAttribute('title', 'Clique para filtrar por este dia');
            }
        });

        // Initialize dark mode from localStorage
        if (localStorage.getItem('dark-mode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    });

    // Enhanced UX for date filtering
    document.addEventListener('livewire:init', function () {
        if (typeof Livewire !== 'undefined') {
            Livewire.on('date-filtered', () => {
                showNotification('📅 Filtro aplicado!', 'info');
            });
        }
    });

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        if (document.visibilityState === 'visible' && typeof @this !== 'undefined' && @this.loadData) {
            @this.loadData();
        }
    }, 300000);

    // Dark mode toggle
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('dark-mode', isDark);
        showNotification(isDark ? '🌙 Modo escuro ativado' : '☀️ Modo claro ativado', 'info');
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-xl text-white font-bold shadow-2xl transform transition-all duration-300 ${
            type === 'success' ? 'bg-gradient-to-r from-emerald-500 to-teal-600' :
            type === 'error' ? 'bg-gradient-to-r from-red-500 to-pink-600' :
            'bg-gradient-to-r from-blue-500 to-indigo-600'
        }`;
        notification.innerHTML = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Add subtle animations on hover
    document.addEventListener('mouseover', function(e) {
        if (e.target.classList.contains('calendar-day') && e.target.classList.contains('has-transactions')) {
            e.target.style.transform = 'scale(1.1)';
        }
    });

    document.addEventListener('mouseout', function(e) {
        if (e.target.classList.contains('calendar-day')) {
            e.target.style.transform = 'scale(1)';
        }
    });

    // Adicionar loading states para melhor UX
    document.addEventListener('livewire:init', () => {
        if (typeof Livewire !== 'undefined') {
            Livewire.on('transaction-deleted', () => {
                // Mostrar notificação de sucesso
                showNotification('✅ Transação excluída com sucesso!', 'success');
            });
        }
    });
</script>
@endpush
