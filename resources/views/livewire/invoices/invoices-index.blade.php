<div class="w-full">

    <!-- Main Content Layout -->
    <div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

            <!-- Left Column - Calendar & Chart (25% - 1 col) -->
            <div class="lg:col-span-1 space-y-3">
                <!-- Calendar - Modernizado -->
                <div class="relative  rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
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

                        <!-- Bot\u00e3o de Dicas -->
                        <button wire:click="toggleTips"
                            class="p-2 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-lightbulb text-sm"></i>
                        </button>
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
                <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
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
                    <div class="">
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
                            <div>
                                <div id="apex-pie" class="w-full" style="height: 240px;"></div>
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
            <div class="lg:col-span-3 space-y-3">
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
                <div class="space-y-3">
                    <!-- Transactions Content -->
                    @if ($invoices && count($invoices) > 0)
                        @if ($viewMode === 'cards')
                            <!-- Cards View -->
                            <div class="cards-scroll max-h-[50vh] lg:max-h-[60vh] overflow-auto pr-2">


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

                                    <div class="category-group mb-8 transform transition-all duration-500 expanded"
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
                                                        data-expanded="true"
                                                        style="--cat-color: {{ $catColor }};"
                                                        aria-label="Alternar visibilidade da categoria {{ $catName }}">

                                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 toggle-label">Ocultar</span>

                                                        <!-- Toggle Switch -->
                                                         <div class="relative w-11 h-6 rounded-full transition-colors duration-300 toggle-switch"
                                                             style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%); --cat-color: {{ $catColor }};">
                                                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 toggle-thumb" style="transform: translateX(20px);"></div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Conteúdo (Grid de invoices) -->
                                            <div
                                                class="group-invoices-wrapper overflow-hidden transition-all duration-500">
                                                <div class="px-6 pb-6">
                                                    <div
                                                        class="group-invoices grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 ultrawind:grid-cols-6 gap-5">
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
                            <div class="cards-scroll max-h-[70vh] lg:max-h-[82vh] overflow-auto pr-2">
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
                (function(){
                    'use strict';

                    function writeDebug(msg){
                        // debug removed - noop
                    }

                    function toggleCategoryByButton(btn){
                        if(!btn) return;
                        var categoryId = btn.getAttribute('data-category-id');
                        if(!categoryId) return writeDebug('no categoryId');
                        var group = document.querySelector('.category-group[data-category-id="' + categoryId + '"]');
                        if(!group) return writeDebug('group not found for ' + categoryId);
                        var wrapper = group.querySelector('.group-invoices-wrapper');
                        var thumb = btn.querySelector('.toggle-thumb');
                        var label = btn.querySelector('.toggle-label');

                        var isExpanded = group.classList.contains('expanded');
                        writeDebug('toggleCategory: cat=' + categoryId + ' expanded=' + isExpanded);

                        if(isExpanded){
                            // collapse
                            group.classList.remove('expanded');
                            group.classList.add('collapsed');
                            if(wrapper){ wrapper.style.maxHeight = '0px'; wrapper.style.opacity = '0'; }
                            if(thumb) thumb.style.transform = 'translateX(0)';
                            if(label) label.textContent = 'Oculto';
                            btn.setAttribute('data-expanded','false');
                            writeDebug('collapsed ' + categoryId);
                        }else{
                            // expand
                            group.classList.add('expanded');
                            group.classList.remove('collapsed');
                            if(wrapper){
                                // set to scrollHeight to animate, then remove inline maxHeight after transition
                                var h = wrapper.scrollHeight || wrapper.querySelector('.group-invoices')?.scrollHeight || 400;
                                wrapper.style.maxHeight = h + 'px';
                                wrapper.style.opacity = '1';
                                setTimeout(function(){ try{ wrapper.style.maxHeight = null; }catch(e){} }, 520);
                            }
                            if(thumb) thumb.style.transform = 'translateX(20px)';
                            if(label) label.textContent = 'Mostrar';
                            btn.setAttribute('data-expanded','true');
                            writeDebug('expanded ' + categoryId);
                        }
                    }

                    // Delegated click handler ensures it works after Livewire re-renders
                    document.addEventListener('click', function(e){
                        var btn = e.target.closest('.category-toggle-btn');
                        if(!btn) return;
                        e.preventDefault();
                        e.stopPropagation();
                        toggleCategoryByButton(btn);
                    }, true);

                    // Initialize default expanded state on load
                    function initStates(){
                        document.querySelectorAll('.category-group').forEach(function(g){
                            var wrapper = g.querySelector('.group-invoices-wrapper');
                            if(g.classList.contains('expanded')){
                                if(wrapper){ wrapper.style.maxHeight = (wrapper.scrollHeight || 400) + 'px'; wrapper.style.opacity = '1'; }
                            }else{
                                if(wrapper){ wrapper.style.maxHeight = '0px'; wrapper.style.opacity = '0'; }
                            }
                        });
                        writeDebug('initStates done');
                    }

                    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initStates);
                    else initStates();

                    if(window.Livewire){
                        document.addEventListener('livewire:update', initStates);
                        document.addEventListener('livewire:load', initStates);
                        if(typeof Livewire.hook === 'function') Livewire.hook('message.processed', initStates);
                    }
                })();
            </script>
                    @else
                        <!-- Empty State -->
                        <div
                            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-xl p-12 text-center">
                            <div
                                class="w-24 h-24 bg-gradient-to-br from-gray-300 to-gray-400 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-4">💳 Nenhuma transação
                                encontrada</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                                @if ($selectedDate)
                                    Não há transações registradas para o dia
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.
                                @else
                                    Comece criando sua primeira transação para organizar suas finanças.
                                @endif
                            </p>

                            @if ($bankId)
                                <div
                                    class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                                    <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-bold rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        � Nova Despesa
                                    </a>

                                    <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        📊 Upload CSV
                                    </a>

                                    @if ($selectedDate)
                                        <button wire:click="clearDateSelection"
                                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
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

        .gradient-border>div {
            border-radius: calc(1rem - 2px);
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

    <!-- JavaScript for Enhanced Interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Carrega o script de inicialização do gráfico (arquivo externo) - aguarda ApexCharts
            (function loadChartsScript() {
                function tryLoad() {
                    if (typeof ApexCharts !== 'undefined' || window.__apexChartsLoaded) {
                        var s = document.createElement('script');
                        s.src = '/js/invoices-charts.js?v=' + Date.now();
                        s.async = false;
                        document.head.appendChild(s);
                    } else {
                        setTimeout(tryLoad, 100);
                    }
                }
                tryLoad();
            })();

            // File upload handler (guarded)
            (function() {
                const fileUploadEl = document.getElementById('fileUpload');
                if (!fileUploadEl) return;
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
        document.addEventListener('livewire:load', function() {
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
    </script>
</div>
