@props(['consortium'])

@php
    // Card no mesmo padrão dos cards de Clientes. Os números vêm do withCount
    // da lista; se o card for usado fora dela, cai nas consultas do model.
    $c = $consortium;
    $members = $c->members_count ?? $c->active_participants_count;
    $contemplated = $c->contemplated_total ?? $c->contemplated_count;
    $collected = (float) ($c->collected_total ?? $c->total_collected);
    $overdue = (int) ($c->overdue_total ?? $c->getOverduePaymentsCount());
    $goal = (float) $c->monthly_value * (int) $c->duration_months * max(1, (int) $members);
    $pct = $goal > 0 ? min(100, round($collected / $goal * 100)) : 0;
    $slots = max(0, (int) $c->max_participants - (int) $members);
    $isDraw = $c->mode !== 'payoff';

    $style = match ($c->status) {
        'completed' => ['band' => 'from-emerald-400 via-teal-500 to-cyan-500', 'pill' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300', 'icon' => 'bi-check-circle-fill'],
        'cancelled' => ['band' => 'from-slate-300 via-slate-400 to-slate-500', 'pill' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300', 'icon' => 'bi-pause-circle-fill'],
        default => ['band' => 'from-indigo-500 via-purple-500 to-fuchsia-500', 'pill' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300', 'icon' => 'bi-play-circle-fill'],
    };

    // Situação do sorteio sem consulta extra
    $drawChip = null;
    if ($isDraw && $c->status === 'active') {
        $startsIn = $c->start_date && now()->lt($c->start_date) ? (int) ceil(now()->startOfDay()->diffInDays($c->start_date->copy()->startOfDay())) : 0;
        $eligible = $c->eligible_total ?? $c->eligibleParticipantsCount();
        if ($startsIn > 0) {
            $drawChip = ['text' => 'Começa em ' . $startsIn . ' dia' . ($startsIn > 1 ? 's' : ''), 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300', 'icon' => 'bi-hourglass-split'];
        } elseif ($eligible === 0) {
            $drawChip = ['text' => !$members ? 'Sem participantes' : ($contemplated >= $members ? 'Todos contemplados' : 'Ninguém apto ao sorteio'), 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', 'icon' => 'bi-people'];
        } else {
            $last = $c->last_draw_at ? \Carbon\Carbon::parse($c->last_draw_at) : null;
            $wait = $last ? max(0, (int) ceil($c->frequencyDays() * 0.8) - (int) floor($last->copy()->startOfDay()->diffInDays(now()->startOfDay()))) : 0;
            $drawChip = $wait > 0
                ? ['text' => 'Próximo sorteio em ' . $wait . ' dia' . ($wait > 1 ? 's' : ''), 'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300', 'icon' => 'bi-calendar-event']
                : ['text' => 'Sorteio liberado', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300', 'icon' => 'bi-shuffle', 'ready' => true];
        }
    }
    $iconBtn = 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition';
@endphp

<div class="consortium-card-v2 group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-500/60 transition-all duration-300">
    <div class="h-1.5 w-full bg-gradient-to-r {{ $style['band'] }}"></div>

    <div class="flex flex-1 flex-col p-4">
        {{-- Topo: modo + status --}}
        <div class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                <i class="bi {{ $isDraw ? 'bi-shuffle' : 'bi-check2-all' }} text-[10px]"></i>{{ $isDraw ? 'Sorteio ' . mb_strtolower($c->draw_frequency_label) : 'Por quitação' }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $style['pill'] }}">
                <i class="bi {{ $style['icon'] }} text-[10px]"></i>{{ $c->status_label }}
            </span>
        </div>

        {{-- Identidade --}}
        <a href="{{ route('consortiums.show', $c) }}" class="mt-3 flex items-center gap-3 min-w-0">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-md shadow-purple-500/20 group-hover:scale-105 transition-transform">
                <i class="bi bi-piggy-bank text-xl text-white"></i>
            </div>
            <div class="min-w-0">
                <h3 class="line-clamp-2 text-base font-bold leading-snug text-slate-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors" title="{{ $c->name }}">{{ $c->name }}</h3>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                    <i class="bi bi-calendar3 mr-0.5"></i>{{ $c->duration_months }} meses · início {{ $c->start_date?->format('d/m/Y') }}
                </p>
            </div>
        </a>

        @if($c->description)
            <p class="mt-2 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $c->description }}</p>
        @endif

        {{-- Números --}}
        <div class="mt-3 grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 py-2 text-center">
            <div class="px-1">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mensal</p>
                <p class="text-sm font-bold leading-6 text-indigo-700 dark:text-indigo-300 truncate">R$ {{ number_format((float) $c->monthly_value, fmod((float) $c->monthly_value, 1) ? 2 : 0, ',', '.') }}</p>
            </div>
            <div class="px-1">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Vagas</p>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $members }}<span class="text-xs font-semibold text-slate-400">/{{ $c->max_participants }}</span></p>
            </div>
            <div class="px-1">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Contempl.</p>
                <p class="text-base font-bold text-amber-600 dark:text-amber-400">{{ $contemplated }}</p>
            </div>
        </div>

        {{-- Arrecadação --}}
        <div class="mt-3">
            <div class="flex items-baseline justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Arrecadado</span>
                <span class="font-semibold text-slate-700 dark:text-slate-200">R$ {{ number_format($collected, 0, ',', '.') }} <span class="font-normal text-slate-400">de {{ number_format($goal, 0, ',', '.') }}</span></span>
            </div>
            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        {{-- Situação --}}
        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px] font-medium">
            @if($drawChip)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $drawChip['class'] }}"><i class="bi {{ $drawChip['icon'] }}"></i>{{ $drawChip['text'] }}</span>
            @endif
            @if($overdue > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-rose-700 dark:text-rose-300"><i class="bi bi-exclamation-circle"></i>{{ $overdue }} parcela{{ $overdue > 1 ? 's' : '' }} vencida{{ $overdue > 1 ? 's' : '' }}</span>
            @elseif($members > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-emerald-700 dark:text-emerald-300"><i class="bi bi-check2-circle"></i>Pagamentos em dia</span>
            @endif
            @if($c->status === 'active' && $slots > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 dark:bg-sky-500/10 px-2 py-0.5 text-sky-700 dark:text-sky-300"><i class="bi bi-person-plus"></i>{{ $slots }} vaga{{ $slots > 1 ? 's' : '' }}</span>
            @endif
        </div>

        {{-- Ações principais --}}
        <div class="mt-auto pt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('consortiums.show', $c) }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                <i class="bi bi-eye"></i>Abrir
            </a>
            @if($drawChip['ready'] ?? false)
                <a href="{{ route('consortiums.draw', $c) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-purple-200 dark:border-purple-500/40 bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 dark:hover:bg-purple-500/20 px-3 py-2 text-xs font-semibold text-purple-700 dark:text-purple-300 transition">
                    <i class="bi bi-shuffle"></i>Sortear
                </a>
            @else
                <a href="{{ route('consortiums.add-participants', $c) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-indigo-200 dark:border-indigo-500/40 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-3 py-2 text-xs font-semibold text-indigo-700 dark:text-indigo-300 transition {{ $slots === 0 || $c->status !== 'active' ? 'pointer-events-none opacity-40' : '' }}">
                    <i class="bi bi-person-plus"></i>Participantes
                </a>
            @endif
        </div>

        {{-- Ações rápidas --}}
        <div class="mt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-2">
            <a href="{{ route('consortiums.edit', $c) }}" class="{{ $iconBtn }} hover:text-indigo-600" title="Editar"><i class="bi bi-pencil"></i></a>
            <a href="{{ route('consortiums.add-participants', $c) }}" class="{{ $iconBtn }} hover:text-sky-600" title="Adicionar participantes"><i class="bi bi-person-plus"></i></a>
            @if($isDraw)
                <a href="{{ route('consortiums.draw', $c) }}" class="{{ $iconBtn }} hover:text-purple-600" title="Sorteio"><i class="bi bi-shuffle"></i></a>
            @endif
            <button type="button" wire:click="$dispatch('openExportModal', { consortiumId: {{ $c->id }} })" class="{{ $iconBtn }} hover:text-emerald-600" title="Exportar"><i class="bi bi-download"></i></button>
            @if(($members ?? 0) === 0 && (int) ($c->draws_count ?? 0) === 0)
                <button type="button" wire:click="confirmDelete({{ $c->id }})" class="{{ $iconBtn }} hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10" title="Excluir"><i class="bi bi-trash"></i></button>
            @else
                <span class="{{ $iconBtn }} opacity-30 cursor-not-allowed" title="Tem participantes ou sorteios: desative na tela do consórcio"><i class="bi bi-trash"></i></span>
            @endif
        </div>
    </div>
</div>
