<!-- Página Dashboard Financeiro Moderna -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 p-4">
    <!-- Header da Página -->
    <div class="mb-8">
        <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 shadow-2xl overflow-hidden">
            <!-- Elementos decorativos de fundo -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 via-purple-600/20 to-indigo-700/20"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-48 translate-x-48"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/5 rounded-full translate-y-36 -translate-x-36"></div>

            <div class="relative z-10">
                <!-- Header com título e estatísticas em uma linha -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Título -->
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-2xl p-3 mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Dashboard Financeiro</h1>
                            <p class="text-blue-100">Gerencie seus cartões e despesas</p>
                        </div>
                    </div>

                    <!-- Estatísticas rápidas no header - agora em linha -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-red-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Total Despesas</p>
                                    <p class="text-white font-bold text-sm">R$ {{ number_format(abs($totalMonth), 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-yellow-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Maior Gasto</p>
                                    <p class="text-white font-bold text-sm">R$ {{ $highestInvoice ? number_format(abs($highestInvoice['value']), 2, ',', '.') : '0,00' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-green-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Menor Gasto</p>
                                    <p class="text-white font-bold text-sm">R$ {{ $lowestInvoice ? number_format(abs($lowestInvoice['value']), 2, ',', '.') : '0,00' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-blue-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Transações</p>
                                    <p class="text-white font-bold text-sm">{{ $totalTransactions }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Esquerda - Calendário e Estatísticas (1/4) -->
        <div class="w-full lg:w-1/4 flex flex-col space-y-6">
            <!-- Calendário Moderno -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl p-2 mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Calendário</h3>
                    </div>
                </div>

                <!-- Navegação do Calendário com selects e botões -->
                <div class="flex items-center justify-between mb-6">
                    <button wire:click="previousMonth"
                        class="p-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <div class="flex items-center space-x-2">
                        <select wire:model="month" class="rounded-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 dark:text-white text-sm font-medium shadow-inner focus:ring-2 focus:ring-purple-500">
                            @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select>
                        <select wire:model="year" class="rounded-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 dark:text-white text-sm font-medium shadow-inner focus:ring-2 focus:ring-purple-500">
                            @foreach(range(now()->year-5, now()->year+2) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="nextMonth"
                        class="p-2 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
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
                    @foreach($calendarDays as $day)
                    <div wire:click="selectDate('{{ $day['date'] }}')"
                        class="relative min-h-[40px] p-2 border-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 {{ $day['isCurrentMonth'] ? 'bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 hover:from-blue-50 hover:to-purple-50 dark:hover:from-gray-700 dark:hover:to-gray-600 border-gray-200 dark:border-gray-600' : 'bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 border-gray-300 dark:border-gray-500' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/25' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg shadow-green-500/25' : '' }}">
                        <div class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-600 dark:text-green-400' : '' }}">
                            {{ $day['day'] }}
                        </div>
                        @if(!empty($day['invoices']))
                        <div class="absolute bottom-1 right-1">
                            <div class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg"></div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if($selectedDate)
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

            <!-- Gráfico Pie ApexCharts Moderno -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-2 mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Gastos por Categoria</h4>
                </div>
                <div wire:ignore id="pie-category-chart" class="w-full h-64"></div>
            </div>
        </div>
        <!-- Área Central - Transações (2/4) -->
        <div class="w-full lg:w-2/4 flex flex-col">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6 flex flex-col h-full">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-xl p-2 mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                @if($selectedDate)
                                Despesas de {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                                @else
                                Despesas do Mês
                                @endif
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Gerencie seus gastos</p>
                        </div>
                    </div>

                    <!-- Botão para adicionar nova despesa -->
                    <button class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-4 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nova Despesa
                    </button>
                </div>

                <!-- Container com scroll personalizado para as transações -->
                <div id="transactionsContainer" class="flex-grow overflow-y-auto scrollbar-modern pr-2 max-h-[700px]">
                    <!-- Grid responsivo com tamanhos fixos -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 auto-rows-fr">
                    @if($selectedDate)
                    @if(isset($calendarInvoices[$selectedDate]))
                    @foreach($calendarInvoices[$selectedDate] as $invoice)
                    <div class="invoice-card bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden cursor-pointer group h-48 flex flex-col"
                         onclick="toggleCardExpansion(this)">
                        <!-- Card Header com gradiente baseado no tipo (apenas despesas/negativos) -->
                        <div class="bg-gradient-to-r from-red-500 to-pink-600 p-3 flex-shrink-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    @if(isset($invoice['category']) && $invoice['category']['icon'])
                                        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                            @if(Str::startsWith($invoice['category']['icon'], 'icons8-'))
                                            <span class="w-4 h-4 {{ $invoice['category']['icon'] }} text-white"></span>
                                            @else
                                            <img class="w-4 h-4" src="{{ $invoice['category']['icon'] }}" alt="{{ $invoice['category']['name'] ?? 'Categoria' }}">
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-white font-bold text-base">
                                            - R$ {{ number_format(abs($invoice['value']), 2, ',', '.') }}
                                        </p>
                                        <p class="text-white/80 text-xs">
                                            {{ $invoice['category']['name'] ?? 'Sem Categoria' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-3 h-3 text-white expand-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 flex-grow flex flex-col">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 mb-3 flex-shrink-0" onclick="event.stopPropagation()">
                                {{ $invoice['description'] }}
                            </h4>

                            <!-- Expandable Details (Hidden by default) -->
                            <div class="card-details space-y-2 flex-grow">
                                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                    <div class="w-5 h-5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-2">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</span>
                                </div>

                                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                    <div class="w-5 h-5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-2">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ $invoice['bank']['name'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions (Hidden by default, shown on hover) -->
                        <div class="card-actions bg-gradient-to-r from-gray-50/90 to-white/90 dark:from-gray-700/90 dark:to-gray-600/90 backdrop-blur-sm px-6 py-4 flex items-center justify-between opacity-0 transform translate-y-2 transition-all duration-300"
                             onclick="event.stopPropagation()">
                            <div class="flex items-center space-x-2">
                                <button wire:click="editInvoice({{ $invoice['id'] }})"
                                   class="inline-flex items-center p-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                   title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <button wire:click="copyInvoice({{ $invoice['id'] }})"
                                   class="inline-flex items-center p-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                   title="Copiar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>

                                <button wire:click="confirmDeleteInvoice({{ $invoice['id'] }})"
                                        class="inline-flex items-center p-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                        title="Excluir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded-lg">
                                ID: {{ $invoice['id'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-12 lg:col-span-2">
                        <div class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl p-8">
                            <div class="w-16 h-16 bg-gradient-to-r from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">Nenhuma despesa encontrada</h3>
                            <p class="text-gray-500 dark:text-gray-400">Não há despesas registradas para {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.</p>
                        </div>
                    </div>
                    @endif
                    @else
                    @if(!empty($allInvoices))
                    @foreach($allInvoices as $invoice)
                    <div class="invoice-card bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden cursor-pointer group"
                         onclick="toggleCardExpansion(this)">
                        <!-- Card Header - Sempre vermelho para despesas -->
                        <div class="bg-gradient-to-r from-red-500 to-pink-600 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    @if(isset($invoice['category']) && $invoice['category']['icon'])
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                            @if(Str::startsWith($invoice['category']['icon'], 'icons8-'))
                                            <span class="w-5 h-5 {{ $invoice['category']['icon'] }} text-white"></span>
                                            @else
                                            <img class="w-5 h-5" src="{{ $invoice['category']['icon'] }}" alt="{{ $invoice['category']['name'] ?? 'Categoria' }}">
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-white font-bold text-lg">
                                            - R$ {{ number_format(abs($invoice['value']), 2, ',', '.') }}
                                        </p>
                                        <p class="text-white/80 text-sm">
                                            {{ $invoice['category']['name'] ?? 'Sem Categoria' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-4 h-4 text-white expand-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 mb-3" onclick="event.stopPropagation()">
                                {{ $invoice['description'] }}
                            </h4>

                            <!-- Expandable Details (Hidden by default) -->
                            <div class="card-details space-y-3">
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</span>
                                </div>

                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ $invoice['bank']['name'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions (Hidden by default, shown on hover) -->
                        <div class="card-actions bg-gradient-to-r from-gray-50/90 to-white/90 dark:from-gray-700/90 dark:to-gray-600/90 backdrop-blur-sm px-6 py-4 flex items-center justify-between opacity-0 transform translate-y-2 transition-all duration-300"
                             onclick="event.stopPropagation()">
                            <div class="flex items-center space-x-2">
                                <button wire:click="editInvoice({{ $invoice['id'] }})"
                                   class="inline-flex items-center p-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                   title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <button wire:click="copyInvoice({{ $invoice['id'] }})"
                                   class="inline-flex items-center p-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                   title="Copiar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>

                                <button wire:click="confirmDeleteInvoice({{ $invoice['id'] }})"
                                        class="inline-flex items-center p-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                        title="Excluir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded-lg">
                                ID: {{ $invoice['id'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-12 lg:col-span-2">
                        <div class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl p-8">
                            <div class="w-16 h-16 bg-gradient-to-r from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">Nenhuma despesa encontrada</h3>
                            <p class="text-gray-500 dark:text-gray-400">Não há despesas registradas para este mês.</p>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        <!-- Sidebar Direita - Cartões (1/4) -->
        <div class="w-full lg:w-1/4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl p-2 mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Meus Cartões</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($paginatedBanks) }} cartão(ões)</p>
                        </div>
                    </div>
                    <a href="{{ route('banks.create') }}"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white p-2 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                        title="Adicionar Novo Cartão">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </a>
                </div>

                <!-- Container com scroll para os cartões -->
                <div class="space-y-4 max-h-[600px] overflow-y-auto scrollbar-modern pr-2">
                    @foreach ($paginatedBanks as $bank)
                    @php
                    $cardColors = [
                    'inter' => 'from-orange-500 via-orange-600 to-orange-700',
                    'nubank' => 'from-purple-500 via-purple-600 to-purple-700',
                    'santander' => 'from-red-500 via-red-600 to-red-700',
                    'caixa' => 'from-blue-500 via-blue-600 to-blue-700',
                    'bb' => 'from-yellow-400 via-yellow-500 to-yellow-600',
                    'itau' => 'from-blue-600 via-blue-700 to-blue-800',
                    'bradesco' => 'from-red-600 via-red-700 to-red-800'
                    ];
                    $cardColor = 'from-gray-900 via-gray-800 to-gray-900';
                    foreach ($cardColors as $bankName => $color) {
                    if (stripos($bank->name, $bankName) !== false || stripos($bank->caminho_icone, $bankName) !== false) {
                    $cardColor = $color;
                    break;
                    }
                    }
                    @endphp
                    <div class="group relative">
                        <div class="relative bg-gradient-to-br {{ $cardColor }} rounded-2xl shadow-2xl border border-gray-700 dark:border-gray-600 p-6 h-40 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl group-hover:shadow-black/25 overflow-hidden">
                            <!-- Elementos decorativos de fundo -->
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-white/5 to-transparent rounded-2xl"></div>
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>

                            <!-- Logo do banco -->
                            <div class="absolute top-4 right-4">
                                <div class="w-14 h-14 rounded-xl bg-white/20 dark:bg-black/30 backdrop-blur-sm p-2 flex items-center justify-center border border-white/20">
                                    <img class="w-10 h-10 object-contain" src="{{ asset($bank->caminho_icone) }}" alt="{{ $bank->name }}">
                                </div>
                            </div>

                            <!-- Chip do cartão -->
                            <div class="absolute top-4 left-4">
                                <div class="w-10 h-7 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-md flex items-center justify-center shadow-lg">
                                    <div class="w-6 h-4 bg-yellow-300 rounded-sm opacity-80"></div>
                                </div>
                            </div>

                            <!-- Número do cartão -->
                            <div class="absolute top-16 left-4">
                                <div class="text-white/90 text-sm font-mono tracking-wider font-bold">
                                    •••• •••• •••• {{ preg_replace('/[^0-9]/', '', $bank->description) ? substr(preg_replace('/[^0-9]/', '', $bank->description), -4) : '1234' }}
                                </div>
                            </div>

                            <!-- Nome do cartão -->
                            <div class="absolute bottom-8 left-4 right-4">
                                <h3 class="text-white font-bold text-base truncate">{{ $bank->name }}</h3>
                                <p class="text-white/70 text-xs">Cartão de Crédito</p>
                            </div>

                            <!-- Bandeira (VISA/Master) -->
                            <div class="absolute bottom-4 right-4">
                                <div class="text-white/80 text-sm font-bold bg-white/10 px-3 py-1 rounded-lg backdrop-blur-sm">VISA</div>
                            </div>
                        </div>

                        <!-- Overlay com ações (aparece no hover) -->
                        <div class="absolute inset-0 bg-black/60 rounded-2xl opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center backdrop-blur-sm">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('invoices.index', ['bankId' => $bank->id_bank]) }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 backdrop-blur-sm rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                    title="Visualizar Transações">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Ver
                                </a>
                                <a href="{{ route('banks.edit', $bank->id_bank) }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-gray-500 to-gray-600 backdrop-blur-sm rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                    title="Editar Cartão">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Editar
                                </a>
                                <button wire:click="openDeleteModal({{ $bank->id_bank }})"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 backdrop-blur-sm rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                    title="Excluir Cartão">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if ($paginatedBanks->isEmpty())
                    <div class="text-center py-12">
                        <div class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl p-8">
                            <div class="w-16 h-16 bg-gradient-to-r from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">Nenhum Cartão</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                Adicione seu primeiro cartão para começar a controlar suas despesas!
                            </p>
                            <a href="{{ route('banks.create') }}"
                                class="inline-flex items-center bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-4 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Adicionar Cartão
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Paginação moderna -->
                @if($paginatedBanks->hasPages())
                <div class="mt-6 flex justify-center items-center space-x-2">
                    @if($paginatedBanks->onFirstPage())
                    <button class="px-3 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    @else
                    <button class="px-3 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 transform hover:scale-105" wire:click="goToPage({{ $paginatedBanks->currentPage() - 1 }})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    @endif

                    @for($i = 1; $i <= $paginatedBanks->lastPage(); $i++)
                    <button class="px-3 py-2 rounded-xl font-medium transition-all duration-200 transform hover:scale-105 {{ $paginatedBanks->currentPage() === $i ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                    wire:click="goToPage({{ $i }})">{{ $i }}</button>
                    @endfor

                    @if($paginatedBanks->hasMorePages())
                    <button class="px-3 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 transform hover:scale-105" wire:click="goToPage({{ $paginatedBanks->currentPage() + 1 }})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @else
                    <button class="px-3 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Delete Moderno -->
    @if($showDeleteModal)
    <div id="deleteBankModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="relative p-6 w-full max-w-md max-h-full">
            <div class="relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/30">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200/50 dark:border-gray-600/50 rounded-t-3xl">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-xl p-2 mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Confirmar Exclusão
                        </h3>
                    </div>
                    <button type="button" wire:click="closeDeleteModal"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-xl p-2 transition-colors duration-200 dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-r from-red-100 to-pink-100 dark:from-red-900/30 dark:to-pink-900/30 mb-4">
                        <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3 class="mb-5 text-lg font-bold text-gray-700 dark:text-gray-300">Tem certeza que deseja excluir este cartão?</h3>
                    <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Esta ação não pode ser desfeita e todas as transações associadas a este cartão serão perdidas.</p>

                    <div class="flex justify-center items-center space-x-4">
                        <button type="button" wire:click="closeDeleteModal"
                            class="inline-flex items-center text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-xl text-sm font-medium px-5 py-3 transition-all duration-200 transform hover:scale-105 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </button>
                        <button type="button" wire:click="delete"
                            class="inline-flex items-center text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-xl text-sm font-medium px-5 py-3 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Sim, excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Estilos CSS Modernos -->
    <style>
        /* Scrollbar customizada */
        .scrollbar-modern {
            scrollbar-width: thin;
            scrollbar-color: rgba(107, 114, 128, 0.3) transparent;
        }

        .scrollbar-modern::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-modern::-webkit-scrollbar-track {
            background: rgba(107, 114, 128, 0.1);
            border-radius: 10px;
        }

        .scrollbar-modern::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(147, 51, 234, 0.5));
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .scrollbar-modern::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(147, 51, 234, 0.8));
        }

        /* Limitação de texto */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Animações para os cards de invoice */
        .invoice-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

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
            border-top: 1px solid rgba(209, 213, 219, 0.3);
        }

        .dark .invoice-card.expanded .card-details {
            border-top-color: rgba(75, 85, 99, 0.3);
        }

        /* Animações de hover suaves */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Gradientes animados */
        @keyframes gradient-shift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        .animated-gradient {
            background: linear-gradient(-45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b);
            background-size: 400% 400%;
            animation: gradient-shift 8s ease infinite;
        }

        /* Efeito de vidro */
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        /* Animação de entrada */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsividade melhorada */
        @media (max-width: 768px) {
            .invoice-card {
                margin-bottom: 1rem;
            }

            .card-actions {
                position: static !important;
                opacity: 1 !important;
                transform: none !important;
                max-height: none !important;
                margin-top: 1rem !important;
                padding-top: 1rem !important;
                border-top: 1px solid rgba(209, 213, 219, 0.3);
            }
        }
    </style>

    <!-- JavaScript Melhorado -->
    <script>
        // Card expansion functionality melhorada
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

        // Adiciona animação de entrada aos cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.invoice-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });

        // Smooth scrolling para containers
        const containers = document.querySelectorAll('.scrollbar-modern');
        containers.forEach(container => {
            container.style.scrollBehavior = 'smooth';
        });

        // Performance optimization para hover effects
        let hoverTimeout;
        document.addEventListener('mouseover', function(e) {
            if (e.target.closest('.invoice-card')) {
                clearTimeout(hoverTimeout);
                const card = e.target.closest('.invoice-card');
                const actions = card.querySelector('.card-actions');
                if (actions) {
                    actions.style.transition = 'all 0.2s ease-out';
                }
            }
        });

        document.addEventListener('mouseout', function(e) {
            if (e.target.closest('.invoice-card')) {
                hoverTimeout = setTimeout(() => {
                    const card = e.target.closest('.invoice-card');
                    const actions = card.querySelector('.card-actions');
                    if (actions) {
                        actions.style.transition = 'all 0.3s ease-out';
                    }
                }, 100);
            }
        });
    </script>

    <!-- Script do Gráfico ApexCharts Melhorado -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chart = null;

            function renderPieChart(data) {
                // Sempre busca o elemento atualizado do DOM
                const chartDiv = document.querySelector("#pie-category-chart");
                if (!chartDiv) return;

                // Sempre destrói o gráfico anterior antes de criar um novo
                if (chart) {
                    chart.destroy();
                    chart = null;
                }

                const categories = Array.isArray(data) ? data.map(item => item.name) : [];
                const values = Array.isArray(data) ? data.map(item => parseFloat(item.total)) : [];

                if (values.length === 0 || values.every(v => v === 0)) {
                    chartDiv.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full py-12 text-gray-400">
                            <div class="w-16 h-16 bg-gradient-to-r from-gray-300 to-gray-400 rounded-2xl flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-600 dark:text-gray-300">Nenhum dado disponível</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adicione algumas despesas para ver o gráfico</span>
                        </div>
                    `;
                    return;
                }

                chartDiv.innerHTML = '';
                const options = {
                    series: values,
                    chart: {
                        type: 'donut',
                        height: 250,
                        fontFamily: 'Inter, ui-sans-serif, system-ui',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        }
                    },
                    labels: categories,
                    legend: {
                        show: false
                    },
                    colors: [
                        '#ef4444', '#f97316', '#f59e0b', '#eab308',
                        '#84cc16', '#22c55e', '#10b981', '#14b8a6',
                        '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1',
                        '#8b5cf6', '#a855f7', '#c084fc', '#d946ef',
                        '#ec4899', '#f43f5e'
                    ],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '14px',
                                        fontWeight: 600,
                                        color: '#374151',
                                        formatter: function (val) {
                                            return val;
                                        }
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        fontWeight: 700,
                                        color: '#111827',
                                        formatter: function (val) {
                                            return 'R$ ' + parseFloat(val).toLocaleString('pt-BR', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    },
                                    total: {
                                        show: true,
                                        showAlways: false,
                                        label: 'Total Gasto',
                                        fontSize: '14px',
                                        fontWeight: 600,
                                        color: '#6b7280',
                                        formatter: function (w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return 'R$ ' + total.toLocaleString('pt-BR', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold',
                            colors: ['#ffffff']
                        },
                        formatter: function(val, opts) {
                            const seriesValue = opts.w.globals.series[opts.seriesIndex];
                            return 'R$ ' + seriesValue.toLocaleString('pt-BR', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        },
                        dropShadow: {
                            enabled: true,
                            top: 1,
                            left: 1,
                            blur: 1,
                            color: '#000',
                            opacity: 0.3
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['#ffffff']
                    },
                    tooltip: {
                        enabled: true,
                        theme: 'light',
                        style: {
                            fontSize: '12px',
                        },
                        y: {
                            formatter: function(val, opts) {
                                const total = opts.series.reduce((a, b) => a + b, 0);
                                const percentage = ((val / total) * 100).toFixed(1);
                                return `R$ ${val.toLocaleString('pt-BR', { minimumFractionDigits: 2 })} (${percentage}%)`;
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 200
                            },
                            legend: {
                                show: false
                            }
                        }
                    }]
                };
                chart = new ApexCharts(chartDiv, options);
                chart.render();
            }

            // Inicializa com os dados do backend
            renderPieChart(@json($pieData ?? []));

            // Atualiza quando o Livewire emitir o evento
            if (window.Livewire) {
                Livewire.on('update-pie-chart', ({ data }) => {
                    renderPieChart(data);
                });
            }
        });
    </script>

</div>
