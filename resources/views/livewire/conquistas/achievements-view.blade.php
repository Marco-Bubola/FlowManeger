<div class="ch-ach-wrap">
    <!-- Resumo de nível -->
    <div class="ch-ach-summary">
        <div class="ch-ach-level">
            <span class="ch-ach-level-num">{{ $level['level'] ?? 1 }}</span>
            <span class="ch-ach-level-label">NÍVEL</span>
        </div>
        <div class="ch-ach-stats">
            <div><strong>{{ number_format($level['xp'] ?? 0, 0, ',', '.') }}</strong><span>XP total</span></div>
            <div><strong>{{ $level['current_streak'] ?? 0 }}</strong><span>streak atual</span></div>
            <div><strong>{{ $level['best_streak'] ?? 0 }}</strong><span>melhor streak</span></div>
        </div>
    </div>

    <div class="ch-ach-cols">
        <!-- Badges -->
        <div class="ch-ach-badges">
            <h4><i class="bi bi-award"></i> Conquistas</h4>
            @if(count($achievements) === 0)
                <div class="ch-empty">Nenhuma conquista cadastrada ainda.</div>
            @else
            <div class="ch-badges-grid">
                @foreach($achievements as $a)
                <div class="ch-badge {{ $a['unlocked'] ? 'ch-badge--on' : 'ch-badge--off' }}" style="--badge-color: {{ $a['color'] }}" title="{{ $a['description'] }}">
                    <span class="ch-badge-icon"><i class="bi {{ $a['icon'] }}"></i></span>
                    <span class="ch-badge-name">{{ $a['name'] }}</span>
                    <span class="ch-badge-pts">{{ $a['points'] }} pts</span>
                    @unless($a['unlocked'])<span class="ch-badge-lock"><i class="bi bi-lock-fill"></i></span>@endunless
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Histórico de XP -->
        <div class="ch-ach-xplog">
            <h4><i class="bi bi-clock-history"></i> Histórico de XP</h4>
            @if(count($xpLogs) === 0)
                <div class="ch-empty">Sem atividade ainda. Conclua hábitos para ganhar XP!</div>
            @else
            <ul class="ch-xplog-list">
                @foreach($xpLogs as $log)
                <li>
                    <span class="ch-xplog-amt {{ $log['amount'] >= 0 ? 'pos' : 'neg' }}">{{ $log['amount'] >= 0 ? '+' : '' }}{{ $log['amount'] }} XP</span>
                    <span class="ch-xplog-reason">{{ $log['reason'] }}</span>
                    <span class="ch-xplog-when">{{ $log['when'] }}</span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
