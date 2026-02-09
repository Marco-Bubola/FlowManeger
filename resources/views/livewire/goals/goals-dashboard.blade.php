<div class="w-full bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800" style="min-height: 100vh; display: flex; flex-direction: column;">
    <!-- Header Moderno -->
    <x-sales-header
        title="📊 Dashboard de Metas"
        description="Acompanhe o progresso das suas metas e objetivos">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    <i class="bi bi-bullseye mr-1"></i>Metas e Objetivos
                </span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('goals.board', ['boardId' => $boards->first()->id ?? 1]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg shadow-lg transition-all duration-200">
                <i class="bi bi-kanban"></i>
                <span class="font-medium">Abrir Quadro</span>
            </a>
        </x-slot>
    </x-sales-header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- KPIs Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stat-card
                title="Total de Metas"
                :value="$stats['total']"
                icon="bi bi-bullseye"
                color="blue"
                :subtitle="$stats['pending'] . ' pendentes'"
            />

            <x-stat-card
                title="Em Andamento"
                :value="$stats['active']"
                icon="bi bi-play-circle"
                color="green"
                :subtitle="'Meta: ' . $stats['total']"
            />

            <x-stat-card
                title="Concluídas"
                :value="$stats['completed']"
                icon="bi bi-check-circle"
                color="purple"
                :subtitle="'🎉 Parabéns!'"
            />

            <x-stat-card
                title="Taxa de Conclusão"
                :value="number_format($stats['avgProgress'], 1) . '%'"
                icon="bi bi-graph-up-arrow"
                color="orange"
            >
                <x-progress-ring
                    :percentage="$stats['avgProgress']"
                    size="sm"
                    color="orange"
                    label=""
                />
            </x-stat-card>
        </div>

        <!-- Achievements Section -->
        @if($achievementStats['unlocked'] > 0)
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 shadow-xl text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="bi bi-trophy-fill text-yellow-300"></i>
                    Conquistas de Metas
                </h3>
                <a href="{{ route('achievements.index') }}" class="text-sm bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    Ver Todas
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <x-trophy-badge rarity="bronze" size="md" :count="$achievementStats['by_rarity']['bronze'] ?? 0" />
                        <div>
                            <p class="text-2xl font-bold">{{ $achievementStats['by_rarity']['bronze'] ?? 0 }}</p>
                            <p class="text-sm text-purple-200">Bronze</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <x-trophy-badge rarity="silver" size="md" :count="$achievementStats['by_rarity']['silver'] ?? 0" />
                        <div>
                            <p class="text-2xl font-bold">{{ $achievementStats['by_rarity']['silver'] ?? 0 }}</p>
                            <p class="text-sm text-purple-200">Prata</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <x-trophy-badge rarity="gold" size="md" :count="$achievementStats['by_rarity']['gold'] ?? 0" />
                        <div>
                            <p class="text-2xl font-bold">{{ $achievementStats['by_rarity']['gold'] ?? 0 }}</p>
                            <p class="text-sm text-purple-200">Ouro</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($recentAchievements->count() > 0)
            <div class="mt-4 pt-4 border-t border-white/20">
                <p class="text-sm text-purple-200 mb-3">Últimas Conquistas:</p>
                <div class="flex gap-2">
                    @foreach($recentAchievements as $achievement)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 flex items-center gap-2">
                            <i class="{{ $achievement->achievement->icon }} text-xl"></i>
                            <span class="text-sm">{{ $achievement->achievement->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Alertas e Boards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Coluna Esquerda: Metas Urgentes -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Alertas -->
                @if($stats['delayed'] > 0 || $stats['upcoming'] > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($stats['delayed'] > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-red-500 rounded-lg">
                                <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['delayed'] }}</p>
                                <p class="text-sm text-red-600 dark:text-red-300">Metas atrasadas</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($stats['upcoming'] > 0)
                    <div class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-amber-500 rounded-lg">
                                <i class="bi bi-clock-history text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $stats['upcoming'] }}</p>
                                <p class="text-sm text-amber-600 dark:text-amber-300">Vencendo em 7 dias</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Metas Urgentes -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-fire text-red-500"></i>
                            Metas Urgentes
                        </h3>
                        <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-2 py-1 rounded-full">
                            {{ count($urgentGoals) }} metas
                        </span>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($urgentGoals as $goal)
                        <div class="group bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl p-4 transition-all duration-200 cursor-pointer border-l-4"
                             style="border-color: {{ $goal['board_color'] ?? '#6B7280' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-slate-900 dark:text-white">{{ $goal['title'] }}</h4>
                                        @if($goal['is_atrasada'])
                                        <span class="text-xs bg-red-500 text-white px-2 py-0.5 rounded-full">Atrasada</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-2">
                                        {{ $goal['board'] }} • {{ $goal['list'] }}
                                    </p>

                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2 mb-2">
                                        <div class="h-2 rounded-full transition-all duration-500 {{ $goal['progresso'] < 30 ? 'bg-red-500' : ($goal['progresso'] < 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                                             style="width: {{ $goal['progresso'] }}%">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-calendar3"></i>
                                            {{ \Carbon\Carbon::parse($goal['data_vencimento'])->format('d/m/Y') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-graph-up"></i>
                                            {{ $goal['progresso'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right ml-4">
                                    @if($goal['days_left'] < 0)
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                        {{ abs($goal['days_left']) }} dias atrás
                                    </span>
                                    @else
                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                        {{ $goal['days_left'] }} dias
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="bi bi-check-circle text-green-500 text-4xl mb-2"></i>
                            <p class="text-slate-600 dark:text-slate-400">Nenhuma meta urgente no momento! 👏</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Gráfico de Progresso -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-bar-chart text-blue-500"></i>
                        Distribuição de Progresso
                    </h3>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach(['0-25', '26-50', '51-75', '76-99', '100'] as $range)
                        <div class="text-center">
                            <div class="h-32 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-end justify-center p-2 mb-2">
                                <div class="w-full {{ $range === '100' ? 'bg-green-500' : 'bg-blue-500' }} rounded-t transition-all duration-500"
                                     style="height: {{ ($progressStats[$range] / max(1, $stats['active'])) * 100 }}%">
                                </div>
                            </div>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $range }}%</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $progressStats[$range] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Coluna Direita -->
            <div class="space-y-6">

                <!-- Quadros/Boards -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-kanban text-purple-500"></i>
                        Meus Quadros
                    </h3>

                    <div class="space-y-3">
                        @forelse($boards as $board)
                        <a href="{{ route('goals.board', ['boardId' => $board['id']]) }}"
                           class="block group bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl p-4 transition-all duration-200"
                           style="border-left: 4px solid {{ $board['color'] }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $board['icon'] }} text-xl" style="color: {{ $board['color'] }}"></i>
                                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ $board['name'] }}</h4>
                                    @if($board['is_favorite'])
                                    <i class="bi bi-star-fill text-amber-400 text-xs"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-slate-600 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <i class="bi bi-list-task"></i>
                                    {{ $board['lists_count'] }} listas
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    {{ $board['active_goals'] }} metas
                                </span>
                            </div>
                        </a>
                        @empty
                        <p class="text-center text-slate-500 dark:text-slate-400 py-4">Nenhum quadro criado</p>
                        @endforelse
                    </div>
                </div>

                <!-- Metas por Período -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-calendar-range text-indigo-500"></i>
                        Por Período
                    </h3>
                    <div class="space-y-2">
                        @forelse($goalsByPeriodo as $periodo)
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $periodo['label'] }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-full">
                                    {{ $periodo['count'] }}
                                </span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ number_format($periodo['avgProgress'], 0) }}%
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Sem dados</p>
                        @endforelse
                    </div>
                </div>

                <!-- Atividades Recentes -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history text-slate-500"></i>
                        Atividades Recentes
                    </h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @forelse($recentActivities as $activity)
                        <div class="flex items-start gap-3 text-sm">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                                 style="background-color: {{ $activity['color'] }}20">
                                <i class="{{ $activity['icon'] }}" style="color: {{ $activity['color'] }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $activity['user_name'] }}</span>
                                    {{ $activity['description'] }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $activity['time_ago'] }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Nenhuma atividade</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs de Hábitos Diários -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stat-card
                title="Hábitos Ativos"
                :value="$habitStats['total_habits'] ?? 0"
                icon="bi bi-list-check"
                color="purple"
            />
            <x-stat-card
                title="Concluídos Hoje"
                :value="($habitStats['completed_today'] ?? 0) . '/' . ($habitStats['total_habits'] ?? 0)"
                icon="bi bi-check-circle"
                color="green"
            />
            <x-stat-card
                title="Pendentes Hoje"
                :value="$habitStats['pending_today'] ?? 0"
                icon="bi bi-hourglass-split"
                color="amber"
            />
            <x-stat-card
                title="Taxa de Conclusão"
                :value="($habitStats['completion_rate_today'] ?? 0) . '%'"
                icon="bi bi-graph-up"
                color="blue"
            />
        </div>
        <!-- Lista de Hábitos -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-calendar-check text-purple-500"></i>
                Hábitos Diários
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($habits as $habit)
                <div class="bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900 dark:to-indigo-900 rounded-xl p-4 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <i class="{{ $habit->icon }} text-2xl" style="color: {{ $habit->color }}"></i>
                        <span class="font-bold text-lg">{{ $habit->name }}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $habit->description }}</p>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-full">Frequência: {{ $habit->goal_frequency }}</span>
                        <span class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-1 rounded-full">Streak: {{ $habit->streak->current_streak ?? 0 }}</span>
                        <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2 py-1 rounded-full">Taxa: {{ $habit->completion_rate ?? 0 }}%</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Nenhum hábito cadastrado</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
