<div class="daily-habits-dashboard-page pes-page w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    <x-pessoal-header title="Hábitos" subtitle="Toque no círculo para marcar o hábito de hoje" icon="bi-check2-square"
        gradient="from-emerald-500 via-teal-500 to-cyan-500" active="habitos">
        <x-slot name="actions">
            <a href="{{ route('daily-habits.create') }}" wire:navigate aria-label="Novo hábito"
               class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 shadow-md shadow-purple-500/25">
                <i class="bi bi-plus-lg"></i><span class="app-ph-label">Novo hábito</span>
            </a>
        </x-slot>
    </x-pessoal-header>

    @if (count($habits) > 0)
        <div class="dash-kpis pes-kpis mb-4">
            <x-dash.kpi label="Feitos hoje" tone="emerald" icon="bi-check2-circle"
                :value="$stats['completed_today'] . ' de ' . $stats['scheduled_today']" />
            <x-dash.kpi label="Sequência atual" tone="amber" icon="bi-fire"
                :value="$stats['current_streak'] . ($stats['current_streak'] == 1 ? ' dia' : ' dias')" />
            <x-dash.kpi label="Recorde" tone="purple" icon="bi-trophy"
                :value="$stats['best_streak'] . ($stats['best_streak'] == 1 ? ' dia' : ' dias')" />
            <x-dash.kpi label="Últimos 30 dias" tone="sky" icon="bi-calendar-check"
                :value="$stats['rate30'] . '%'" />
        </div>

        <div class="pes-day-bar mb-4" aria-label="Progresso de hoje">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                <span>Progresso de hoje</span>
                <span>{{ $stats['completion_percentage'] }}%</span>
            </div>
            <div class="h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/80 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-500" style="width: {{ $stats['completion_percentage'] }}%"></div>
            </div>
        </div>

        <div class="pes-habits grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 pb-6">
            @foreach ($habits as $habit)
                <div wire:key="habit-{{ $habit['id'] }}"
                     class="pes-habit flex items-center gap-3 rounded-2xl border bg-white/90 dark:bg-slate-800/90 p-3 shadow-sm transition
                            {{ $habit['done'] ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/80 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}
                            {{ $habit['scheduled'] ? '' : 'opacity-70' }}">
                    <button type="button" wire:click="toggleHabit({{ $habit['id'] }})" wire:loading.attr="disabled"
                            wire:target="toggleHabit({{ $habit['id'] }})"
                            aria-pressed="{{ $habit['done'] ? 'true' : 'false' }}"
                            aria-label="{{ $habit['done'] ? 'Desmarcar' : 'Marcar' }} {{ $habit['name'] }}"
                            class="pes-habit-check flex items-center justify-center w-11 h-11 shrink-0 rounded-full border-2 transition active:scale-90
                                   {{ $habit['done'] ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-emerald-400' }}">
                        <i class="bi bi-check-lg text-xl" wire:loading.remove wire:target="toggleHabit({{ $habit['id'] }})"></i>
                        <i class="bi bi-arrow-repeat animate-spin text-lg text-slate-400" wire:loading wire:target="toggleHabit({{ $habit['id'] }})"></i>
                    </button>

                    <span class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl text-white shadow-sm" style="background: {{ $habit['color'] }};">
                        <i class="bi {{ $habit['icon'] }} text-lg"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-800 dark:text-white truncate {{ $habit['done'] ? 'line-through decoration-emerald-500/60' : '' }}">{{ $habit['name'] }}</p>
                        <p class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                            <span title="Sequência atual"><i class="bi bi-fire text-orange-500"></i> {{ $habit['current_streak'] }} {{ $habit['current_streak'] == 1 ? 'dia' : 'dias' }}</span>
                            <span title="Recorde"><i class="bi bi-trophy text-purple-500"></i> {{ $habit['longest_streak'] }}</span>
                            <span title="Dias feitos nos últimos 30"><i class="bi bi-calendar-check text-sky-500"></i> {{ $habit['rate30'] }}%</span>
                            @unless ($habit['scheduled'])
                                <span class="text-slate-400">Fora da agenda hoje</span>
                            @endunless
                        </p>
                    </div>

                    <a href="{{ route('daily-habits.edit', ['habitId' => $habit['id']]) }}" wire:navigate aria-label="Editar {{ $habit['name'] }}"
                       class="flex items-center justify-center w-9 h-9 shrink-0 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            @endforeach
        </div>

        @if (count($recentAchievements) > 0)
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-700 p-4 text-white shadow-lg mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold flex items-center gap-2"><i class="bi bi-trophy-fill text-yellow-300"></i> Conquistas de hábitos</h3>
                    <a href="{{ route('achievements.index') }}" wire:navigate class="text-xs font-semibold bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg">Ver todas</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach ($recentAchievements as $a)
                        <div class="flex items-center gap-3 rounded-xl bg-white/10 p-2.5">
                            <span class="flex items-center justify-center w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500"><i class="{{ $a['icon'] }}"></i></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $a['name'] }}</p>
                                <p class="text-xs text-indigo-200">{{ $a['points'] }} pts</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center text-center py-14 px-4 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/60 dark:bg-slate-800/40">
            <span class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                <i class="bi bi-calendar-plus text-3xl text-emerald-500"></i>
            </span>
            <h2 class="text-lg font-bold text-slate-700 dark:text-slate-200 mb-1">Nenhum hábito ainda</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 max-w-sm">Crie o primeiro hábito e marque com um toque todo dia.</p>
            <a href="{{ route('daily-habits.create') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 shadow-md">
                <i class="bi bi-plus-lg"></i> Criar primeiro hábito
            </a>
        </div>
    @endif
</div>
