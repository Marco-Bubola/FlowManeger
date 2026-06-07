<div class="conquistas-hub" x-data="conquistasHub()" x-init="init()">

    <link rel="stylesheet" href="{{ asset('assets/css/conquistas-hub.css') }}">

    <!-- ───────── HEADER ───────── -->
    <div class="ch-header">
        <div class="ch-header-titles">
            <h1 class="ch-title"><i class="bi bi-trophy-fill"></i> Metas &amp; Hábitos</h1>
            <p class="ch-subtitle">Seu centro de conquistas — metas, hábitos e progresso em um só lugar</p>
        </div>

        <!-- Nível / XP -->
        <div class="ch-level-card">
            <div class="ch-level-badge">
                <span class="ch-level-num">{{ $level['level'] ?? 1 }}</span>
                <span class="ch-level-label">NÍVEL</span>
            </div>
            <div class="ch-level-info">
                <div class="ch-level-xp">
                    <i class="bi bi-stars"></i> {{ number_format($level['xp'] ?? 0, 0, ',', '.') }} XP
                    <span class="ch-streak"><i class="bi bi-fire"></i> {{ $level['current_streak'] ?? 0 }} dias</span>
                </div>
                <div class="ch-xp-bar">
                    <div class="ch-xp-bar-fill" style="width: {{ $level['progress'] ?? 0 }}%"></div>
                </div>
                <div class="ch-xp-next">Faltam {{ max(0, ($level['xp_ceiling'] ?? 0) - ($level['xp'] ?? 0)) }} XP para o nível {{ ($level['level'] ?? 1) + 1 }}</div>
            </div>
        </div>
    </div>

    <!-- ───────── ABAS ───────── -->
    <div class="ch-tabs" role="tablist">
        @php
            $tabs = [
                'hoje'      => ['Hoje', 'bi-sun'],
                'metas'     => ['Metas', 'bi-kanban'],
                'habitos'   => ['Hábitos', 'bi-check2-square'],
                'insights'  => ['Insights', 'bi-graph-up-arrow'],
                'conquistas'=> ['Conquistas', 'bi-award'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <button type="button" wire:click="setTab('{{ $key }}')"
                    class="ch-tab {{ $activeTab === $key ? 'ch-tab--active' : '' }}"
                    role="tab" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                <i class="bi {{ $tab[1] }}"></i>
                <span>{{ $tab[0] }}</span>
            </button>
        @endforeach
    </div>

    <!-- ───────── CONTEÚDO ───────── -->
    <div class="ch-content">

        {{-- ===== ABA HOJE ===== --}}
        @if($activeTab === 'hoje')
        <div class="ch-today" wire:key="tab-hoje">

            <!-- Linha 1: Anel de progresso + frase do dia -->
            <div class="ch-today-top">
                <div class="ch-progress-card">
                    <div class="ch-ring" style="--pct: {{ $dayProgress['percent'] }}">
                        <svg viewBox="0 0 120 120">
                            <circle class="ch-ring-bg" cx="60" cy="60" r="52"></circle>
                            <circle class="ch-ring-fg" cx="60" cy="60" r="52"
                                    stroke-dasharray="326.7"
                                    stroke-dashoffset="{{ 326.7 - (326.7 * $dayProgress['percent'] / 100) }}"></circle>
                        </svg>
                        <div class="ch-ring-center">
                            <span class="ch-ring-pct">{{ $dayProgress['percent'] }}%</span>
                            <span class="ch-ring-sub">{{ $dayProgress['done'] }}/{{ $dayProgress['total'] }} hábitos</span>
                        </div>
                    </div>
                    <div class="ch-progress-text">
                        <h3>Progresso de hoje</h3>
                        <p>
                            @if($dayProgress['total'] === 0)
                                Nenhum hábito agendado para hoje. Que tal criar um?
                            @elseif($dayProgress['percent'] === 100)
                                🎉 Você concluiu todos os hábitos de hoje!
                            @else
                                Continue! Faltam {{ $dayProgress['total'] - $dayProgress['done'] }} hábito(s).
                            @endif
                        </p>
                    </div>
                </div>

                <div class="ch-quote-card">
                    <i class="bi bi-quote ch-quote-icon"></i>
                    <p class="ch-quote-text">{{ $quote['text'] ?? '' }}</p>
                    <p class="ch-quote-author">— {{ $quote['author'] ?: 'Anônimo' }}
                        @if(($quote['source'] ?? '') === 'api')<span class="ch-quote-src">via API</span>@endif
                    </p>
                </div>
            </div>

            <!-- Hábitos de hoje -->
            <div class="ch-section">
                <div class="ch-section-head">
                    <h3><i class="bi bi-check2-square"></i> Hábitos de hoje</h3>
                    <a href="{{ route('daily-habits.create') }}" class="ch-link-btn" wire:navigate><i class="bi bi-plus-lg"></i> Novo hábito</a>
                </div>

                @if(count($todayHabits) === 0)
                    <div class="ch-empty"><i class="bi bi-emoji-smile"></i> Sem hábitos para hoje.</div>
                @else
                <div class="ch-habits-grid">
                    @foreach($todayHabits as $h)
                    <button type="button"
                            wire:click="toggleHabit({{ $h['id'] }})"
                            class="ch-habit {{ $h['done'] ? 'ch-habit--done' : '' }}"
                            style="--habit-color: {{ $h['color'] }}"
                            wire:key="habit-{{ $h['id'] }}">
                        <span class="ch-habit-check"><i class="bi {{ $h['done'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i></span>
                        <span class="ch-habit-icon"><i class="bi {{ $h['icon'] }}"></i></span>
                        <span class="ch-habit-name">{{ $h['name'] }}</span>
                        @if($h['done'])<span class="ch-habit-xp">+{{ \App\Services\GamificationService::XP_HABIT_COMPLETE }} XP</span>@endif
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Metas vencendo -->
            <div class="ch-section">
                <div class="ch-section-head">
                    <h3><i class="bi bi-alarm"></i> Metas vencendo em 7 dias</h3>
                    <button type="button" wire:click="setTab('metas')" class="ch-link-btn"><i class="bi bi-arrow-right"></i> Ver Kanban</button>
                </div>

                @if(count($urgentGoals) === 0)
                    <div class="ch-empty"><i class="bi bi-check2-all"></i> Nenhuma meta vencendo. Tudo em dia!</div>
                @else
                <div class="ch-goals-grid">
                    @foreach($urgentGoals as $g)
                    <div class="ch-goal-card" style="--goal-color: {{ $g['cor'] }}" wire:key="ugoal-{{ $g['id'] }}">
                        <div class="ch-goal-top">
                            <span class="ch-goal-due"><i class="bi bi-calendar-event"></i> {{ $g['vencimento'] }}</span>
                            <span class="ch-goal-prio ch-prio-{{ $g['prioridade'] }}">{{ ucfirst($g['prioridade'] ?? '') }}</span>
                        </div>
                        <h4 class="ch-goal-title">{{ $g['title'] }}</h4>
                        <div class="ch-goal-bar"><div class="ch-goal-bar-fill" style="width: {{ $g['progresso'] }}%"></div></div>
                        <div class="ch-goal-foot">
                            <span>{{ number_format($g['progresso'], 0) }}%</span>
                            @if($aiConfigured || true)
                            <button type="button" wire:click="suggestHabits({{ $g['id'] }})" class="ch-ai-btn" title="Sugerir hábitos com IA">
                                <i class="bi bi-magic"></i> IA
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Sugestões da IA -->
            @if(count($aiHabitSuggestions) > 0)
            <div class="ch-section ch-ai-section">
                <div class="ch-section-head">
                    <h3><i class="bi bi-magic"></i> Sugestões de hábitos (IA)</h3>
                    <span class="ch-ai-tag">{{ $aiConfigured ? 'Claude API' : 'modo local' }}</span>
                </div>
                <div class="ch-ai-grid">
                    @foreach($aiHabitSuggestions as $i => $s)
                    <div class="ch-ai-card" wire:key="ai-{{ $i }}">
                        <span class="ch-ai-icon"><i class="bi {{ $s['icon'] }}"></i></span>
                        <div class="ch-ai-body">
                            <strong>{{ $s['name'] }}</strong>
                            <p>{{ $s['description'] }}</p>
                        </div>
                        <button type="button" wire:click="createHabitFromSuggestion({{ $i }})" class="ch-ai-add" title="Criar este hábito">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ===== ABA METAS (embute Kanban existente) ===== --}}
        @if($activeTab === 'metas')
        <div wire:key="tab-metas" class="ch-embed">
            @livewire(\App\Livewire\Goals\GoalsDashboard::class, key: 'embed-goals')
        </div>
        @endif

        {{-- ===== ABA HÁBITOS (embute dashboard existente) ===== --}}
        @if($activeTab === 'habitos')
        <div wire:key="tab-habitos" class="ch-embed">
            @livewire(\App\Livewire\DailyHabits\DailyHabitsDashboard::class, key: 'embed-habits')
        </div>
        @endif

        {{-- ===== ABA INSIGHTS ===== --}}
        @if($activeTab === 'insights')
        <div wire:key="tab-insights" class="ch-insights">
            <livewire:conquistas.insights-view :key="'insights-view'" />
        </div>
        @endif

        {{-- ===== ABA CONQUISTAS ===== --}}
        @if($activeTab === 'conquistas')
        <div wire:key="tab-conquistas" class="ch-achievements">
            <livewire:conquistas.achievements-view :key="'ach-view'" />
        </div>
        @endif
    </div>

    <!-- Camada de animação de XP (confete + "+XP" subindo) -->
    <div id="ch-xp-fx" class="ch-xp-fx" aria-hidden="true"></div>
