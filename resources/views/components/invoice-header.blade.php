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
        <!-- Breadcrumb -->
        @if(isset($breadcrumb))
            {{ $breadcrumb }}
        @endif

        <!-- Top Section: Title + Bank Logo + Métricas + Actions -->
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

            <div class="flex items-center gap-3 lg:gap-6 flex-wrap">
                <!-- Lado Direito: Métricas Financeiras -->
                <div class="flex items-center gap-4">
                    <!-- Despesas -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Despesas</span>
                        <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-600">
                            R$ {{ number_format($totalExpenses, 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Total do mês</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                    <!-- Transações -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transações</span>
                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                            {{ $totalTransactions }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Quantidade</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                    <!-- Média -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Média</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            R$ {{ number_format($average, 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Por transação</span>
                    </div>
                </div>

                <!-- Divider vertical entre métricas e actions -->
                <div class="hidden lg:block h-16 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                <!-- Quick Actions Container -->
                @if ($showQuickActions && $bankId)
                    <div class="flex flex-col gap-3">
                        <!-- Botões Nova e Upload -->
                        <div class="flex items-center gap-3">
                            <!-- Botão Nova Fatura - Ultra Moderno -->
                            <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                                class="group relative inline-flex items-center justify-center gap-2.5 px-5 py-3 overflow-hidden rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-emerald-400/50">
                                <!-- Fundo gradiente animado -->
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 via-teal-500 to-green-600"></div>
                                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-600/0 via-teal-400/40 to-green-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <!-- Brilho superior -->
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"></div>

                                <!-- Efeito de brilho animado -->
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                </div>

                                <!-- Shadow glow -->
                                <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                                <!-- Conteúdo -->
                                <div class="relative flex items-center gap-2.5 z-10">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-white/30 rounded-full blur-md group-hover:bg-white/50 transition-all duration-300"></div>
                                        <i class="fas fa-plus-circle text-xl text-white relative group-hover:rotate-90 transition-transform duration-500"></i>
                                    </div>
                                    <span class="font-black text-sm text-white tracking-wider uppercase drop-shadow-lg">Criar</span>
                                </div>

                                <!-- Partículas decorativas -->
                                <div class="absolute top-1 right-1 w-2 h-2 bg-white/60 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-500"></div>
                                <div class="absolute bottom-1 left-1 w-1.5 h-1.5 bg-white/40 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-700"></div>
                            </a>

                            <!-- Botão Upload - Ultra Moderno -->
                            <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                                class="group relative inline-flex items-center justify-center gap-2.5 px-5 py-3 overflow-hidden rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-400/50">
                                <!-- Fundo gradiente animado -->
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 via-indigo-600 to-purple-700"></div>
                                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/0 via-indigo-400/40 to-purple-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <!-- Brilho superior -->
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"></div>

                                <!-- Efeito de brilho animado -->
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                </div>

                                <!-- Shadow glow -->
                                <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 rounded-2xl blur-lg opacity-50 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                                <!-- Conteúdo -->
                                <div class="relative flex items-center gap-2.5 z-10">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-white/30 rounded-full blur-md group-hover:bg-white/50 transition-all duration-300"></div>
                                        <i class="fas fa-cloud-upload-alt text-xl text-white relative group-hover:scale-125 group-hover:-translate-y-1 transition-transform duration-500"></i>
                                    </div>
                                    <span class="font-black text-sm text-white tracking-wider uppercase drop-shadow-lg">Upload</span>
                                </div>

                                <!-- Partículas decorativas -->
                                <div class="absolute top-1 right-1 w-2 h-2 bg-white/60 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-500"></div>
                                <div class="absolute bottom-1 left-1 w-1.5 h-1.5 bg-white/40 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-700"></div>
                            </a>
                        </div>

                        <!-- View Mode Toggle (abaixo dos botões) - Ultra Moderno -->
                        @if($bankId && $invoicesCount > 0)
                            <div role="tablist" aria-label="Modo de visualização" class="relative inline-flex rounded-2xl p-1.5 shadow-lg border-2 border-white/20 dark:border-slate-600/30 overflow-hidden backdrop-blur-sm" style="background: linear-gradient(135deg, rgba(148, 163, 184, 0.15) 0%, rgba(203, 213, 225, 0.1) 100%);">
                                <!-- Fundo com gradiente -->
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-200/80 via-slate-100/60 to-slate-200/80 dark:from-slate-800/80 dark:via-slate-700/60 dark:to-slate-800/80"></div>

                                <!-- Brilho animado de fundo -->
                                <div class="absolute inset-0 opacity-50">
                                    <div class="absolute top-0 left-1/4 w-1/2 h-full bg-gradient-to-b from-white/30 to-transparent dark:from-white/10 blur-xl"></div>
                                </div>

                                <button type="button" wire:click="setViewMode('cards')" role="tab"
                                    aria-pressed="{{ $viewMode === 'cards' ? 'true' : 'false' }}"
                                    class="relative inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-black rounded-xl transition-all duration-300 overflow-hidden group {{ $viewMode === 'cards' ? 'text-white' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                                    @if($viewMode === 'cards')
                                        <!-- Fundo ativo com gradiente -->
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600"></div>
                                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/0 via-purple-400/40 to-pink-500/0"></div>
                                        <!-- Brilho superior -->
                                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                                        <!-- Shadow colorido -->
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-xl blur opacity-75 -z-10"></div>
                                    @endif
                                    <i class="fas fa-th-large text-base relative z-10 {{ $viewMode === 'cards' ? 'animate-pulse' : 'group-hover:scale-110' }} transition-transform duration-300"></i>
                                    <span class="relative z-10 uppercase tracking-wider">Cards</span>
                                </button>

                                <button type="button" wire:click="setViewMode('list')" role="tab"
                                    aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}"
                                    class="relative inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-black rounded-xl transition-all duration-300 overflow-hidden group {{ $viewMode === 'list' ? 'text-white' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                                    @if($viewMode === 'list')
                                        <!-- Fundo ativo com gradiente -->
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600"></div>
                                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/0 via-purple-400/40 to-pink-500/0"></div>
                                        <!-- Brilho superior -->
                                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                                        <!-- Shadow colorido -->
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-xl blur opacity-75 -z-10"></div>
                                    @endif
                                    <i class="fas fa-list-ul text-base relative z-10 {{ $viewMode === 'list' ? 'animate-pulse' : 'group-hover:scale-110' }} transition-transform duration-300"></i>
                                    <span class="relative z-10 uppercase tracking-wider">Lista</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
