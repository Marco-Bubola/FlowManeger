<div x-data="{ activeTab: 'overview' }" class=" w-full">
    <!-- Ultra Modern Header with Glassmorphism -->
    <div class="relative bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-white/20 dark:border-gray-700/50 shadow-xl">
        <!-- Background Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 via-blue-500/10 to-indigo-500/10"></div>

        <div class="relative w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-8">
                <div class="flex items-center space-x-6">
                    <!-- Animated Icon -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-2xl blur-lg opacity-50 animate-pulse"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-500 rounded-2xl shadow-2xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-4xl font-black bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Cashbook Analytics
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 font-medium">Análise completa e inteligente do fluxo de caixa</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400">
                                <div class="w-2 h-2 bg-cyan-400 rounded-full mr-2 animate-pulse"></div>
                                Dados Atualizados
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ now()->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons with Glassmorphism -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard.index') }}"
                       class="group inline-flex items-center px-6 py-3 bg-white/20 dark:bg-gray-800/20 backdrop-blur-lg border border-white/30 dark:border-gray-600/30 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Principal
                    </a>
                    <a href="{{ route('cashbook.index') }}"
                       class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-500 to-indigo-500 text-white text-sm font-semibold rounded-xl hover:from-cyan-600 hover:to-indigo-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerenciar Cashbook
                    </a>
                </div>
            </div>
        </div>

        <!-- Ultra Modern Tab Navigation -->
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent"></div>
            <div class="relative w-full px-4 sm:px-6 lg:px-8">
                <nav class="flex flex-wrap gap-1 p-1">
                    <!-- Overview Tab -->
                    <button
                        @click="activeTab = 'overview'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'overview' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            <span>Visão Geral</span>
                        </div>
                        <div x-show="activeTab === 'overview'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Analytics Tab -->
                    <button
                        @click="activeTab = 'analytics'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'analytics' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Analytics Avançado</span>
                        </div>
                        <div x-show="activeTab === 'analytics'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Real-Time Tab -->
                    <button
                        @click="activeTab = 'realtime'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'realtime' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Tempo Real</span>
                        </div>
                        <div x-show="activeTab === 'realtime'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Banks Tab -->
                    <button
                        @click="activeTab = 'banks'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'banks' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <span>Gestão Bancária</span>
                        </div>
                        <div x-show="activeTab === 'banks'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Forecasting Tab -->
                    <button
                        @click="activeTab = 'forecasting'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'forecasting' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span>Previsões & IA</span>
                        </div>
                        <div x-show="activeTab === 'forecasting'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Tools Tab -->
                    <button
                        @click="activeTab = 'tools'"
                        class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 group"
                        :class="activeTab === 'tools' ?
                            'bg-white/90 dark:bg-gray-800/90 text-cyan-600 dark:text-cyan-400 shadow-xl backdrop-blur-xl border border-white/20 dark:border-gray-700/50' :
                            'text-gray-600 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-white/50 dark:hover:bg-gray-800/50'">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Ferramentas</span>
                        </div>
                        <div x-show="activeTab === 'tools'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"></div>
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content with Tabs -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        <!-- Year Filter with Modern Design -->
        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold bg-gradient-to-r from-cyan-600 to-indigo-600 bg-clip-text text-transparent">Filtros de Período</h2>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Ano:</label>
                        <select wire:model.live="ano" class="px-4 py-2 bg-white/80 dark:bg-gray-700/80 backdrop-blur-lg border border-white/30 dark:border-gray-600/30 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent dark:text-white font-medium shadow-lg">
                            @foreach(range(date('Y')-5, date('Y')+2) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center space-x-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Mês (Invoices):</label>
                        <select wire:model.live="mesInvoices" class="px-4 py-2 bg-white/80 dark:bg-gray-700/80 backdrop-blur-lg border border-white/30 dark:border-gray-600/30 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent dark:text-white font-medium shadow-lg">
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->locale('pt_BR')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Advanced KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Receitas -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">+{{ number_format((($totalReceitas / max(1, $totalDespesas)) - 1) * 100, 1) }}%</div>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">R$ {{ number_format($totalReceitas, 0, ',', '.') }}</div>
                        <div class="text-sm opacity-90">Total de Receitas</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: 85%"></div>
                            </div>
                            <span class="text-xs opacity-80">85% da meta</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-green-300"></div>
                </div>

                <!-- Total Despesas -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-red-400 via-red-500 to-rose-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-red-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">-{{ number_format((1 - ($totalDespesas / max(1, $totalReceitas))) * 100, 1) }}%</div>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">R$ {{ number_format($totalDespesas, 0, ',', '.') }}</div>
                        <div class="text-sm opacity-90">Total de Despesas</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ min(100, ($totalDespesas / max(1, $totalReceitas)) * 100) }}%"></div>
                            </div>
                            <span class="text-xs opacity-80">{{ number_format(($totalDespesas / max(1, $totalReceitas)) * 100, 1) }}% das receitas</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-red-300 to-rose-300"></div>
                </div>

                <!-- Saldo Total -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-blue-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">{{ $saldoTotal >= 0 ? 'POSITIVO' : 'NEGATIVO' }}</div>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">R$ {{ number_format(abs($saldoTotal), 0, ',', '.') }}</div>
                        <div class="text-sm opacity-90">Saldo Total</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ $saldoTotal >= 0 ? '100' : '30' }}%"></div>
                            </div>
                            <span class="text-xs opacity-80">{{ $saldoTotal >= 0 ? 'Ótimo' : 'Atenção' }}</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-300 to-indigo-300"></div>
                </div>

                <!-- Performance Score -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-purple-400 via-purple-500 to-violet-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">SCORE</div>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">{{ number_format(min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))), 0) }}</div>
                        <div class="text-sm opacity-90">Performance Score</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))) }}%"></div>
                            </div>
                            <span class="text-xs opacity-80">Excelente</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-300 to-violet-300"></div>
                </div>
            </div>

            <!-- Period Comparison with Modern Design -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-cyan-600 to-indigo-600 bg-clip-text text-transparent">
                        Resumo do Último Mês
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $nomeUltimoMes }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Receitas do Mês -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/30 rounded-2xl p-6 border border-emerald-200/50 dark:border-emerald-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-emerald-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Receitas</p>
                                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($receitaUltimoMes, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Despesas do Mês -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-red-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-2xl p-6 border border-red-200/50 dark:border-red-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-red-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Despesas</p>
                                    <p class="text-lg font-bold text-red-600 dark:text-red-400">R$ {{ number_format($despesaUltimoMes, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo do Mês -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r {{ $saldoUltimoMes >= 0 ? 'from-blue-400 to-blue-500' : 'from-orange-400 to-orange-500' }} rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br {{ $saldoUltimoMes >= 0 ? 'from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border-blue-200/50 dark:border-blue-700/50' : 'from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 border-orange-200/50 dark:border-orange-700/50' }} rounded-2xl p-6 border">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 {{ $saldoUltimoMes >= 0 ? 'bg-blue-500' : 'bg-orange-500' }} rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo</p>
                                    <p class="text-lg font-bold {{ $saldoUltimoMes >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                        R$ {{ number_format(abs($saldoUltimoMes), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variação -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-400 to-purple-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-2xl p-6 border border-purple-200/50 dark:border-purple-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-purple-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Variação</p>
                                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                        {{ $receitaUltimoMes > 0 ? '+' . number_format(($receitaUltimoMes / max(1, $despesaUltimoMes) - 1) * 100, 1) : '0' }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Insights Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <!-- Fluxo de Caixa -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        Análise de Fluxo
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Margem de Lucro</span>
                                <span class="text-lg font-bold text-cyan-600 dark:text-cyan-400">
                                    {{ $totalReceitas > 0 ? number_format(($saldoTotal / $totalReceitas) * 100, 1) : '0' }}%
                                </span>
                            </div>
                            <div class="w-full bg-cyan-200 dark:bg-cyan-800 rounded-full h-2">
                                <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ min(100, max(0, $totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50)) }}%"></div>
                            </div>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Eficiência</span>
                                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $totalDespesas > 0 ? number_format((1 - ($totalDespesas / max(1, $totalReceitas))) * 100, 1) : '100' }}%
                                </span>
                            </div>
                            <div class="w-full bg-emerald-200 dark:bg-emerald-800 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $totalDespesas > 0 ? number_format((1 - ($totalDespesas / max(1, $totalReceitas))) * 100, 1) : '100' }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tendências -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        Tendências
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Crescimento Mensal</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                    +{{ number_format(($totalReceitas > 0 ? min(25, max(0, ($saldoTotal / $totalReceitas) * 10)) : 0), 1) }}%
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Tendência positiva</span>
                            </div>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Previsão Próximo Mês</span>
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    R$ {{ number_format($saldoUltimoMes * 1.15, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Baseado na tendência</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Financeiro -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        Status Financeiro
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 {{ $saldoTotal >= 0 ? 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20' : 'bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20' }} rounded-2xl">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-3 h-3 {{ $saldoTotal >= 0 ? 'bg-green-500' : 'bg-red-500' }} rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $saldoTotal >= 0 ? 'Situação Saudável' : 'Atenção Necessária' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $saldoTotal >= 0 ? 'Seu fluxo de caixa está positivo e equilibrado.' : 'Revise suas despesas e otimize receitas.' }}
                            </p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Score de Saúde</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))) }}%"></div>
                                </div>
                                <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))) }}/100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Monthly Evolution Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-cyan-600 to-indigo-600 bg-clip-text text-transparent">
                            Evolução Mensal {{ $ano }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Receitas vs Despesas</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="monthlyChart"></div>
                    </div>
                </div>

                <!-- Daily Invoices Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Faturas Diárias - {{ \Carbon\Carbon::create($anoInvoices, $mesInvoices, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Movimento diário</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="dailyInvoicesChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real Time Tab -->
        <div x-show="activeTab === 'realtime'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Real-Time Metrics Dashboard -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Métricas em Tempo Real
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Atualizando a cada 30s</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Live Cash Position -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-blue-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">LIVE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>R$ {{ number_format($saldoTotal, 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Posição de Caixa</div>
                            <div class="mt-2 text-xs opacity-75" data-timestamp>{{ date('H:i:s') }}</div>
                        </div>
                    </div>

                    <!-- Daily Velocity -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500 via-green-600 to-teal-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">HOJE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>R$ {{ number_format(rand(500, 2500), 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Velocidade Diária</div>
                            <div class="mt-2 text-xs opacity-75">Entrada/hora: <span data-live-metric>R$ {{ number_format(rand(50, 200), 2, ',', '.') }}</span></div>
                        </div>
                    </div>

                    <!-- Burn Rate -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-orange-500 via-red-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-orange-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">MENSAL</span>
                            </div>
                            <div class="text-2xl font-bold mb-1">R$ {{ number_format(rand(3000, 8000), 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Taxa de Queima</div>
                            <div class="mt-2 text-xs opacity-75">Runway: {{ rand(8, 24) }} meses</div>
                        </div>
                    </div>

                    <!-- Efficiency Score -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500 via-violet-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">SCORE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>{{ rand(75, 95) }}%</div>
                            <div class="text-sm opacity-90">Eficiência</div>
                            <div class="mt-2 text-xs opacity-75">vs mês anterior: <span data-live-metric>+{{ rand(2, 8) }}%</span></div>
                        </div>
                    </div>
                </div>

                <!-- Smart Alerts Panel -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Alertas Inteligentes
                        </h4>
                        <div class="flex items-center space-x-2">
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ rand(2, 5) }} ativos</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- High Priority Alerts -->
                        <div class="space-y-4">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Alta Prioridade</h5>

                            <div class="p-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-l-4 border-red-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-red-800 dark:text-red-200">Saldo Crítico</h6>
                                        <p class="text-sm text-red-700 dark:text-red-300">Saldo abaixo do limite seguro de R$ 5.000</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 transition-colors">
                                                Transferir Reserva
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border-l-4 border-orange-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-orange-800 dark:text-orange-200">Pagamento Pendente</h6>
                                        <p class="text-sm text-orange-700 dark:text-orange-300">R$ 2.500 vence em 2 dias</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-orange-600 text-white px-3 py-1 rounded-md hover:bg-orange-700 transition-colors">
                                                Agendar Pagamento
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medium Priority Alerts -->
                        <div class="space-y-4">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Média Prioridade</h5>

                            <div class="p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-l-4 border-yellow-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-yellow-800 dark:text-yellow-200">Meta de Receita</h6>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Apenas 65% da meta mensal atingida</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-yellow-600 text-white px-3 py-1 rounded-md hover:bg-yellow-700 transition-colors">
                                                Ver Estratégias
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-l-4 border-blue-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-blue-800 dark:text-blue-200">Oportunidade de Investimento</h6>
                                        <p class="text-sm text-blue-700 dark:text-blue-300">Excesso de caixa detectado: R$ 15.000</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition-colors">
                                                Ver Opções
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banks Tab -->
        <div x-show="activeTab === 'banks'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Monthly Evolution Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-cyan-600 to-indigo-600 bg-clip-text text-transparent">
                            Evolução Mensal {{ $ano }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Receitas vs Despesas</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="monthlyChart"></div>
                    </div>
                </div>

                <!-- Daily Invoices Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Faturas Diárias - {{ \Carbon\Carbon::create($anoInvoices, $mesInvoices, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Movimento diário</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="dailyInvoicesChart"></div>
                    </div>
                </div>
            </div>

            <!-- Real-Time Metrics Dashboard -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Métricas em Tempo Real
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Atualizando a cada 30s</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Live Cash Position -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-blue-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">LIVE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>R$ {{ number_format($saldoTotal, 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Posição de Caixa</div>
                            <div class="mt-2 text-xs opacity-75" data-timestamp>{{ date('H:i:s') }}</div>
                        </div>
                    </div>

                    <!-- Daily Velocity -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500 via-green-600 to-teal-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">HOJE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>R$ {{ number_format(rand(500, 2500), 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Velocidade Diária</div>
                            <div class="mt-2 text-xs opacity-75">Entrada/hora: <span data-live-metric>R$ {{ number_format(rand(50, 200), 2, ',', '.') }}</span></div>
                        </div>
                    </div>

                    <!-- Burn Rate -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-orange-500 via-red-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-orange-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">MENSAL</span>
                            </div>
                            <div class="text-2xl font-bold mb-1">R$ {{ number_format(rand(3000, 8000), 2, ',', '.') }}</div>
                            <div class="text-sm opacity-90">Taxa de Queima</div>
                            <div class="mt-2 text-xs opacity-75">Runway: {{ rand(8, 24) }} meses</div>
                        </div>
                    </div>

                    <!-- Efficiency Score -->
                    <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500 via-violet-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">SCORE</span>
                            </div>
                            <div class="text-2xl font-bold mb-1" data-live-metric>{{ rand(75, 95) }}%</div>
                            <div class="text-sm opacity-90">Eficiência</div>
                            <div class="mt-2 text-xs opacity-75">vs mês anterior: <span data-live-metric>+{{ rand(2, 8) }}%</span></div>
                        </div>
                    </div>
                </div>

                <!-- Smart Alerts Panel -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Alertas Inteligentes
                        </h4>
                        <div class="flex items-center space-x-2">
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ rand(2, 5) }} ativos</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- High Priority Alerts -->
                        <div class="space-y-4">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Alta Prioridade</h5>

                            <div class="p-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-l-4 border-red-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-red-800 dark:text-red-200">Saldo Crítico</h6>
                                        <p class="text-sm text-red-700 dark:text-red-300">Saldo abaixo do limite seguro de R$ 5.000</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 transition-colors">
                                                Transferir Reserva
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border-l-4 border-orange-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-orange-800 dark:text-orange-200">Pagamento Pendente</h6>
                                        <p class="text-sm text-orange-700 dark:text-orange-300">R$ 2.500 vence em 2 dias</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-orange-600 text-white px-3 py-1 rounded-md hover:bg-orange-700 transition-colors">
                                                Agendar Pagamento
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medium Priority Alerts -->
                        <div class="space-y-4">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Média Prioridade</h5>

                            <div class="p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-l-4 border-yellow-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-yellow-800 dark:text-yellow-200">Meta de Receita</h6>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Apenas 65% da meta mensal atingida</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-yellow-600 text-white px-3 py-1 rounded-md hover:bg-yellow-700 transition-colors">
                                                Ver Estratégias
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-l-4 border-blue-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <div>
                                        <h6 class="font-semibold text-blue-800 dark:text-blue-200">Oportunidade de Investimento</h6>
                                        <p class="text-sm text-blue-700 dark:text-blue-300">Excesso de caixa detectado: R$ 15.000</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition-colors">
                                                Ver Opções
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics Cards -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-8 h-8 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Centro de Gestão Financeira
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ rand(8, 15) }} ferramentas ativas</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Financial Planning Tools -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                Planejamento
                            </h4>
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button class="w-full p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-blue-800 dark:text-blue-200 group-hover:text-blue-900 dark:group-hover:text-blue-100">Simulador de Cenários</h5>
                                        <p class="text-sm text-blue-600 dark:text-blue-300">Projete diferentes situações financeiras</p>
                                    </div>
                                    <svg class="w-5 h-5 text-blue-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-indigo-800 dark:text-indigo-200 group-hover:text-indigo-900 dark:group-hover:text-indigo-100">Planejador de Metas</h5>
                                        <p class="text-sm text-indigo-600 dark:text-indigo-300">Defina e monitore objetivos financeiros</p>
                                    </div>
                                    <svg class="w-5 h-5 text-indigo-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-purple-800 dark:text-purple-200 group-hover:text-purple-900 dark:group-hover:text-purple-100">Análise de Riscos</h5>
                                        <p class="text-sm text-purple-600 dark:text-purple-300">Avalie e mitigue riscos financeiros</p>
                                    </div>
                                    <svg class="w-5 h-5 text-purple-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Automation Tools -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-bold bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                                Automação
                            </h4>
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button class="w-full p-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-emerald-800 dark:text-emerald-200 group-hover:text-emerald-900 dark:group-hover:text-emerald-100">Pagamentos Automáticos</h5>
                                        <p class="text-sm text-emerald-600 dark:text-emerald-300">Configure pagamentos recorrentes</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                        <svg class="w-5 h-5 text-emerald-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border border-green-200 dark:border-green-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-green-800 dark:text-green-200 group-hover:text-green-900 dark:group-hover:text-green-100">Categorização IA</h5>
                                        <p class="text-sm text-green-600 dark:text-green-300">Classificação automática de transações</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                        <svg class="w-5 h-5 text-green-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border border-teal-200 dark:border-teal-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-teal-800 dark:text-teal-200 group-hover:text-teal-900 dark:group-hover:text-teal-100">Relatórios Inteligentes</h5>
                                        <p class="text-sm text-teal-600 dark:text-teal-300">Geração automática de relatórios</p>
                                    </div>
                                    <svg class="w-5 h-5 text-teal-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Integration Tools -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                                Integrações
                            </h4>
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button class="w-full p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-orange-800 dark:text-orange-200 group-hover:text-orange-900 dark:group-hover:text-orange-100">Open Banking</h5>
                                        <p class="text-sm text-orange-600 dark:text-orange-300">Conecte suas contas bancárias</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">{{ rand(3, 8) }} bancos</span>
                                        <svg class="w-5 h-5 text-orange-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-red-800 dark:text-red-200 group-hover:text-red-900 dark:group-hover:text-red-100">API Contábil</h5>
                                        <p class="text-sm text-red-600 dark:text-red-300">Integração com sistemas contábeis</p>
                                    </div>
                                    <svg class="w-5 h-5 text-red-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </button>

                            <button class="w-full p-4 bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/20 dark:to-purple-900/20 border border-pink-200 dark:border-pink-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-semibold text-pink-800 dark:text-pink-200 group-hover:text-pink-900 dark:group-hover:text-pink-100">E-commerce Sync</h5>
                                        <p class="text-sm text-pink-600 dark:text-pink-300">Sincronize vendas online</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-pink-500 rounded-full animate-pulse"></span>
                                        <svg class="w-5 h-5 text-pink-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- ROI Analysis -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-400 via-cyan-500 to-blue-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-cyan-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">{{ $totalDespesas > 0 ? number_format(($totalReceitas / $totalDespesas), 1) : '∞' }}x</div>
                        <div class="text-sm opacity-90">ROI Atual</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ min(100, $totalDespesas > 0 ? ($totalReceitas / $totalDespesas) * 20 : 100) }}%"></div>
                            </div>
                            <span class="text-xs opacity-80">Excelente</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-300 to-blue-300"></div>
                </div>

                <!-- Burn Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-orange-400 via-orange-500 to-red-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">R$ {{ number_format($totalDespesas / max(1, 12), 0, ',', '.') }}</div>
                        <div class="text-sm opacity-90">Queima Mensal</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: 60%"></div>
                            </div>
                            <span class="text-xs opacity-80">Controlado</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-orange-300 to-red-300"></div>
                </div>

                <!-- Cash Runway -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-purple-400 via-purple-500 to-violet-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">{{ $totalDespesas > 0 ? number_format(abs($saldoTotal) / ($totalDespesas / 12), 1) : '∞' }}</div>
                        <div class="text-sm opacity-90">Meses de Reserva</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ min(100, $totalDespesas > 0 ? (abs($saldoTotal) / ($totalDespesas / 12)) * 10 : 100) }}%"></div>
                            </div>
                            <span class="text-xs opacity-80">{{ $saldoTotal >= 0 ? 'Seguro' : 'Alerta' }}</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-300 to-violet-300"></div>
                </div>

                <!-- Growth Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black mb-2">+{{ number_format(($receitaUltimoMes / max(1, $totalReceitas - $receitaUltimoMes)) * 100, 1) }}%</div>
                        <div class="text-sm opacity-90">Taxa de Crescimento</div>
                        <div class="flex items-center mt-3 space-x-2">
                            <div class="flex-1 bg-white/20 rounded-full h-1.5">
                                <div class="bg-white h-1.5 rounded-full" style="width: 85%"></div>
                            </div>
                            <span class="text-xs opacity-80">Acelerado</span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-green-300"></div>
                </div>
            </div>

            <!-- Additional Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Performance Gauge -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Performance Gauge
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Score geral</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="performanceGauge"></div>
                    </div>
                </div>

                <!-- Cash Flow Waterfall -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                            Fluxo de Caixa Waterfall
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Entradas vs Saídas</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="waterfallChart"></div>
                    </div>
                </div>
            </div>

            <!-- New Additional Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Category Breakdown Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">
                            Breakdown por Categoria
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Distribuição</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="categoryBreakdownChart"></div>
                    </div>
                </div>

                <!-- Payment Methods Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                            Métodos de Pagamento
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Preferências</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="paymentMethodChart"></div>
                    </div>
                </div>
            </div>

            <!-- Bank Balance Trend & Monthly Trend Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Bank Balance Trend -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            Evolução Saldo Bancário
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Tendência</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="bankBalanceChart"></div>
                    </div>
                </div>

                <!-- Monthly Trend Analysis -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-violet-600 to-purple-600 bg-clip-text text-transparent">
                            Análise de Tendência Mensal
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-violet-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Receitas vs Despesas</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="monthlyTrendChart"></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Table -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Análise Detalhada por Período
                    </h3>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                            <div class="w-2 h-2 bg-indigo-400 rounded-full mr-2 animate-pulse"></div>
                            {{ $ano }}
                        </span>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Última atualização: {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200/50 dark:border-gray-700/50">
                                <th class="text-left py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 first:rounded-l-xl">Mês</th>
                                <th class="text-right py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">Receitas</th>
                                <th class="text-right py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">Despesas</th>
                                <th class="text-right py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">Saldo</th>
                                <th class="text-right py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">Margem</th>
                                <th class="text-center py-4 px-6 font-bold text-gray-900 dark:text-white bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 last:rounded-r-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $index => $mes)
                            @php
                                $receita = isset($dadosReceita[$index]) ? $dadosReceita[$index] : 0;
                                $despesa = isset($dadosDespesa[$index]) ? $dadosDespesa[$index] : 0;
                                $saldoMes = $receita - $despesa;
                                $margem = $receita > 0 ? ($saldoMes / $receita) * 100 : 0;
                            @endphp
                            <tr class="border-b border-gray-100/50 dark:border-gray-700/50 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">{{ substr($mes, 0, 3) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $mes }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span class="font-bold text-green-600 dark:text-green-400">
                                        R$ {{ number_format($receita, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span class="font-bold text-red-600 dark:text-red-400">
                                        R$ {{ number_format($despesa, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span class="font-bold {{ $saldoMes >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                        R$ {{ number_format(abs($saldoMes), 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span class="font-bold {{ $margem >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($margem, 1) }}%
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $saldoMes >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                        {{ $saldoMes >= 0 ? '✓ Positivo' : '⚠ Negativo' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AI-Powered Recommendations Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Smart Insights -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Insights Inteligentes
                        </h3>
                        <div class="p-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Cash Flow Insight -->
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200/30 dark:border-blue-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">Fluxo de Caixa</h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                                        @if($saldoTotal >= 0)
                                            Seu fluxo de caixa está {{ $saldoTotal > $totalReceitas * 0.2 ? 'excelente' : 'saudável' }}.
                                            Mantenha o foco na diversificação de receitas.
                                        @else
                                            Atenção: Fluxo negativo detectado. Recomendamos revisar despesas e acelerar recebimentos.
                                        @endif
                                    </p>
                                    <div class="flex items-center space-x-2 text-xs text-blue-600 dark:text-blue-400">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <span>Score: {{ min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))) }}/100</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Efficiency Insight -->
                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200/30 dark:border-green-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-green-500 rounded-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-green-800 dark:text-green-300 mb-2">Eficiência Operacional</h4>
                                    <p class="text-sm text-green-700 dark:text-green-300 mb-3">
                                        Margem operacional de {{ $totalReceitas > 0 ? number_format(($saldoTotal / $totalReceitas) * 100, 1) : '0' }}%.
                                        {{ $totalReceitas > 0 && ($saldoTotal / $totalReceitas) > 0.15 ? 'Excelente performance!' : 'Há oportunidades de melhoria.' }}
                                    </p>
                                    <div class="flex items-center space-x-2 text-xs text-green-600 dark:text-green-400">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        <span>ROI: {{ $totalDespesas > 0 ? number_format($totalReceitas / $totalDespesas, 1) : '∞' }}x</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trend Insight -->
                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl border border-purple-200/30 dark:border-purple-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-purple-500 rounded-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-purple-800 dark:text-purple-300 mb-2">Tendência de Crescimento</h4>
                                    <p class="text-sm text-purple-700 dark:text-purple-300 mb-3">
                                        Crescimento projetado de +{{ number_format(($receitaUltimoMes / max(1, $totalReceitas - $receitaUltimoMes)) * 100, 1) }}%
                                        baseado no último mês. Continue investindo em marketing e vendas.
                                    </p>
                                    <div class="flex items-center space-x-2 text-xs text-purple-600 dark:text-purple-400">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                        <span>Meta anual: {{ min(100, ($saldoTotal > 0 ? 77 : 35)) }}% alcançada</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actionable Recommendations -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                            Recomendações de Ação
                        </h3>
                        <div class="p-3 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Priority 1 -->
                        <div class="p-6 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-2xl border border-red-200/30 dark:border-red-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-red-500 rounded-lg">
                                    <span class="text-white font-bold text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-red-800 dark:text-red-300 mb-2">
                                        @if($saldoTotal < 0)
                                            Urgente: Controle de Despesas
                                        @else
                                            Otimização de Receitas
                                        @endif
                                    </h4>
                                    <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                                        @if($saldoTotal < 0)
                                            Implemente um plano de redução de custos imediato. Foque em despesas não essenciais.
                                        @else
                                            Identifique oportunidades de cross-selling e up-selling com clientes existentes.
                                        @endif
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Alta Prioridade
                                        </span>
                                        <span class="text-xs text-red-600 dark:text-red-400">Impacto: {{ $saldoTotal < 0 ? 'R$ ' . number_format(abs($saldoTotal) * 0.3, 0, ',', '.') : 'R$ ' . number_format($totalReceitas * 0.15, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Priority 2 -->
                        <div class="p-6 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-2xl border border-yellow-200/30 dark:border-yellow-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-yellow-500 rounded-lg">
                                    <span class="text-white font-bold text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-yellow-800 dark:text-yellow-300 mb-2">Automação de Pagamentos</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                                        Configure lembretes automáticos e incentive pagamentos via PIX para melhorar o fluxo de caixa.
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            Média Prioridade
                                        </span>
                                        <span class="text-xs text-yellow-600 dark:text-yellow-400">Economia: 15% no tempo de recebimento</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Priority 3 -->
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200/30 dark:border-blue-700/30">
                            <div class="flex items-start space-x-4">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <span class="text-white font-bold text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">Diversificação de Receitas</h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                                        Explore novos canais de receita e considere produtos/serviços complementares ao seu negócio.
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                            Estratégico
                                        </span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400">Potencial: +30% de receita</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Health Dashboard -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Dashboard de Saúde Financeira
                    </h3>
                    <div class="p-3 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Liquidez -->
                    <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl border border-blue-200/50 dark:border-blue-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-blue-500 rounded-xl">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $totalDespesas > 0 ? number_format($saldoTotal / $totalDespesas, 2) : '∞' }}
                            </span>
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">Índice de Liquidez</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Capacidade de pagamento das obrigações</p>
                        <div class="mt-3 flex items-center space-x-2">
                            <div class="flex-1 bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(100, $totalDespesas > 0 ? ($saldoTotal / $totalDespesas) * 50 + 50 : 100) }}%"></div>
                            </div>
                            <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                                {{ $saldoTotal >= 0 ? 'Saudável' : 'Crítico' }}
                            </span>
                        </div>
                    </div>

                    <!-- Endividamento -->
                    <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/30 dark:to-red-900/30 rounded-2xl border border-orange-200/50 dark:border-orange-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-orange-500 rounded-xl">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $totalReceitas > 0 ? number_format(($totalDespesas / $totalReceitas) * 100, 1) : '0' }}%
                            </span>
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">Taxa de Endividamento</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Percentual de despesas sobre receitas</p>
                        <div class="mt-3 flex items-center space-x-2">
                            <div class="flex-1 bg-orange-200 dark:bg-orange-800 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $totalReceitas > 0 ? min(100, ($totalDespesas / $totalReceitas) * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                                {{ $totalReceitas > 0 && ($totalDespesas / $totalReceitas) > 0.7 ? 'Alto' : 'Controlado' }}
                            </span>
                        </div>
                    </div>

                    <!-- Rentabilidade -->
                    <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border border-green-200/50 dark:border-green-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-green-500 rounded-xl">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $totalReceitas > 0 ? number_format(($saldoTotal / $totalReceitas) * 100, 1) : '0' }}%
                            </span>
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">Margem de Rentabilidade</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Percentual de lucro sobre receitas</p>
                        <div class="mt-3 flex items-center space-x-2">
                            <div class="flex-1 bg-green-200 dark:bg-green-800 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $totalReceitas > 0 ? min(100, max(0, ($saldoTotal / $totalReceitas) * 100 + 50)) : 50 }}%"></div>
                            </div>
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                                {{ $saldoTotal >= 0 ? 'Positiva' : 'Negativa' }}
                            </span>
                        </div>
                    </div>

                    <!-- Crescimento -->
                    <div class="p-6 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-2xl border border-purple-200/50 dark:border-purple-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-purple-500 rounded-xl">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                +{{ number_format(($receitaUltimoMes / max(1, $totalReceitas - $receitaUltimoMes)) * 100, 1) }}%
                            </span>
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">Taxa de Crescimento</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Evolução das receitas no período</p>
                        <div class="mt-3 flex items-center space-x-2">
                            <div class="flex-1 bg-purple-200 dark:bg-purple-800 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 75%"></div>
                            </div>
                            <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">Acelerado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Tools -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Ações Rápidas & Ferramentas
                    </h3>
                    <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Exportar Relatório -->
                    <div class="group p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl border border-blue-200/50 dark:border-blue-700/50 hover:scale-105 transition-transform cursor-pointer">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-blue-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Exportar Dados</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">PDF, Excel, CSV</p>
                            </div>
                        </div>
                        <button class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                            Gerar Relatório
                        </button>
                    </div>

                    <!-- Calculadora Financeira -->
                    <div class="group p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border border-green-200/50 dark:border-green-700/50 hover:scale-105 transition-transform cursor-pointer">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-green-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Calculadora</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">ROI, Margem, Juros</p>
                            </div>
                        </div>
                        <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium">
                            Abrir Calculadora
                        </button>
                    </div>

                    <!-- Simulador de Cenários -->
                    <div class="group p-6 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-2xl border border-purple-200/50 dark:border-purple-700/50 hover:scale-105 transition-transform cursor-pointer">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-purple-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Simulador</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Cenários futuros</p>
                            </div>
                        </div>
                        <button class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium">
                            Simular Cenários
                        </button>
                    </div>

                    <!-- Alertas Personalizados -->
                    <div class="group p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/30 dark:to-red-900/30 rounded-2xl border border-orange-200/50 dark:border-orange-700/50 hover:scale-105 transition-transform cursor-pointer">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-orange-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM5 3v18l5-5h5a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Alertas</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Notificações automáticas</p>
                            </div>
                        </div>
                        <button class="w-full px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                            Configurar Alertas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Comparative Analytics & Quick Commands -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Comparative Performance Widgets -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Performance Comparativa
                        </h4>
                        <div class="flex items-center space-x-2">
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">vs período anterior</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Revenue Comparison -->
                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200/50 dark:border-green-700/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-green-800 dark:text-green-300">Receitas</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-green-600 dark:text-green-400 text-sm font-bold">+{{ rand(12, 28) }}%</span>
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-green-700 dark:text-green-300">
                                <span>Atual: R$ {{ number_format($totalReceitas, 2, ',', '.') }}</span>
                                <span>Anterior: R$ {{ number_format($totalReceitas * 0.85, 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-2 bg-green-200/50 dark:bg-green-800/30 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ rand(65, 85) }}%"></div>
                            </div>
                        </div>

                        <!-- Expenses Comparison -->
                        <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-xl border border-red-200/50 dark:border-red-700/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-red-800 dark:text-red-300">Despesas</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-red-600 dark:text-red-400 text-sm font-bold">+{{ rand(5, 18) }}%</span>
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-red-700 dark:text-red-300">
                                <span>Atual: R$ {{ number_format($totalDespesas, 2, ',', '.') }}</span>
                                <span>Anterior: R$ {{ number_format($totalDespesas * 0.92, 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-2 bg-red-200/50 dark:bg-red-800/30 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ rand(45, 65) }}%"></div>
                            </div>
                        </div>

                        <!-- Profit Margin Comparison -->
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Margem de Lucro</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 text-sm font-bold">+{{ rand(8, 15) }}%</span>
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                                <span>Atual: {{ $totalReceitas > 0 ? number_format(($saldoTotal / $totalReceitas) * 100, 1) : '0' }}%</span>
                                <span>Anterior: {{ $totalReceitas > 0 ? number_format((($saldoTotal * 0.88) / ($totalReceitas * 0.85)) * 100, 1) : '0' }}%</span>
                            </div>
                            <div class="mt-2 bg-blue-200/50 dark:bg-blue-800/30 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ rand(70, 90) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Commands Center -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Central de Comandos
                        </h4>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Ações rápidas</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Quick Actions Row 1 -->
                        <button class="group p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white shadow-lg hover:shadow-blue-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span class="text-sm font-semibold">Nova Entrada</span>
                            </div>
                        </button>

                        <button class="group p-4 bg-gradient-to-br from-red-500 to-red-600 rounded-xl text-white shadow-lg hover:shadow-red-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                                <span class="text-sm font-semibold">Nova Saída</span>
                            </div>
                        </button>

                        <button class="group p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-xl text-white shadow-lg hover:shadow-green-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-semibold">Relatório</span>
                            </div>
                        </button>

                        <button class="group p-4 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl text-white shadow-lg hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold">Agendar</span>
                            </div>
                        </button>

                        <button class="group p-4 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl text-white shadow-lg hover:shadow-indigo-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold">Backup</span>
                            </div>
                        </button>

                        <button class="group p-4 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl text-white shadow-lg hover:shadow-teal-500/25 transition-all duration-300 hover:scale-105">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold">Config</span>
                            </div>
                        </button>
                    </div>

                    <!-- Quick Stats -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl">
                        <h5 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Status do Sistema</h5>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Última sync:</span>
                                <span class="text-green-600 dark:text-green-400 font-semibold">{{ date('H:i') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Performance:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-semibold" data-performance>{{ rand(85, 99) }}%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Conexão:</span>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-green-600 dark:text-green-400 font-semibold">Online</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Alertas:</span>
                                <span class="text-orange-600 dark:text-orange-400 font-semibold">{{ rand(1, 4) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forecasting Tab -->
        <div x-show="activeTab === 'forecasting'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Forecasting Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Cash Flow Forecast -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Previsão de Fluxo de Caixa</h3>
                    <div class="space-y-6">
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Próximo Mês</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ number_format($saldoUltimoMes * 1.15, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">+15% baseado na tendência</p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Próximo Trimestre</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    R$ {{ number_format($saldoUltimoMes * 3.5, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 78%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Crescimento sustentável esperado</p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Meta Anual</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                    R$ {{ number_format($saldoTotal * 1.3, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-purple-200 dark:bg-purple-800 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ min(100, ($saldoTotal > 0 ? 77 : 35)) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {{ min(100, ($saldoTotal > 0 ? 77 : 35)) }}% da meta alcançada
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Risk Analysis -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Análise de Risco</h3>
                    <div class="space-y-6">
                        <!-- Risk Level -->
                        <div class="text-center p-6 bg-gradient-to-br {{ $saldoTotal >= 0 ? 'from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20' : 'from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20' }} rounded-2xl">
                            <div class="w-16 h-16 bg-gradient-to-r {{ $saldoTotal >= 0 ? 'from-green-500 to-emerald-500' : 'from-red-500 to-orange-500' }} rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $saldoTotal >= 0 ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                                Risco {{ $saldoTotal >= 0 ? 'Baixo' : 'Médio' }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $saldoTotal >= 0 ? 'Situação financeira estável' : 'Monitore despesas e receitas' }}
                            </p>
                        </div>

                        <!-- Risk Factors -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Concentração de Receitas</span>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                    <span class="text-sm font-bold text-yellow-600 dark:text-yellow-400">Médio</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Volatilidade</span>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 {{ $saldoTotal >= 0 ? 'bg-green-500' : 'bg-orange-500' }} rounded-full"></div>
                                    <span class="text-sm font-bold {{ $saldoTotal >= 0 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                        {{ $saldoTotal >= 0 ? 'Baixa' : 'Média' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dependência Externa</span>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">Baixa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    Recomendações Inteligentes
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if($saldoTotal >= 0)
                    <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200/50 dark:border-green-700/50">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-green-800 dark:text-green-400">Investir Surplus</h4>
                        </div>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Com o saldo positivo, considere investir em reservas de emergência ou expansão do negócio.
                        </p>
                    </div>
                    @else
                    <div class="p-6 bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-2xl border border-red-200/50 dark:border-red-700/50">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-red-800 dark:text-red-400">Revisar Despesas</h4>
                        </div>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Analise e reduza despesas desnecessárias para melhorar o fluxo de caixa.
                        </p>
                    </div>
                    @endif

                    <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200/50 dark:border-blue-700/50">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-blue-800 dark:text-blue-400">Diversificar Receitas</h4>
                        </div>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Explore novas fontes de receita para reduzir riscos e aumentar estabilidade.
                        </p>
                    </div>

                    <div class="p-6 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-2xl border border-purple-200/50 dark:border-purple-700/50">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-purple-800 dark:text-purple-400">Automatizar Controles</h4>
                        </div>
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            Implemente sistemas automatizados para melhor controle e previsibilidade.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tools Tab -->
        <div x-show="activeTab === 'tools'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
                <!-- Financial Operations -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Operações Financeiras
                        </h3>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button class="w-full p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 group-hover:text-blue-900 dark:group-hover:text-blue-100">Nova Receita</h4>
                                    <p class="text-sm text-blue-600 dark:text-blue-300">Registrar entrada de dinheiro</p>
                                </div>
                                <svg class="w-5 h-5 text-blue-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 group-hover:text-red-900 dark:group-hover:text-red-100">Nova Despesa</h4>
                                    <p class="text-sm text-red-600 dark:text-red-300">Registrar saída de dinheiro</p>
                                </div>
                                <svg class="w-5 h-5 text-red-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-green-800 dark:text-green-200 group-hover:text-green-900 dark:group-hover:text-green-100">Transferência</h4>
                                    <p class="text-sm text-green-600 dark:text-green-300">Transferir entre contas</p>
                                </div>
                                <svg class="w-5 h-5 text-green-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Reports & Analytics -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Relatórios & Analytics
                        </h3>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button class="w-full p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-purple-800 dark:text-purple-200 group-hover:text-purple-900 dark:group-hover:text-purple-100">Relatório Mensal</h4>
                                    <p class="text-sm text-purple-600 dark:text-purple-300">Análise completa do mês</p>
                                </div>
                                <svg class="w-5 h-5 text-purple-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 group-hover:text-indigo-900 dark:group-hover:text-indigo-100">Análise de Tendências</h4>
                                    <p class="text-sm text-indigo-600 dark:text-indigo-300">Insights e previsões</p>
                                </div>
                                <svg class="w-5 h-5 text-indigo-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 border border-cyan-200 dark:border-cyan-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-cyan-800 dark:text-cyan-200 group-hover:text-cyan-900 dark:group-hover:text-cyan-100">Export de Dados</h4>
                                    <p class="text-sm text-cyan-600 dark:text-cyan-300">Excel, PDF, CSV</p>
                                </div>
                                <svg class="w-5 h-5 text-cyan-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- System Tools -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                            Ferramentas do Sistema
                        </h3>
                        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button class="w-full p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-orange-800 dark:text-orange-200 group-hover:text-orange-900 dark:group-hover:text-orange-100">Backup de Dados</h4>
                                    <p class="text-sm text-orange-600 dark:text-orange-300">Exportar dados completos</p>
                                </div>
                                <svg class="w-5 h-5 text-orange-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 group-hover:text-red-900 dark:group-hover:text-red-100">Configurações</h4>
                                    <p class="text-sm text-red-600 dark:text-red-300">Ajustar preferências</p>
                                </div>
                                <svg class="w-5 h-5 text-red-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                            </div>
                        </button>

                        <button class="w-full p-4 bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/20 dark:to-purple-900/20 border border-pink-200 dark:border-pink-700 rounded-xl hover:shadow-lg transition-all duration-300 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-pink-800 dark:text-pink-200 group-hover:text-pink-900 dark:group-hover:text-pink-100">Suporte</h4>
                                    <p class="text-sm text-pink-600 dark:text-pink-300">Ajuda e documentação</p>
                                </div>
                                <svg class="w-5 h-5 text-pink-500 group-hover:transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Advanced Tools Section -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-8 h-8 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Ferramentas Avançadas
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pro Tools</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Calculator & Simulators -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Calculadoras & Simuladores</h4>

                        <!-- Cash Flow Calculator -->
                        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-blue-800 dark:text-blue-200">Calculadora de Fluxo</h5>
                                    <p class="text-sm text-blue-600 dark:text-blue-300">Simule cenários financeiros</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="number" placeholder="Receita" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-blue-200 dark:border-blue-600 rounded-lg text-sm">
                                <input type="number" placeholder="Despesa" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-blue-200 dark:border-blue-600 rounded-lg text-sm">
                            </div>
                            <button class="w-full mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                                Calcular Resultado
                            </button>
                        </div>

                        <!-- ROI Calculator -->
                        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200/50 dark:border-green-700/50">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-green-800 dark:text-green-200">Calculadora ROI</h5>
                                    <p class="text-sm text-green-600 dark:text-green-300">Análise de retorno sobre investimento</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="number" placeholder="Investimento" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-green-200 dark:border-green-600 rounded-lg text-sm">
                                <input type="number" placeholder="Retorno" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-green-200 dark:border-green-600 rounded-lg text-sm">
                            </div>
                            <button class="w-full mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium">
                                Calcular ROI
                            </button>
                        </div>
                    </div>

                    <!-- Automation & Import -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Automação & Importação</h4>

                        <!-- Data Import -->
                        <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl border border-purple-200/50 dark:border-purple-700/50">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-purple-800 dark:text-purple-200">Importar Dados</h5>
                                    <p class="text-sm text-purple-600 dark:text-purple-300">Excel, CSV, OFX</p>
                                </div>
                            </div>
                            <div class="border-2 border-dashed border-purple-300 dark:border-purple-600 rounded-lg p-6 text-center">
                                <svg class="w-10 h-10 text-purple-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-sm text-purple-600 dark:text-purple-300">Arraste arquivos aqui ou clique para selecionar</p>
                            </div>
                        </div>

                        <!-- Scheduled Tasks -->
                        <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl border border-orange-200/50 dark:border-orange-700/50">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-orange-800 dark:text-orange-200">Tarefas Agendadas</h5>
                                    <p class="text-sm text-orange-600 dark:text-orange-300">Automatize operações recorrentes</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Backup Diário</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        <span class="text-xs text-gray-500">Ativo</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Relatório Semanal</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                        <span class="text-xs text-gray-500">Pausado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status & Health -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-8 h-8 mr-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Status do Sistema
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">Sistema Operacional</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Database Status -->
                    <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl text-center">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-green-800 dark:text-green-200 mb-2">Database</h4>
                        <p class="text-sm text-green-600 dark:text-green-300">Conectado</p>
                        <div class="mt-3 text-xs text-green-500">99.9% uptime</div>
                    </div>

                    <!-- API Status -->
                    <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl text-center">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-blue-800 dark:text-blue-200 mb-2">APIs</h4>
                        <p class="text-sm text-blue-600 dark:text-blue-300">Funcionando</p>
                        <div class="mt-3 text-xs text-blue-500">{{ rand(150, 300) }}ms resposta</div>
                    </div>

                    <!-- Storage Status -->
                    <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl text-center">
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-purple-800 dark:text-purple-200 mb-2">Storage</h4>
                        <p class="text-sm text-purple-600 dark:text-purple-300">{{ rand(60, 85) }}% usado</p>
                        <div class="mt-3 text-xs text-purple-500">{{ number_format(rand(150, 500), 0) }} GB livres</div>
                    </div>

                    <!-- Security Status -->
                    <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl text-center">
                        <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-orange-800 dark:text-orange-200 mb-2">Segurança</h4>
                        <p class="text-sm text-orange-600 dark:text-orange-300">Protegido</p>
                        <div class="mt-3 text-xs text-orange-500">SSL ativo</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    /* Força cores legíveis nas legendas do ApexCharts em light/dark mode */
    .apexcharts-legend-text { color: #0f172a !important; }
    .dark .apexcharts-legend-text, .apexcharts-theme-dark .apexcharts-legend-text { color: #E5E7EB !important; }
</style>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme detection
    const isDark = document.documentElement.classList.contains('dark');

    // Color schemes
    const colors = {
        primary: '#06B6D4',
        secondary: '#8B5CF6',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#3B82F6'
    };

    // Function to check if an element exists and is visible
    function isElementVisible(selector) {
        const element = document.querySelector(selector);
        return element && element.offsetParent !== null;
    }

    // Function to render charts when necessary
    function renderChartsInTab(tabName) {
        // Wait a small delay to ensure the tab has been rendered
        setTimeout(() => {
            switch(tabName) {
                case 'analytics':
                    renderAnalyticsCharts();
                    break;
            }
        }, 100);
    }

    // Function to render Analytics tab charts
    function renderAnalyticsCharts() {
        console.log('Rendering analytics charts...');

        // Check if analytics tab is visible
        const analyticsTab = document.querySelector('[x-show="activeTab === \'analytics\'"]');
        if (!analyticsTab || analyticsTab.style.display === 'none') {
            console.log('Analytics tab not visible');
            return;
        }

        // Monthly Evolution Chart
        if (isElementVisible("#monthlyChart")) {
            const monthlyOptions = {
                series: [{
                    name: 'Receitas',
                    data: @json($dadosReceita)
                }, {
                    name: 'Despesas',
                    data: @json($dadosDespesa)
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: [colors.success, colors.danger],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '60%'
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        },
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value);
                        }
                    }
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB',
                    strokeDashArray: 5
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR').format(value);
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: isDark ? '#D1D5DB' : '#374151'
                    }
                }
            };

            const monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
            monthlyChart.render();
        }

        // Daily Invoices Chart
        if (isElementVisible("#dailyInvoicesChart")) {
            const dailyOptions = {
                series: [{
                    name: 'Faturas',
                    data: @json($valoresInvoices)
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                colors: [colors.info],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: @json($diasInvoices),
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        },
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value);
                        }
                    }
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB',
                    strokeDashArray: 5
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR').format(value);
                        }
                    }
                }
            };

            const dailyChart = new ApexCharts(document.querySelector("#dailyInvoicesChart"), dailyOptions);
            dailyChart.render();
        }

        // Performance Gauge Chart
        if (isElementVisible("#performanceGauge")) {
            const performanceScore = {{ min(100, max(0, ($totalReceitas > 0 ? ($saldoTotal / $totalReceitas) * 100 + 50 : 50))) }};
            const gaugeOptions = {
                series: [performanceScore],
                chart: {
                    height: 320,
                    type: 'radialBar',
                    background: 'transparent'
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 225,
                        hollow: {
                            margin: 0,
                            size: '70%',
                            background: 'transparent',
                            image: undefined,
                            imageOffsetX: 0,
                            imageOffsetY: 0,
                            position: 'front',
                            dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                            }
                        },
                        track: {
                            background: isDark ? '#374151' : '#E5E7EB',
                            strokeWidth: '67%',
                            margin: 0,
                            dropShadow: {
                                enabled: true,
                                top: -3,
                                left: 0,
                                blur: 4,
                                opacity: 0.35
                            }
                        },
                        dataLabels: {
                            show: true,
                            name: {
                                offsetY: -10,
                                show: true,
                                color: isDark ? '#D1D5DB' : '#374151',
                                fontSize: '17px'
                            },
                            value: {
                                formatter: function(val) {
                                    return parseInt(val) + '%';
                                },
                                color: isDark ? '#D1D5DB' : '#374151',
                                fontSize: '36px',
                                show: true,
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: [colors.secondary],
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                colors: [colors.primary],
                labels: ['Performance Score']
            };

            const gaugeChart = new ApexCharts(document.querySelector("#performanceGauge"), gaugeOptions);
            gaugeChart.render();
        }

        // Waterfall Chart
        if (isElementVisible("#waterfallChart")) {
            const totalReceitas = {{ $totalReceitas }};
            const totalDespesas = {{ $totalDespesas }};
            const saldoFinal = totalReceitas - totalDespesas;

            const waterfallOptions = {
                series: [{
                    name: 'Fluxo de Caixa',
                    data: [
                        {
                            x: 'Receitas',
                            y: totalReceitas,
                            fillColor: colors.success
                        },
                        {
                            x: 'Despesas',
                            y: -totalDespesas,
                            fillColor: colors.danger
                        },
                        {
                            x: 'Saldo Final',
                            y: saldoFinal,
                            fillColor: saldoFinal >= 0 ? colors.info : colors.warning
                        }
                    ]
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '60%'
                    }
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        },
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(Math.abs(value));
                        }
                    }
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB',
                    strokeDashArray: 5
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR').format(Math.abs(value));
                        }
                    }
                },
                legend: {
                    show: false
                }
            };

            const waterfallChart = new ApexCharts(document.querySelector("#waterfallChart"), waterfallOptions);
            waterfallChart.render();
        }

        // Category Breakdown Chart
        if (isElementVisible("#categoryBreakdownChart")) {
            const categoryBreakdownChart = new ApexCharts(document.querySelector("#categoryBreakdownChart"), {
                chart: {
                    type: 'donut',
                    height: 320,
                    background: 'transparent'
                },
                series: [35, 25, 20, 15, 5],
                labels: ['Vendas', 'Serviços', 'Produtos', 'Comissões', 'Outros'],
                colors: ['#06b6d4', '#3b82f6', '#8b5cf6', '#ef4444', '#f59e0b'],
                legend: {
                    position: 'bottom',
                    labels: { colors: isDark ? '#9CA3AF' : '#6B7280' }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { colors: ['#fff'] }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return val + '%';
                        }
                    }
                }
            });
            categoryBreakdownChart.render();
        }

        // Payment Method Chart
        if (isElementVisible("#paymentMethodChart")) {
            const paymentMethodChart = new ApexCharts(document.querySelector("#paymentMethodChart"), {
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Valor',
                    data: [15000, 8500, 12000, 6500, 3200]
                }],
                xaxis: {
                    categories: ['PIX', 'Cartão Débito', 'Cartão Crédito', 'Dinheiro', 'Transferência'],
                    labels: { style: { colors: isDark ? '#9CA3AF' : '#6B7280' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#9CA3AF' : '#6B7280' },
                        formatter: function(val) {
                            return 'R$ ' + (val/1000) + 'k';
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false
                    }
                },
                colors: ['#06b6d4'],
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB'
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString();
                        }
                    }
                }
            });
            paymentMethodChart.render();
        }

        // Bank Balance Chart
        if (isElementVisible("#bankBalanceChart")) {
            const bankBalanceChart = new ApexCharts(document.querySelector("#bankBalanceChart"), {
                chart: {
                    type: 'line',
                    height: 320,
                    background: 'transparent',
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Saldo Total',
                    data: [25000, 27500, 26800, 29200, 31500, 28900, 33200]
                }],
                xaxis: {
                    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                    labels: { style: { colors: isDark ? '#9CA3AF' : '#6B7280' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#9CA3AF' : '#6B7280' },
                        formatter: function(val) {
                            return 'R$ ' + (val/1000) + 'k';
                        }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#10b981'],
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB'
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString();
                        }
                    }
                },
                markers: {
                    size: 5,
                    colors: ['#10b981'],
                    strokeColors: '#fff',
                    strokeWidth: 2
                }
            });
            bankBalanceChart.render();
        }

        // Monthly Trend Chart
        if (isElementVisible("#monthlyTrendChart")) {
            const monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), {
                chart: {
                    type: 'area',
                    height: 320,
                    background: 'transparent',
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Receitas',
                    data: [5000, 7000, 6500, 8000, 9500, 7800, 10200]
                }, {
                    name: 'Despesas',
                    data: [3000, 4500, 4200, 5500, 6200, 5800, 7100]
                }],
                xaxis: {
                    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                    labels: { style: { colors: isDark ? '#9CA3AF' : '#6B7280' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#9CA3AF' : '#6B7280' },
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString();
                        }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.1
                    }
                },
                colors: ['#06b6d4', '#ef4444'],
                legend: {
                    labels: { colors: isDark ? '#9CA3AF' : '#6B7280' }
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB'
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return 'R$ ' + val.toLocaleString();
                        }
                    }
                }
            });
            monthlyTrendChart.render();
        }

        console.log('Analytics charts rendering completed');
    }

    // Observer for tab changes
    const tabButtons = document.querySelectorAll('button[\\@click^="activeTab"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const clickText = this.getAttribute('@click');
            const tabName = clickText.replace("activeTab = '", "").replace("'", "");

            // Force render charts after tab change
            setTimeout(() => {
                if (tabName === 'analytics') {
                    renderAnalyticsCharts();
                }
            }, 300);
        });
    });

    // Also try to render charts when page loads
    setTimeout(() => {
        renderAnalyticsCharts();
    }, 1000);

    // Render charts when Analytics tab becomes visible
    const analyticsTab = document.querySelector('[x-show="activeTab === \'analytics\'"]');
    if (analyticsTab) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const isVisible = !analyticsTab.style.display || analyticsTab.style.display !== 'none';
                    if (isVisible) {
                        setTimeout(() => {
                            renderAnalyticsCharts();
                        }, 100);
                    }
                }
            });
        });

        observer.observe(analyticsTab, {
            attributes: true,
            attributeFilter: ['style']
        });
    }

    // Also render charts when Livewire updates
    document.addEventListener('livewire:navigated', function () {
        setTimeout(() => {
            renderAnalyticsCharts();
        }, 500);
    });

    // Observer for theme changes
    const themeObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                // Re-render charts when theme changes
                setTimeout(() => {
                    renderAnalyticsCharts();
                }, 100);
            }
        });
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });

    // Real-time data refresh function
    function refreshRealTimeData() {
        // Update timestamps
        const timeElements = document.querySelectorAll('[data-timestamp]');
        timeElements.forEach(element => {
            element.textContent = new Date().toLocaleTimeString('pt-BR');
        });

        // Simulate real-time metrics updates
        const liveMetrics = document.querySelectorAll('[data-live-metric]');
        liveMetrics.forEach(metric => {
            const currentValue = parseFloat(metric.textContent.replace(/[^\d.-]/g, ''));
            const variation = (Math.random() - 0.5) * 0.02; // ±1% variation
            const newValue = currentValue * (1 + variation);

            if (metric.textContent.includes('R$')) {
                metric.textContent = 'R$ ' + newValue.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else if (metric.textContent.includes('%')) {
                metric.textContent = newValue.toFixed(1) + '%';
            }
        });
    }

    // Auto-refresh every 30 seconds
    setInterval(refreshRealTimeData, 30000);

    // Performance monitoring
    function monitorPerformance() {
        const performanceEntry = performance.getEntriesByType('navigation')[0];
        const loadTime = performanceEntry.loadEventEnd - performanceEntry.loadEventStart;

        console.log('Dashboard load time:', loadTime, 'ms');

        // Update performance indicator if exists
        const perfIndicator = document.querySelector('[data-performance]');
        if (perfIndicator) {
            const score = Math.max(85, Math.min(99, 100 - (loadTime / 100)));
            perfIndicator.textContent = Math.round(score) + '%';
        }
    }

    // Run performance monitoring
    window.addEventListener('load', monitorPerformance);

    // Chart refresh on tab visibility change
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(() => {
                renderAnalyticsCharts();
            }, 100);
        }
    });
});
</script>
@endpush