</div>

<script>
function conquistasHub() {
    return {
        init() {
            // Carrega canvas-confetti sob demanda (layout não tem @stack scripts)
            if (typeof confetti === 'undefined' && !document.getElementById('fm-confetti-lib')) {
                const s = document.createElement('script');
                s.id = 'fm-confetti-lib';
                s.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js';
                document.head.appendChild(s);
            }

            this.$wire.on('habit-toggled', (e) => {
                const detail = Array.isArray(e) ? e[0] : e;
                if (detail && detail.done) {
                    this.popXp(detail.xp || 10);
                }
            });
            this.$wire.on('level-up', () => {
                this.fireConfetti();
            });
        },
        popXp(amount) {
            const layer = document.getElementById('ch-xp-fx');
            if (!layer) return;
            const el = document.createElement('div');
            el.className = 'ch-xp-pop';
            el.textContent = '+' + amount + ' XP';
            el.style.left = (40 + Math.random() * 20) + '%';
            layer.appendChild(el);
            setTimeout(() => el.remove(), 1400);
            this.fireConfetti(0.4);
        },
        fireConfetti(scale = 1) {
            if (typeof confetti !== 'function') return;
            confetti({
                particleCount: Math.round(60 * scale),
                spread: 70,
                origin: { y: 0.7 },
                colors: ['#a490c2', '#4a4e8f', '#34d399', '#e6e6fa', '#fbbf24']
            });
        }
    };
}
</script>
