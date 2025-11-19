@props([
    'title' => 'Transações Financeiras',
    'description' => 'Gerencie suas finanças com estilo e eficiência',
    'totalTransactions' => 0,
    'totalExpenses' => 0,
    'average' => 0,
    'bankId' => null,
    'bank' => null,
    'showQuickActions' => true,
    'selectedDate' => null,
    'viewMode' => 'cards',
    'invoicesCount' => 0,
])

<!-- Header Moderno para Invoices (estilo consistente com cashbook) -->
<div
    class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-pink-50/80 dark:from-slate-800/90 dark:via-purple-900/30 dark:to-pink-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div
        class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-pink-400/20 to-rose-400/20 rounded-full transform translate-x-12 -translate-y-12">
    </div>
    <div
        class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-emerald-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8">
    </div>

    <div class="relative px-6 py-4">
        <!-- Top Section: Title + Bank Logo + Actions -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 mb-4">
            <div class="flex items-center gap-4">
                <!-- Botão Voltar -->
                <a href="{{ route('banks.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-150 mr-2 focus:outline-none focus:ring-2 focus:ring-purple-300 group shadow-none">
                    <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
                </a>
                <!-- Logo do Banco (se disponível) -->
                @if($bank && $bank->caminho_icone)
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
                        $bankKey = strtolower(Str::slug($bank->name));
                        if(Str::contains($bankKey, 'inter')) $bankKey = 'inter';
                        elseif(Str::contains($bankKey, 'nubank')) $bankKey = 'nubank';
                        elseif(Str::contains($bankKey, 'santander')) $bankKey = 'santander';
                        elseif(Str::contains($bankKey, 'caixa')) $bankKey = 'caixa';
                        elseif(Str::contains($bankKey, 'bradesco')) $bankKey = 'bradesco';
                        elseif(Str::contains($bankKey, 'itau')) $bankKey = 'itau';
                        elseif(Str::contains($bankKey, 'bb') || Str::contains($bankKey, 'banco-do-brasil')) $bankKey = 'bb';
                        $gradient = $cardColors[$bankKey] ?? 'from-purple-200 via-pink-200 to-blue-200';
                    @endphp
                    <div class="relative flex items-center justify-center w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 rounded-2xl shadow-xl overflow-visible">
                        <span class="absolute inset-0 rounded-2xl pointer-events-none border-4 border-transparent bg-clip-padding z-0 {{ 'bg-gradient-to-br ' . $gradient }}"></span>
                        <div class="relative w-[88px] h-[88px] bg-white/80 dark:bg-slate-800/80 rounded-2xl flex items-center justify-center overflow-hidden z-10">
                            @php
                                $icone = $bank->caminho_icone;
                                $isFullUrl = Str::startsWith($icone, ['http', 'https', '/assets', 'assets']);
                            @endphp
                            <img src="{{ $isFullUrl ? $icone : asset('storage/' . ltrim($icone, '/')) }}"
                                 alt="{{ $bank->name ?? 'Bank' }}"
                                 class="w-full h-full max-w-24 max-h-24 select-none"
                                 style="aspect-ratio:1/1; object-fit:contain; object-position:center; padding:0.5rem; background:transparent;" />
                        </div>
                    </div>
                @else
                    <div class="relative flex items-center justify-center w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-2xl shadow-md shadow-purple-400/20">
                        <i class="fas fa-university text-white text-3xl"></i>
                    </div>
                @endif

                <div class="space-y-1 ml-2">
                    <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100 leading-tight tracking-tight drop-shadow-sm">
                        {{ $title }}
                    </h1>
                    <div class="flex items-center gap-2 flex-wrap mt-0.5">
                        <p class="text-base font-medium text-slate-600 dark:text-slate-400">{{ $description }}</p>
                        @if($bank)
                            <span class="text-sm px-2 py-0.5 rounded-md bg-gradient-to-r from-blue-500/20 to-purple-500/20 text-blue-700 dark:text-blue-300 font-semibold border border-blue-300/30 dark:border-blue-500/30 shadow-sm">
                                {{ $bank->name ?? 'Banco' }}
                            </span>
                        @endif
                        @if($selectedDate)
                            <span class="inline-flex items-center gap-1.5 text-sm px-2 py-1 rounded-md bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-semibold border border-purple-300/50 dark:border-purple-500/30 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                                <button wire:click="clearDateSelection" class="ml-0.5 hover:text-purple-900 dark:hover:text-purple-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- View Mode Toggle -->
                @if($bankId && $invoicesCount > 0)
                    <div role="tablist" aria-label="Modo de visualização" class="inline-flex rounded-xl bg-blue-50 dark:bg-blue-900/50 p-1 shadow-sm">
                        <button type="button" wire:click="setViewMode('cards')" role="tab"
                            aria-pressed="{{ $viewMode === 'cards' ? 'true' : 'false' }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-base font-semibold rounded-lg transition-all duration-150 {{ $viewMode === 'cards' ? 'bg-white text-slate-900 shadow' : 'text-blue-600 dark:text-blue-300' }}">
                            <i class="fas fa-th text-base"></i>
                            <span class="hidden sm:inline">Cards</span>
                        </button>
                        <button type="button" wire:click="setViewMode('list')" role="tab"
                            aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-base font-semibold rounded-lg transition-all duration-150 {{ $viewMode === 'list' ? 'bg-white text-slate-900 shadow' : 'text-blue-600 dark:text-blue-300' }}">
                            <i class="fas fa-list text-base"></i>
                            <span class="hidden sm:inline">Lista</span>
                        </button>
                    </div>
                @endif

                <!-- Quick Actions -->
                @if ($showQuickActions && $bankId)
                    <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                        class="group relative inline-flex items-center justify-center px-5 py-2 bg-gradient-to-br from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-2xl text-base tracking-wide focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-150 text-lg"></i>
                        <span>Nova</span>
                    </a>

                    <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                        class="group relative inline-flex items-center justify-center px-5 py-2 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-2xl text-base tracking-wide focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <i class="fas fa-upload mr-2 group-hover:scale-110 transition-transform duration-150 text-lg"></i>
                        <span>Upload</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <!-- Total Despesas Card -->
            <div
                class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100/60 dark:border-gray-700/60 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Despesas</p>
                                <p class="text-2xl font-extrabold text-red-600 dark:text-red-400">
                                    R$ {{ number_format($totalExpenses, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/40 dark:to-pink-900/40 px-5 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-700 dark:text-red-300">Total do mês</span>
                        <i class="fas fa-arrow-down text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>

            <!-- Total Transações Card -->
            <div
                class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100/60 dark:border-gray-700/60 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Transações</p>
                                <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                                    {{ $totalTransactions }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/40 dark:to-indigo-900/40 px-5 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-blue-700 dark:text-blue-300">Quantidade</span>
                        <i class="fas fa-receipt text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Média Card -->
            <div
                class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100/60 dark:border-gray-700/60 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Média</p>
                                <p class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">
                                    R$ {{ number_format($average, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/40 dark:to-pink-900/40 px-5 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-purple-700 dark:text-purple-300">Por transação</span>
                        <i class="fas fa-calculator text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
