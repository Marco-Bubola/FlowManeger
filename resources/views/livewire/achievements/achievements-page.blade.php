<div class="achievements-page mobile-393-base min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-900 dark:via-purple-950 dark:to-indigo-950">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/achievements-ultrawide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <x-pessoal-header title="Conquistas" subtitle="Suas medalhas e marcos alcançados" icon="bi-trophy" gradient="from-amber-400 via-orange-500 to-rose-500" active="conquistas" />

    <div class="w-full">
        @php
            $r = $stats['by_rarity'] ?? [];
            $maior = ($r['platinum'] ?? 0) > 0 ? 'Platina' : ((($r['gold'] ?? 0) > 0) ? 'Ouro' : ((($r['silver'] ?? 0) > 0) ? 'Prata' : ((($r['bronze'] ?? 0) > 0) ? 'Bronze' : 'Nenhuma')));
            $raridades = [
                'bronze' => ['Bronze', 'from-amber-600 to-orange-700'],
                'silver' => ['Prata', 'from-slate-300 to-slate-500'],
                'gold' => ['Ouro', 'from-yellow-300 to-amber-500'],
                'platinum' => ['Platina', 'from-cyan-300 to-indigo-400'],
            ];
        @endphp
        <div class="dash-kpis pes-kpis mb-3">
            <x-dash.kpi label="Desbloqueadas" tone="purple" icon="bi-trophy-fill" :value="$stats['unlocked_count'] . ' de ' . $stats['total_count']" />
            <x-dash.kpi label="Concluído" tone="emerald" icon="bi-pie-chart" :value="round($stats['completion_rate']) . '%'" />
            <x-dash.kpi label="Pontos" tone="amber" icon="bi-star-fill" :value="number_format($stats['total_points'], 0, ',', '.')" />
            <x-dash.kpi label="Maior raridade" tone="sky" icon="bi-gem" :value="$maior" />
        </div>

        <div class="grid grid-cols-4 gap-2 mb-4">
            @foreach($raridades as $key => [$nome, $grad])
                <div class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 rounded-xl bg-white/90 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 px-2 py-2 text-center">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-br {{ $grad }} text-white text-xs"><i class="bi bi-trophy-fill"></i></span>
                    <span class="text-xs text-slate-600 dark:text-slate-300"><b class="text-sm text-slate-800 dark:text-white">{{ $r[$key] ?? 0 }}</b> {{ $nome }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-2 mb-4">
            <select wire:model.live="filterRarity" aria-label="Raridade" class="w-full min-w-0 px-2.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                <option value="all">Raridade</option>
                <option value="bronze">Bronze</option>
                <option value="silver">Prata</option>
                <option value="gold">Ouro</option>
                <option value="platinum">Platina</option>
            </select>
            <select wire:model.live="filterCategory" aria-label="Categoria" class="w-full min-w-0 px-2.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                <option value="all">Categoria</option>
                <option value="habits">Hábitos</option>
                <option value="goals">Metas</option>
                <option value="streak">Sequências</option>
                <option value="general">Geral</option>
            </select>
            <select wire:model.live="sortBy" aria-label="Ordenar por" class="w-full min-w-0 px-2.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                <option value="order">Ordenar</option>
                <option value="points">Pontos</option>
                <option value="rarity">Raridade</option>
                <option value="name">Nome</option>
            </select>
        </div>

        <!-- Grid de Conquistas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($achievements as $achievement)
                @php
                    $unlocked = in_array($achievement->id, $unlockedIds);
                    $userAchievement = $userAchievements->get($achievement->id);
                    $unlockedAt = $userAchievement ? $userAchievement->unlocked_at : null;
                @endphp
                <x-achievement-card
                    :achievement="$achievement"
                    :unlocked="$unlocked"
                    :unlockedAt="$unlockedAt"
                />
            @endforeach
        </div>

        @if($achievements->isEmpty())
            <div class="text-center py-16">
                <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="bi bi-trophy text-6xl text-slate-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Nenhuma conquista encontrada</h3>
                <p class="text-slate-600 dark:text-slate-400">Tente ajustar os filtros</p>
            </div>
        @endif
    </div>
</div>
