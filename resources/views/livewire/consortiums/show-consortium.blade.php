<div class="show-consortium-page w-full mobile-393-base">
    @php
        $c = $this->consortium;
        $sum = $this->summary;
        $isDraw = $c->mode !== 'payoff';
        $pct = $sum['goal'] > 0 ? min(100, round($sum['collected'] / $sum['goal'] * 100)) : 0;
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $statusPill = match ($c->status) {
            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
            'cancelled' => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
            default => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        };
        // Por que o sorteio não está liberado (frase curta, sem tooltip)
        $drawHint = null;
        if ($isDraw && !$sum['can_draw']) {
            $drawHint = match (true) {
                $c->status !== 'active' => 'Consórcio ' . mb_strtolower($c->status_label) . ': sem sorteio.',
                $c->start_date && now()->lt($c->start_date) => 'Começa em ' . $c->start_date->format('d/m/Y') . '.',
                $sum['members'] === 0 => 'Adicione participantes para sortear.',
                $sum['eligible'] === 0 && $sum['contemplated'] >= $sum['members'] => 'Todos já foram contemplados.',
                $sum['eligible'] === 0 => 'Ninguém apto: precisa ter 1 parcela paga e nada vencido há mais de 30 dias.',
                ($sum['next_draw_in'] ?? 0) > 0 => 'Próximo sorteio libera em ' . $sum['next_draw_in'] . ' dia' . ($sum['next_draw_in'] > 1 ? 's' : '') . '.',
                default => null,
            };
        }
        $tabs = [
            'overview' => ['label' => 'Visão geral', 'icon' => 'bi-grid', 'badge' => null],
            'participants' => ['label' => 'Participantes', 'icon' => 'bi-people', 'badge' => $sum['members']],
            'payments' => ['label' => 'Parcelas', 'icon' => 'bi-wallet2', 'badge' => $sum['late_count'] ?: null, 'badgeClass' => 'bg-rose-500 text-white'],
            'draws' => ['label' => 'Sorteios', 'icon' => 'bi-shuffle', 'badge' => $sum['draws'] ?: null],
            'contemplated' => ['label' => 'Contemplados', 'icon' => 'bi-trophy', 'badge' => $sum['contemplated'] ?: null],
        ];
        if (!$isDraw) {
            unset($tabs['draws']);
        }
    @endphp

    {{-- Cabeçalho no padrão das telas de cliente --}}
    <div class="relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.12),transparent_32%)]"></div>
        <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

        <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <a href="{{ route('consortiums.index') }}" title="Voltar"
                       class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                        <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                    </a>
                    <div class="hidden sm:flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-purple-500/25">
                        <i class="bi bi-piggy-bank text-white text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <a href="{{ route('consortiums.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-piggy-bank mr-1"></i>Consórcios</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-indigo-600 dark:text-indigo-300">Detalhes</span>
                        </nav>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">{{ $c->name }}</h1>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statusPill }}">{{ $c->status_label }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                            <span class="inline-flex items-center gap-1"><i class="bi bi-cash"></i>{{ $money($c->monthly_value) }}/mês</span>
                            <span class="inline-flex items-center gap-1"><i class="bi bi-hourglass-split"></i>{{ $c->duration_months }} meses</span>
                            <span class="inline-flex items-center gap-1"><i class="bi bi-calendar3"></i>Início {{ $c->start_date?->format('d/m/Y') }}</span>
                            <span class="inline-flex items-center gap-1"><i class="bi {{ $isDraw ? 'bi-shuffle' : 'bi-check2-all' }}"></i>{{ $isDraw ? 'Sorteio ' . mb_strtolower($c->draw_frequency_label) : 'Resgate por quitação' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    @if ($isDraw)
                        @if ($sum['can_draw'])
                            <a href="{{ route('consortiums.draw', $c) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-semibold shadow-md shadow-indigo-500/25 transition">
                                <i class="bi bi-shuffle"></i>Fazer sorteio
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-200/80 dark:bg-slate-700/70 text-slate-500 dark:text-slate-400 text-sm font-semibold cursor-not-allowed" title="{{ $drawHint }}">
                                <i class="bi bi-shuffle"></i>Fazer sorteio
                            </span>
                        @endif
                    @endif
                    @if ($c->canAddParticipants())
                        <a href="{{ route('consortiums.add-participants', $c) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-indigo-200 dark:border-indigo-500/40 bg-white/90 dark:bg-slate-900/70 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-sm font-semibold shadow-sm transition">
                            <i class="bi bi-person-plus"></i>Participantes
                        </a>
                    @endif
                    <a href="{{ route('consortiums.edit', $c) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white/90 dark:bg-slate-900/70 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold shadow-sm transition">
                        <i class="bi bi-pencil"></i>Editar
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false" title="Mais ações"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 bg-white/90 dark:bg-slate-900/70 text-slate-600 dark:text-slate-300 shadow-sm hover:text-indigo-600">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div x-show="open" x-cloak x-transition
                            class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl text-sm">
                            <button type="button" @click="open = false" wire:click="$dispatch('openExportModal', { consortiumId: {{ $c->id }} })"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60"><i class="bi bi-download text-indigo-500"></i>Exportar</button>
                            <button type="button" @click="open = false" wire:click="$dispatch('openToggleConsortiumModal', { consortiumId: {{ $c->id }} })"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60">
                                @if ($c->status === 'active')<i class="bi bi-pause-circle text-amber-500"></i>Desativar @else<i class="bi bi-play-circle text-emerald-500"></i>Ativar @endif
                            </button>
                            <button type="button" @click="open = false" wire:click="$dispatch('openDeleteConsortiumModal', { consortiumId: {{ $c->id }} })"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 border-t border-slate-100 dark:border-slate-700"><i class="bi bi-trash"></i>Excluir</button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($c->description || $drawHint)
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    @if ($drawHint)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200/70 dark:border-amber-500/30 px-2.5 py-1 font-medium text-amber-700 dark:text-amber-300"><i class="bi bi-info-circle"></i>{{ $drawHint }}</span>
                    @endif
                    @if ($c->description)
                        <span class="text-slate-500 dark:text-slate-400">{{ $c->description }}</span>
                    @endif
                </div>
            @endif

            <div class="mt-4 -mx-1 overflow-x-auto">
                <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                    @foreach ($tabs as $key => $tab)
                        <button type="button" wire:click="setTab('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                                {{ $activeTab === $key
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                            <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                            @if ($tab['badge'])
                                <span class="ml-0.5 rounded-full px-1.5 text-[10px] font-bold leading-4 {{ $activeTab === $key ? 'bg-white/25 text-white' : ($tab['badgeClass'] ?? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300') }}">{{ $tab['badge'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    {{-- Números principais --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="rounded-2xl border border-indigo-200/70 dark:border-indigo-500/30 bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-500/10 dark:to-slate-900 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow"><i class="bi bi-people"></i></span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Participantes</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $sum['members'] }}<span class="text-sm font-semibold text-slate-400">/{{ $c->max_participants }}</span></p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-amber-200/70 dark:border-amber-500/30 bg-gradient-to-br from-amber-50 to-white dark:from-amber-500/10 dark:to-slate-900 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow"><i class="bi bi-trophy"></i></span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Contemplados</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $sum['contemplated'] }}<span class="text-sm font-semibold text-slate-400">/{{ $sum['members'] }}</span></p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-emerald-200/70 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-500/10 dark:to-slate-900 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"><i class="bi bi-cash-stack"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Arrecadado · {{ $pct }}%</p>
                    <p class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white truncate">{{ $money($sum['collected']) }}</p>
                </div>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-900/40"><div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $pct }}%"></div></div>
        </div>
        <div class="rounded-2xl border {{ $sum['late_count'] ? 'border-rose-200/70 dark:border-rose-500/30 from-rose-50' : 'border-slate-200/70 dark:border-slate-700 from-slate-50' }} bg-gradient-to-br to-white dark:from-slate-800/40 dark:to-slate-900 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $sum['late_count'] ? 'bg-gradient-to-br from-rose-500 to-red-600' : 'bg-gradient-to-br from-slate-400 to-slate-500' }} text-white shadow"><i class="bi {{ $sum['late_count'] ? 'bi-exclamation-triangle' : 'bi-check2-circle' }}"></i></span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Parcelas vencidas</p>
                    @if ($sum['late_count'])
                        <p class="text-lg sm:text-xl font-bold text-rose-600 dark:text-rose-400 truncate">{{ $sum['late_count'] }} · {{ $money($sum['late_amount']) }}</p>
                    @else
                        <p class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400">Nenhuma</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
        <div class="p-4 sm:p-6">
            @if ($activeTab === 'overview')
                @php
                    $lastDraw = $isDraw ? $c->draws()->with('winner.client')->orderByDesc('draw_date')->first() : null;
                    $recentPayments = \App\Models\ConsortiumPayment::whereIn('consortium_participant_id', $c->participants()->select('id'))
                        ->with('participant.client')->where('status', 'paid')->orderByDesc('payment_date')->limit(5)->get();
                    $upcoming = $isDraw ? $c->getUpcomingDrawDates(3) : [];
                    $row = 'flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 dark:border-slate-800 last:border-0 text-sm';
                @endphp
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 p-4">
                        <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-info-circle text-indigo-500"></i>Como funciona este consórcio</h3>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Mensalidade</span><span class="font-semibold text-slate-900 dark:text-white">{{ $money($c->monthly_value) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Duração</span><span class="font-semibold text-slate-900 dark:text-white">{{ $c->duration_months }} meses</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Cada um paga no total</span><span class="font-semibold text-slate-900 dark:text-white">{{ $money($c->monthly_value * $c->duration_months) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Início</span><span class="font-semibold text-slate-900 dark:text-white">{{ $c->start_date?->format('d/m/Y') }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Modo</span><span class="font-semibold text-slate-900 dark:text-white">{{ $isDraw ? 'Sorteio ' . mb_strtolower($c->draw_frequency_label) : 'Quitou, levou' }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Vagas livres</span><span class="font-semibold text-slate-900 dark:text-white">{{ $c->getRemainingSlots() }} de {{ $c->max_participants }}</span></div>
                    </section>

                    <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 p-4">
                        <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-cash-stack text-emerald-500"></i>Dinheiro</h3>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Previsto (participantes atuais)</span><span class="font-semibold text-slate-900 dark:text-white">{{ $money($sum['goal']) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Já recebido</span><span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($sum['collected']) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">A receber</span><span class="font-semibold text-amber-600 dark:text-amber-400">{{ $money($sum['pending_amount']) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Vencido</span><span class="font-semibold {{ $sum['late_amount'] ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $money($sum['late_amount']) }}</span></div>
                        <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">Parcelas pagas</span><span class="font-semibold text-slate-900 dark:text-white">{{ $sum['paid_count'] }} de {{ $sum['total_count'] }}</span></div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $pct }}%"></div></div>
                    </section>

                    <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 p-4">
                        @if ($isDraw)
                            <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-shuffle text-purple-500"></i>Sorteios</h3>
                            @if ($lastDraw)
                                <div class="rounded-xl bg-purple-50 dark:bg-purple-500/10 p-3 text-sm">
                                    <p class="text-xs text-purple-700 dark:text-purple-300">Último · #{{ $lastDraw->draw_number }} em {{ $lastDraw->draw_date->format('d/m/Y') }}</p>
                                    <p class="font-semibold text-slate-900 dark:text-white"><i class="bi bi-trophy-fill text-amber-500 mr-1"></i>{{ $lastDraw->winner->client->name ?? '—' }}</p>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum sorteio feito ainda.</p>
                            @endif
                            @if (count($upcoming) && $c->status === 'active')
                                <p class="mt-3 mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Próximos previstos</p>
                                @foreach ($upcoming as $u)
                                    <div class="{{ $row }}"><span class="text-slate-500 dark:text-slate-400">#{{ $u['draw_number'] }}</span><span class="font-semibold text-slate-900 dark:text-white">{{ $u['date']->format('d/m/Y') }}</span></div>
                                @endforeach
                            @endif
                        @else
                            <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-check2-all text-purple-500"></i>Quitou, levou</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Quem paga todas as parcelas é contemplado na hora e pode resgatar os produtos.</p>
                        @endif
                    </section>
                </div>

                <section class="mt-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 p-4">
                    <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-clock-history text-indigo-500"></i>Últimos pagamentos</h3>
                    @forelse ($recentPayments as $payment)
                        <div class="{{ $row }}">
                            <span class="min-w-0 truncate text-slate-700 dark:text-slate-200">{{ $payment->participant->client->name ?? '—' }} <span class="text-slate-400">· {{ $payment->reference_month_name }}/{{ $payment->reference_year }}</span></span>
                            <span class="shrink-0 text-right"><span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</span> <span class="text-xs text-slate-400">{{ optional($payment->payment_date)->format('d/m') }}</span></span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum pagamento registrado ainda.</p>
                    @endforelse
                </section>

            @elseif ($activeTab === 'participants')
                @if ($this->participants->isEmpty())
                    <div class="py-12 text-center">
                        <i class="bi bi-people text-5xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-3 font-semibold text-slate-700 dark:text-slate-200">Nenhum participante ainda</p>
                        @if ($c->canAddParticipants())
                            <a href="{{ route('consortiums.add-participants', $c) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md"><i class="bi bi-person-plus"></i>Adicionar participantes</a>
                        @endif
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($this->participants as $participant)
                            @php
                                $paidN = $participant->payments->where('status', 'paid')->count();
                                $lateN = $participant->payments->filter->is_late->count();
                                $ppct = $c->duration_months ? round($paidN / $c->duration_months * 100) : 0;
                                $pStatus = match (true) {
                                    $participant->status === 'quit' => ['Desistiu', 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
                                    $participant->is_contemplated => ['Contemplado', 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'],
                                    $lateN > 0 => ['Em atraso', 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'],
                                    default => ['Em dia', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'],
                                };
                            @endphp
                            <div wire:key="participant-{{ $participant->id }}" class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center {{ $participant->status === 'quit' ? 'opacity-60' : '' }}">
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $participant->participation_number }}</span>
                                    @if ($participant->client)
                                        <x-client-avatar :client="$participant->client" size="w-10 h-10 text-sm" rounded="rounded-full" />
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ $participant->client ? route('clients.consortiums', $participant->client) : '#' }}" class="block truncate font-semibold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300">{{ $participant->client->name ?? 'Cliente removido' }}</a>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">Entrou em {{ optional($participant->entry_date)->format('d/m/Y') }}@if($participant->client?->phone) · {{ $participant->client->phone }}@endif</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 sm:w-[46%]">
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $pStatus[1] }}">
                                        @if ($participant->is_contemplated)<i class="bi bi-trophy-fill"></i>@endif{{ $pStatus[0] }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex justify-between text-xs"><span class="text-slate-500 dark:text-slate-400">{{ $paidN }}/{{ $c->duration_months }} pagas</span><span class="font-semibold text-slate-700 dark:text-slate-200">{{ $money($participant->total_paid) }}</span></div>
                                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full {{ $lateN ? 'bg-rose-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}" style="width: {{ $ppct }}%"></div></div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <button type="button" wire:click="openPayments({{ $participant->id }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" title="Ver parcelas"><i class="bi bi-wallet2"></i></button>
                                        @if (!$participant->is_contemplated)
                                            <button type="button" wire:click="confirmToggleParticipant({{ $participant->id }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 {{ $participant->status === 'active' ? 'hover:text-amber-600' : 'hover:text-emerald-600' }}" title="{{ $participant->status === 'active' ? 'Marcar como desistente' : 'Reativar' }}"><i class="bi {{ $participant->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i></button>
                                            <button type="button" wire:click="confirmDeleteParticipant({{ $participant->id }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="Remover"><i class="bi bi-trash"></i></button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            @elseif ($activeTab === 'payments')
                @if ($this->payments->isEmpty())
                    <div class="py-12 text-center">
                        <i class="bi bi-wallet2 text-5xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-3 font-semibold text-slate-700 dark:text-slate-200">Nenhuma parcela ainda</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">As parcelas aparecem quando você adiciona participantes.</p>
                    </div>
                @else
                    <div class="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
                        @foreach (['all' => 'Todos', 'late' => 'Com vencidas', 'pending' => 'Com pendentes', 'done' => 'Quitados'] as $k => $label)
                            <button type="button" wire:click="$set('paymentsFilter', '{{ $k }}')"
                                class="rounded-full px-3 py-1 transition {{ $paymentsFilter === $k ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-700' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="space-y-3">
                        @foreach ($this->payments as $participant)
                            @php
                                $total = $participant->payments->count();
                                $paid = $participant->payments->where('status', 'paid')->count();
                                $overdue = $participant->payments->filter->is_late->count();
                                $pending = $total - $paid;
                                $show = match ($paymentsFilter) {
                                    'late' => $overdue > 0,
                                    'pending' => $pending > 0,
                                    'done' => $pending === 0,
                                    default => true,
                                };
                                $open = (int) $expandedParticipant === (int) $participant->id;
                                $next = $participant->payments->where('status', '!=', 'paid')->sortBy('due_date')->first();
                            @endphp
                            @continue(!$show)
                            <div wire:key="pay-group-{{ $participant->id }}" class="overflow-hidden rounded-2xl border {{ $open ? 'border-indigo-300 dark:border-indigo-500/50 shadow-md' : 'border-slate-200/80 dark:border-slate-700/70' }}">
                                <button type="button" wire:click="togglePayments({{ $participant->id }})" class="flex w-full flex-col gap-3 p-4 text-left sm:flex-row sm:items-center hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                    <div class="flex min-w-0 flex-1 items-center gap-3">
                                        @if ($participant->client)
                                            <x-client-avatar :client="$participant->client" size="w-10 h-10 text-sm" rounded="rounded-full" />
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $participant->client->name ?? 'Cliente removido' }} <span class="text-xs font-normal text-slate-400">#{{ $participant->participation_number }}</span></p>
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                                @if ($next) Próxima: {{ $next->due_date->format('d/m/Y') }} · {{ $money($next->amount) }} @else Todas as parcelas pagas @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-semibold">
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-emerald-700 dark:text-emerald-300">{{ $paid }}/{{ $total }} pagas</span>
                                        @if ($overdue)
                                            <span class="rounded-full bg-rose-50 dark:bg-rose-500/10 px-2.5 py-1 text-rose-700 dark:text-rose-300">{{ $overdue }} vencida{{ $overdue > 1 ? 's' : '' }}</span>
                                        @endif
                                        <span class="hidden sm:inline text-slate-700 dark:text-slate-200">{{ $money($participant->total_paid) }}</span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform {{ $open ? 'rotate-180' : '' }}"></i>
                                    </div>
                                </button>

                                @if ($open)
                                    <div class="border-t border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($participant->payments as $payment)
                                            <div wire:key="pay-{{ $payment->id }}" class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between {{ $payment->is_late ? 'bg-rose-50/50 dark:bg-rose-500/5' : '' }}">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200">{{ str_pad($payment->reference_month, 2, '0', STR_PAD_LEFT) }}</span>
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $payment->reference_month_name }}/{{ $payment->reference_year }}
                                                            <span class="ml-1 inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-bold align-middle {{ $payment->status_color }}">{{ $payment->status_label }}{{ $payment->is_late ? ' há ' . $payment->days_late . 'd' : '' }}</span>
                                                        </p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">Vence {{ optional($payment->due_date)->format('d/m/Y') }}@if($payment->payment_date) · paga em {{ $payment->payment_date->format('d/m/Y') }}@if($payment->payment_method) ({{ $payment->payment_method }})@endif @endif</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between gap-4 sm:justify-end">
                                                    <span class="text-base font-bold text-slate-900 dark:text-white">{{ $money($payment->amount) }}</span>
                                                    <div class="min-w-[120px] text-right">
                                                        @if ($payment->status !== 'paid')
                                                            @livewire('consortiums.record-payment', ['payment' => $payment], key('payment-'.$participant->id.'-'.$payment->id))
                                                        @else
                                                            @livewire('consortiums.cancel-payment', ['payment' => $payment], key('cancel-payment-'.$participant->id.'-'.$payment->id))
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            @elseif ($activeTab === 'draws')
                @if ($this->draws->isEmpty())
                    <div class="py-12 text-center">
                        <i class="bi bi-shuffle text-5xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-3 font-semibold text-slate-700 dark:text-slate-200">Nenhum sorteio feito ainda</p>
                        @if ($drawHint)<p class="text-sm text-slate-500 dark:text-slate-400">{{ $drawHint }}</p>@endif
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($this->draws as $draw)
                            <div class="flex items-center justify-between gap-3 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 text-white"><i class="bi bi-trophy-fill"></i></span>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">Sorteio #{{ $draw->draw_number }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $draw->draw_date->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if ($draw->winner)
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $draw->winner->client->name ?? '—' }}</p>
                                        <p class="text-xs text-indigo-600 dark:text-indigo-300">Participação #{{ $draw->winner->participation_number }}</p>
                                    @else
                                        <span class="rounded-lg bg-slate-100 dark:bg-slate-800 px-2 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300">Sem vencedor</span>
                                    @endif
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
                            <div class="rounded-2xl border border-amber-200/80 dark:border-amber-700/50 bg-gradient-to-br from-amber-50/80 to-white dark:from-amber-900/10 dark:to-slate-900 p-5">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="w-12 h-12 shrink-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-900 dark:text-slate-100">{{ $participant->client->name ?? 'N/A' }}</p>
                                            <p class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">Participação #{{ $participant->participation_number }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if($participant->contemplation && $participant->contemplation->status !== 'redeemed' && in_array($participant->contemplation->redemption_type ?? 'pending', ['cash', 'pending']))
                                            <button type="button" wire:click="markCashRedeemed({{ $participant->contemplation->id }})"
                                                wire:confirm="Confirmar que o valor em dinheiro foi entregue a {{ $participant->client->name ?? 'este participante' }}?"
                                                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-100 rounded-lg hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 transition-colors">
                                                <i class="bi bi-cash-coin"></i><span>Pago em dinheiro</span>
                                            </button>
                                        @endif
                                        @if($participant->contemplation && ($participant->contemplation->status === 'pending' || ($participant->contemplation->status === 'redeemed' && $participant->contemplation->redemption_type === 'products')) && in_array($participant->contemplation->redemption_type ?? 'pending', ['pending', 'products']))
                                            <a href="{{ route('consortiums.contemplation.products', $participant->contemplation) }}"
                                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 transition-colors">
                                                <i class="bi bi-{{ $participant->contemplation->products ? 'pencil-square' : 'box-seam' }} text-base"></i>
                                                <span>{{ $participant->contemplation->products ? 'Editar produtos' : 'Resgatar em produtos' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-slate-400">Data Contemplação:</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ optional($participant->contemplation_date ?? $participant->contemplation?->contemplation_date)->format('d/m/Y') ?? '—' }}</span>
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
                                            @php
                                                $redeemed = collect($participant->contemplation->products);
                                                $images = \App\Models\Product::whereIn('id', $redeemed->pluck('product_id')->filter())->pluck('image', 'id');
                                            @endphp
                                            <div class="mt-4 border-t border-amber-200/70 pt-3 dark:border-amber-500/20">
                                                <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><i class="bi bi-box-seam text-purple-500"></i>Produtos resgatados</p>
                                                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200/70 bg-white dark:divide-slate-800 dark:border-slate-700 dark:bg-slate-900">
                                                    @foreach($redeemed as $product)
                                                        @php $img = $images[$product['product_id'] ?? 0] ?? null; @endphp
                                                        <div class="flex items-center gap-3 px-3 py-2">
                                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-purple-50 dark:bg-purple-500/10">
                                                                @if($img)
                                                                    <img src="{{ asset('storage/products/' . $img) }}" alt="" class="h-full w-full object-cover">
                                                                @else
                                                                    <i class="bi bi-box-seam text-purple-400"></i>
                                                                @endif
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $product['product_name'] ?? $product['name'] ?? 'Produto' }}</p>
                                                                <p class="text-xs text-slate-500">{{ $product['quantity'] ?? 0 }} × R$ {{ number_format($product['price'] ?? 0, 2, ',', '.') }}</p>
                                                            </div>
                                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">R$ {{ number_format(($product['price'] ?? 0) * ($product['quantity'] ?? 0), 2, ',', '.') }}</span>
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

                <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-900/40 backdrop-blur-md"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                        class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                        <div class="absolute inset-0 bg-gradient-to-br from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-500/5 via-transparent to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-500/5"></div>

                        <div class="relative z-10">
                            <div class="text-center pt-8 pb-4">
                                <div class="relative inline-flex items-center justify-center">
                                    <div class="absolute w-24 h-24 bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-400/30 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-500/30 rounded-full animate-pulse"></div>
                                    <div class="relative w-16 h-16 bg-gradient-to-br from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-500 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-600 rounded-full flex items-center justify-center shadow-lg">
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
                                <div class="bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-50 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-50 dark:from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-900/20 dark:to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-900/20 rounded-2xl p-4 border border-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-200/50">
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
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-500 to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-600 hover:from-{{ $selectedParticipant->status === 'active' ? 'orange' : 'indigo' }}-600 hover:to-{{ $selectedParticipant->status === 'active' ? 'red' : 'purple' }}-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
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
    @livewire('consortiums.delete-consortium', ['consortium' => $this->consortium], key('delete-consortium-modal-'.$this->consortium->id))
</div>
