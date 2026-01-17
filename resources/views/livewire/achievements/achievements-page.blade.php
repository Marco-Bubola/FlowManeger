<div class="min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-900 dark:via-purple-950 dark:to-indigo-950">
    <!-- Header -->
    <x-sales-header
        title="Conquistas"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Conquistas', 'url' => null]
        ]"
    />

    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Header -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stat-card
                title="Total de Conquistas"
                :value="$stats['unlocked_count'] . '/' . $stats['total_count']"
                icon="bi bi-trophy-fill"
                color="purple"
            >
                <div class="flex items-center gap-4">
                    <x-progress-ring
                        :percentage="$stats['completion_rate']"
                        size="sm"
                        color="purple"
                    />
                    <span class="text-sm text-slate-600 dark:text-slate-400">
                        {{ round($stats['completion_rate']) }}% completo
                    </span>
                </div>
            </x-stat-card>

            <x-stat-card
                title="Pontos Totais"
                :value="$stats['total_points']"
                icon="bi bi-star-fill"
                color="orange"
                :subtitle="'De ' . \App\Models\Achievement::sum('points') . ' possíveis'"
            />

            <x-stat-card
                title="Troféus de Bronze"
                :value="$stats['by_rarity']['bronze'] ?? 0"
                icon="bi bi-trophy-fill"
                color="orange"
            />

            <x-stat-card
                title="Troféus de Prata"
                :value="$stats['by_rarity']['silver'] ?? 0"
                icon="bi bi-trophy-fill"
                color="cyan"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <x-stat-card
                title="Troféus de Ouro"
                :value="$stats['by_rarity']['gold'] ?? 0"
                icon="bi bi-trophy-fill"
                color="pink"
            />

            <x-stat-card
                title="Troféus de Platina"
                :value="$stats['by_rarity']['platinum'] ?? 0"
                icon="bi bi-gem"
                color="blue"
            />

            <!-- Troféu animado para o rarity mais alto -->
            <div class="lg:col-span-2 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-200 mb-1">Maior Raridade Desbloqueada</p>
                        <h3 class="text-2xl font-bold capitalize">
                            @if(($stats['by_rarity']['platinum'] ?? 0) > 0)
                                Platina
                            @elseif(($stats['by_rarity']['gold'] ?? 0) > 0)
                                Ouro
                            @elseif(($stats['by_rarity']['silver'] ?? 0) > 0)
                                Prata
                            @else
                                Bronze
                            @endif
                        </h3>
                    </div>
                    <x-trophy-badge
                        :rarity="($stats['by_rarity']['platinum'] ?? 0) > 0 ? 'platinum' : (($stats['by_rarity']['gold'] ?? 0) > 0 ? 'gold' : (($stats['by_rarity']['silver'] ?? 0) > 0 ? 'silver' : 'bronze'))"
                        size="xl"
                    />
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-8 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-wrap gap-4">
                <!-- Filtro de Raridade -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-filter mr-2"></i>Raridade
                    </label>
                    <select wire:model.live="filterRarity" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="all">Todas</option>
                        <option value="bronze">Bronze</option>
                        <option value="silver">Prata</option>
                        <option value="gold">Ouro</option>
                        <option value="platinum">Platina</option>
                    </select>
                </div>

                <!-- Filtro de Categoria -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-tag mr-2"></i>Categoria
                    </label>
                    <select wire:model.live="filterCategory" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="all">Todas</option>
                        <option value="habits">Hábitos</option>
                        <option value="goals">Metas</option>
                        <option value="streak">Sequências</option>
                        <option value="general">Geral</option>
                    </select>
                </div>

                <!-- Ordenação -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-sort-down mr-2"></i>Ordenar por
                    </label>
                    <select wire:model.live="sortBy" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="order">Padrão</option>
                        <option value="points">Pontos</option>
                        <option value="rarity">Raridade</option>
                        <option value="name">Nome</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Grid de Conquistas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
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
