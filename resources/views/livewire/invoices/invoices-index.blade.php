<div class="invoices-index-page w-full app-viewport-fit mobile-393-base relative">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/invoices-index-ultrawide.css') }}">

    <x-loading-overlay message="Carregando faturas..." />

    <!-- Main Content Layout -->
    <div class="w-full invoices-shell">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 invoices-layout">

            <!-- Left Column - Calendar & Chart (25% - 1 col) -->
            <div class="lg:col-span-1 space-y-3 invoices-sidebar">
                <!-- Calendar - Modernizado -->
                <div class="relative rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1 invoices-calendar-panel"
                    style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(139, 92, 246, 0.05) 100%); border: 2px solid rgba(59, 130, 246, 0.2);">

                    <!-- Efeito de brilho decorativo -->
                    <div class="absolute top-0 right-0 w-48 h-48 rounded-full blur-3xl opacity-10"
                        style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);"></div>

                    <!-- Header estilizado -->
                    <div class="relative px-5 py-4 backdrop-blur-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <!-- Ícone -->
                            <div class="relative group">
                                <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                    style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);"></div>
                                <div class="relative w-12 h-12 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                    style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(139, 92, 246, 0.15) 100%);">
                                    <svg class="w-6 h-6 text-gray-900 dark:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Título -->
                            <div class="flex-1">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">
                                    Calendário
                                </h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ $currentMonthName ?? 'Carregando...' }}</p>
                            </div>
                        </div>

                        <!-- Navegação -->
                        <div class="flex items-center justify-between gap-2">
                            <button wire:click="previousMonth"
                                class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 transition-all duration-200 transform hover:scale-105 border border-white/20 dark:border-gray-700/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <div class="flex items-center gap-2 flex-1 invoices-period-controls">

                                {{-- Custom Month Dropdown --}}
                                <div class="inv-cs flex-1 relative">
                                    <select wire:model.live="month" id="inv-month-native" class="inv-cs-native" aria-label="Mês">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMM') }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="inv-cs-trigger" data-cs-for="inv-month-native" aria-haspopup="listbox" aria-expanded="false" aria-label="Selecionar mês">
                                        <span class="inv-cs-val">{{ \Carbon\Carbon::create()->month($month)->locale('pt_BR')->isoFormat('MMMM') }}</span>
                                        <svg class="inv-cs-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <div class="inv-cs-panel" role="listbox" aria-label="Selecionar mês" hidden>
                                        @foreach (range(1, 12) as $m)
                                            <button type="button" role="option"
                                                class="inv-cs-opt {{ $month == $m ? 'inv-cs-opt--active' : '' }}"
                                                data-cs-val="{{ $m }}" data-cs-for="inv-month-native"
                                                aria-selected="{{ $month == $m ? 'true' : 'false' }}">
                                                <span class="inv-cs-num">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</span>
                                                <span class="inv-cs-lbl">{{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMMM') }}</span>
                                                @if($month == $m)<svg class="inv-cs-chk" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Custom Year Dropdown --}}
                                <div class="inv-cs inv-cs--year relative">
                                    <select wire:model.live="year" id="inv-year-native" class="inv-cs-native" aria-label="Ano">
                                        @foreach (range(now()->year - 5, now()->year + 2) as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="inv-cs-trigger" data-cs-for="inv-year-native" aria-haspopup="listbox" aria-expanded="false" aria-label="Selecionar ano">
                                        <span class="inv-cs-val">{{ $year }}</span>
                                        <svg class="inv-cs-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <div class="inv-cs-panel" role="listbox" aria-label="Selecionar ano" hidden>
                                        @foreach (range(now()->year - 5, now()->year + 2) as $y)
                                            <button type="button" role="option"
                                                class="inv-cs-opt {{ $year == $y ? 'inv-cs-opt--active' : '' }}"
                                                data-cs-val="{{ $y }}" data-cs-for="inv-year-native"
                                                aria-selected="{{ $year == $y ? 'true' : 'false' }}">
                                                <span class="inv-cs-lbl">{{ $y }}</span>
                                                @if($year == $y)<svg class="inv-cs-chk" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

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

                    <!-- Conteúdo do calendário -->
                    <div class="px-5 pb-5">
                        <!-- Feedback temporário -->
                        @if (session('message'))
                            <div
                                class="mb-3 p-2 bg-green-100 border border-green-300 rounded-xl text-xs text-green-800">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session('debug_info'))
                            <div class="mb-3 p-2 bg-blue-100 border border-blue-300 rounded-xl text-xs text-blue-800">
                                <strong>Debug:</strong> {{ session('debug_info') }}
                            </div>
                        @endif

                        @if (session('calendar_debug'))
                            <div
                                class="mb-3 p-2 bg-yellow-100 border border-yellow-300 rounded-xl text-xs text-yellow-800">
                                <strong>Calendar:</strong> {{ session('calendar_debug') }}
                            </div>
                        @endif

                        <!-- Cabeçalho dos dias -->
                        <div class="grid grid-cols-7 gap-1 mb-3">
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                D</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                S</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                T</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                Q</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                Q</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                S</div>
                            <div
                                class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">
                                S</div>
                        </div>

                        <!-- Dias do calendário -->
                        <div class="grid grid-cols-7">
                            @if (isset($calendarDays) && is_array($calendarDays))
                                @foreach ($calendarDays as $day)
                                    <div wire:click="selectDate('{{ $day['date'] }}')"
                                        class="relative min-h-[42px] p-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md {{ $day['isCurrentMonth'] ? 'bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-2 border-white/40 dark:border-gray-700/40 hover:border-blue-300 dark:hover:border-blue-600' : 'bg-gray-100/60 dark:bg-gray-700/60 backdrop-blur-sm border-2 border-gray-200/40 dark:border-gray-600/40' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/30 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 shadow-lg shadow-green-500/30' : '' }}">
                                        <div
                                            class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-700 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-700 dark:text-green-400' : '' }}">
                                            {{ $day['day'] }}
                                        </div>
                                        @if (!empty($day['invoices']))
                                            <div class="absolute bottom-1 right-1">
                                                <div
                                                    class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg animate-pulse">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <!-- Placeholder enquanto carrega -->
                                @for ($i = 0; $i < 42; $i++)
                                    <div
                                        class="relative min-h-[42px] p-2 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 backdrop-blur-sm border-2 border-gray-200/40 dark:border-gray-600/40">
                                        <div class="text-sm font-bold text-gray-400 dark:text-gray-500">-</div>
                                    </div>
                                @endfor
                            @endif
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

                <!-- Chart Section - Modernizado -->
                <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1 invoices-chart-panel"
                    style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border: 2px solid rgba(139, 92, 246, 0.2);">

                    <!-- Efeito de brilho decorativo -->
                    <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-10"
                        style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>

                    <!-- Header estilizado -->
                    <div class="relative px-6 py-5 backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <!-- Ícone com animação -->
                            <div class="relative group">
                                <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                    style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>
                                <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                    style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(59, 130, 246, 0.15) 100%);">
                                    <svg class="w-7 h-7 text-gray-900 dark:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Título -->
                            <div class="flex-1">
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-1">
                                    Por Categoria
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Visualização das despesas por
                                    categoria</p>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Chart -->
                    <div class="invoices-chart-body">
                        @php
                            $categoriesChartData = collect($categoriesData ?? [])
                                ->map(function ($c) use ($invoicesByCategory) {
                                    $label = $c['label'] ?? ($c['name'] ?? 'Sem categoria');
                                    $value = (float) ($c['value'] ?? 0);
                                    $found = collect($invoicesByCategory)->first(function ($g) use ($label) {
                                        return isset($g['category']['name']) && $g['category']['name'] === $label;
                                    });
                                    $color =
                                        $found['category']['hexcolor_category'] ??
                                        ($found['category']['hexcolor_category'] ?? '#667eea');
                                    return [
                                        'label' => $label,
                                        'value' => $value,
                                        'color' => $color,
                                    ];
                                })
                                ->values()
                                ->toArray();
                        @endphp

                        @if (count($categoriesChartData) > 0)
                            <div class="invoices-chart-canvas-wrap">
                                <div id="apex-pie" wire:ignore class="w-full invoices-chart-canvas" style="height: 240px;"></div>
                                <script type="application/json" id="categories-data">@json($categoriesChartData)</script>
                            </div>
                        @else
                            <div
                                class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl p-8 text-center shadow-lg border border-white/20 dark:border-gray-700/30">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhuma transação</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Não há transações por categoria
                                    neste período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transactions Section -->
            <div class="lg:col-span-3 space-y-3 invoices-main">
                <!-- Header moderno consistente -->
                <x-invoice-header
                    :total-transactions="$totalTransactions ?? 0"
                    :total-expenses="$totalDespesas ?? 0"
                    :average="$totalTransactions > 0 ? $totalDespesas / $totalTransactions : 0"
                    :bank-id="$bankId"
                    :bank="$bank"
                    :selected-date="$selectedDate"
                    :view-mode="$viewMode"
                    :invoices-count="count($invoices)"
                    :show-quick-actions="true">

                    <x-slot name="extraActions">
                        <button wire:click="toggleTips"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="bi bi-lightbulb text-sm"></i>
                            <span class="text-xs font-black uppercase tracking-wide">Dicas</span>
                        </button>
                    </x-slot>

                    <!-- Breadcrumb -->
                    <x-slot name="breadcrumb">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-home mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                <i class="fas fa-file-invoice mr-1"></i>Faturas
                            </span>
                        </div>
                    </x-slot>
                </x-invoice-header>

                <!-- Transactions List -->
                <div class="space-y-3 transactions-section">
                    <!-- Transactions Content -->
                    @if ($invoices && count($invoices) > 0)
                        @if ($viewMode === 'cards')
                            <!-- Cards View -->
                            <div class="cards-scroll invoices-cards-scroll max-h-[50vh] lg:max-h-[60vh] overflow-auto pr-2">


                                @foreach ($invoicesByCategory as $group)
                                    @php
                                        $cat = $group['category'] ?? [];
                                        $catColor = $cat['hexcolor_category'] ?? '#667eea';
                                        $catName = $cat['name'] ?? 'Sem categoria';
                                        $catIcon = $cat['icone'] ?? 'fas fa-tag';
                                        $catId = $group['category_id'] ?? 'sem_categoria';
                                        $catTotal = $group['total'] ?? 0;
                                        $catCount = count($group['invoices']);
                                    @endphp

                                    <div class="category-group mb-8 transform transition-all duration-500 collapsed"
                                        wire:key="invoice-category-{{ $catId }}"
                                        data-category-id="{{ $catId }}">
                                        <!-- Card moderno com glassmorphism e cores da categoria -->
                                        <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                                            style="background: linear-gradient(135deg, {{ $catColor }}15 0%, {{ $catColor }}08 100%); border: 2px solid {{ $catColor }}40;">

                                            <!-- Efeito de brilho decorativo -->
                                            <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-20"
                                                style="background: {{ $catColor }};"></div>

                                            <!-- Header super estilizado -->
                                            <div class="relative px-6 py-5 backdrop-blur-sm">
                                                <div class="flex items-center justify-between">
                                                    <!-- Esquerda: Ícone + Info -->
                                                    <div class="flex items-center gap-4 flex-1">
                                                        <!-- Ícone com animação e fundo translúcido -->
                                                        <div class="relative group">
                                                            <div class="absolute inset-0 rounded-2xl blur-3xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                                                style="background: {{ $catColor }}33;"></div>
                                                            <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                                                style="background: linear-gradient(135deg, {{ $catColor }}33 0%, {{ $catColor }}22 100%);">
                                                                <i
                                                                    class="{{ $catIcon }} text-gray-900 text-2xl"></i>
                                                            </div>
                                                        </div>

                                                        <!-- Info com texto mais legível (preto no claro) -->
                                                        <div class="flex-1">
                                                            <h3
                                                                class="text-2xl font-black mb-1 text-gray-900 dark:text-white">
                                                                {{ $catName }}
                                                            </h3>
                                                            <div class="flex items-center gap-4 text-sm">
                                                                <span
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold text-gray-900 dark:text-white shadow-lg"
                                                                    style="background: linear-gradient(135deg, {{ $catColor }}22 0%, {{ $catColor }}11 100%);">
                                                                    <svg class="w-4 h-4 text-gray-900" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                        </path>
                                                                    </svg>
                                                                    <span
                                                                        class="text-sm font-medium">{{ $catCount }}
                                                                        {{ $catCount === 1 ? 'transação' : 'transações' }}</span>
                                                                </span>
                                                                <span
                                                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full font-bold text-black shadow-lg transform hover:scale-105 transition-transform"
                                                                    style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                                    <svg class="w-4 h-4 text-black" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                        </path>
                                                                    </svg>
                                                                    <span>R$
                                                                        {{ number_format($catTotal, 2, ',', '.') }}</span>
                                                                </span>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Direita: Botão Toggle (apenas ícone) -->
                                                    <!-- Toggle Switch Estilizado -->
                                                    <button type="button"
                                                        class="category-toggle-btn flex items-center gap-3 px-4 py-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent"
                                                        data-category-id="{{ $catId }}"
                                                        data-expanded="false"
                                                        aria-expanded="false"
                                                        style="--cat-color: {{ $catColor }};"
                                                        aria-label="Alternar visibilidade da categoria {{ $catName }}">

                                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 toggle-label">Mostrar</span>

                                                        <!-- Toggle Switch -->
                                                         <div class="relative w-11 h-6 rounded-full transition-colors duration-300 toggle-switch"
                                                             style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%); --cat-color: {{ $catColor }};">
                                                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 toggle-thumb" style="transform: translateX(0);"></div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Conteúdo (Grid de invoices) -->
                                            <div
                                                class="group-invoices-wrapper overflow-hidden transition-all duration-500">
                                                <div class="px-6 pb-6">
                                                    <div
                                                        class="group-invoices invoice-category-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 ultrawind:grid-cols-6 gap-5">
                                                        @foreach ($group['invoices'] as $invoice)
                                                            @php
                                                                $value = abs(
                                                                    is_array($invoice)
                                                                        ? $invoice['value']
                                                                        : $invoice->value,
                                                                );
                                                                $desc = is_array($invoice)
                                                                    ? $invoice['description'] ?? ''
                                                                    : $invoice->description;
                                                                $invoiceDate = isset($invoice['invoice_date'])
                                                                    ? \Carbon\Carbon::parse(
                                                                        $invoice['invoice_date'],
                                                                    )->format('d/m/Y')
                                                                    : (isset($invoice->invoice_date)
                                                                        ? \Carbon\Carbon::parse(
                                                                            $invoice->invoice_date,
                                                                        )->format('d/m/Y')
                                                                        : '');
                                                            @endphp

                                                            <div class="invoice-card group relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden cursor-pointer"
                                                                onclick="toggleCardExpansion(this)">
                                                                <!-- Borda superior colorida com gradiente -->
                                                                <div class="absolute top-0 left-0 right-0 h-1.5 transition-all duration-300 group-hover:h-2"
                                                                    style="background: linear-gradient(90deg, {{ $catColor }} 0%, {{ $catColor }}aa 100%);">
                                                                </div>

                                                                <!-- Conteúdo do card -->
                                                                <div class="p-4 pt-5 pb-3">
                                                                    <div
                                                                        class="flex items-start justify-between gap-3 mb-3">
                                                                        <!-- Descrição -->
                                                                        <div class="flex-1 min-w-0">
                                                                            <h4
                                                                                class="text-base font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 transition-all duration-300">
                                                                                {{ $desc }}
                                                                            </h4>
                                                                            <div
                                                                                class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                                                <svg class="w-3.5 h-3.5"
                                                                                    fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                                    </path>
                                                                                </svg>
                                                                                <span
                                                                                    class="font-medium">{{ $invoiceDate }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Valor com badge estilizado usando cor da categoria -->
                                                                        <div class="flex-shrink-0">
                                                                            <div class="inline-flex items-center gap-1 px-3 py-2 rounded-full font-bold text-sm shadow-md text-black"
                                                                                style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                                                <svg class="w-3.5 h-3.5 text-black"
                                                                                    fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                                    </path>
                                                                                </svg>
                                                                                <span>{{ number_format($value, 2, ',', '.') }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Ações centralizadas do card -->
                                                                    @php
                                                                        $invoiceId = is_array($invoice)
                                                                            ? $invoice['id_invoice'] ??
                                                                                ($invoice['id'] ?? '')
                                                                            : $invoice->id_invoice ?? $invoice->id;
                                                                    @endphp
                                                                    <div class="card-actions">
                                                                        <div
                                                                            class="flex items-center justify-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                                                                            <a href="{{ route('invoices.edit', ['invoiceId' => $invoiceId, 'return_month' => $month, 'return_year' => $year]) }}"
                                                                                class="inline-flex items-center gap-2 px-3 py-2 bg-white shadow-md hover:shadow-lg rounded-full border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-blue-600 hover:text-blue-800 transition text-xs font-semibold">
                                                                                <svg class="w-4 h-4" fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                                    </path>
                                                                                </svg>
                                                                                <span
                                                                                    class="hidden sm:inline">Editar</span>
                                                                            </a>
                                                                            <a href="{{ route('invoices.copy', $invoiceId) }}"
                                                                                class="inline-flex items-center gap-2 px-3 py-2 bg-white shadow-md hover:shadow-lg rounded-full border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-emerald-600 hover:text-emerald-800 transition text-xs font-semibold">
                                                                                <svg class="w-4 h-4" fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                                                    </path>
                                                                                </svg>
                                                                                <span
                                                                                    class="hidden sm:inline">Copiar</span>
                                                                            </a>
                                                                            <button
                                                                                wire:click="confirmDelete({{ $invoiceId }})"
                                                                                class="inline-flex items-center gap-2 px-3 py-2 bg-white shadow-md hover:shadow-lg rounded-full border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-red-600 hover:text-red-800 transition text-xs font-semibold">
                                                                                <svg class="w-4 h-4" fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                                    </path>
                                                                                </svg>
                                                                                <span
                                                                                    class="hidden sm:inline">Excluir</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Indicador de hover -->
                                                                <div class="absolute bottom-0 left-0 right-0 h-0 group-hover:h-1 transition-all duration-300"
                                                                    style="background: {{ $catColor }};"></div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- List View -->
                            <div class="cards-scroll invoices-list-scroll max-h-[70vh] lg:max-h-[82vh] overflow-auto pr-2">
                                <div
                                    class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl overflow-hidden">
                                    <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                                        @foreach ($invoices as $invoice)
                                            <div
                                                class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-all duration-300">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-4">
                                                        <div
                                                            class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                                                            @if (
                                                                (is_array($invoice) ? $invoice['category'] ?? null : $invoice->category) &&
                                                                    (is_array($invoice) ? $invoice['category']['icone'] ?? null : $invoice->category->icone))
                                                                <i
                                                                    class="{{ is_array($invoice) ? $invoice['category']['icone'] ?? '' : $invoice->category->icone }} text-white text-lg"></i>
                                                            @else
                                                                <svg class="w-6 h-6 text-white" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                    </path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                                                {{ is_array($invoice) ? $invoice['description'] ?? '' : $invoice->description }}
                                                            </p>
                                                            <div
                                                                class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                                                <span class="flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                        </path>
                                                                    </svg>
                                                                    {{ \Carbon\Carbon::parse(is_array($invoice) ? $invoice['invoice_date'] : $invoice->invoice_date)->format('d/m/Y') }}
                                                                </span>
                                                                @if (is_array($invoice) ? $invoice['category'] ?? null : $invoice->category)
                                                                    <span
                                                                        class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-medium">
                                                                        {{ is_array($invoice) ? $invoice['category']['name'] ?? '' : $invoice->category->name }}
                                                                    </span>
                                                                @endif
                                                                @if (is_array($invoice) ? $invoice['installments'] ?? null : $invoice->installments)
                                                                    <span
                                                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-lg text-xs font-medium">
                                                                        {{ is_array($invoice) ? $invoice['installments'] ?? '' : $invoice->installments }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center space-x-4">
                                                        <div class="text-right">
                                                            <p class="text-xl font-black text-red-600">
                                                                - R$
                                                                {{ number_format(abs(is_array($invoice) ? $invoice['value'] : $invoice->value), 2, ',', '.') }}
                                                            </p>
                                                            @if (is_array($invoice) ? $invoice['client'] ?? null : $invoice->client)
                                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                    {{ is_array($invoice) ? $invoice['client']['name'] ?? '' : $invoice->client->name }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        @if ($bankId)
                                                            <div class="flex items-center space-x-2">
                                                                <a href="{{ route('invoices.edit', [
                                                                    'invoiceId' => is_array($invoice)
                                                                        ? $invoice['id_invoice'] ?? ($invoice['id'] ?? '')
                                                                        : $invoice->id_invoice ?? $invoice->id,
                                                                    'return_month' => $month,
                                                                    'return_year' => $year,
                                                                ]) }}"
                                                                    class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                        </path>
                                                                    </svg>
                                                                </a>

                                                                <a href="{{ route('invoices.copy', is_array($invoice) ? $invoice['id_invoice'] ?? ($invoice['id'] ?? '') : $invoice->id_invoice ?? $invoice->id) }}"
                                                                    class="p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-all duration-200">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                                        </path>
                                                                    </svg>
                                                                </a>

                                                                <button
                                                                    wire:click="confirmDelete({{ is_array($invoice) ? $invoice['id_invoice'] ?? ($invoice['id'] ?? '') : $invoice->id_invoice ?? $invoice->id }})"
                                                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all duration-200">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                        </path>
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
                            </div>
                        @endif

            <!-- Debug panel removed -->

            <script>
                (function () {
                    'use strict';

                    var STORAGE_KEY = 'invoices-category-visibility-v2';
                    var stateCache = null;

                    function readState() {
                        if (stateCache) {
                            return stateCache;
                        }

                        try {
                            stateCache = JSON.parse(window.sessionStorage.getItem(STORAGE_KEY) || '{}');
                        } catch (error) {
                            stateCache = {};
                        }

                        return stateCache;
                    }

                    function saveState() {
                        try {
                            window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(readState()));
                        } catch (error) {
                            // noop
                        }
                    }

                    function setExpandedState(categoryId, expanded) {
                        var group = document.querySelector('.category-group[data-category-id="' + categoryId + '"]');
                        var button = document.querySelector('.category-toggle-btn[data-category-id="' + categoryId + '"]');

                        if (!group || !button) {
                            return;
                        }

                        var wrapper = group.querySelector('.group-invoices-wrapper');
                        var thumb = button.querySelector('.toggle-thumb');
                        var label = button.querySelector('.toggle-label');
                        var content = group.querySelector('.group-invoices');
                        var contentHeight = content ? content.scrollHeight : 400;

                        group.classList.toggle('expanded', expanded);
                        group.classList.toggle('collapsed', !expanded);
                        button.setAttribute('data-expanded', expanded ? 'true' : 'false');
                        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                        if (label) {
                            label.textContent = expanded ? 'Ocultar' : 'Mostrar';
                        }

                        if (thumb) {
                            thumb.style.transform = expanded ? 'translateX(20px)' : 'translateX(0)';
                        }

                        if (wrapper) {
                            wrapper.style.opacity = expanded ? '1' : '0';
                            wrapper.style.maxHeight = expanded ? contentHeight + 'px' : '0px';

                            if (expanded) {
                                window.setTimeout(function () {
                                    if (group.classList.contains('expanded')) {
                                        wrapper.style.maxHeight = 'none';
                                    }
                                }, 520);
                            }
                        }
                    }

                    function syncAllCategories() {
                        var savedState = readState();

                        document.querySelectorAll('.category-group').forEach(function (group) {
                            var categoryId = group.getAttribute('data-category-id');

                            if (!categoryId) {
                                return;
                            }

                            var expanded = Object.prototype.hasOwnProperty.call(savedState, categoryId)
                                ? Boolean(savedState[categoryId])
                                : false;

                            setExpandedState(categoryId, expanded);
                        });
                    }

                    function handleToggleClick(event) {
                        var button = event.target.closest('.category-toggle-btn');

                        if (!button) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        var categoryId = button.getAttribute('data-category-id');
                        var expanded = button.getAttribute('data-expanded') === 'true';

                        readState()[categoryId] = !expanded;
                        saveState();
                        setExpandedState(categoryId, !expanded);
                    }

                    if (!window.__invoicesCategoryToggleBound) {
                        document.addEventListener('click', handleToggleClick, true);
                        window.__invoicesCategoryToggleBound = true;
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', syncAllCategories, { once: true });
                    } else {
                        syncAllCategories();
                    }

                    document.addEventListener('livewire:update', syncAllCategories);
                    document.addEventListener('livewire:load', syncAllCategories);
                    document.addEventListener('livewire:navigated', syncAllCategories);

                    if (window.Livewire && typeof window.Livewire.hook === 'function' && !window.__invoicesCategoryHookBound) {
                        window.Livewire.hook('message.processed', function () {
                            window.setTimeout(syncAllCategories, 30);
                        });
                        window.__invoicesCategoryHookBound = true;
                    }
                })();
            </script>
                    @else
                        <!-- Empty State -->
                        <div
                            class="invoices-empty-state bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl p-12 text-center">
                            <div class="invoices-empty-state-inner">
                                <div class="invoices-empty-kicker inline-flex items-center justify-center gap-2 mb-4 px-4 py-2 rounded-full text-xs font-black uppercase tracking-[0.22em] text-indigo-700 dark:text-indigo-200 bg-indigo-500/10 border border-indigo-400/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2m-2 0h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"></path>
                                    </svg>
                                    Painel vazio
                                </div>
                                <div
                                    class="invoices-empty-hero w-24 h-24 bg-gradient-to-br from-slate-200 via-slate-100 to-slate-300 dark:from-slate-600 dark:via-slate-500 dark:to-slate-700 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-slate-900/10 dark:shadow-black/20">
                                    <svg class="w-11 h-11 text-white/95" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="invoices-empty-title text-2xl font-black text-gray-900 dark:text-white mb-3">Nenhuma transação encontrada</h3>
                                <p class="invoices-empty-copy text-gray-600 dark:text-gray-400 mb-4 max-w-xl mx-auto">
                                    @if ($selectedDate)
                                        Não há transações registradas para o dia {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.
                                    @else
                                        Seu painel está pronto para começar. Crie uma nova transação ou envie um CSV para preencher este período com rapidez.
                                    @endif
                                </p>

                                <div class="invoices-empty-highlights flex flex-wrap items-center justify-center gap-2 mb-8">
                                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/70 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-sm font-semibold border border-white/30 dark:border-slate-600/40">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                        Crie manualmente
                                    </span>
                                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/70 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-sm font-semibold border border-white/30 dark:border-slate-600/40">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                                        Importe em lote
                                    </span>
                                    @if ($selectedDate)
                                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/70 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-sm font-semibold border border-white/30 dark:border-slate-600/40">
                                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                            Filtro ativo
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($bankId)
                                <div class="invoices-empty-actions flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                                        class="invoices-empty-action invoices-empty-action-primary inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-bold rounded-2xl hover:from-purple-600 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Nova Despesa
                                    </a>

                                    <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                                        class="invoices-empty-action invoices-empty-action-secondary inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-2xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload CSV
                                    </a>

                                    @if ($selectedDate)
                                        <button wire:click="clearDateSelection"
                                            class="invoices-empty-action inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold rounded-2xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Limpar Filtro
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
    @if ($showDeleteModal)
        <div
            class="fixed inset-0 bg-gray-600/50 dark:bg-gray-900/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-20 mx-auto p-5 border border-white/20 w-96 shadow-2xl rounded-2xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl">
                <div class="mt-3 text-center">
                    <div
                        class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 shadow-lg">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mt-4">🗑️ Excluir Transação</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 px-4">
                        Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita e todos os dados
                        serão perdidos permanentemente.
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

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 40px rgba(139, 92, 246, 0.6);
            }
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

        .invoices-index-page {
            padding: 0.35rem;
        }

        .invoices-shell {
            width: 100%;
            max-width: none;
            margin-inline: 0;
        }

        .invoices-layout {
            display: grid;
            align-items: start;
        }

        .invoices-sidebar,
        .invoices-main {
            min-width: 0;
        }

        .invoices-main {
            align-self: start;
        }

        .invoices-calendar-panel .relative.px-5.py-4.backdrop-blur-sm {
            padding: 1rem;
        }

        /* ── Período controls base ── */
        .invoices-period-controls { min-width: 0; }

        /* ── Custom Period Select ── */
        .inv-cs { position: relative; min-width: 0; }
        .inv-cs--year { flex: 0 0 7.5rem; }

        /* Hidden native select (Livewire target) */
        .inv-cs-native {
            position: absolute; opacity: 0; pointer-events: none;
            width: 0; height: 0; overflow: hidden; border: 0; padding: 0;
        }

        /* Trigger button */
        .inv-cs-trigger {
            display: flex; align-items: center; justify-content: space-between;
            gap: 0.5rem; width: 100%; min-height: 2.9rem;
            padding: 0.72rem 0.95rem; border-radius: 1rem;
            border: 1.5px solid rgba(255,255,255,0.28);
            background: linear-gradient(135deg, rgba(255,255,255,0.97) 0%, rgba(239,246,255,0.95) 100%);
            color: rgb(15,23,42); font-size: 0.8rem; font-weight: 800;
            letter-spacing: 0.05em; text-transform: uppercase;
            box-shadow: 0 6px 20px rgba(59,130,246,.1), 0 2px 6px rgba(0,0,0,.04);
            cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent;
            transition: border-color .2s, box-shadow .2s, transform .2s;
        }
        .dark .inv-cs-trigger {
            background: linear-gradient(135deg, rgba(30,41,59,0.97) 0%, rgba(51,65,85,0.95) 100%);
            color: rgb(248,250,252); border-color: rgba(148,163,184,0.22);
            box-shadow: 0 6px 20px rgba(15,23,42,.3);
        }
        .inv-cs-trigger:hover, .inv-cs-trigger[aria-expanded="true"] {
            border-color: rgba(99,102,241,.55);
            box-shadow: 0 8px 24px rgba(99,102,241,.22), 0 2px 6px rgba(0,0,0,.06);
            transform: translateY(-1px);
        }
        .inv-cs-val {
            flex: 1; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; text-align: left;
        }
        .inv-cs-chevron {
            width: 1rem; height: 1rem; flex-shrink: 0; color: rgb(99,102,241);
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }
        .dark .inv-cs-chevron { color: rgb(165,180,252); }
        .inv-cs-trigger[aria-expanded="true"] .inv-cs-chevron { transform: rotate(180deg); }

        /* Dropdown panel */
        .inv-cs-panel {
            position: absolute; top: calc(100% + .45rem); left: 0; z-index: 9999;
            min-width: 100%; max-height: 15rem; overflow-y: auto; overflow-x: hidden;
            border-radius: 1.25rem; padding: .45rem;
            display: flex; flex-direction: column; gap: .15rem;
            background: rgba(255,255,255,0.99);
            backdrop-filter: blur(24px) saturate(1.6);
            -webkit-backdrop-filter: blur(24px) saturate(1.6);
            border: 1.5px solid rgba(99,102,241,.22);
            box-shadow: 0 24px 64px rgba(99,102,241,.18), 0 8px 24px rgba(0,0,0,.08),
                        inset 0 1px 0 rgba(255,255,255,.8);
            transform-origin: top center;
            animation: inv-cs-open .2s cubic-bezier(.34,1.56,.64,1) both;
        }
        .dark .inv-cs-panel {
            background: rgba(15,23,42,0.99);
            border-color: rgba(99,102,241,.28);
            box-shadow: 0 24px 64px rgba(99,102,241,.22), 0 8px 24px rgba(0,0,0,.45),
                        inset 0 1px 0 rgba(255,255,255,.04);
        }
        .inv-cs-panel[hidden] { display: none !important; }
        @keyframes inv-cs-open {
            from { opacity:0; transform: scaleY(.82) translateY(-6px); }
            to   { opacity:1; transform: scaleY(1)   translateY(0); }
        }
        .inv-cs-panel::-webkit-scrollbar { width: 5px; }
        .inv-cs-panel::-webkit-scrollbar-track { background: transparent; }
        .inv-cs-panel::-webkit-scrollbar-thumb {
            background: rgba(99,102,241,.22); border-radius: 999px;
        }
        .dark .inv-cs-panel::-webkit-scrollbar-thumb { background: rgba(165,180,252,.15); }

        /* Options */
        .inv-cs-opt {
            display: flex; align-items: center; gap: .55rem;
            width: 100%; padding: .56rem .8rem; border-radius: .8rem;
            font-size: .82rem; font-weight: 600; color: rgb(30,41,59);
            text-align: left; cursor: pointer; background: transparent;
            border: 1px solid transparent; white-space: nowrap;
            transition: background .14s, color .14s, transform .14s;
        }
        .dark .inv-cs-opt { color: rgb(203,213,225); }
        .inv-cs-opt:hover {
            background: linear-gradient(135deg, rgba(99,102,241,.1) 0%, rgba(139,92,246,.07) 100%);
            border-color: rgba(99,102,241,.2); color: rgb(79,70,229);
            transform: translateX(2px);
        }
        .dark .inv-cs-opt:hover {
            background: linear-gradient(135deg, rgba(99,102,241,.18) 0%, rgba(139,92,246,.12) 100%);
            color: rgb(165,180,252);
        }
        .inv-cs-opt--active {
            background: linear-gradient(135deg, rgba(99,102,241,.14) 0%, rgba(139,92,246,.1) 100%);
            border-color: rgba(99,102,241,.28); color: rgb(67,56,202); font-weight: 800;
        }
        .dark .inv-cs-opt--active {
            background: linear-gradient(135deg, rgba(99,102,241,.25) 0%, rgba(139,92,246,.18) 100%);
            border-color: rgba(99,102,241,.4); color: rgb(165,180,252);
        }
        .inv-cs-num {
            font-size: .7rem; font-weight: 700; color: rgb(148,163,184);
            min-width: 1.3rem; font-variant-numeric: tabular-nums;
        }
        .dark .inv-cs-num { color: rgb(71,85,105); }
        .inv-cs-lbl { flex: 1; text-transform: capitalize; }
        .inv-cs-chk { width: 1rem; height: 1rem; color: rgb(99,102,241); flex-shrink: 0; margin-left: auto; }
        .dark .inv-cs-chk { color: rgb(165,180,252); }

        .invoices-empty-state {
            position: relative;
            overflow: hidden;
        }

        .invoices-empty-state::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(99, 102, 241, 0.14), transparent 34%),
                radial-gradient(circle at bottom left, rgba(236, 72, 153, 0.12), transparent 32%);
            pointer-events: none;
        }

        .invoices-empty-state-inner,
        .invoices-empty-actions {
            position: relative;
            z-index: 1;
        }

        .invoices-empty-state-inner {
            max-width: 48rem;
            margin: 0 auto;
        }

        .invoices-empty-title {
            line-height: 1.08;
            letter-spacing: -0.02em;
        }

        .invoices-empty-copy {
            font-size: 1rem;
            line-height: 1.7;
        }

        .invoices-empty-actions {
            gap: 0.85rem;
        }

        .invoices-empty-action {
            min-width: 13.5rem;
        }

        .invoices-calendar-panel .flex.items-center.gap-3.mb-4 {
            margin-bottom: 0.85rem;
        }

        .invoices-chart-panel .relative.px-6.py-5.backdrop-blur-sm {
            padding: 1rem 1rem 0.85rem;
        }

        .invoices-chart-canvas-wrap {
            position: relative;
            min-height: 240px;
            padding: 0 1rem 1rem;
        }

        .invoices-chart-canvas {
            min-height: 240px;
        }

        /* Invoice Card Interactions */
        .invoice-card {
            overflow: visible;
        }

        .invoice-card:hover .card-actions {
            opacity: 1 !important;
            transform: translateY(0) !important;
            max-height: 160px !important;
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
            /* assegura que o conteúdo apareça sem cortes */
            overflow: visible !important;
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

        /* Scrollbar styles for the cards/list scroll area */
        .cards-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 100, 100, 0.22) transparent;
        }

        .cards-scroll::-webkit-scrollbar {
            width: 10px;
        }

        .cards-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .cards-scroll::-webkit-scrollbar-thumb {
            background: rgba(100, 100, 100, 0.18);
            border-radius: 9999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .dark .cards-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.06);
        }

        /* Animações e transições suaves para category groups */
        .category-group .group-invoices-wrapper {
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(.4,0,.2,1), opacity 0.3s ease;
            max-height: 0;
            opacity: 0;
        }

        .category-group.expanded .group-invoices-wrapper {
            max-height: 5000px;
            opacity: 1;
        }

        /* Toggle button hover/active styles */
        .category-toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border-color: var(--cat-color);
        }

        .category-toggle-btn .toggle-switch {
            box-shadow: inset 0 0 6px rgba(0,0,0,0.06);
        }

        .category-toggle-btn .toggle-thumb {
            will-change: transform;
        }

        .toggle-icon.rotate {
            transform: rotate(180deg);
        }

        .category-group {
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-group:nth-child(1) {
            animation-delay: 0.1s;
        }

        .category-group:nth-child(2) {
            animation-delay: 0.2s;
        }

        .category-group:nth-child(3) {
            animation-delay: 0.3s;
        }

        .category-group:nth-child(4) {
            animation-delay: 0.4s;
        }

        .category-group:nth-child(5) {
            animation-delay: 0.5s;
        }

        /* Efeito de sombra 3D */
        .shadow-3xl {
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        /* Transição suave para wrapper de invoices */
        .group-invoices-wrapper {
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Toggle icon animation */
        .toggle-icon {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Efeito de shimmer para loading */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .shimmer {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            background-size: 1000px 100%;
        }
    </style>
    <!-- Include ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" onload="window.__apexChartsLoaded = true;"></script>
    <script src="{{ asset('js/invoices-charts.js') }}?v=20260401"></script>

    <!-- JavaScript for Enhanced Interactions -->
    <script>
        function initializeInvoicesIndexPage() {
            if (!window.__invoicesIndexBindingsAttached) {
                // Add keyboard navigation for calendar
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'n') {
                        e.preventDefault();
                        @if ($bankId)
                            window.location.href = '{{ route('invoices.create', ['bankId' => $bankId]) }}';
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
                window.__invoicesIndexBindingsAttached = true;
            }

            // Add tooltips to calendar days
            const calendarDays = document.querySelectorAll('.calendar-day');
            calendarDays.forEach(day => {
                if (day.classList.contains('has-invoices')) {
                    day.setAttribute('title', 'Clique para filtrar por este dia');
                }
            });

            // Category toggles are handled by the delegated handler above.
            // initStates() will set initial wrapper states; persistence is intentionally
            // disabled here to avoid conflicts with Livewire re-renders.

            // Dados do gráfico expostos para arquivo JS externo
            try {
                window.__categoriesChartData = @json($categoriesChartData ?? []);
            } catch (e) {
                window.__categoriesChartData = [];
            }

            if (typeof window.renderInvoicesDonut === 'function') {
                window.setTimeout(window.renderInvoicesDonut, 80);
            }

            // File upload handler (guarded)
            (function() {
                const fileUploadEl = document.getElementById('fileUpload');
                if (!fileUploadEl || fileUploadEl.dataset.bound === 'true') return;
                fileUploadEl.dataset.bound = 'true';
                fileUploadEl.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Simular upload com feedback visual
                        const button = document.querySelector('[onclick*="fileUpload"]');
                        const originalText = button ? button.innerHTML : '';
                        if (button) button.innerHTML =
                            '<svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>🔄 Processando...';

                        setTimeout(() => {
                            if (button) button.innerHTML = originalText;
                            showNotification(
                                '📊 Arquivo processado com sucesso!\n\nFuncionalidade de upload será implementada em breve.',
                                'success');
                        }, 2000);
                    }
                });
            })();

            // Initialize dark mode from localStorage
            if (localStorage.getItem('dark-mode') === 'true') {
                document.documentElement.classList.add('dark');
            }

            // Print styles
            if (!document.getElementById('invoices-print-styles')) {
                const style = document.createElement('style');
                style.id = 'invoices-print-styles';
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
            }
        }

        if (!window.__invoicesIndexPageInitialized) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeInvoicesIndexPage, { once: true });
            } else {
                initializeInvoicesIndexPage();
            }

            document.addEventListener('livewire:navigated', initializeInvoicesIndexPage);
            window.__invoicesIndexPageInitialized = true;
        }

        // Card expansion functionality
        function toggleCardExpansion(card) {
            const isExpanded = card.classList.contains('expanded');
            const expandIcon = card.querySelector('.expand-icon');

            if (isExpanded) {
                // Collapse
                card.classList.remove('expanded');
                if (expandIcon) {
                    expandIcon.style.transform = 'rotate(0deg)';
                }
            } else {
                // Expand
                card.classList.add('expanded');
                if (expandIcon) {
                    expandIcon.style.transform = 'rotate(180deg)';
                }
            }
        }

        // Enhanced UX for date filtering (without Livewire dependency)
        document.addEventListener('livewire:load', function() {
            if (!window.livewire || typeof window.livewire.on !== 'function') {
                return;
            }

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
                csv +=
                    `${invoice.invoice_date},"${invoice.description}","${invoice.category?.name || 'Sem categoria'}",${Math.abs(invoice.value)},"${invoice.client?.name || ''}"\n`;
            });

            const blob = new Blob([csv], {
                type: 'text/csv'
            });
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

        // ── Custom Calendar Period Selects (event delegation — survives Livewire morphing) ──
        (function () {
            'use strict';

            function closePanels(except) {
                document.querySelectorAll('.inv-cs-panel:not([hidden])').forEach(function (p) {
                    if (p === except) return;
                    p.hidden = true;
                    var t = p.parentElement && p.parentElement.querySelector('.inv-cs-trigger');
                    if (t) t.setAttribute('aria-expanded', 'false');
                });
            }

            if (!window.__invCsBound) {
                window.__invCsBound = true;

                document.addEventListener('click', function (e) {
                    // Trigger click → open/close panel
                    var trigger = e.target.closest('.inv-cs-trigger');
                    if (trigger) {
                        e.preventDefault();
                        e.stopPropagation();
                        var wrap  = trigger.closest('.inv-cs');
                        var panel = wrap && wrap.querySelector('.inv-cs-panel');
                        if (!panel) return;
                        var isOpen = !panel.hidden;
                        closePanels();
                        if (!isOpen) {
                            panel.hidden = false;
                            trigger.setAttribute('aria-expanded', 'true');
                            var active = panel.querySelector('.inv-cs-opt--active');
                            if (active) setTimeout(function () { active.scrollIntoView({ block: 'nearest' }); }, 30);
                        }
                        return;
                    }

                    // Option click → update native select + close
                    var opt = e.target.closest('.inv-cs-opt');
                    if (opt) {
                        e.preventDefault();
                        e.stopPropagation();
                        var val      = opt.dataset.csVal;
                        var nativeEl = document.getElementById(opt.dataset.csFor);
                        var wrap2    = opt.closest('.inv-cs');
                        var trigger2 = wrap2 && wrap2.querySelector('.inv-cs-trigger');
                        var valEl    = trigger2 && trigger2.querySelector('.inv-cs-val');

                        if (nativeEl && nativeEl.value !== val) {
                            nativeEl.value = val;
                            // Update trigger label immediately (Livewire will confirm on re-render)
                            if (valEl) {
                                var sel = nativeEl.querySelector('option[value="' + val + '"]');
                                if (sel) valEl.textContent = sel.text;
                            }
                            // Dispatch change so Livewire wire:model.live fires
                            nativeEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        closePanels();
                        return;
                    }

                    // Click outside → close
                    if (!e.target.closest('.inv-cs')) closePanels();
                }, true);

                // ESC closes panel
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closePanels();
                });
            }
        })();
    </script>
</div>
