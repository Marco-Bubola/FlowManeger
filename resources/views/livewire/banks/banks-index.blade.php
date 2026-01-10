<div class="w-full">

    @php
        // Agrupar por categoria para o gráfico
        $groupedForChart = collect($allInvoices ?? [])->groupBy(function($invoice) {
            return $invoice['category']['id_category'] ?? 'sem-categoria';
        });

        $displayTotal = collect($allInvoices ?? [])->sum(fn($invoice) => abs($invoice['value'] ?? 0));
        $displayLabel = $selectedDate
            ? 'Despesas de ' . \Carbon\Carbon::parse($selectedDate)->format('d/m/Y')
            : 'Despesas do mês';

        $categoriesChartData = $groupedForChart->map(function ($invoices, $categoryId) {
            $firstInvoice = $invoices->first();
            $category = $firstInvoice['category'] ?? null;
            return [
                'label' => $category['name'] ?? 'Sem categoria',
                'value' => $invoices->sum(fn($inv) => abs($inv['value'] ?? 0)),
                'color' => $category['hexcolor_category'] ?? '#6366f1',
            ];
        })->filter(fn($item) => ($item['value'] ?? 0) > 0)->values()->toArray();

        $currentMonthLabel = \Carbon\Carbon::create($year, $month, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');
    @endphp

    <!-- Main Content Layout -->
    <div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

            <!-- Left Column - Calendar & Chart (25% - 1 col) -->
            <div class="lg:col-span-1 space-y-3">
                <!-- Calendar - Modernizado -->
                <div class="relative rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
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
                                    {{ $currentMonthLabel ?? 'Carregando...' }}</p>
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
                    </div>

                    <!-- Conteúdo do calendário -->
                    <div class="px-5 pb-5">
                        <!-- Cabeçalho dos dias -->
                        <div class="grid grid-cols-7 gap-1 mb-3">
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">D</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">S</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">T</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">Q</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">Q</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">S</div>
                            <div class="text-center text-xs font-bold text-gray-700 dark:text-gray-300 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl">S</div>
                        </div>

                        <!-- Dias do calendário -->
                        <div class="grid grid-cols-7">
                            @if (isset($calendarDays) && is_array($calendarDays))
                                @foreach ($calendarDays as $day)
                                    <div wire:click="selectDate('{{ $day['date'] }}')"
                                        class="relative min-h-[42px] p-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md {{ $day['isCurrentMonth'] ?? true ? 'bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-2 border-white/40 dark:border-gray-700/40 hover:border-blue-300 dark:hover:border-blue-600' : 'bg-gray-100/60 dark:bg-gray-700/60 backdrop-blur-sm border-2 border-gray-200/40 dark:border-gray-600/40' }} {{ $day['isToday'] ?? false ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/30 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 shadow-lg shadow-green-500/30' : '' }}">
                                        <div class="text-sm font-bold {{ $day['isCurrentMonth'] ?? true ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ?? false ? 'text-blue-700 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-700 dark:text-green-400' : '' }}">
                                            {{ $day['day'] }}
                                        </div>
                                        @if (!empty($day['invoices']))
                                            <div class="absolute bottom-1 right-1">
                                                <div class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg animate-pulse"></div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                    style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border: 2px solid rgba(139, 92, 246, 0.2);">
                    <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-10"
                        style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>

                    <div class="relative px-6 py-5 backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <div class="relative group">
                                <div class="absolute inset-0 rounded-2xl blur-2xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                    style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);"></div>
                                <div class="relative w-12 h-12 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 shadow-xl"
                                    style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(59, 130, 246, 0.15) 100%);">
                                    <svg class="w-6 h-6 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $displayLabel }}</h3>
                                <p class="text-2xl font-extrabold text-transparent bg-clip-text" style="background-image: linear-gradient(135deg, #8b5cf6, #3b82f6);">
                                    R$ {{ number_format($displayTotal, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="relative px-6 pb-6">
                        @if (count($categoriesChartData) > 0)
                            <div id="categories-chart" class="w-full h-64"></div>
                            <script type="application/json" id="categories-data">@json($categoriesChartData)</script>
                            <script>
                                // Atualizar dados globais do gráfico sempre que o componente renderizar
                                (function() {
                                    try {
                                        const dataElement = document.getElementById('categories-data');
                                        if (dataElement) {
                                            const data = JSON.parse(dataElement.textContent);
                                            window.__categoriesChartData = Array.isArray(data) ? data : [];
                                            console.log('[banks-dashboard] Dados do gráfico atualizados:', window.__categoriesChartData);
                                        }
                                    } catch (error) {
                                        console.error('[banks-dashboard] Erro ao atualizar dados do gráfico:', error);
                                        window.__categoriesChartData = [];
                                    }
                                })();
                            </script>
                        @else
                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">Sem dados</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Section - Header and Banks (75% - 3 cols) -->
            <div class="lg:col-span-3 space-y-3">
                <!-- Header usando componente x-invoice-header com métricas inline -->
                <div class="relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border border-white/20 dark:border-slate-700/30 rounded-2xl shadow-xl overflow-hidden">
                    <!-- Efeitos decorativos (do componente original) -->
                    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-pink-400/20 to-rose-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-emerald-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

                    <div class="relative px-6 py-4">
                        <!-- Breadcrumb -->
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-home mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                <i class="fas fa-credit-card mr-1"></i>Bancos e Cartões
                            </span>
                        </div>

                        <!-- Top Section: Title + Metrics (adaptado do componente) -->
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 mb-4">
                            <!-- Logo + Title Section -->
                            <div class="flex items-center gap-4">
                                <!-- Ícone do componente original -->
                                <div class="relative flex items-center justify-center w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-2xl shadow-md shadow-purple-400/20">
                                    <i class="fas fa-university text-white text-3xl"></i>
                                </div>

                                <div class="space-y-1 ml-2">
                                    <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100 leading-tight tracking-tight drop-shadow-sm">
                                        Bancos e Cartões
                                    </h1>
                                    <p class="text-base font-medium text-slate-600 dark:text-slate-400">Gerencie seus bancos e cartões de crédito</p>
                                </div>
                            </div>

                            <!-- Lado Direito: Métricas Financeiras -->
                            <div class="flex items-center gap-4">
                                <!-- Despesas -->
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Despesas</span>
                                    <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-600">
                                        R$ {{ number_format($displayTotal, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Total do mês</span>
                                </div>

                                <!-- Divider -->
                                <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                                <!-- Transações -->
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transações</span>
                                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                                        {{ collect($allInvoices ?? [])->count() }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Quantidade</span>
                                </div>

                                <!-- Divider -->
                                <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                                <!-- Média -->
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Média</span>
                                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                        R$ {{ number_format(collect($allInvoices ?? [])->count() > 0 ? $displayTotal / collect($allInvoices ?? [])->count() : 0, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Por transação</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid: 3/4 para Invoices + 1/4 para Banks -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

                    <!-- Coluna 1: Todas as Invoices (3/4) -->
                    <div class="lg:col-span-3 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-file-invoice text-2xl text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                <div>
                                    <h2 class="text-xl font-black text-gray-900 dark:text-white">Todas as Transações</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ collect($allInvoices ?? [])->count() }} transações</p>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Invoices Agrupadas por Categoria -->
                        <div class="space-y-4 max-h-[600px] overflow-y-auto scrollbar-modern pr-2">
                            @php
                                // Agrupar invoices por categoria manualmente
                                $groupedByCategory = collect($allInvoices ?? [])->groupBy(function($invoice) {
                                    return $invoice['category']['id_category'] ?? 'sem-categoria';
                                })->map(function($invoices, $categoryId) {
                                    $firstInvoice = $invoices->first();
                                    $category = $firstInvoice['category'] ?? null;

                                    return [
                                        'name' => $category['name'] ?? 'Sem Categoria',
                                        'icon' => $category['icone'] ?? 'fas fa-question-circle',
                                        'color' => $category['hexcolor_category'] ?? '#6366f1',
                                        'total' => $invoices->sum(function($inv) { return abs($inv['value'] ?? 0); }),
                                        'invoices' => $invoices->all()
                                    ];
                                });
                            @endphp

                            @if($groupedByCategory->count() > 0)
                                @foreach($groupedByCategory as $categoryId => $group)
                                    @php
                                        $catName = $group['name'] ?? 'Sem Categoria';
                                        $catIcon = $group['icon'] ?? 'fas fa-question-circle';
                                        $catColor = $group['color'] ?? '#6366f1';
                                        $catTotal = $group['total'] ?? 0;
                                        $catCount = count($group['invoices'] ?? []);
                                        $catId = $categoryId;
                                    @endphp

                                    <div class="category-group expanded" data-category-id="{{ $catId }}">
                                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-gray-700/30 overflow-hidden shadow-lg">
                                            <!-- Header da Categoria -->
                                            <div class="px-6 py-4" style="background: linear-gradient(135deg, {{ $catColor }}15 0%, {{ $catColor }}05 100%);">
                                                <div class="flex items-center justify-between">
                                                    <!-- Esquerda: Ícone + Info -->
                                                    <div class="flex items-center gap-4">
                                                        <div class="relative group">
                                                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-md"
                                                                style="background: linear-gradient(135deg, {{ $catColor }}33 0%, {{ $catColor }}22 100%);">
                                                                <i class="{{ $catIcon }} text-gray-900 text-lg"></i>
                                                            </div>
                                                        </div>

                                                        <div class="flex-1">
                                                            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $catName }}</h3>
                                                            <div class="flex items-center gap-3 text-xs">
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold text-gray-900 dark:text-white"
                                                                    style="background: linear-gradient(135deg, {{ $catColor }}22 0%, {{ $catColor }}11 100%);">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                    </svg>
                                                                    <span>{{ $catCount }} {{ $catCount === 1 ? 'item' : 'itens' }}</span>
                                                                </span>
                                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full font-bold text-black shadow-md"
                                                                    style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                    <span>R$ {{ number_format($catTotal, 2, ',', '.') }}</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Direita: Toggle Button -->
                                                    <button type="button"
                                                        class="category-toggle-btn flex items-center gap-2 px-3 py-2 rounded-lg bg-white/90 dark:bg-gray-800/90 shadow-md hover:shadow-lg transition-all"
                                                        data-category-id="{{ $catId }}"
                                                        data-expanded="true">
                                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 toggle-label">Mostrar</span>
                                                        <div class="relative w-9 h-5 rounded-full transition-colors"
                                                            style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform toggle-thumb" style="transform: translateX(16px);"></div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Grid de Invoices -->
                                            <div class="group-invoices-wrapper overflow-hidden transition-all duration-500">
                                                <div class="px-6 pb-6">
                                                    <div class="group-invoices grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                        @foreach($group['invoices'] as $invoice)
                                                            @php
                                                                $value = abs($invoice['value'] ?? 0);
                                                                $desc = $invoice['description'] ?? 'Sem descrição';
                                                                $invoiceDate = isset($invoice['invoice_date']) ? \Carbon\Carbon::parse($invoice['invoice_date'])->format('d/m/Y') : 'Sem data';
                                                                $invoiceId = $invoice['id_invoice'] ?? $invoice['id'] ?? null;
                                                            @endphp

                                                            <div class="invoice-card group relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                                                                <!-- Borda superior colorida -->
                                                                <div class="absolute top-0 left-0 right-0 h-1 transition-all duration-300 group-hover:h-1.5"
                                                                    style="background: linear-gradient(90deg, {{ $catColor }} 0%, {{ $catColor }}aa 100%);"></div>

                                                                <!-- Conteúdo -->
                                                                <div class="p-4 pt-5">
                                                                    <div class="flex items-start justify-between gap-2 mb-3">
                                                                        <!-- Descrição -->
                                                                        <div class="flex-1 min-w-0">
                                                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1 line-clamp-2">{{ $desc }}</h4>
                                                                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                                </svg>
                                                                                <span>{{ $invoiceDate }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Valor -->
                                                                        <div class="flex-shrink-0">
                                                                            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full font-bold text-xs shadow-sm text-black"
                                                                                style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%);">
                                                                                <span>{{ number_format($value, 2, ',', '.') }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Ações -->
                                                                    @if($invoiceId)
                                                                        <div class="card-actions">
                                                                            <div class="flex items-center justify-center gap-1 pt-2 border-t border-gray-100 dark:border-gray-700">
                                                                                <a href="{{ route('invoices.edit', $invoiceId) }}"
                                                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-white shadow-sm hover:shadow-md rounded-lg border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-blue-600 hover:text-blue-800 transition text-xs font-semibold">
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                                    </svg>
                                                                                    <span class="hidden sm:inline">Editar</span>
                                                                                </a>
                                                                                <button wire:click="confirmDelete({{ $invoiceId }})"
                                                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-white shadow-sm hover:shadow-md rounded-lg border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-red-600 hover:text-red-800 transition text-xs font-semibold">
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                                    </svg>
                                                                                    <span class="hidden sm:inline">Excluir</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endif
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
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-300 to-gray-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhuma transação</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Comece criando sua primeira transação</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Coluna 2: Banks (1/4) -->
                    <div class="lg:col-span-1 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/30 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-credit-card text-2xl text-purple-600 dark:text-purple-400 mr-3"></i>
                                <div>
                                    <h2 class="text-xl font-black text-gray-900 dark:text-white">Meus Bancos</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $paginatedBanks->total() ?? count($paginatedBanks) }} bancos</p>
                                </div>
                            </div>
                            <a href="{{ route('banks.create') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                                <i class="fas fa-plus"></i>
                                <span>Novo</span>
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
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Nenhum cartão cadastrado</h3>
                                    <p class="text-gray-600 dark:text-gray-300">Adicione seu primeiro cartão para começar</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Paginação -->
                    @if ($paginatedBanks->hasPages())
                        <div class="mt-6 flex justify-center items-center space-x-2">
                            {{ $paginatedBanks->links() }}
                        </div>
                    @endif
                </div>
                </div>
            </div>
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

        /* Calendar days */
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
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function() {
            function ensureGlobalData() {
                const dataElement = document.getElementById('categories-data');
                if (dataElement) {
                    try {
                        const data = JSON.parse(dataElement.textContent);
                        window.__categoriesChartData = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('[banks-dashboard] Erro ao parsear dados:', error);
                        window.__categoriesChartData = [];
                    }
                } else {
                    window.__categoriesChartData = [];
                }
            }

            function renderChart() {
                console.log('[banks-dashboard] Tentando renderizar gráfico...');

                if (typeof window.ApexCharts === 'undefined') {
                    console.log('[banks-dashboard] ApexCharts não disponível ainda, tentando novamente em 100ms');
                    setTimeout(renderChart, 100);
                    return;
                }

                ensureGlobalData();
                const chartElement = document.querySelector('#categories-chart');

                if (!chartElement) {
                    console.error('[banks-dashboard] Elemento #categories-chart não encontrado no DOM');
                    console.log('[banks-dashboard] Elementos disponíveis:', document.querySelectorAll('[id]'));
                    // Tentar novamente após pequeno delay
                    setTimeout(renderChart, 200);
                    return;
                }

                const chartData = window.__categoriesChartData || [];
                if (!Array.isArray(chartData) || chartData.length === 0) {
                    console.log('[banks-dashboard] Sem dados para o gráfico');
                    return;
                }

                console.log('[banks-dashboard] Renderizando gráfico com', chartData.length, 'categorias');

                // Limpar gráfico existente
                if (window.__banksChartInstance) {
                    try {
                        window.__banksChartInstance.destroy();
                    } catch(e) {
                        console.log('[banks-dashboard] Erro ao destruir gráfico anterior:', e);
                    }
                }

                const options = {
                    series: chartData.map(item => item.value || 0),
                    labels: chartData.map(item => item.label || 'Sem categoria'),
                    colors: chartData.map(item => item.color || '#6366f1'),
                    chart: {
                        type: 'donut',
                        height: 256,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toFixed(1) + '%';
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%'
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return 'R$ ' + val.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        }
                    }
                };

                window.__banksChartInstance = new ApexCharts(chartElement, options);
                window.__banksChartInstance.render();
                console.log('[banks-dashboard] Gráfico renderizado com sucesso');
            }

            function bootstrapCharts() {
                ensureGlobalData();
                loadScriptOnce('https://cdn.jsdelivr.net/npm/apexcharts', 'apexcharts-cdn-script', () => typeof window.ApexCharts !== 'undefined')
                    .then(() => {
                        console.log('[banks-dashboard] ApexCharts carregado');
                        renderChart();
                    })
                    .catch(error => console.error('[banks-dashboard] Falha ao carregar scripts do gráfico', error));
            }

            // Inicializar
            function init() {
                console.log('[banks-dashboard] Inicializando gráfico');
                ensureGlobalData();
                renderChart();
            }

            // Carregar inicialmente
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(init, 100);
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(init, 100);
                });
            }

            // Re-renderizar após atualizações do Livewire
            if (window.Livewire) {
                document.addEventListener('livewire:update', function() {
                    console.log('[banks-dashboard] Livewire atualizado, re-renderizando gráfico');
                    setTimeout(() => {
                        ensureGlobalData();
                        renderChart();
                    }, 200);
                });
            }
        })();
    </script>

    <!-- Toggle Script para Categorias -->
    <script>
        (function(){
            'use strict';

            function toggleCategoryByButton(btn){
                if(!btn) return;
                var categoryId = btn.getAttribute('data-category-id');
                if(!categoryId) return;
                var group = document.querySelector('.category-group[data-category-id="' + categoryId + '"]');
                if(!group) return;
                var wrapper = group.querySelector('.group-invoices-wrapper');
                var thumb = btn.querySelector('.toggle-thumb');
                var label = btn.querySelector('.toggle-label');

                var isExpanded = group.classList.contains('expanded');

                if(isExpanded){
                    // collapse
                    group.classList.remove('expanded');
                    group.classList.add('collapsed');
                    if(wrapper){ wrapper.style.maxHeight = '0px'; wrapper.style.opacity = '0'; }
                    if(thumb) thumb.style.transform = 'translateX(0)';
                    if(label) label.textContent = 'Oculto';
                    btn.setAttribute('data-expanded','false');
                }else{
                    // expand
                    group.classList.add('expanded');
                    group.classList.remove('collapsed');
                    if(wrapper){
                        var h = wrapper.scrollHeight || wrapper.querySelector('.group-invoices')?.scrollHeight || 400;
                        wrapper.style.maxHeight = h + 'px';
                        wrapper.style.opacity = '1';
                        setTimeout(function(){ try{ wrapper.style.maxHeight = null; }catch(e){} }, 520);
                    }
                    if(thumb) thumb.style.transform = 'translateX(16px)';
                    if(label) label.textContent = 'Mostrar';
                    btn.setAttribute('data-expanded','true');
                }
            }

            document.addEventListener('click', function(e){
                var btn = e.target.closest('.category-toggle-btn');
                if(!btn) return;
                e.preventDefault();
                e.stopPropagation();
                toggleCategoryByButton(btn);
            }, true);

            function initStates(){
                document.querySelectorAll('.category-group').forEach(function(g){
                    var wrapper = g.querySelector('.group-invoices-wrapper');
                    if(g.classList.contains('expanded')){
                        if(wrapper){ wrapper.style.maxHeight = (wrapper.scrollHeight || 400) + 'px'; wrapper.style.opacity = '1'; }
                    }else{
                        if(wrapper){ wrapper.style.maxHeight = '0px'; wrapper.style.opacity = '0'; }
                    }
                });
            }

            if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initStates);
            else initStates();

            if(window.Livewire){
                document.addEventListener('livewire:update', initStates);
                document.addEventListener('livewire:load', initStates);
            }
        })();
    </script>

    <!-- Resto dos estilos e scripts do arquivo original -->
</div>
