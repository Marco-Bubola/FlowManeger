<!-- Página Dashboard Financeiro Moderna -->
<div
    class="w-full">
    <!-- Header da Página -->
    <div class="mb-8">
        <div
            class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 shadow-2xl overflow-hidden">
            <!-- Elementos decorativos de fundo -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 via-purple-600/20 to-indigo-700/20"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-48 translate-x-48"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/5 rounded-full translate-y-36 -translate-x-36"></div>

            <div class="relative z-10">
                <!-- Breadcrumb dentro do header -->
                <div class="flex items-center gap-2 text-sm text-white/70 mb-4">
                    <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-white font-medium">
                        <i class="fas fa-credit-card mr-1"></i>Bancos e Cartões
                    </span>
                </div>

                <!-- Header com título e estatísticas em uma linha -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Título -->
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-2xl p-3 mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
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
                                    <svg class="w-4 h-4 text-red-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Total Despesas</p>
                                    <p class="text-white font-bold text-sm">R$
                                        {{ number_format(abs($totalMonth), 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-yellow-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-yellow-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Maior Gasto</p>
                                    <p class="text-white font-bold text-sm">R$
                                        {{ $highestInvoice ? number_format(abs($highestInvoice['value']), 2, ',', '.') : '0,00' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-green-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-green-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-xs">Menor Gasto</p>
                                    <p class="text-white font-bold text-sm">R$
                                        {{ $lowestInvoice ? number_format(abs($lowestInvoice['value']), 2, ',', '.') : '0,00' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 min-w-[140px]">
                            <div class="flex items-center">
                                <div class="bg-blue-500/20 rounded-lg p-2 mr-2">
                                    <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                        </path>
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

    @php
        $displayTotal = collect($allInvoices ?? [])->sum(fn($invoice) => abs($invoice['value'] ?? 0));
        $displayLabel = $selectedDate
            ? 'Despesas de ' . \Carbon\Carbon::parse($selectedDate)->format('d/m/Y')
            : 'Despesas do mês';
        $categoriesChartData = collect($invoicesByCategory ?? [])->map(function ($group) {
            return [
                'label' => $group['category']['name'] ?? 'Sem categoria',
                'value' => $group['total'] ?? 0,
                'color' => $group['category']['hexcolor_category'] ?? '#6366f1',
            ];
        })->filter(fn($item) => ($item['value'] ?? 0) > 0)->values()->toArray();
        $currentMonthLabel = \Carbon\Carbon::create($year, $month, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');
    @endphp

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Esquerda - Calendário e Estatísticas (1/4) -->
        <div class="w-full lg:w-1/4 flex flex-col space-y-6">
            <div class="relative rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(139, 92, 246, 0.05) 100%); border: 2px solid rgba(59, 130, 246, 0.2);">
                <div class="absolute top-0 right-0 w-48 h-48 rounded-full blur-3xl opacity-10"
                    style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);"></div>

                <div class="relative px-5 py-4 backdrop-blur-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="relative group">
                            <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);"></div>
                            <div class="relative w-12 h-12 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(139, 92, 246, 0.15) 100%);">
                                <svg class="w-6 h-6 text-gray-900 dark:text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Calendário</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ \Illuminate\Support\Str::ucfirst($currentMonthLabel) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <button wire:click="previousMonth"
                            class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 transition-all duration-200 transform hover:scale-105 border border-white/20 dark:border-gray-700/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div class="flex items-center gap-2 flex-1">
                            <select wire:model.live="month"
                                class="flex-1 rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg text-gray-900 dark:text-white text-sm font-semibold shadow-md focus:ring-2 focus:ring-blue-500 transition-all">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMM') }}
                                    </option>
                                @endforeach
                            </select>
                            <select wire:model.live="year"
                                class="rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg text-gray-900 dark:text-white text-sm font-semibold shadow-md focus:ring-2 focus:ring-blue-500 transition-all">
                                @foreach (range(now()->year - 5, now()->year + 2) as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="nextMonth"
                            class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 transition-all duration-200 transform hover:scale-105 border border-white/20 dark:border-gray-700/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="grid grid-cols-7 gap-1 mb-3">
                        @foreach (['D', 'S', 'T', 'Q', 'Q', 'S', 'S'] as $label)
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                {{ $label }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7">
                        @foreach ($calendarDays as $day)
                            <div wire:click="selectDate('{{ $day['date'] }}')"
                                class="calendar-day relative min-h-[42px] p-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md {{ $day['isCurrentMonth'] ? 'bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-2 border-white/40 dark:border-gray-700/40 hover:border-blue-300 dark:hover:border-blue-600' : 'bg-gray-100/60 dark:bg-gray-700/60 backdrop-blur-sm border-2 border-gray-200/40 dark:border-gray-600/40' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/30 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 shadow-lg shadow-green-500/30' : '' }} {{ !empty($day['invoices']) ? 'has-invoices' : '' }}">
                                <div
                                    class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-700 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-700 dark:text-green-400' : '' }}">
                                    {{ $day['day'] }}
                                </div>
                                @if (!empty($day['invoices']))
                                    <div class="absolute bottom-1 right-1">
                                        <div class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg animate-pulse"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($selectedDate)
                        <div class="mt-4">
                            <button wire:click="clearDateSelection"
                                class="w-full text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 rounded-xl py-2.5 px-4 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Limpar Filtro
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border: 2px solid rgba(139, 92, 246, 0.2);">
                <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-10"
                    style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>

                <div class="relative px-6 py-5 backdrop-blur-sm">
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>
                            <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                style="background: linear-gradient(135deg, rgba(139,92,246,0.2) 0%, rgba(59,130,246,0.15) 100%);">
                                <svg class="w-7 h-7 text-gray-900 dark:text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.911c.969 0 1.371 1.24.588 1.81l-3.974 2.888a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.54 1.118l-3.973-2.888a1 1 0 00-1.176 0l-3.973 2.888c-.785.57-1.84-.196-1.54-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.08 10.1c-.783-.57-.38-1.81.588-1.81h4.912a1 1 0 00.95-.69l1.519-4.674z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-1">Gastos por Categoria</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Visualização dinâmica da distribuição de despesas</p>
                        </div>
                    </div>
                </div>

                <div class="relative px-6 pb-6">
                    @if (count($categoriesChartData) > 0)
                        <div id="apex-pie" class="w-full" style="height: 240px;"></div>
                        <script type="application/json" id="categories-data">@json($categoriesChartData)</script>
                    @else
                        <div
                            class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl p-8 text-center shadow-lg border border-white/20 dark:border-gray-700/30">
                            <div
                                class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhuma despesa categorizada</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Adicione transações para ver o gráfico atualizado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Área Central - Resumo e Grupos (2/4) -->
        <div class="w-full lg:w-2/4 flex flex-col space-y-4">
            @php
                $displayTotal = collect($allInvoices ?? [])->sum(fn($invoice) => abs($invoice['value'] ?? 0));
                $displayLabel = $selectedDate
                    ? 'Despesas de ' . \Carbon\Carbon::parse($selectedDate)->format('d/m/Y')
                    : 'Despesas do mês';
                $categoriesChartData = collect($invoicesByCategory ?? [])->map(function ($group) {
                    return [
                        'label' => $group['category']['name'] ?? 'Sem categoria',
                        'value' => $group['total'] ?? 0,
                        'color' => $group['category']['hexcolor_category'] ?? '#6366f1',
                    ];
                })->filter(fn($item) => ($item['value'] ?? 0) > 0)->values()->toArray();
            @endphp

            <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.08) 0%, rgba(249, 115, 22, 0.05) 100%); border: 2px solid rgba(244, 63, 94, 0.2);">
                <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-10"
                    style="background: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);"></div>

                <div class="relative px-6 py-5 backdrop-blur-sm flex items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                style="background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);"></div>
                            <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                style="background: linear-gradient(135deg, rgba(244,63,94,0.2) 0%, rgba(251,113,133,0.15) 100%);">
                                <svg class="w-7 h-7 text-gray-900 dark:text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $displayLabel }}</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white">Resumo de Gastos</h3>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">Total exibido</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white">R$
                            {{ number_format($displayTotal, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>



            <div class="cards-scroll max-h-[60vh] lg:max-h-[70vh] overflow-auto pr-2">
                @if (!empty($invoicesByCategory))
                    @foreach ($invoicesByCategory as $group)
                        @php
                            $cat = $group['category'] ?? [];
                            $catColor = $cat['hexcolor_category'] ?? '#f87171';
                            $catName = $cat['name'] ?? 'Sem categoria';
                            $catIcon = $cat['icone'] ?? 'fas fa-tag';
                            $catId = $group['category_id'] ?? 'sem_categoria';
                            $catTotal = $group['total'] ?? 0;
                            $catCount = isset($group['invoices']) ? count($group['invoices']) : 0;
                        @endphp

                        <div class="category-group mb-8 transform transition-all duration-500 expanded" data-category-id="{{ $catId }}">
                            <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                                style="background: linear-gradient(135deg, {{ $catColor }}15 0%, {{ $catColor }}08 100%); border: 2px solid {{ $catColor }}40;">
                                <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-20" style="background: {{ $catColor }};"></div>

                                <div class="relative px-6 py-5 backdrop-blur-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-4 flex-1">
                                            <div class="relative group">
                                                <div class="absolute inset-0 rounded-2xl blur-3xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                                    style="background: {{ $catColor }}33;"></div>
                                                <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                                    style="background: linear-gradient(135deg, {{ $catColor }}33 0%, {{ $catColor }}22 100%);">
                                                    @if (Str::startsWith($catIcon, 'fas') || Str::startsWith($catIcon, 'bi') || Str::startsWith($catIcon, 'ph'))
                                                        <i class="{{ $catIcon }} text-gray-900 text-2xl"></i>
                                                    @else
                                                        <img src="{{ $catIcon }}" alt="{{ $catName }}" class="w-8 h-8">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-black mb-1 text-gray-900 dark:text-white">
                                                    {{ $catName }}
                                                </h3>
                                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold text-gray-900 dark:text-white shadow-lg"
                                                        style="background: linear-gradient(135deg, {{ $catColor }}22 0%, {{ $catColor }}11 100%);">
                                                        <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium">{{ $catCount }} {{ $catCount === 1 ? 'transação' : 'transações' }}</span>
                                                    </span>
                                                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full font-bold text-black shadow-lg transform hover:scale-105 transition-transform"
                                                        style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span>R$ {{ number_format($catTotal, 2, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="category-toggle-btn flex items-center gap-3 px-4 py-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent"
                                            data-category-id="{{ $catId }}" data-expanded="true" style="--cat-color: {{ $catColor }};"
                                            aria-label="Alternar visibilidade da categoria {{ $catName }}">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 toggle-label">Ocultar</span>
                                            <div class="relative w-11 h-6 rounded-full transition-colors duration-300 toggle-switch"
                                                style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 toggle-thumb"
                                                    style="transform: translateX(20px);"></div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div class="group-invoices-wrapper overflow-hidden transition-all duration-500">
                                    <div class="px-6 pb-6">
                                        <div class="group-invoices grid grid-cols-1 sm:grid-cols-2 gap-5">
                                            @foreach ($group['invoices'] as $invoice)
                                                @php
                                                    $value = abs($invoice['value'] ?? 0);
                                                    $desc = $invoice['description'] ?? '';
                                                    $invoiceDate = isset($invoice['date']) ? \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') : '';
                                                    $bankName = $invoice['bank']['name'] ?? 'Banco não informado';
                                                @endphp

                                                <div class="invoice-card group relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden cursor-pointer"
                                                    onclick="toggleCardExpansion(this)">
                                                    <div class="absolute top-0 left-0 right-0 h-1.5 transition-all duration-300 group-hover:h-2"
                                                        style="background: linear-gradient(90deg, {{ $catColor }} 0%, {{ $catColor }}aa 100%);"></div>

                                                    <div class="p-4 pt-5 pb-3">
                                                        <div class="flex items-start justify-between gap-3 mb-3">
                                                            <div class="flex-1 min-w-0">
                                                                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 transition-all duration-300">
                                                                    {{ $desc }}
                                                                </h4>
                                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                    <span class="font-medium">{{ $invoiceDate }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="inline-flex items-center gap-1 px-3 py-2 rounded-full font-bold text-sm shadow-md text-black"
                                                                    style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                    <span>{{ number_format($value, 2, ',', '.') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-details">
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                                <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                </div>
                                                                <span class="font-medium">{{ $invoiceDate }}</span>
                                                            </div>
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                                <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                                    </svg>
                                                                </div>
                                                                <span class="font-medium">{{ $bankName }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl border border-white/20 dark:border-gray-700/30 shadow-xl p-12 text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nenhuma despesa encontrada</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @if ($selectedDate)
                                Não há transações registradas para {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.
                            @else
                                Ainda não existem transações para este período.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
        <!-- Sidebar Direita - Cartões (1/4) -->
        <div class="w-full lg:w-1/4">
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl p-2 mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Meus Cartões</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($paginatedBanks) }}
                                cartão(ões)</p>
                        </div>
                    </div>
                    <a href="{{ route('banks.create') }}"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white p-2 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                        title="Adicionar Novo Cartão">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
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
                                'bradesco' => 'from-red-600 via-red-700 to-red-800',
                            ];
                            $cardColor = 'from-gray-900 via-gray-800 to-gray-900';
                            foreach ($cardColors as $bankName => $color) {
                                if (
                                    stripos($bank->name, $bankName) !== false ||
                                    stripos($bank->caminho_icone, $bankName) !== false
                                ) {
                                    $cardColor = $color;
                                    break;
                                }
                            }
                        @endphp
                        <div class="group relative">
                            <div
                                class="relative bg-gradient-to-br {{ $cardColor }} rounded-2xl shadow-2xl border border-gray-700 dark:border-gray-600 p-6 h-40 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl group-hover:shadow-black/25 overflow-hidden">
                                <!-- Elementos decorativos de fundo -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-transparent via-white/5 to-transparent rounded-2xl">
                                </div>
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-16 translate-x-16">
                                </div>
                                <div
                                    class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12">
                                </div>

                                <!-- Logo do banco -->
                                <div class="absolute top-4 right-4">
                                    <div
                                        class="w-14 h-14 rounded-xl bg-white/20 dark:bg-black/30 backdrop-blur-sm p-2 flex items-center justify-center border border-white/20">
                                        <img class="w-10 h-10 object-contain" src="{{ asset($bank->caminho_icone) }}"
                                            alt="{{ $bank->name }}">
                                    </div>
                                </div>

                                <!-- Chip do cartão -->
                                <div class="absolute top-4 left-4">
                                    <div
                                        class="w-10 h-7 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-md flex items-center justify-center shadow-lg">
                                        <div class="w-6 h-4 bg-yellow-300 rounded-sm opacity-80"></div>
                                    </div>
                                </div>

                                <!-- Número do cartão -->
                                <div class="absolute top-16 left-4">
                                    <div class="text-white/90 text-sm font-mono tracking-wider font-bold">
                                        •••• •••• ••••
                                        {{ preg_replace('/[^0-9]/', '', $bank->description) ? substr(preg_replace('/[^0-9]/', '', $bank->description), -4) : '1234' }}
                                    </div>
                                </div>

                                <!-- Nome do cartão -->
                                <div class="absolute bottom-8 left-4 right-4">
                                    <h3 class="text-white font-bold text-base truncate">{{ $bank->name }}</h3>
                                    <p class="text-white/70 text-xs">Cartão de Crédito</p>
                                </div>

                                <!-- Bandeira (VISA/Master) -->
                                <div class="absolute bottom-4 right-4">
                                    <div
                                        class="text-white/80 text-sm font-bold bg-white/10 px-3 py-1 rounded-lg backdrop-blur-sm">
                                        VISA</div>
                                </div>
                            </div>

                            <!-- Overlay com ações (aparece no hover) -->
                            <div
                                class="absolute inset-0 bg-black/60 rounded-2xl opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center backdrop-blur-sm">
                                <div class="flex flex-wrap items-center justify-center gap-2 p-2">
                                    <!-- Linha 1: Ver Transações -->
                                    <div class="w-full flex justify-center mb-2">
                                        <a href="{{ route('invoices.index', ['bankId' => $bank->id_bank]) }}"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 backdrop-blur-sm rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                            title="Visualizar Transações">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Ver Transações
                                        </a>
                                    </div>

                                    <!-- Linha 2: Criar Invoice e Upload -->
                                    <div class="w-full flex justify-center gap-2 mb-2">
                                        <a href="{{ route('invoices.create', ['bankId' => $bank->id_bank]) }}"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 backdrop-blur-sm rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                            title="Criar Nova Invoice">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Nova
                                        </a>

                                        <a href="{{ route('invoices.upload', ['bankId' => $bank->id_bank]) }}"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-purple-600 backdrop-blur-sm rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                            title="Upload de Arquivo">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            Upload
                                        </a>
                                    </div>

                                    <!-- Linha 3: Editar e Excluir -->
                                    <div class="w-full flex justify-center gap-2">
                                        <a href="{{ route('banks.edit', $bank->id_bank) }}"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-gray-500 to-gray-600 backdrop-blur-sm rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                            title="Editar Cartão">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Editar
                                        </a>
                                        <button wire:click="openDeleteModal({{ $bank->id_bank }})"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 backdrop-blur-sm rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                            title="Excluir Cartão">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($paginatedBanks->isEmpty())
                        <div class="text-center py-12">
                            <div
                                class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl p-8">
                                <div
                                    class="w-16 h-16 bg-gradient-to-r from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">Nenhum Cartão
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                    Adicione seu primeiro cartão para começar a controlar suas despesas!
                                </p>
                                <a href="{{ route('banks.create') }}"
                                    class="inline-flex items-center bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-4 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Adicionar Cartão
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Paginação moderna -->
                @if ($paginatedBanks->hasPages())
                    <div class="mt-6 flex justify-center items-center space-x-2">
                        @if ($paginatedBanks->onFirstPage())
                            <button
                                class="px-3 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @else
                            <button
                                class="px-3 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 transform hover:scale-105"
                                wire:click="goToPage({{ $paginatedBanks->currentPage() - 1 }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif

                        @for ($i = 1; $i <= $paginatedBanks->lastPage(); $i++)
                            <button
                                class="px-3 py-2 rounded-xl font-medium transition-all duration-200 transform hover:scale-105 {{ $paginatedBanks->currentPage() === $i ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                                wire:click="goToPage({{ $i }})">{{ $i }}</button>
                        @endfor

                        @if ($paginatedBanks->hasMorePages())
                            <button
                                class="px-3 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 transition-all duration-200 transform hover:scale-105"
                                wire:click="goToPage({{ $paginatedBanks->currentPage() + 1 }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @else
                            <button
                                class="px-3 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>


    <!-- Modal de Delete Moderno -->
    @if ($showDeleteModal)
        <div id="deleteBankModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="relative p-6 w-full max-w-md max-h-full">
                <div
                    class="relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/30">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200/50 dark:border-gray-600/50 rounded-t-3xl">
                        <div class="flex items-center">
                            <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-xl p-2 mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Confirmar Exclusão
                            </h3>
                        </div>
                        <button type="button" wire:click="closeDeleteModal"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-xl p-2 transition-colors duration-200 dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="p-6 text-center">
                        <div
                            class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-r from-red-100 to-pink-100 dark:from-red-900/30 dark:to-pink-900/30 mb-4">
                            <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </div>
                        <h3 class="mb-5 text-lg font-bold text-gray-700 dark:text-gray-300">Tem certeza que deseja
                            excluir este cartão?</h3>
                        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Esta ação não pode ser desfeita e
                            todas as transações associadas a este cartão serão perdidas.</p>

                        <div class="flex justify-center items-center space-x-4">
                            <button type="button" wire:click="closeDeleteModal"
                                class="inline-flex items-center text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-xl text-sm font-medium px-5 py-3 transition-all duration-200 transform hover:scale-105 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </button>
                            <button type="button" wire:click="delete"
                                class="inline-flex items-center text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-xl text-sm font-medium px-5 py-3 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
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

            0%,
            100% {
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

    <!-- Loader compartilhado para ApexCharts -->
    <script>
        (function() {
            const chartsScriptUrl = "{{ asset('js/invoices-charts.js') }}";
            const fallbackData = @json($categoriesChartData ?? []);

            function ensureGlobalData() {
                try {
                    window.__categoriesChartData = Array.isArray(fallbackData) ? fallbackData : [];
                } catch (error) {
                    window.__categoriesChartData = [];
                }
            }

            function loadScriptOnce(src, id, readyCheck) {
                return new Promise((resolve, reject) => {
                    if (typeof readyCheck === 'function' && readyCheck()) {
                        resolve();
                        return;
                    }

                    let existing = id ? document.getElementById(id) : null;
                    if (existing) {
                        if (existing.getAttribute('data-loaded') === 'true') {
                            resolve();
                        } else {
                            existing.addEventListener('load', () => {
                                existing.setAttribute('data-loaded', 'true');
                                resolve();
                            }, {
                                once: true
                            });
                            existing.addEventListener('error', reject, {
                                once: true
                            });
                        }
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = src;
                    script.async = false;
                    if (id) script.id = id;
                    script.addEventListener('load', () => {
                        script.setAttribute('data-loaded', 'true');
                        resolve();
                    }, {
                        once: true
                    });
                    script.addEventListener('error', reject, {
                        once: true
                    });
                    document.head.appendChild(script);
                });
            }

            function bootstrapCharts() {
                ensureGlobalData();
                loadScriptOnce('https://cdn.jsdelivr.net/npm/apexcharts', 'apexcharts-cdn-script', () => typeof window.ApexCharts !== 'undefined')
                    .then(() => loadScriptOnce(chartsScriptUrl, 'banks-invoices-chart-script'))
                    .catch(error => console.error('[banks-dashboard] Falha ao carregar scripts do gráfico', error));
            }

            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                bootstrapCharts();
            } else {
                document.addEventListener('DOMContentLoaded', function handleReady() {
                    document.removeEventListener('DOMContentLoaded', handleReady);
                    bootstrapCharts();
                });
            }
        })();
    </script>

</div>
</div>
