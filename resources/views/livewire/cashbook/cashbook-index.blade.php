<div x-data="{ showFilters: false }" class="w-full">

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
                            <button wire:click="changeMonth('next')"
                                class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 transition-all duration-200 transform hover:scale-105 border border-white/20 dark:border-gray-700/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <!-- Botão de Dicas -->
                        <button wire:click="toggleTips"
                            class="p-2 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-lightbulb text-sm"></i>
                        </button>
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
                                <div id="apex-pie-cashbook" class="w-full" style="height: 240px;"></div>
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
                <x-cashbook-header :total-transactions="$transactionsCount ?? 0" :total-balance="$totals['balance'] ?? 0" :total-income="$totals['income'] ?? 0" :total-expense="abs($totals['expense'] ?? 0)"
                    :show-quick-actions="true">

                    <!-- Breadcrumb -->
                    <x-slot name="breadcrumb">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <a href="{{ route('dashboard') }}"
                                class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-home mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                <i class="fas fa-book mr-1"></i>Livro Caixa
                            </span>
                        </div>
                    </x-slot>
                </x-cashbook-header>

                <!-- Transactions List -->
                <div class="space-y-3">
                    <!-- Controls -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button onclick="expandAllCategories()"
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-expand-alt mr-2"></i>Expandir Todas
                            </button>
                            <button onclick="collapseAllCategories()"
                                class="px-4 py-2 bg-gradient-to-r from-slate-500 to-slate-600 text-white text-sm font-bold rounded-xl hover:from-slate-600 hover:to-slate-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-compress-alt mr-2"></i>Recolher Todas
                            </button>
                        </div>
                    </div>

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

                            <div class="category-group mb-8 transform transition-all duration-500 expanded"
                                data-category-id="{{ $index }}">
                                <!-- Card moderno com glassmorphism e cores da categoria -->
                                <div class="relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-1"
                                    style="background: linear-gradient(135deg, {{ $catColor }}15 0%, {{ $catColor }}08 100%); border: 2px solid {{ $catColor }}40;">

                                    <!-- Efeito de brilho decorativo -->
                                    <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl opacity-20"
                                        style="background: {{ $catColor }};"></div>

                                    <!-- Header super estilizado -->
                                    <div class="relative px-6 py-5 backdrop-blur-sm">
                                        <div class="flex items-center justify-between gap-6">
                                            <!-- Esquerda: Ícone + Nome da Categoria -->
                                            <div class="flex items-center gap-4">
                                                <!-- Ícone com animação e fundo translúcido -->
                                                <div class="relative group">
                                                    <div class="absolute inset-0 rounded-2xl blur-3xl opacity-30 group-hover:opacity-45 transition-opacity duration-300"
                                                        style="background: {{ $catColor }}33;"></div>
                                                    <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-xl"
                                                        style="background: linear-gradient(135deg, {{ $catColor }}33 0%, {{ $catColor }}22 100%);">
                                                        <i class="{{ $catIcon }} text-gray-900 text-2xl"></i>
                                                    </div>
                                                </div>

                                                <!-- Nome e contador -->
                                                <div>
                                                    <h3 class="text-2xl font-black mb-1 text-gray-900 dark:text-white">
                                                        {{ $catName }}
                                                    </h3>
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold text-gray-900 dark:text-white shadow-lg"
                                                        style="background: linear-gradient(135deg, {{ $catColor }}22 0%, {{ $catColor }}11 100%);">
                                                        <svg class="w-4 h-4 text-gray-900" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                            </path>
                                                        </svg>
                                                        <span class="text-sm font-medium">{{ $catCount }}
                                                            {{ $catCount === 1 ? 'transação' : 'transações' }}</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Divider -->
                                            <div
                                                class="h-16 w-px bg-gradient-to-b from-transparent via-gray-300 dark:via-gray-600 to-transparent">
                                            </div>

                                            <!-- Receitas -->
                                            <div class="flex items-center gap-3">
                                                <div class="relative group">
                                                    <div class="absolute inset-0 rounded-xl blur-2xl opacity-40 group-hover:opacity-60 transition-opacity"
                                                        style="background: linear-gradient(135deg, #22c55e 0%, #10b981 100%);">
                                                    </div>
                                                    <div class="relative w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"
                                                        style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(16, 185, 129, 0.15) 100%);">
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                                        Receitas</p>
                                                    <p class="text-lg font-black text-green-600 dark:text-green-400">
                                                        R$ {{ number_format($catIncome, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Divider -->
                                            <div
                                                class="h-16 w-px bg-gradient-to-b from-transparent via-gray-300 dark:via-gray-600 to-transparent">
                                            </div>

                                            <!-- Despesas -->
                                            <div class="flex items-center gap-3">
                                                <div class="relative group">
                                                    <div class="absolute inset-0 rounded-xl blur-2xl opacity-40 group-hover:opacity-60 transition-opacity"
                                                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                                    </div>
                                                    <div class="relative w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"
                                                        style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.15) 100%);">
                                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                                        Despesas</p>
                                                    <p class="text-lg font-black text-red-600 dark:text-red-400">
                                                        R$ {{ number_format($catExpense, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Divider -->
                                            <div
                                                class="h-16 w-px bg-gradient-to-b from-transparent via-gray-300 dark:via-gray-600 to-transparent">
                                            </div>

                                            <!-- Saldo -->
                                            <div class="flex items-center gap-3">
                                                <div class="relative group">
                                                    <div class="absolute inset-0 rounded-xl blur-2xl opacity-40 group-hover:opacity-60 transition-opacity"
                                                        style="background: linear-gradient(135deg, {{ $catBalance >= 0 ? '#3b82f6 0%, #6366f1' : '#f97316 0%, #ea580c' }} 100%);">
                                                    </div>
                                                    <div class="relative w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"
                                                        style="background: linear-gradient(135deg, {{ $catBalance >= 0 ? 'rgba(59, 130, 246, 0.2) 0%, rgba(99, 102, 241, 0.15)' : 'rgba(249, 115, 22, 0.2) 0%, rgba(234, 88, 12, 0.15)' }} 100%);">
                                                        <svg class="w-5 h-5 {{ $catBalance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                                        Saldo</p>
                                                    <p
                                                        class="text-lg font-black {{ $catBalance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                                        R$ {{ number_format($catBalance, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Divider -->
                                            <div
                                                class="h-16 w-px bg-gradient-to-b from-transparent via-gray-300 dark:via-gray-600 to-transparent">
                                            </div>

                                            <!-- Direita: Botão Toggle -->
                                            <button type="button"
                                                class="category-toggle-btn flex items-center gap-3 px-4 py-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent"
                                                data-category-id="{{ $index }}" data-expanded="true"
                                                style="--cat-color: {{ $catColor }};"
                                                aria-label="Alternar visibilidade da categoria {{ $catName }}">

                                                <span
                                                    class="text-xs font-semibold text-gray-700 dark:text-gray-300 toggle-label">Ocultar</span>

                                                <!-- Toggle Switch -->
                                                <div class="relative w-11 h-6 rounded-full transition-colors duration-300 toggle-switch"
                                                    style="background: linear-gradient(135deg, {{ $catColor }} 0%, {{ $catColor }}CC 100%); --cat-color: {{ $catColor }};">
                                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 toggle-thumb"
                                                        style="transform: translateX(20px);"></div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Conteúdo (Grid de transações) -->
                                    <div
                                        class="group-transactions-wrapper overflow-hidden transition-all duration-500">
                                        <div class="px-6 pb-6">
                                            <div
                                                class="group-transactions grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 ultrawind:grid-cols-6 gap-5">
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
                                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                                <!-- Descrição -->
                                                                <div class="flex-1 min-w-0">
                                                                    <h4
                                                                        class="text-base font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 transition-all duration-300">
                                                                        {{ $desc }}
                                                                    </h4>
                                                                    <div
                                                                        class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                                        <svg class="w-3.5 h-3.5" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                            </path>
                                                                        </svg>
                                                                        {{ $transactionDate }}
                                                                    </div>
                                                                </div>

                                                                <!-- Badge de tipo (receita/despesa) -->
                                                                <span
                                                                    class="px-2.5 py-1 text-xs font-bold rounded-lg shadow-md {{ $typeId == 1 ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white' : 'bg-gradient-to-br from-red-500 to-rose-600 text-white' }}">
                                                                    <i
                                                                        class="fas {{ $typeId == 1 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                                                    {{ $typeId == 1 ? 'Receita' : 'Despesa' }}
                                                                </span>
                                                            </div>

                                                            <!-- Valor principal -->
                                                            <div
                                                                class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                                                                <p
                                                                    class="text-2xl font-black {{ $typeId == 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
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
                                        Não há transações registradas para o período selecionado. Comece adicionando uma
                                        nova transação!
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
        <div x-data="{ modalOpen: true }" x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[99999] overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md">
            </div>

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                    x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                    class="relative w-full max-w-lg bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                    <!-- Efeitos visuais -->
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                    <div
                        class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl">
                    </div>

                    <!-- Content -->
                    <div class="relative z-10 p-8">
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl mb-4 shadow-2xl">
                                <i class="fas fa-trash-alt text-white text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Excluir Transação?</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                Esta ação é permanente e não pode ser desfeita.
                            </p>
                            @if ($deletingTransaction)
                                <p
                                    class="text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/50 rounded-xl p-3 mb-6">
                                    <strong>#{{ $deletingTransaction->id }}</strong> —
                                    {{ $deletingTransaction->description ?? 'Sem descrição' }}
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button wire:click="$set('showDeleteModal', false)"
                                class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">
                                Cancelar
                            </button>
                            <button wire:click="deleteTransaction"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Excluir
                            </button>
                        </div>
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

        .category-group.expanded .group-transactions-wrapper {
            max-height: 10000px;
            opacity: 1;
        }
    </style>
</div>

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            let cashbookPieChart = null; // Variável para manter a instância do gráfico

            const initOrUpdateCashbookPieChart = (data) => {
                const chartEl = document.querySelector("#apex-pie-cashbook");
                if (!chartEl) return;

                // Se uma instância do gráfico existir, destrua-a antes de criar uma nova
                if (cashbookPieChart) {
                    cashbookPieChart.destroy();
                    cashbookPieChart = null;
                }

                // Se não houver dados, limpe a área do gráfico e não renderize um novo
                if (!data || data.length === 0) {
                    chartEl.innerHTML = `<div class="flex items-center justify-center h-full text-slate-500 p-4">Sem dados para o gráfico.</div>`;
                    return;
                }

                const series = data.map(item => item.value);
                const labels = data.map(item => item.label);
                const colors = data.map(item => item.color);

                const options = {
                    series: series,
                    labels: labels,
                    colors: colors,
                    chart: {
                        type: 'donut',
                        height: 240,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function (w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                                        }
                                    }
                                }
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                // Crie uma nova instância do gráfico e renderize-a
                cashbookPieChart = new ApexCharts(chartEl, options);
                cashbookPieChart.render();
            };

            // Inicialize o gráfico com o primeiro lote de dados passados do servidor
            initOrUpdateCashbookPieChart(@json($categoriesChartData));

            // Ouça o evento de atualização do componente Livewire
            Livewire.on('cashbook-chart-updated', event => {
                const eventData = Array.isArray(event) ? event[0].data : event.data;
                initOrUpdateCashbookPieChart(eventData);
            });

            // Sistema de notificação
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-[100000] px-6 py-3 rounded-xl text-white font-bold shadow-2xl transform transition-all duration-300 ${
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

            Livewire.on('transaction-deleted', () => {
                showNotification('✅ Transação excluída com sucesso!', 'success');
            });
        });

        // Funções globais para expandir/recolher todas (DEVE estar no escopo global)
        function expandAllCategories() {
            document.querySelectorAll('.category-group').forEach(group => {
                const btn = group.querySelector('.category-toggle-btn');
                if (!btn) return;

                const wrapper = group.querySelector('.group-transactions-wrapper');
                const toggleSwitch = btn.querySelector('.toggle-switch');
                const toggleThumb = btn.querySelector('.toggle-thumb');
                const toggleLabel = btn.querySelector('.toggle-label');
                const catColor = btn.style.getPropertyValue('--cat-color');

                group.classList.add('expanded');
                setTimeout(() => {
                    if (wrapper) wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
                }, 50);

                if (toggleThumb) toggleThumb.style.transform = 'translateX(20px)';
                if (toggleSwitch) toggleSwitch.style.background =
                    `linear-gradient(135deg, ${catColor} 0%, ${catColor}CC 100%)`;
                if (toggleLabel) toggleLabel.textContent = 'Ocultar';
                btn.dataset.expanded = 'true';
            });
        }

        function collapseAllCategories() {
            document.querySelectorAll('.category-group').forEach(group => {
                const btn = group.querySelector('.category-toggle-btn');
                if (!btn) return;

                const toggleSwitch = btn.querySelector('.toggle-switch');
                const toggleThumb = btn.querySelector('.toggle-thumb');
                const toggleLabel = btn.querySelector('.toggle-label');

                group.classList.remove('expanded');
                if (toggleThumb) toggleThumb.style.transform = 'translateX(0)';
                if (toggleSwitch) toggleSwitch.style.background = '#cbd5e0';
                if (toggleLabel) toggleLabel.textContent = 'Mostrar';
                btn.dataset.expanded = 'false';
            });
        }

        // Category toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar categorias expandidas
            setTimeout(() => {
                document.querySelectorAll('.category-group.expanded').forEach(group => {
                    const wrapper = group.querySelector('.group-transactions-wrapper');
                    if (wrapper) {
                        wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
                        wrapper.style.opacity = '1';
                    }
                });
            }, 100);

            // Toggle de categorias
            document.querySelectorAll('.category-toggle-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const categoryGroup = this.closest('.category-group');
                    const wrapper = categoryGroup.querySelector('.group-transactions-wrapper');
                    const isExpanded = this.dataset.expanded === 'true';
                    const toggleSwitch = this.querySelector('.toggle-switch');
                    const toggleThumb = this.querySelector('.toggle-thumb');
                    const toggleLabel = this.querySelector('.toggle-label');
                    const catColor = this.style.getPropertyValue('--cat-color');

                    if (isExpanded) {
                        // Colapsar
                        categoryGroup.classList.remove('expanded');
                        toggleThumb.style.transform = 'translateX(0)';
                        toggleSwitch.style.background = '#cbd5e0';
                        toggleLabel.textContent = 'Mostrar';
                        this.dataset.expanded = 'false';
                    } else {
                        // Expandir
                        categoryGroup.classList.add('expanded');
                        setTimeout(() => {
                            wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
                        }, 50);
                        toggleThumb.style.transform = 'translateX(20px)';
                        toggleSwitch.style.background =
                            `linear-gradient(135deg, ${catColor} 0%, ${catColor}CC 100%)`;
                        toggleLabel.textContent = 'Ocultar';
                        this.dataset.expanded = 'true';
                    }
                });
            });
        });
    </script>
@endpush
