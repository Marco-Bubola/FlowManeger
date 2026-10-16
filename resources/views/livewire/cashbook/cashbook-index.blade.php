<div x-data="{ showFilters: false }" class="cashbook-index-page w-full mobile-393-base relative">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cashbook-index-ultrawide.css') }}">

    <x-loading-overlay message="Carregando lançamentos..." />

    @php
        $cxIncome = $totals['income'] ?? 0;
        $cxExpense = abs($totals['expense'] ?? 0);
        $cxBalance = $totals['balance'] ?? 0;
        $cxCount = $transactionsCount ?? 0;
    @endphp
    <x-cashbook-page-header title="Livro caixa" subtitle="Entradas e saídas do seu dinheiro, mês a mês" icon="bi-journal-text" active="caixa" :back-route="route('dashboard')">
        <x-slot:actions>
            <button type="button" wire:click="toggleTips" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-lightbulb text-amber-500"></i><span class="hidden sm:inline">Dicas</span></button>
            <a href="{{ route('cashbook.create') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50"><i class="bi bi-plus-lg"></i>Novo lançamento</a>
        </x-slot:actions>
    </x-cashbook-page-header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-gestao-stat label="Entradas" :value="'R$ ' . number_format($cxIncome, 2, ',', '.')" icon="bi-arrow-down-left" tone="emerald" />
        <x-gestao-stat label="Saídas" :value="'R$ ' . number_format($cxExpense, 2, ',', '.')" icon="bi-arrow-up-right" tone="rose" />
        <x-gestao-stat label="Saldo do mês" :value="'R$ ' . number_format($cxBalance, 2, ',', '.')" icon="bi-wallet2" :tone="$cxBalance >= 0 ? 'indigo' : 'rose'" />
        <x-gestao-stat label="Lançamentos" :value="$cxCount" icon="bi-list-check" tone="sky" />
    </div>

    <!-- Main Content Layout -->
    <div class="w-full">
        <div class="cashbook-main-grid grid grid-cols-1 lg:grid-cols-4 gap-4">

            <!-- Left Column - Calendar & Chart (25% - 1 col) -->
            <div class="cashbook-side lg:col-span-1 space-y-3">
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
                                    {{ $monthName ?? 'Carregando...' }}</p>
                            </div>
                        </div>

                        <!-- Navegação -->
                        <div class="flex items-center justify-between gap-2">
                            <button wire:click="changeMonth('previous')"
                                class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 transition-all duration-200 transform hover:scale-105 border border-white/20 dark:border-gray-700/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <select wire:model.live="month"
                                    class="flex-1 min-w-0 capitalize rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg text-gray-900 dark:text-white text-sm font-semibold shadow-md focus:ring-2 focus:ring-blue-500 transition-all">
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">
                                            {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMM') }}
                                        </option>
                                    @endforeach
                                </select>
                                <select wire:model.live="year"
                                    style="width: 5.5rem" class="shrink-0 rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg text-gray-900 dark:text-white text-sm font-semibold shadow-md focus:ring-2 focus:ring-blue-500 transition-all">
                                    @foreach (range(now()->year - 5, now()->year + 2) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button wire:click="changeMonth('next')"
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
                        <!-- Dias da Semana -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
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

                        <!-- Dias do Calendário -->
                        <div class="grid grid-cols-7">
                            @foreach ($this->calendarDays as $day)
                                @if ($day['day'])
                                    <div wire:click="selectDate('{{ $day['date'] }}')"
                                        class="relative min-h-[42px] p-2 rounded-xl cursor-pointer transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md {{ $day['isCurrentMonth'] ? 'bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-2 border-white/40 dark:border-gray-700/40 hover:border-blue-300 dark:hover:border-blue-600' : 'bg-gray-100/60 dark:bg-gray-700/60 backdrop-blur-sm border-2 border-gray-200/40 dark:border-gray-600/40' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-500/30 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30' : '' }} {{ $selectedDate === $day['date'] ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 shadow-lg shadow-green-500/30' : '' }}">
                                        <div
                                            class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-700 dark:text-blue-400' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-700 dark:text-green-400' : '' }}">
                                            {{ $day['day'] }}
                                        </div>
                                        @if ($day['hasTransactions'])
                                            <div class="absolute bottom-1 right-1">
                                                <div
                                                    class="w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full shadow-lg animate-pulse">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="relative min-h-[42px] p-2"></div>
                                @endif
                            @endforeach
                        </div>

                        @if (isset($selectedDate) && $selectedDate)
                            <div class="mt-4">
                                <button wire:click="clearDateFilter"
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
                                <p class="text-sm text-gray-600 dark:text-gray-400">Visualização das transações por
                                    categoria</p>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Chart -->
                    <div class="">
                        @if (count($categoriesChartData) > 0)
                            <div>
                                <div id="apex-pie-cashbook" wire:ignore class="w-full" style="min-height: 240px;"></div>
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

                <livewire:cashbook.sales-sync-toggle />

                <!-- Transactions List -->
                <div class="space-y-3">
                    <!-- Busca, filtros e expandir/recolher -->
                    @php
                        $cxFilterOn = $typeFilter !== '' || $statusFilter !== '' || $categoryFilter !== '' || $clientFilter !== '' || $cofrinhoFilter !== '';
                        $opt = fn ($on) => 'fm-fs-opt' . ($on ? ' is-on' : '');
                    @endphp
                    <x-list-toolbar placeholder="Buscar lançamento, categoria ou cliente" class="!mb-0">
                        <x-toolbar.group>
                            <x-toolbar.chip @click="showFilters = true" :active="$cxFilterOn" icon="bi-funnel">Filtros</x-toolbar.chip>
                            <x-toolbar.chip onclick="expandAllCategories()" icon="bi-arrows-expand">Expandir todas</x-toolbar.chip>
                            <x-toolbar.chip onclick="collapseAllCategories()" icon="bi-arrows-collapse">Recolher todas</x-toolbar.chip>
                        </x-toolbar.group>
                    </x-list-toolbar>

                    <x-filter-sheet title="Filtros do livro caixa" subtitle="Valem para o mês escolhido" :active="$cxFilterOn"
                        target="typeFilter,statusFilter,categoryFilter,clientFilter,cofrinhoFilter,clearFilters">
                        <div class="fm-fs-group">
                            <h4><i class="bi bi-arrow-left-right text-indigo-500"></i>Tipo</h4>
                            <div class="fm-fs-options">
                                @foreach(['' => 'Todos', '1' => 'Entradas', '2' => 'Saídas'] as $val => $lbl)
                                    <button type="button" wire:click="$set('typeFilter', '{{ $val }}')" class="{{ $opt($typeFilter === (string) $val) }}">{{ $lbl }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="fm-fs-group">
                            <h4><i class="bi bi-hourglass-split text-amber-500"></i>Situação</h4>
                            <div class="fm-fs-options">
                                @foreach(['' => 'Todos', 'confirmed' => 'Confirmados', 'pending' => 'Pendentes'] as $val => $lbl)
                                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')" class="{{ $opt($statusFilter === $val) }}">{{ $lbl }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="fm-fs-group">
                            <h4><i class="bi bi-tags text-purple-500"></i>Categoria</h4>
                            <select wire:model.live="categoryFilter">
                                <option value="">Todas as categorias</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fm-fs-group">
                            <h4><i class="bi bi-person text-sky-500"></i>Cliente</h4>
                            <select wire:model.live="clientFilter">
                                <option value="">Todos os clientes</option>
                                @foreach($clients as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(count($cofrinhos))
                            <div class="fm-fs-group">
                                <h4><i class="bi bi-piggy-bank text-emerald-500"></i>Cofrinho</h4>
                                <select wire:model.live="cofrinhoFilter">
                                    <option value="">Todos (com e sem cofrinho)</option>
                                    @foreach($cofrinhos as $cf)
                                        <option value="{{ $cf->id }}">{{ $cf->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </x-filter-sheet>

                    <!-- Transactions Content -->
                    <div class="space-y-3 max-h-[calc(100vh-200px)] overflow-y-auto cards-scroll">
                        @forelse($transactionsByCategory as $index => $categoryGroup)
                            @php
                                $catColor = $categoryGroup['color'] ?? '#667eea';
                                $catName = $categoryGroup['name'] ?? 'Sem Categoria';
                                $catIcon = 'fas fa-tag';
                                $catTotal = $categoryGroup['total'] ?? 0;
                                $catCount = $categoryGroup['count'] ?? 0;

                                // Calcular receitas e despesas da categoria
                                $catIncome = 0;
                                $catExpense = 0;
                                foreach ($categoryGroup['transactions'] as $trans) {
                                    if ($trans['type_id'] == 1) {
                                        $catIncome += $trans['value'];
                                    } else {
                                        $catExpense += abs($trans['value']);
                                    }
                                }
                                $catBalance = $catIncome - $catExpense;
                            @endphp

                            <div class="category-group mb-4 transition-all duration-500 expanded"
                                data-category-id="{{ $index }}">
                                <!-- Card moderno com glassmorphism e cores da categoria -->
                                <div class="relative overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300"
                                    style="background: linear-gradient(135deg, {{ $catColor }}15 0%, {{ $catColor }}08 100%); border: 2px solid {{ $catColor }}40;">

                                    <!-- Efeito de brilho decorativo -->
                                    <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-20"
                                        style="background: {{ $catColor }};"></div>

                                    {{-- Cabeçalho da categoria: nome, 3 totais que não quebram linha e o botão Ocultar.
                                         Em telas estreitas (notebook 1366px, iPad, celular) os totais descem para uma linha própria. --}}
                                    <div class="cx-cat-head relative px-4 sm:px-5 py-4">
                                        <div class="cx-cat-id">
                                            <span class="cx-cat-icon" style="background: {{ $catColor }}26; color: {{ $catColor }};">
                                                <i class="bi bi-tag-fill"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <h3 class="cx-cat-name" title="{{ $catName }}">{{ $catName }}</h3>
                                                <span class="cx-cat-count">{{ $catCount }} {{ $catCount === 1 ? 'lançamento' : 'lançamentos' }}</span>
                                            </div>
                                        </div>

                                        <div class="cx-cat-stats">
                                            <div class="cx-cat-stat">
                                                <span class="cx-cat-stat-l"><i class="bi bi-arrow-up text-emerald-500"></i>Receitas</span>
                                                <span class="cx-cat-stat-v text-emerald-600 dark:text-emerald-400">R$ {{ number_format($catIncome, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="cx-cat-stat">
                                                <span class="cx-cat-stat-l"><i class="bi bi-arrow-down text-rose-500"></i>Despesas</span>
                                                <span class="cx-cat-stat-v text-rose-600 dark:text-rose-400">R$ {{ number_format($catExpense, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="cx-cat-stat">
                                                <span class="cx-cat-stat-l"><i class="bi bi-scale {{ $catBalance >= 0 ? 'text-sky-500' : 'text-orange-500' }}"></i>Saldo</span>
                                                <span class="cx-cat-stat-v {{ $catBalance >= 0 ? 'text-sky-600 dark:text-sky-400' : 'text-orange-600 dark:text-orange-400' }}">R$ {{ number_format($catBalance, 2, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="category-toggle-btn cx-cat-toggle"
                                            data-category-id="{{ $index }}" data-expanded="true"
                                            style="--cat-color: {{ $catColor }};"
                                            aria-label="Alternar visibilidade da categoria {{ $catName }}">
                                            <span class="toggle-label">Ocultar</span>
                                            <div class="relative shrink-0 rounded-full transition-colors duration-300 toggle-switch"
                                                style="width: 40px; height: 22px; background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%); --cat-color: {{ $catColor }};">
                                                <div class="absolute bg-white rounded-full shadow-md transition-transform duration-300 toggle-thumb"
                                                    style="top: 3px; left: 3px; width: 16px; height: 16px; transform: translateX(18px);"></div>
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Conteúdo (Grid de transações) -->
                                    <div
                                        class="group-transactions-wrapper overflow-hidden transition-all duration-500">
                                        <div class="px-4 sm:px-5 pb-5">
                                            <div
                                                class="group-transactions cx-tx-grid">
                                                @foreach ($categoryGroup['transactions'] as $transaction)
                                                    @php
                                                        $value = abs($transaction['value'] ?? 0);
                                                        $desc = $transaction['description'] ?? '';
                                                        $transactionDate = isset($transaction['time'])
                                                            ? $transaction['time']
                                                            : '';
                                                        $typeId = $transaction['type_id'] ?? 0;
                                                        $clientName = $transaction['client_name'] ?? null;
                                                    @endphp

                                                    <div
                                                        class="transaction-card group relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
                                                        <!-- Borda superior colorida com gradiente -->
                                                        <div class="absolute top-0 left-0 right-0 h-1.5 transition-all duration-300 group-hover:h-2"
                                                            style="background: linear-gradient(90deg, {{ $catColor }} 0%, {{ $catColor }}aa 100%);">
                                                        </div>

                                                        <!-- Conteúdo do card -->
                                                        <div class="p-4 pt-5 pb-3">
                                                            <h4 class="cx-tx-title" title="{{ $desc }}">{{ $desc }}</h4>
                                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><i class="bi bi-calendar3"></i>{{ $transactionDate }}</span>
                                                                <span class="cx-tx-badge {{ $typeId == 1 ? 'is-in' : 'is-out' }}">
                                                                    <i class="bi {{ $typeId == 1 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>{{ $typeId == 1 ? 'Receita' : 'Despesa' }}
                                                                </span>
                                                            </div>

                                                            <!-- Valor principal -->
                                                            <div
                                                                class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                                                                <p
                                                                    class="text-xl font-black whitespace-nowrap {{ $typeId == 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                                    {{ $typeId == 1 ? '+' : '-' }} R$
                                                                    {{ number_format($value, 2, ',', '.') }}
                                                                </p>
                                                            </div>

                                                            <!-- Cliente (se houver) -->
                                                            @if ($clientName)
                                                                <div
                                                                    class="mb-3 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                                        </path>
                                                                    </svg>
                                                                    <span
                                                                        class="font-medium">{{ $clientName }}</span>
                                                                </div>
                                                            @endif

                                                            <!-- Actions -->
                                                            <div class="card-actions">
                                                                @if (isset($transaction['id']))
                                                                <div
                                                                    class="flex items-center justify-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                                                                    <a href="{{ route('cashbook.edit', ['cashbook' => $transaction['id'], 'return_month' => $month, 'return_year' => $year]) }}"
                                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-white shadow-md hover:shadow-lg rounded-full border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-blue-600 hover:text-blue-800 transition text-xs font-semibold">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                            </path>
                                                                        </svg>
                                                                        <span class="hidden sm:inline">Editar</span>
                                                                    </a>
                                                                    <button
                                                                        wire:click="confirmDelete({{ $transaction['id'] }})"
                                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-white shadow-md hover:shadow-lg rounded-full border border-gray-100 dark:bg-gray-800/80 dark:border-gray-700 text-red-600 hover:text-red-800 transition text-xs font-semibold">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                            </path>
                                                                        </svg>
                                                                        <span class="hidden sm:inline">Excluir</span>
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
                                </div>
                            </div>
                        @empty
                            <!-- Empty State -->
                            <div
                                class="relative overflow-hidden bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 p-16 text-center">
                                <div
                                    class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-500/5 to-purple-500/5 rounded-full blur-3xl">
                                </div>
                                <div class="relative">
                                    <div
                                        class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 rounded-3xl mb-6 floating-animation">
                                        <i class="fas fa-book text-6xl text-blue-500 dark:text-blue-400"></i>
                                    </div>
                                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">
                                        📖 Nenhuma Transação Encontrada
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                                        @if($search !== '' || $cxFilterOn)
                                            Nenhum lançamento com essa busca ou filtro neste mês. Tente outro termo ou limpe os filtros.
                                        @else
                                            Não há transações registradas para o período selecionado. Comece adicionando uma
                                            nova transação!
                                        @endif
                                    </p>
                                    <a href="{{ route('cashbook.create') }}"
                                        class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-xl hover:shadow-2xl transform hover:scale-105">
                                        <i class="fas fa-plus"></i>
                                        Nova Transação
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <x-delete-dialog confirm="deleteTransaction" cancel="$set('showDeleteModal', false)" title="Excluir lançamento?" confirm-label="Excluir lançamento" icon="bi-journal-x">
            O lançamento <strong>{{ $deletingTransaction->description ?? 'sem descrição' }}</strong> será apagado do livro caixa. Não dá para desfazer.
        </x-delete-dialog>
    @endif

    <!-- Custom Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
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

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            background-size: 1000px 100%;
        }

        .shadow-3xl {
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        /* Estilos dos botões de ação dos cards */
        .card-actions {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity 0.3s ease-out, max-height 0.3s ease-out;
        }

        .transaction-card:hover .card-actions {
            opacity: 1;
            max-height: 100px;
        }

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

        /* Estilos para expandir/retrair categorias */
        .group-transactions-wrapper {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out, opacity 0.5s ease-out;
        }


        /* Cabeçalho da categoria e cartões de lançamento (sem quebrar valores no notebook) */
        .cx-cat-head { display: flex; align-items: center; gap: .75rem 1rem; flex-wrap: wrap; }
        .cx-cat-id { display: flex; align-items: center; gap: .75rem; min-width: 0; flex: 1 1 180px; }
        .cx-cat-icon { width: 2.75rem; height: 2.75rem; flex-shrink: 0; border-radius: .9rem; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
        .cx-cat-name { font-size: 1.15rem; font-weight: 900; line-height: 1.2; color: rgb(17 24 39); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .cx-cat-name { color: #fff; }
        .cx-cat-count { font-size: .72rem; font-weight: 600; color: rgb(75 85 99); white-space: nowrap; }
        .dark .cx-cat-count { color: rgb(156 163 175); }
        .cx-cat-stats { display: grid; grid-template-columns: repeat(3, minmax(0, auto)); gap: .5rem 1.25rem; order: 2; }
        .cx-cat-stat { display: flex; flex-direction: column; min-width: 0; }
        .cx-cat-stat-l { display: inline-flex; align-items: center; gap: .25rem; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(75 85 99); white-space: nowrap; }
        .dark .cx-cat-stat-l { color: rgb(156 163 175); }
        .cx-cat-stat-v { font-size: .98rem; font-weight: 900; white-space: nowrap; }
        .cx-cat-toggle { order: 3; display: inline-flex; align-items: center; gap: .5rem; flex-shrink: 0; padding: .4rem .6rem .4rem .8rem; border-radius: .8rem; background: rgb(255 255 255 / .9); box-shadow: 0 2px 8px -3px rgb(15 23 42 / .25); font-size: .72rem; font-weight: 700; color: rgb(55 65 81); }
        .dark .cx-cat-toggle { background: rgb(31 41 55 / .9); color: rgb(209 213 219); }
        /* Abaixo de 1500px os totais vão para a linha de baixo, ocupando a largura toda */
        @media (max-width: 1499px) {
            .cx-cat-stats { order: 4; flex: 1 1 100%; grid-template-columns: repeat(3, minmax(0, 1fr)); padding: .55rem .75rem; border-radius: .9rem; background: rgb(255 255 255 / .6); }
            .dark .cx-cat-stats { background: rgb(17 24 39 / .45); }
        }
        @media (max-width: 480px) { .cx-cat-stat-v { font-size: .85rem; } .cx-cat-stats { gap: .4rem .6rem; padding: .5rem .6rem; } }
        .cx-tx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 250px), 1fr)); gap: .9rem; }
        html body .cashbook-main-grid .group-transactions.cx-tx-grid { grid-template-columns: repeat(auto-fill, minmax(min(100%, 230px), 1fr)) !important; }
        .cx-tx-title { font-size: .95rem; font-weight: 800; line-height: 1.3; color: rgb(17 24 39); margin-bottom: .35rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; }
        .dark .cx-tx-title { color: #fff; }
        .cx-tx-badge { display: inline-flex; align-items: center; gap: .2rem; flex-shrink: 0; font-size: .66rem; font-weight: 700; padding: .15rem .45rem; border-radius: 999px; }
        .cx-tx-badge.is-in { background: rgb(220 252 231); color: rgb(21 128 61); }
        .cx-tx-badge.is-out { background: rgb(254 226 226); color: rgb(185 28 28); }
        .dark .cx-tx-badge.is-in { background: rgb(20 83 45 / .5); color: rgb(134 239 172); }
        .dark .cx-tx-badge.is-out { background: rgb(127 29 29 / .5); color: rgb(252 165 165); }
        .category-group.expanded .group-transactions-wrapper {
            max-height: 10000px;
            opacity: 1;
        }
    </style>

    {{-- Scripts da tela. Antes ficavam num @push('scripts') que o layout do app nunca imprimia,
         por isso o gráfico, "Expandir/Recolher todas" e o botão Ocultar de cada categoria não funcionavam. --}}
    <script>
        (() => {
            const setGroup = (group, open) => {
                const btn = group.querySelector('.category-toggle-btn');
                const wrapper = group.querySelector('.group-transactions-wrapper');
                if (!btn) return;
                const sw = btn.querySelector('.toggle-switch');
                const thumb = btn.querySelector('.toggle-thumb');
                const label = btn.querySelector('.toggle-label');
                const color = btn.style.getPropertyValue('--cat-color');
                group.classList.toggle('expanded', open);
                if (wrapper) wrapper.style.maxHeight = open ? wrapper.scrollHeight + 'px' : '0px';
                if (thumb) thumb.style.transform = open ? 'translateX(18px)' : 'translateX(0)';
                if (sw) sw.style.background = open ? `linear-gradient(135deg, ${color} 0%, ${color}CC 100%)` : '#cbd5e0';
                if (label) label.textContent = open ? 'Ocultar' : 'Mostrar';
                btn.dataset.expanded = open ? 'true' : 'false';
            };
            window.expandAllCategories = () => document.querySelectorAll('.category-group').forEach(g => setGroup(g, true));
            window.collapseAllCategories = () => document.querySelectorAll('.category-group').forEach(g => setGroup(g, false));

            if (!window.__cxToggleBound) {
                window.__cxToggleBound = true;
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.category-toggle-btn');
                    if (!btn) return;
                    e.stopPropagation();
                    setGroup(btn.closest('.category-group'), btn.dataset.expanded !== 'true');
                });
            }

            let pie = null;
            const drawPie = (data) => {
                const el = document.querySelector('#apex-pie-cashbook');
                if (!el || typeof ApexCharts === 'undefined') return;
                if (pie) { pie.destroy(); pie = null; }
                el.innerHTML = '';
                if (!data || !data.length) return;
                pie = new ApexCharts(el, {
                    series: data.map(i => Number(i.value)),
                    labels: data.map(i => i.label),
                    colors: data.map(i => i.color),
                    chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', fontSize: '12px' },
                    stroke: { width: 2 },
                    plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total',
                        formatter: (w) => 'R$ ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) } } } } },
                    tooltip: { y: { formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) } },
                });
                pie.render();
            };
            drawPie(@json($categoriesChartData));

            const bindLivewire = () => {
                if (window.__cxLivewireBound) return;
                window.__cxLivewireBound = true;
                Livewire.on('cashbook-chart-updated', (event) => {
                    const payload = Array.isArray(event) ? event[0] : event;
                    drawPie(payload?.data ?? []);
                });
            };
            if (window.Livewire) bindLivewire(); else document.addEventListener('livewire:init', bindLivewire, { once: true });
        })();
    </script>
</div>
