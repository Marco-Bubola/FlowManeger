<div class="w-full">
    <!-- Header moderno consistente -->
    <x-invoice-header
        :total-transactions="$totalTransactions ?? 0"
        :total-expenses="$totalDespesas ?? 0"
        :bank-id="$bankId"
        :show-quick-actions="true"
    />

    <!-- Main Content Layout -->
    <div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

            <!-- Left Column - Calendar & Chart (25% - 1 col) -->
            <div class="lg:col-span-1 space-y-3">
                <!-- Calendar -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl p-3 rounded-xl border border-white/20 dark:border-gray-700/30 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            📅 Calendário
                        </h3>
                    </div>

                    <!-- Navegação do Calendário com selects e botões -->
                    <div class="flex items-center justify-between mb-3">
                        <button wire:click="previousMonth"
                            class="p-1.5 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div class="flex items-center space-x-2">
                            <select wire:model.live="month"
                                class="rounded-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 dark:text-white text-sm font-medium shadow-inner focus:ring-2 focus:ring-purple-500">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMM') }}
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
                        <button wire:click="nextMonth"
                            class="p-1.5 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Exibir mês/ano atual -->
                    <div class="mb-2 text-center">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ $currentMonthName ?? 'Carregando...' }}
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
                        @endif

                        <!-- Cabeçalho do calendário -->
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
                            @if(isset($calendarDays) && is_array($calendarDays))
                                @foreach ($calendarDays as $day)
                                    <div wire:click="selectDate('{{ $day['date'] }}')"
                                        class="relative min-h-[40px] p-2 border-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 {{ $day['isCurrentMonth'] ? 'bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 hover:from-blue-50 hover:to-purple-50 dark:hover:from-gray-700 dark:hover:to-gray-600 border-gray-200 dark:border-gray-600' : 'bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 border-gray-300 dark:border-gray-500' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/25' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg shadow-green-500/25' : '' }}">
                                        <div class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-600 dark:text-green-400' : '' }}">
                                            {{ $day['day'] }}
                                        </div>
                                        @if (!empty($day['invoices']))
                                            <div class="absolute bottom-1 right-1">
                                                <div class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
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

                        @if ($selectedDate)
                            <div class="mt-4">
                                <button wire:click="clearDateSelection"
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
                            @foreach($categoriesWithTransactions as $category)
                                @php
                                    $invoicesCollection = collect($invoices);
                                    $categoryId = is_array($category) ? ($category['id_category'] ?? null) : $category->id_category;
                                    $categoryTotal = abs($invoicesCollection->where('category_id', $categoryId)->sum(function($invoice) {
                                        return is_array($invoice) ? ($invoice['value'] ?? 0) : $invoice->value;
                                    }));
                                    $percentage = $totalDespesas > 0 ? ($categoryTotal / $totalDespesas) * 100 : 0;
                                @endphp
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            @if(is_array($category) ? ($category['icone'] ?? null) : $category->icone)
                                                <i class="{{ is_array($category) ? ($category['icone'] ?? '') : $category->icone }} text-lg text-gray-700 dark:text-gray-300"></i>
                                            @else
                                                <div class="w-5 h-5 bg-gray-300 dark:bg-gray-600 rounded"></div>
                                            @endif
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ is_array($category) ? ($category['name'] ?? '') : $category->name }}</span>
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
                                        <span>{{ $invoicesCollection->where('category_id', $categoryId)->count() }} transações</span>
                                    </div>
                                </div>
                            @endforeach

                            @if(collect($categoriesWithTransactions)->isEmpty())
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

                <!-- Transactions Section -->
                <div class="lg:col-span-3 space-y-3">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Total Despesas Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Despesas</p>
                                            <p class="text-xl font-black text-red-600 dark:text-red-400">
                                                R$ {{ number_format($totalDespesas, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900 dark:to-pink-900 px-4 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-red-700 dark:text-red-300">Total do mês</span>
                                    <i class="fas fa-arrow-down text-red-600 dark:text-red-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Total Transações Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Transações</p>
                                            <p class="text-xl font-black text-blue-600 dark:text-blue-400">
                                                {{ $totalTransactions }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 px-4 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-blue-700 dark:text-blue-300">Quantidade</span>
                                    <i class="fas fa-receipt text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Média Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Média</p>
                                            <p class="text-xl font-black text-purple-600 dark:text-purple-400">
                                                @if($totalTransactions > 0)
                                                    R$ {{ number_format($totalDespesas / $totalTransactions, 2, ',', '.') }}
                                                @else
                                                    R$ 0,00
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900 dark:to-pink-900 px-4 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-purple-700 dark:text-purple-300">Por transação</span>
                                    <i class="fas fa-calculator text-purple-600 dark:text-purple-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Filter Banner -->
                    @if($selectedDate)
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl p-6 rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-900 dark:text-white">📅 Transações do Dia</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <button wire:click="clearDateSelection"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-sm font-medium rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Limpar Filtro
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Transactions List -->
                    <div class="space-y-3">
                        <!-- Controls -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="leading-tight">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">💳 Transações</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ count($invoices) }} registro(s)</p>
                                    </div>
                                </div>
                            </div>
                            @if($bankId && count($invoices) > 0)
                                <div class="flex items-center space-x-2">
                                    <button wire:click="$toggle('viewMode')"
                                        class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900 hover:bg-blue-100 dark:hover:bg-blue-800 rounded-lg transition-colors duration-200">
                                        @if($viewMode === 'cards')
                                            <i class="fas fa-list mr-1 text-xs"></i>
                                            Lista
                                        @else
                                            <i class="fas fa-th mr-1 text-xs"></i>
                                            Cards
                                        @endif
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Transactions Content -->
                    @if($invoices && count($invoices) > 0)
                        @if($viewMode === 'cards')
                            <!-- Cards View -->
                            <div class="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-6">
                                @foreach($invoices as $invoice)
                                    <div class="invoice-card bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden cursor-pointer"
                                         onclick="toggleCardExpansion(this)">
                                        <!-- Card Header -->
                                        <div class="bg-gradient-to-r from-red-500 to-pink-600 p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    @if((is_array($invoice) ? ($invoice['category'] ?? null) : $invoice->category) && (is_array($invoice) ? ($invoice['category']['icone'] ?? null) : $invoice->category->icone))
                                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                                            <i class="{{ is_array($invoice) ? ($invoice['category']['icone'] ?? '') : $invoice->category->icone }} text-white text-lg"></i>
                                                        </div>
                                                    @else
                                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="text-white font-bold text-lg">
                                                             R$ {{ number_format(abs(is_array($invoice) ? $invoice['value'] : $invoice->value), 2, ',', '.') }}
                                                        </p>
                                                        <p class="text-white/80 text-sm">
                                                            {{ (is_array($invoice) ? ($invoice['category'] ?? null) : $invoice->category) ? (is_array($invoice) ? ($invoice['category']['name'] ?? 'Sem categoria') : $invoice->category->name) : 'Sem categoria' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white expand-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Body -->
                                        <div class="p-6">
                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2" onclick="event.stopPropagation()">
                                                {{ is_array($invoice) ? ($invoice['description'] ?? '') : $invoice->description }}
                                            </h4>

                                            <!-- Expandable Details (Hidden by default) -->
                                            <div class="card-details space-y-3">
                                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                    <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-2">
                                                        <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    {{ \Carbon\Carbon::parse(is_array($invoice) ? $invoice['invoice_date'] : $invoice->invoice_date)->format('d/m/Y') }}
                                                </div>

                                                @if(is_array($invoice) ? ($invoice['client'] ?? null) : $invoice->client)
                                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <div class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-2">
                                                            <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                        </div>
                                                        {{ is_array($invoice) ? ($invoice['client']['name'] ?? '') : $invoice->client->name }}
                                                    </div>
                                                @endif

                                                @if(is_array($invoice) ? ($invoice['installments'] ?? null) : $invoice->installments)
                                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <div class="w-6 h-6 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-2">
                                                            <svg class="w-3 h-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                            </svg>
                                                        </div>
                                                        {{ is_array($invoice) ? ($invoice['installments'] ?? '') : $invoice->installments }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Card Actions (Hidden by default, shown on hover) -->
                                        @if($bankId)
                                            <div class="card-actions bg-gray-50/80 dark:bg-gray-700/50 px-6 py-4 flex items-center justify-between opacity-0 transform translate-y-2 transition-all duration-300"
                                                 onclick="event.stopPropagation()">
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ route('invoices.edit', [
                                                           'invoiceId' => is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id),
                                                           'return_month' => $month,
                                                           'return_year' => $year
                                                       ]) }}"
                                                       class="inline-flex items-center p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                                       title="Editar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>

                                                    <a href="{{ route('invoices.copy', is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id)) }}"
                                                       class="inline-flex items-center p-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                                       title="Copiar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </a>

                                                    <button wire:click="confirmDelete({{ is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id) }})"
                                                            class="inline-flex items-center p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                                            title="Excluir">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- List View -->
                            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl overflow-hidden">
                                <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                                    @foreach($invoices as $invoice)
                                        <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-all duration-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                                                        @if((is_array($invoice) ? ($invoice['category'] ?? null) : $invoice->category) && (is_array($invoice) ? ($invoice['category']['icone'] ?? null) : $invoice->category->icone))
                                                            <i class="{{ is_array($invoice) ? ($invoice['category']['icone'] ?? '') : $invoice->category->icone }} text-white text-lg"></i>
                                                        @else
                                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ is_array($invoice) ? ($invoice['description'] ?? '') : $invoice->description }}</p>
                                                        <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                                            <span class="flex items-center">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                </svg>
                                                                {{ \Carbon\Carbon::parse(is_array($invoice) ? $invoice['invoice_date'] : $invoice->invoice_date)->format('d/m/Y') }}
                                                            </span>
                                                            @if(is_array($invoice) ? ($invoice['category'] ?? null) : $invoice->category)
                                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-medium">
                                                                    {{ is_array($invoice) ? ($invoice['category']['name'] ?? '') : $invoice->category->name }}
                                                                </span>
                                                            @endif
                                                            @if(is_array($invoice) ? ($invoice['installments'] ?? null) : $invoice->installments)
                                                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-lg text-xs font-medium">
                                                                    {{ is_array($invoice) ? ($invoice['installments'] ?? '') : $invoice->installments }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center space-x-4">
                                                    <div class="text-right">
                                                        <p class="text-xl font-black text-red-600">
                                                            - R$ {{ number_format(abs(is_array($invoice) ? $invoice['value'] : $invoice->value), 2, ',', '.') }}
                                                        </p>
                                                        @if(is_array($invoice) ? ($invoice['client'] ?? null) : $invoice->client)
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ is_array($invoice) ? ($invoice['client']['name'] ?? '') : $invoice->client->name }}</p>
                                                        @endif
                                                    </div>

                                                    @if($bankId)
                                                        <div class="flex items-center space-x-2">
                                                            <a href="{{ route('invoices.edit', [
                                                                   'invoiceId' => is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id),
                                                                   'return_month' => $month,
                                                                   'return_year' => $year
                                                               ]) }}"
                                                               class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            </a>

                                                            <a href="{{ route('invoices.copy', is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id)) }}"
                                                               class="p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-all duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                                </svg>
                                                            </a>

                                                            <button wire:click="confirmDelete({{ is_array($invoice) ? ($invoice['id_invoice'] ?? $invoice['id'] ?? '') : ($invoice->id_invoice ?? $invoice->id) }})"
                                                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl p-12 text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-gray-300 to-gray-400 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-4">💳 Nenhuma transação encontrada</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                                @if($selectedDate)
                                    Não há transações registradas para o dia {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.
                                @else
                                    Comece criando sua primeira transação para organizar suas finanças.
                                @endif
                            </p>

                            @if($bankId)
                                <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                                    <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-bold rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        � Nova Despesa
                                    </a>

                                    <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        📊 Upload CSV
                                    </a>

                                    @if($selectedDate)
                                        <button wire:click="clearDateSelection"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            🔄 Limpar Filtro
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600/50 dark:bg-gray-900/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border border-white/20 w-96 shadow-2xl rounded-2xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 shadow-lg">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mt-4">🗑️ Excluir Transação</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 px-4">
                        Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita e todos os dados serão perdidos permanentemente.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 mt-6 px-4">
                        <button wire:click="cancelDelete"
                                class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                            🔙 Cancelar
                        </button>
                        <button wire:click="deleteInvoice"
                                class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                            🗑️ Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Custom Styles -->
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

        .calendar-day.has-invoices {
            position: relative;
        }

        .calendar-day.has-invoices::after {
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

        /* Invoice Card Interactions */
        .invoice-card:hover .card-actions {
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

        .invoice-card:hover .card-actions {
            margin-top: 0.75rem !important;
            padding-top: 0.75rem !important;
        }

        .invoice-card.expanded .expand-icon {
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

        .invoice-card.expanded .card-details {
            max-height: 200px;
            margin-top: 1rem !important;
            padding-top: 1rem !important;
            border-top: 1px solid rgba(209, 213, 219, 0.5);
        }

        .dark .invoice-card.expanded .card-details {
            border-top-color: rgba(75, 85, 99, 0.5);
        }
    </style>

    <!-- JavaScript for Enhanced Interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add keyboard navigation for calendar
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    @if($bankId)
                        window.location.href = '{{ route("invoices.create", ["bankId" => $bankId]) }}';
                    @endif
                }

                // ESC para limpar filtro
                if (e.key === 'Escape' && @this.selectedDate) {
                    @this.clearDateFilter();
                }

                // Ctrl+R para recarregar dados
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    @this.loadData();
                    showNotification('🔄 Dados atualizados!', 'success');
                }
            });

            // Add tooltips to calendar days
            const calendarDays = document.querySelectorAll('.calendar-day');
            calendarDays.forEach(day => {
                if (day.classList.contains('has-invoices')) {
                    day.setAttribute('title', 'Clique para filtrar por este dia');
                }
            });

            // File upload handler
            document.getElementById('fileUpload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Simular upload com feedback visual
                    const button = document.querySelector('[onclick*="fileUpload"]');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>🔄 Processando...';

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        showNotification('📊 Arquivo processado com sucesso!\n\nFuncionalidade de upload será implementada em breve.', 'success');
                    }, 2000);
                }
            });

            // Initialize dark mode from localStorage
            if (localStorage.getItem('dark-mode') === 'true') {
                document.documentElement.classList.add('dark');
            }

            // Print styles
            const style = document.createElement('style');
            style.textContent = `
                @media print {
                    .no-print { display: none !important; }
                    body { background: white !important; }
                    .bg-gradient-to-br { background: white !important; }
                    .backdrop-blur-xl { backdrop-filter: none !important; }
                    .shadow-xl { box-shadow: none !important; }
                    .floating-animation { display: none !important; }
                }
            `;
            document.head.appendChild(style);
        });

        // Card expansion functionality
        function toggleCardExpansion(card) {
            const isExpanded = card.classList.contains('expanded');
            const expandIcon = card.querySelector('.expand-icon');
            const cardDetails = card.querySelector('.card-details');

            if (isExpanded) {
                // Collapse
                card.classList.remove('expanded');
                expandIcon.style.transform = 'rotate(0deg)';
            } else {
                // Expand
                card.classList.add('expanded');
                expandIcon.style.transform = 'rotate(180deg)';
            }
        }

        // Enhanced UX for date filtering (without Livewire dependency)
        document.addEventListener('livewire:load', function () {
            window.livewire.on('dateFiltered', () => {
                // Add animation when date is filtered
                const transactionsSection = document.querySelector('.transactions-section');
                if (transactionsSection) {
                    transactionsSection.style.opacity = '0';
                    transactionsSection.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        transactionsSection.style.transition = 'all 0.3s ease';
                        transactionsSection.style.opacity = '1';
                        transactionsSection.style.transform = 'translateY(0)';
                    }, 100);
                }

                showNotification('📅 Filtro aplicado!', 'info');
            });
        });

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
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

        // Export functions
        function exportToCSV() {
            const data = @json($invoices);
            let csv = 'Data,Descrição,Categoria,Valor,Cliente\n';

            data.forEach(invoice => {
                csv += `${invoice.invoice_date},"${invoice.description}","${invoice.category?.name || 'Sem categoria'}",${Math.abs(invoice.value)},"${invoice.client?.name || ''}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `despesas_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);

            showNotification('📊 Dados exportados para CSV!', 'success');
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
            if (e.target.classList.contains('calendar-day') && e.target.classList.contains('has-invoices')) {
                e.target.style.transform = 'scale(1.1)';
            }
        });

        document.addEventListener('mouseout', function(e) {
            if (e.target.classList.contains('calendar-day')) {
                e.target.style.transform = 'scale(1)';
            }
        });
    </script>
</div>
