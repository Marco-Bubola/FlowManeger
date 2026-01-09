<div class="w-full">
    <!-- Header Moderno com Gradiente e Glassmorphism -->
    <div class="relative bg-gradient-to-r from-white/80 via-emerald-50/90 to-teal-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <!-- Background decorativo com overflow controlado -->
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-teal-400/20 to-cyan-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-teal-400/10 via-emerald-400/10 to-green-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>
        </div>

        <div class="relative px-8 py-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <!-- Título e Info do Consórcio -->
                <div class="flex items-center gap-6">
                    <!-- Voltar -->
                    <a href="{{ route('consortiums.index') }}"
                        class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 transition-all duration-300 shadow-lg hover:shadow-xl group">
                        <i class="bi bi-arrow-left text-white text-xl group-hover:scale-110 transition-transform duration-300"></i>
                    </a>

                    <!-- Ícone do Consórcio -->
                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 flex items-center justify-center group">
                        <i class="bi bi-people-fill text-4xl text-white group-hover:scale-110 transition-transform duration-300"></i>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-emerald-300 dark:via-teal-300 dark:to-cyan-300 bg-clip-text text-transparent">
                            {{ $this->consortium->name }}
                        </h1>

                        <!-- Badges do consórcio -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold {{ $this->consortium->status === 'active' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white' : ($this->consortium->status === 'completed' ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white' : 'bg-gradient-to-r from-red-500 to-pink-500 text-white') }} shadow-lg">
                                <i class="bi bi-circle-fill mr-2 text-xs"></i>
                                {{ $this->consortium->status_label }}
                            </span>

                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-white/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-lg backdrop-blur-sm">
                                <i class="bi bi-calendar3 mr-2"></i>
                                {{ $this->consortium->draw_frequency_label }}
                            </span>

                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-white/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-lg backdrop-blur-sm">
                                <i class="bi bi-hourglass-split mr-2"></i>
                                {{ $this->consortium->duration_months }} meses
                            </span>

                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-white/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-lg backdrop-blur-sm">
                                <i class="bi bi-ui-checks mr-2"></i>
                                {{ $this->consortium->mode_label ?? 'Sorteio' }}
                            </span>
                        </div>

                        @if ($this->consortium->description)
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 max-w-2xl">
                                <i class="bi bi-info-circle mr-1"></i>{{ $this->consortium->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Botões de Ação - Organização Melhorada -->
                <div class="flex flex-col gap-3">
                    <!-- Linha 1: Ações Principais -->
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($this->consortium->canAddParticipants())
                            <button wire:click="$dispatch('openAddParticipantModal', { consortiumId: {{ $this->consortium->id }} })"
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <i class="bi bi-person-plus-fill text-lg"></i>
                                <span>Adicionar Participante</span>
                            </button>
                        @endif

                        @if ($this->consortium->mode !== 'draw')
                            <div class="flex items-center gap-2 px-5 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl border-2 border-slate-300 dark:border-slate-700 font-semibold shadow-lg">
                                <i class="bi bi-box-seam text-lg"></i>
                                <span>Modo: Resgate por quitação (sem sorteio)</span>
                            </div>
                        @elseif ($this->consortium->canPerformDraw())
                            <a href="{{ route('consortiums.draw', $this->consortium) }}"
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <i class="bi bi-trophy-fill text-lg"></i>
                                <span>Realizar Sorteio</span>
                            </a>
                        @else
                            @php
                                $consortium = $this->consortium;
                                $reasons = [];

                                if ($consortium->status !== 'active') {
                                    $reasons[] = '❌ Consórcio não está ativo (Status: ' . $consortium->status_label . ')';
                                }

                                if ($consortium->active_participants_count === 0) {
                                    $reasons[] = '❌ Nenhum participante ativo cadastrado';
                                }

                                $eligibleCount = $consortium->participants()->where('status', 'active')->where('is_contemplated', false)->count();
                                if ($eligibleCount === 0 && $consortium->active_participants_count > 0) {
                                    $reasons[] = '✅ Todos os participantes já foram contemplados';
                                }

                                if (now()->lt($consortium->start_date)) {
                                    $daysUntilStart = now()->diffInDays($consortium->start_date, false);
                                    $reasons[] = '⏳ Data de início ainda não chegou (Faltam ' . abs($daysUntilStart) . ' dias - ' . $consortium->start_date->format('d/m/Y') . ')';
                                }

                                $lastDraw = $consortium->draws()->orderBy('draw_date', 'desc')->first();
                                if ($lastDraw) {
                                    $daysSinceLastDraw = now()->diffInDays($lastDraw->draw_date);
                                    $frequencyDays = match($consortium->draw_frequency) {
                                        'weekly' => 7,
                                        'biweekly' => 14,
                                        'monthly' => 30,
                                        'quarterly' => 90,
                                        default => 30
                                    };
                                    $requiredDays = ceil($frequencyDays * 0.8);
                                    if ($daysSinceLastDraw < $requiredDays) {
                                        $daysRemaining = $requiredDays - $daysSinceLastDraw;
                                        $nextDrawDate = $lastDraw->draw_date->copy()->addDays($requiredDays)->format('d/m/Y');
                                        $reasons[] = "⏰ Aguarde {$daysRemaining} dia(s) desde o último sorteio (Próximo: {$nextDrawDate})";
                                    }
                                }

                                if (empty($reasons)) {
                                    $reasons[] = '✓ Sistema pronto para sorteio';
                                }

                                $reasonText = implode("\n\n", $reasons);
                                $totalReasons = count($reasons);
                            @endphp
                            <div class="relative group">
                                <button disabled
                                    class="flex items-center gap-2 px-5 py-3 bg-gray-400 dark:bg-gray-600 text-white font-bold rounded-xl shadow-lg opacity-60 cursor-not-allowed">
                                    <i class="bi bi-trophy-fill text-lg"></i>
                                    <span>Sorteio Indisponível</span>
                                </button>
                                <div class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 w-80 p-4 bg-slate-900 dark:bg-slate-700 text-white text-sm rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[9999]">
                                    <div class="font-bold mb-3 flex items-center justify-between gap-2 text-yellow-400 border-b border-slate-600 pb-2">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-info-circle-fill"></i>
                                            Motivos do Bloqueio:
                                        </div>
                                        <span class="text-xs bg-yellow-400/20 px-2 py-1 rounded">{{ $totalReasons }} {{ $totalReasons === 1 ? 'motivo' : 'motivos' }}</span>
                                    </div>
                                    <div class="text-slate-200 space-y-2 whitespace-pre-line leading-relaxed">{{ $reasonText }}</div>
                                    <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-slate-900 dark:bg-slate-700 rotate-45"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Linha 2: Ações Secundárias -->
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('consortiums.edit', $this->consortium) }}"
                            class="flex items-center gap-2 px-4 py-2.5 bg-white/90 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border-2 border-slate-300 dark:border-slate-600 transition-all duration-300 shadow-md hover:shadow-lg backdrop-blur-sm">
                            <i class="bi bi-pencil-fill"></i>
                            <span>Editar</span>
                        </a>

                        <button wire:click="$dispatch('openExportModal', { consortiumId: {{ $this->consortium->id }} })"
                            class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                            <span>Exportar</span>
                        </button>

                        <button wire:click="$dispatch('openToggleConsortiumModal', { consortiumId: {{ $this->consortium->id }} })"
                            @if($this->consortium->status === 'active')
                                class="flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300"
                            @else
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300"
                            @endif>
                            @if($this->consortium->status === 'active')
                                <i class="bi bi-pause-circle"></i>
                                <span>Desativar</span>
                            @else
                                <i class="bi bi-play-circle"></i>
                                <span>Ativar</span>
                            @endif
                        </button>

                        <button wire:click="$dispatch('openDeleteConsortiumModal', { consortiumId: {{ $this->consortium->id }} })"
                            class="flex items-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                            <i class="bi bi-trash3"></i>
                            <span>Excluir</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Participantes Ativos</span>
                <div class="w-10 h-10 bg-emerald-500 dark:bg-emerald-600 rounded-lg flex items-center justify-center"><i class="bi bi-people text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ $this->consortium->active_participants_count }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">de {{ $this->consortium->max_participants }} vagas</div>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Contemplados</span>
                <div class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-lg flex items-center justify-center"><i class="bi bi-trophy text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $this->consortium->contemplated_count }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ number_format(($this->consortium->contemplated_count / max($this->consortium->active_participants_count, 1)) * 100, 1) }}% do total</div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Arrecadação</span>
                <div class="w-10 h-10 bg-purple-500 dark:bg-purple-600 rounded-lg flex items-center justify-center"><i class="bi bi-cash-stack text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">R$ {{ number_format($this->consortium->total_collected, 2, ',', '.') }}</div>
            <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                Real: R$ {{ number_format($this->consortium->total_value_real, 2, ',', '.') }} |
                Possível: R$ {{ number_format($this->consortium->total_value_possible, 2, ',', '.') }}
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl p-6 border border-orange-200 dark:border-orange-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-orange-700 dark:text-orange-400">Progresso</span>
                <div class="w-10 h-10 bg-orange-500 dark:bg-orange-600 rounded-lg flex items-center justify-center"><i class="bi bi-graph-up text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{ number_format($this->consortium->completion_percentage, 1) }}%</div>
            <div class="h-2 bg-orange-200 dark:bg-orange-800 rounded-full mt-3">
                <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full transition-all" style="width: {{ $this->consortium->completion_percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
        <div class="border-b border-slate-200 dark:border-slate-700">
            <nav class="flex gap-2 p-4 overflow-x-auto">
                <button wire:click="setTab('overview')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'overview' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-grid"></i><span>Vis�o Geral</span>
                </button>
                <button wire:click="setTab('participants')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'participants' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-people"></i><span>Participantes</span><span class="px-2 py-0.5 bg-emerald-500 text-white text-xs rounded-full">{{ $this->consortium->active_participants_count }}</span>
                </button>
                <button wire:click="setTab('payments')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'payments' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-wallet2"></i><span>Pagamentos</span>
                </button>
                <button wire:click="setTab('draws')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'draws' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-trophy"></i><span>Sorteios</span><span class="px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full">{{ $this->consortium->draws->count() }}</span>
                </button>
                <button wire:click="setTab('contemplated')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'contemplated' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-star"></i><span>Contemplados</span><span class="px-2 py-0.5 bg-blue-500 text-white text-xs rounded-full">{{ $this->consortium->contemplated_count }}</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            @if ($activeTab === 'overview')
                <div class="space-y-6">
                    <!-- Informações Principais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-700 rounded-xl p-6 border border-slate-200 dark:border-slate-600">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <i class="bi bi-info-circle text-emerald-600"></i>
                                Informações do Consórcio
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-600">
                                    <span class="text-slate-600 dark:text-slate-400">Valor Mensal:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">R$ {{ number_format($this->consortium->monthly_value, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-600">
                                    <span class="text-slate-600 dark:text-slate-400">Duração:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->consortium->duration_months }} meses</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-600">
                                    <span class="text-slate-600 dark:text-slate-400">Data Início:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($this->consortium->start_date)->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-600">
                                    <span class="text-slate-600 dark:text-slate-400">Frequência Sorteios:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->consortium->draw_frequency_label }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-600 dark:text-slate-400">Vagas:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->consortium->max_participants }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700">
                            <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-4 flex items-center gap-2">
                                <i class="bi bi-cash-stack text-emerald-600"></i>
                                Resumo Financeiro
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-emerald-200 dark:border-emerald-800">
                                    <span class="text-emerald-700 dark:text-emerald-300">Valor Total Possível:</span>
                                    <span class="font-bold text-emerald-900 dark:text-emerald-100">R$ {{ number_format($this->consortium->total_value_possible, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-emerald-200 dark:border-emerald-800">
                                    <span class="text-emerald-700 dark:text-emerald-300">Valor Total Real:</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($this->consortium->total_value_real, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-emerald-200 dark:border-emerald-800">
                                    <span class="text-emerald-700 dark:text-emerald-300">Total Arrecadado:</span>
                                    <span class="font-bold text-emerald-900 dark:text-emerald-100">R$ {{ number_format($this->consortium->total_collected, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-emerald-200 dark:border-emerald-800">
                                    <span class="text-emerald-700 dark:text-emerald-300">Pendente:</span>
                                    <span class="font-bold text-orange-600 dark:text-orange-400">R$ {{ number_format($this->consortium->total_value_real - $this->consortium->total_collected, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-emerald-700 dark:text-emerald-300">Taxa Arrecadação:</span>
                                    <span class="font-bold text-emerald-900 dark:text-emerald-100">{{ number_format($this->consortium->completion_percentage, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progresso do Consórcio -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                            <i class="bi bi-graph-up text-purple-600"></i>
                            Progresso Geral
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Participantes</span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $this->consortium->active_participants_count }} / {{ $this->consortium->max_participants }}</span>
                                </div>
                                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all" style="width: {{ ($this->consortium->active_participants_count / $this->consortium->max_participants) * 100 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Arrecadação</span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ number_format($this->consortium->completion_percentage, 1) }}%</span>
                                </div>
                                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all" style="width: {{ $this->consortium->completion_percentage }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Contemplados</span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $this->consortium->contemplated_count }} / {{ $this->consortium->active_participants_count }}</span>
                                </div>
                                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full transition-all" style="width: {{ $this->consortium->active_participants_count > 0 ? ($this->consortium->contemplated_count / $this->consortium->active_participants_count) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas Atividades -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <i class="bi bi-clock-history text-blue-600"></i>
                                Último Sorteio
                            </h3>
                            @php
                                $lastDraw = $this->consortium->draws()->with('winner.client')->orderBy('draw_date', 'desc')->first();
                            @endphp
                            @if($lastDraw)
                                <div class="space-y-2">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Sorteio #{{ $lastDraw->draw_number }}</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Data: {{ \Carbon\Carbon::parse($lastDraw->draw_date)->format('d/m/Y H:i') }}</p>
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">Vencedor: {{ $lastDraw->winner->client->name ?? 'N/A' }}</p>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhum sorteio realizado ainda</p>
                            @endif
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <i class="bi bi-wallet2 text-green-600"></i>
                                Últimos Pagamentos
                            </h3>
                            @php
                                $recentPayments = \App\Models\ConsortiumPayment::whereIn('consortium_participant_id', $this->consortium->participants->pluck('id'))
                                    ->with('participant.client')
                                    ->where('status', 'paid')
                                    ->orderBy('payment_date', 'desc')
                                    ->limit(3)
                                    ->get();
                            @endphp
                            @endphp
                            @if($recentPayments->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($recentPayments as $payment)
                                        <div class="flex justify-between items-center text-sm border-b border-slate-200 dark:border-slate-700 pb-2">
                                            <span class="text-slate-700 dark:text-slate-300">{{ $payment->participant->client->name ?? 'N/A' }}</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhum pagamento registrado</p>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($activeTab === 'participants')
                @if ($this->participants->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-people text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum participante cadastrado</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">Adicione participantes usando o botão acima</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">#</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Cliente</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Data Entrada</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Pagamentos</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Total Pago</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Contemplado</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">A��es</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($this->participants as $participant)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg font-semibold text-sm">
                                                {{ $participant->participation_number }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($participant->client->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('clients.consortiums', $participant->client) }}" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                                                        {{ $participant->client->name ?? 'N/A' }}
                                                    </a>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $participant->client->email ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($participant->entry_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold {{ $participant->status_color }}">
                                                {{ $participant->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <span class="text-slate-700 dark:text-slate-300">
                                                {{ $participant->payments->where('status', 'paid')->count() }} / {{ $this->consortium->duration_months }}
                                            </span>
                                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 mt-1">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $participant->payment_percentage }}%"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-slate-100">
                                            R$ {{ number_format($participant->total_paid, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($participant->is_contemplated)
                                                <i class="bi bi-star-fill text-yellow-500 text-xl"></i>
                                            @else
                                                <i class="bi bi-star text-slate-300 dark:text-slate-600 text-xl"></i>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Desativar/Ativar Participante -->
                                                @if($participant->status === 'active')
                                                    <button
                                                        wire:click="confirmToggleParticipant({{ $participant->id }})"
                                                        type="button"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-orange-600 hover:text-orange-800 hover:bg-orange-50 dark:text-orange-400 dark:hover:text-orange-300 dark:hover:bg-orange-900/20 rounded-lg transition-colors"
                                                        title="Desativar participante"
                                                    >
                                                        <i class="bi bi-pause-circle"></i>
                                                    </button>
                                                @elseif($participant->status === 'quit')
                                                    <button
                                                        wire:click="confirmToggleParticipant({{ $participant->id }})"
                                                        type="button"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                                        title="Reativar participante"
                                                    >
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                @endif

                                                <!-- Excluir Participante -->
                                                <button
                                                    wire:click="confirmDeleteParticipant({{ $participant->id }})"
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    title="Excluir participante"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumo -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300 font-semibold">Total Participantes</p>
                                    <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100 mt-1">{{ $this->participants->count() }}</p>
                                </div>
                                <i class="bi bi-people text-3xl text-emerald-600 dark:text-emerald-400"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 font-semibold">Ativos</p>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ $this->participants->where('status', 'active')->count() }}</p>
                                </div>
                                <i class="bi bi-check-circle text-3xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold">Contemplados</p>
                                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">{{ $this->participants->where('is_contemplated', true)->count() }}</p>
                                </div>
                                <i class="bi bi-star-fill text-3xl text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($activeTab === 'payments')
                @if ($this->payments->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-wallet2 text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum pagamento registrado</p>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach ($this->payments as $participant)
                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-lg overflow-hidden" x-data="{ open: false }">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 p-5 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-b border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold flex items-center justify-center">
                                            {{ $participant->participation_number }}
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Cliente</p>
                                            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                                {{ $participant->client->name ?? 'N/A' }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $participant->client->email ?? '' }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 w-full lg:w-auto">
                                        @php
                                            $total = $participant->payments->count();
                                            $paid = $participant->payments->where('status', 'paid')->count();
                                            $overdue = $participant->payments->where('status', 'overdue')->count();
                                            $pending = $participant->payments->where('status', 'pending')->count();
                                        @endphp
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                                            <i class="bi bi-check-circle-fill text-emerald-500"></i>
                                            <div>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pagas</p>
                                                <p class="font-bold text-slate-900 dark:text-slate-100">{{ $paid }} / {{ $total }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                                            <i class="bi bi-hourglass-split text-amber-500"></i>
                                            <div>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pendentes</p>
                                                <p class="font-bold text-slate-900 dark:text-slate-100">{{ $pending }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                                            <i class="bi bi-exclamation-triangle-fill text-red-500"></i>
                                            <div>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Vencidas</p>
                                                <p class="font-bold text-slate-900 dark:text-slate-100">{{ $overdue }}</p>
                                            </div>
                                        </div>
                                        <div class="hidden lg:flex items-center gap-2 px-3 py-2 rounded-lg bg-white/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                                            <i class="bi bi-wallet2 text-emerald-500"></i>
                                            <div>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Total Pago</p>
                                                <p class="font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($participant->total_paid, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full lg:w-auto flex justify-end">
                                        <button type="button" @click="open = !open"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                                            <span x-text="open ? 'Esconder parcelas' : 'Mostrar parcelas'"></span>
                                            <i class="bi bi-chevron-down text-xs" :class="{ 'rotate-180': open }"></i>
                                        </button>
                                    </div>
                                </div>

                                  <div class="p-5 space-y-3" x-show="open"
                                      x-transition:enter="transition ease-out duration-200"
                                      x-transition:enter-start="opacity-0 scale-95"
                                      x-transition:enter-end="opacity-100 scale-100"
                                      x-transition:leave="transition ease-in duration-150"
                                      x-transition:leave-start="opacity-100 scale-100"
                                      x-transition:leave-end="opacity-0 scale-95">
                                    @foreach ($participant->payments as $payment)
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 text-slate-800 dark:text-slate-100 font-bold flex items-center justify-center">
                                                    {{ str_pad($payment->reference_month, 2, '0', STR_PAD_LEFT) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $payment->reference_month_name }}/{{ $payment->reference_year }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                                        Venc.: {{ optional($payment->due_date)->format('d/m/Y') ?? '-' }}
                                                        @if($payment->payment_date)
                                                            • Pago em {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                                        @endif
                                                    </p>
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold {{ $payment->status_color }}">
                                                            {{ $payment->status_label }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-4 md:gap-6">
                                                <div class="text-right">
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">Valor</p>
                                                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                                                </div>

                                                <div class="min-w-[120px] text-center">
                                                    @if ($payment->status !== 'paid')
                                                        @livewire('consortiums.record-payment', ['payment' => $payment], key('payment-'.$participant->id.'-'.$payment->id))
                                                    @else
                                                        <div class="flex flex-col gap-2">
                                                            <span class="inline-flex items-center justify-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                                <i class="bi bi-check-circle-fill"></i> Pago
                                                            </span>
                                                            @livewire('consortiums.cancel-payment', ['payment' => $payment], key('cancel-payment-'.$participant->id.'-'.$payment->id))
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @elseif ($activeTab === 'draws')
                @if ($this->draws->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-trophy text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum sorteio realizado</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">Use o botão "Realizar Sorteio" para criar um novo sorteio</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($this->draws as $draw)
                            <div class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl p-6 hover:shadow-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <i class="bi bi-trophy-fill text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-slate-100">Sorteio #{{ $draw->draw_number }}</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if ($draw->winner)
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Vencedor</p>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $draw->winner->client->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Participação #{{ $draw->winner->participation_number }}</p>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300">
                                                Sem vencedor
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @elseif ($activeTab === 'contemplated')
                @if ($this->contemplated->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-star text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum participante contemplado ainda</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($this->contemplated as $participant)
                            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $participant->client->name ?? 'N/A' }}</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Participação #{{ $participant->participation_number }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        @if($participant->contemplation && ($participant->contemplation->status === 'pending' || ($participant->contemplation->status === 'redeemed' && $participant->contemplation->redemption_type === 'products')) && in_array($participant->contemplation->redemption_type, ['pending', 'products']))
                                            <a href="{{ route('consortiums.contemplation.products', $participant->contemplation) }}"
                                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 transition-colors">
                                                <i class="bi bi-{{ $participant->contemplation->products ? 'pencil-square' : 'box-seam' }} text-base"></i>
                                                <span>{{ $participant->contemplation->products ? 'Editar Produtos' : 'Registrar Produtos' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-slate-400">Data Contemplação:</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($participant->contemplation_date)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-slate-400">Tipo:</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $participant->contemplation->contemplation_type_label ?? 'N/A' }}</span>
                                    </div>
                                    @if ($participant->contemplation)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-600 dark:text-slate-400">Resgate:</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $participant->contemplation->redemption_type_label ?? 'Pendente' }}</span>
                                        </div>
                                        @if($participant->contemplation->redemption_value)
                                            <div class="flex justify-between text-sm border-t border-yellow-200 dark:border-yellow-700 pt-2 mt-2">
                                                <span class="text-slate-600 dark:text-slate-400">Valor:</span>
                                                <span class="font-bold text-yellow-900 dark:text-yellow-100">R$ {{ number_format($participant->contemplation->redemption_value, 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        @if($participant->contemplation->products)
                                            <div class="mt-4 pt-4 border-t-2 border-yellow-300 dark:border-yellow-600">
                                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3 flex items-center gap-2">
                                                    <i class="bi bi-box-seam-fill text-purple-600"></i>
                                                    Produtos Resgatados
                                                </p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($participant->contemplation->products ?? [] as $product)
                                                        @php
                                                            $productData = \App\Models\Product::find($product['product_id'] ?? 0);
                                                        @endphp
                                                        <div class="bg-gradient-to-br from-white to-purple-50/50 dark:from-zinc-800 dark:to-purple-900/20 rounded-xl shadow-md border-2 border-purple-200 dark:border-purple-700/50 overflow-hidden hover:shadow-xl transition-all">
                                                            <div class="flex gap-3 p-3">
                                                                <!-- Imagem do Produto -->
                                                                <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-lg overflow-hidden shadow-md">
                                                                    @if($productData && $productData->image)
                                                                        <img src="{{ asset('storage/products/' . $productData->image) }}"
                                                                             alt="{{ $product['product_name'] ?? 'Produto' }}"
                                                                             class="w-full h-full object-cover">
                                                                    @else
                                                                        <div class="w-full h-full flex items-center justify-center">
                                                                            <i class="bi bi-box-seam text-3xl text-purple-400 dark:text-purple-500"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <!-- Informações -->
                                                                <div class="flex-1 min-w-0">
                                                                    <h5 class="font-black text-sm text-slate-900 dark:text-white line-clamp-2 mb-1">
                                                                        {{ $product['product_name'] ?? 'N/A' }}
                                                                    </h5>
                                                                    <div class="flex items-center gap-2 mb-2">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded-md text-xs font-bold">
                                                                            <i class="bi bi-hash"></i>
                                                                            {{ $product['quantity'] ?? 0 }}x
                                                                        </span>
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-md text-xs font-bold">
                                                                            <i class="bi bi-currency-dollar"></i>
                                                                            {{ number_format($product['price'] ?? 0, 2, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Subtotal:</span>
                                                                        <span class="text-sm font-black text-purple-600 dark:text-purple-400">
                                                                            R$ {{ number_format(($product['price'] ?? 0) * ($product['quantity'] ?? 0), 2, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Modais de Participantes -->
    @if ($showToggleParticipantModal && $selectedParticipantId)
        @php
            $selectedParticipant = $this->participants->firstWhere('id', $selectedParticipantId);
        @endphp
        @if($selectedParticipant)
            <div x-data="{ modalOpen: true }" x-show="modalOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[99999] overflow-y-auto"
                @keydown.escape.window="modalOpen = false; $wire.set('showToggleParticipantModal', false)">

                <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-900/40 backdrop-blur-md"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                        class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                        <div class="absolute inset-0 bg-gradient-to-br from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-500/5 via-transparent to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-500/5"></div>

                        <div class="relative z-10">
                            <div class="text-center pt-8 pb-4">
                                <div class="relative inline-flex items-center justify-center">
                                    <div class="absolute w-24 h-24 bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-400/30 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-500/30 rounded-full animate-pulse"></div>
                                    <div class="relative w-16 h-16 bg-gradient-to-br from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-500 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-600 rounded-full flex items-center justify-center shadow-lg">
                                        <i class="bi bi-{{ $selectedParticipant->status === 'active' ? 'pause-circle' : 'play-circle' }} text-2xl text-white"></i>
                                    </div>
                                </div>

                                <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                    {{ $selectedParticipant->status === 'active' ? 'Desativar' : 'Reativar' }} Participante?
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                    {{ $selectedParticipant->client->name ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="px-8 pb-4">
                                <div class="bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-50 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-50 dark:from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-900/20 dark:to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-900/20 rounded-2xl p-4 border border-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-200/50">
                                    <div class="text-center">
                                        @if($selectedParticipant->status === 'active')
                                            <i class="bi bi-pause-circle text-3xl text-orange-500 mb-2"></i>
                                            <p class="text-gray-700 dark:text-gray-300">
                                                O participante será marcado como <span class="font-bold text-orange-600">desistente</span>.
                                            </p>
                                        @else
                                            <i class="bi bi-play-circle text-3xl text-emerald-500 mb-2"></i>
                                            <p class="text-gray-700 dark:text-gray-300">
                                                O participante voltará ao status <span class="font-bold text-emerald-600">ativo</span>.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="px-8 pb-8">
                                <div class="flex gap-4">
                                    <button wire:click="$set('showToggleParticipantModal', false)" @click="modalOpen = false"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                                    </button>

                                    <button wire:click="toggleParticipantStatus" @click="modalOpen = false"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-500 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-600 hover:from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'emerald' }}-600 hover:to-{{ $selectedParticipant->status === 'active' ? 'red' : 'teal' }}-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <i class="bi bi-{{ $selectedParticipant->status === 'active' ? 'pause-circle' : 'play-circle' }} mr-2"></i>
                                        {{ $selectedParticipant->status === 'active' ? 'Desativar' : 'Reativar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if ($showDeleteParticipantModal && $selectedParticipantId)
        @php
            $selectedParticipant = $this->participants->firstWhere('id', $selectedParticipantId);
        @endphp
        @if($selectedParticipant)
            <div x-data="{ modalOpen: true }" x-show="modalOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 z-[99999] overflow-y-auto"
                @keydown.escape.window="modalOpen = false; $wire.set('showDeleteParticipantModal', false)">

                <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                        class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                        <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl"></div>

                        <div class="relative z-10">
                            <div class="text-center pt-8 pb-4">
                                <div class="relative inline-flex items-center justify-center">
                                    <div class="absolute w-24 h-24 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-full animate-pulse"></div>
                                    <div class="absolute w-20 h-20 bg-gradient-to-r from-red-500/40 to-pink-600/40 rounded-full animate-ping"></div>
                                    <div class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                        <i class="bi bi-exclamation-triangle text-2xl text-white animate-bounce"></i>
                                    </div>
                                </div>

                                <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                    <i class="bi bi-shield-exclamation text-red-500 mr-2"></i>
                                    Excluir Participante?
                                </h3>
                            </div>

                            <div class="px-8 pb-4">
                                <div class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-4 border border-red-200/50">
                                    <div class="text-center">
                                        <i class="bi bi-person text-3xl text-red-500 mb-2"></i>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            Você está prestes a remover:
                                        </p>
                                        <p class="font-bold text-red-600 dark:text-red-400 text-lg mt-1">
                                            "{{ $selectedParticipant->client->name ?? 'N/A' }}"
                                        </p>
                                    </div>
                                    <div class="mt-4 p-3 {{ $selectedParticipant->payments->where('status', 'paid')->count() > 0 ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700' }} rounded-lg border">
                                        @if($selectedParticipant->payments->where('status', 'paid')->count() > 0)
                                            <p class="text-sm text-orange-800 dark:text-orange-300">
                                                ⚠️ Este participante já realizou <span class="font-bold">{{ $selectedParticipant->payments->where('status', 'paid')->count() }} pagamento(s)</span>.
                                            </p>
                                            <p class="text-sm text-orange-700 dark:text-orange-400 mt-2">
                                                Ele será marcado como <strong>"Desistente"</strong> e seus dados preservados para histórico.
                                            </p>
                                        @else
                                            <p class="text-sm text-amber-800 dark:text-amber-300">
                                                Este participante não possui pagamentos.
                                            </p>
                                            <p class="text-sm text-amber-700 dark:text-amber-400 mt-2">
                                                Será <strong>permanentemente excluído</strong> do sistema.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="px-8 pb-8">
                                <div class="flex gap-4">
                                    <button wire:click="$set('showDeleteParticipantModal', false)" @click="modalOpen = false"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                                    </button>

                                    <button wire:click="removeParticipant({{ $selectedParticipantId }})" @click="modalOpen = false"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border-2 border-red-400/50">
                                        <i class="bi bi-trash3 mr-2"></i>
                                        {{ $selectedParticipant->payments->where('status', 'paid')->count() > 0 ? 'Marcar Desistente' : 'Excluir' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Componentes de Modal (renderizados fora do header) -->
    @livewire('consortiums.export-consortium')
    @livewire('consortiums.add-participant', ['consortium' => $this->consortium], key('add-participant-modal-'.$this->consortium->id))
    @livewire('consortiums.delete-consortium', ['consortium' => $this->consortium], key('delete-consortium-modal-'.$this->consortium->id))
</div>
