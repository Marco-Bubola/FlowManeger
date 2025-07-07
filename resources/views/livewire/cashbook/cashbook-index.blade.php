<div class="min-h-screen bg-gradient-to-br ">
    <!-- Header -->
    <div class="shadow-lg border-b ">
        <div class="w-full px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Title and Icon -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Livro Caixa</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Controle financeiro inteligente</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <!-- Filters Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                            <i class="fas fa-filter mr-2"></i>
                            Filtros
                            <i class="fas fa-chevron-down ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-100 dark:border-gray-700">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <i class="fas fa-sliders-h mr-2 text-blue-600"></i>
                                    Filtros Avançados
                                </h3>
                                <div class="space-y-4">
                                    <!-- Search -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-search mr-1"></i>
                                            Buscar
                                        </label>
                                        <input wire:model.live="search" type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            placeholder="Descrição ou observações...">
                                    </div>

                                    <!-- Category Filter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-tags mr-1"></i>
                                            Categoria
                                        </label>
                                        <select wire:model.live="categoryFilter"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Todas as categorias</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Type Filter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-exchange-alt mr-1"></i>
                                            Tipo
                                        </label>
                                        <select wire:model.live="typeFilter"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Todos os tipos</option>
                                            @foreach($types as $type)
                                            <option value="{{ $type->id_type }}">{{ $type->desc_type }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-flag mr-1"></i>
                                            Status
                                        </label>
                                        <select wire:model.live="statusFilter"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Todos</option>
                                            <option value="confirmed">Confirmadas</option>
                                            <option value="pending">Pendentes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                                    <button wire:click="clearFilters"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-times mr-2"></i>
                                        Limpar Filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('cashbook.create') }}"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus mr-2"></i>
                        Nova Transação
                    </a>
                    <a href="{{ route('cashbook.upload') }}"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-upload mr-2"></i>
                        Upload
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout - 80vw centered -->
    <div class="w-full max-w-[80vw] mx-auto px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            <!-- Left Column - Calendar & Navigation (50%) -->
            <div class="space-y-6">
                <!-- Month Navigation -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-calendar-alt mr-3"></i>
                                Navegação de Meses
                            </h2>
                            <div class="bg-white bg-opacity-20 rounded-lg px-3 py-1 backdrop-blur-sm">
                                <span class="text-white text-sm font-medium">{{ $monthName }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Previous Month -->
                            <button wire:click="changeMonth('previous')"
                                class="group p-3 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 hover:from-gray-100 hover:to-gray-200 dark:hover:from-gray-600 dark:hover:to-gray-500 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-500 group-hover:bg-gray-300 dark:group-hover:bg-gray-400 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <i class="fas fa-chevron-left text-gray-600 dark:text-gray-200 group-hover:text-gray-800 dark:group-hover:text-gray-100 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $prevMonth['name'] ?? 'Mês Anterior' }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            <span class="font-bold {{ ($prevMonth['balance'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                R$ {{ number_format(abs($prevMonth['balance'] ?? 0), 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>

                            <!-- Current Month -->
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg">
                                <div class="text-center text-white">
                                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-calendar-check text-lg"></i>
                                    </div>
                                    <div class="text-sm font-medium mb-1">Mês Atual</div>
                                    <div class="text-lg font-bold">{{ $monthName }}</div>
                                    <div class="text-xs opacity-75 mt-1">
                                        R$ {{ number_format(abs($totals['balance'] ?? 0), 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Next Month -->
                            <button wire:click="changeMonth('next')"
                                class="group p-3 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 hover:from-gray-100 hover:to-gray-200 dark:hover:from-gray-600 dark:hover:to-gray-500 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-2">
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $nextMonth['name'] ?? 'Próximo Mês' }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            <span class="font-bold {{ ($nextMonth['balance'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                R$ {{ number_format(abs($nextMonth['balance'] ?? 0), 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-500 group-hover:bg-gray-300 dark:group-hover:bg-gray-400 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <i class="fas fa-chevron-right text-gray-600 dark:text-gray-200 group-hover:text-gray-800 dark:group-hover:text-gray-100 text-sm"></i>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>                <!-- Calendar Grid -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Calendário
                            </h3>
                            <div class="flex items-center space-x-3 text-xs">
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                    <span class="text-white">Receitas</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                                    <span class="text-white">Despesas</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                    <span class="text-white">Hoje</span>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <!-- Days of Week Header -->
                            <div class="grid grid-cols-7 gap-1 mb-3">
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">DOM</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">SEG</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">TER</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">QUA</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">QUI</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">SEX</div>
                                <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2">SÁB</div>
                            </div>

                            <!-- Calendar Days Grid -->
                            <div class="space-y-1">
                                @foreach($calendarData as $week)
                                <div class="grid grid-cols-7 gap-1">
                                    @foreach($week as $day)
                                    <div class="relative">
                                        <button wire:click="selectDay('{{ $day['date'] }}')"
                                            class="w-full h-12 p-1 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md text-xs
                                                           {{ $day['is_current_month'] ? 'bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600' : 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500' }}
                                                           {{ $day['is_today'] ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : '' }}
                                                           {{ $day['is_weekend'] && $day['is_current_month'] ? 'bg-gray-100 dark:bg-gray-600' : '' }}
                                                           {{ $day['transaction_count'] > 0 ? 'border-2 border-purple-200 dark:border-purple-600' : 'border border-gray-200 dark:border-gray-600' }}">

                                            <!-- Day Number -->
                                            <div class="font-bold mb-1
                                                            {{ $day['is_current_month'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}
                                                            {{ $day['is_today'] ? 'text-blue-600 dark:text-blue-300' : '' }}">
                                                {{ $day['day'] }}
                                            </div>

                                            <!-- Transaction Indicators -->
                                            @if($day['transaction_count'] > 0)
                                            <div class="flex items-center justify-center space-x-1">
                                                @if($day['has_income'])
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                @endif
                                                @if($day['has_expense'])
                                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                                @endif
                                            </div>
                                            @endif
                                        </button>

                                            <!-- Today indicator -->
                                            @if($day['is_today'])
                                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full flex items-center justify-center">
                                                <i class="fas fa-star text-white" style="font-size: 8px;"></i>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Clear Date Filter Button -->
                                @if($dateStart && $dateEnd)
                                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 text-center">
                                    <button wire:click="clearDateFilter"
                                        class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-700 hover:from-gray-200 hover:to-gray-300 dark:hover:from-gray-500 dark:hover:to-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                                        <i class="fas fa-times mr-2"></i>
                                        Limpar Filtro
                                    </button>
                                </div>
                                @endif
                            </div>
                    </div>
                </div>


            </div>

            <!-- Right Column - Transactions (50%) -->
            <div class="space-y-6">
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
                                            R$ {{ number_format(abs($totals['balance'] ?? 0), 2, ',', '.') }}
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
                                                        R$ {{ number_format($categoryGroup['total_receita'], 0, ',', '.') }}
                                                    </div>
                                                @endif
                                                @if($categoryGroup['total_despesa'] > 0)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                        <i class="fas fa-arrow-down mr-1"></i>
                                                        R$ {{ number_format($categoryGroup['total_despesa'], 0, ',', '.') }}
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
                                                            {{ $transaction['type_id'] == 1 ? '+' : '-' }}R$ {{ number_format($transaction['value'], 0, ',', '.') }}
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

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showDeleteModal') }" x-show="show">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700"
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4 sm:p-8 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-12 sm:w-12">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 dark:text-white mb-2">Confirmar Exclusão</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita e todos os dados serão perdidos permanentemente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 sm:px-8 sm:py-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-600">
                    <button wire:click="deleteTransaction" type="button"
                        class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-6 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Confirmar Exclusão
                    </button>
                    <button wire:click="cancelDelete" type="button"
                        class="mt-3 w-full inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-6 py-3 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

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

    // Adicionar loading states para melhor UX
    document.addEventListener('livewire:init', () => {
        Livewire.on('transaction-deleted', () => {
            // Mostrar notificação de sucesso
            console.log('Transação excluída com sucesso!');
        });
    });
</script>
@endpush