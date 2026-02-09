<div class="w-full bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
    style="min-height: 100vh;">

    <!-- Header Padronizado -->
    <x-sales-header title="🎯 Hábitos Diários" description="Construa sua melhor versão, um dia de cada vez">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    <i class="bi bi-calendar-check mr-1"></i>Hábitos Diários
                </span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('daily-habits.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-lg shadow-lg transition-all duration-200">
                <i class="bi bi-plus-circle"></i>
                <span class="font-medium">Novo Hábito</span>
            </a>
        </x-slot>
    </x-sales-header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- KPIs usando componentes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stat-card title="Hábitos Ativos" :value="$stats['total_habits']" icon="bi bi-list-check" color="blue" />

            <x-stat-card title="Concluídos Hoje" :value="$stats['completed_today'] . '/' . $stats['total_habits']" icon="bi bi-check-circle-fill" color="green">
                <x-progress-ring :percentage="$stats['completion_percentage']" size="sm" color="green" :label="$stats['completion_percentage'] . '% completo'" />
            </x-stat-card>


            <x-stat-card title="Melhor Sequência" :value="($stats['best_streak'] ?? 0) . ' dias'" icon="bi bi-fire" color="pink">
                <div class="flex items-center gap-2 text-orange-500">
                    @for ($i = 0; $i < min($stats['best_streak'] ?? 0, 5); $i++)
                        <i class="bi bi-fire text-xl"></i>
                    @endfor
                </div>
            </x-stat-card>
        </div>

        <!-- Achievements Section -->
        @if (($achievementStats['unlocked_count'] ?? 0) > 0)
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 shadow-xl text-white mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        <i class="bi bi-trophy-fill text-yellow-300"></i>
                        Conquistas de Hábitos
                    </h3>
                    <a href="{{ route('achievements.index') }}"
                        class="text-sm bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                        Ver Todas
                    </a>
                </div>

                @if ($recentAchievements->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($recentAchievements as $userAchievement)
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                                        <i class="{{ $userAchievement->achievement->icon }} text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-sm">{{ $userAchievement->achievement->name }}</h4>
                                        <p class="text-xs text-indigo-200">{{ $userAchievement->achievement->points }}
                                            pts</p>
                                    </div>
                                </div>
                                <p class="text-xs text-indigo-200">{{ $userAchievement->achievement->description }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Stats Cards originais comentados -->
        <div class="flex items-center justify-between mb-3">
            <div
                class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-400 to-pink-600 rounded-xl shadow-lg">
                <i class="bi bi-fire text-2xl text-white"></i>
            </div>
            <span class="text-4xl font-black text-slate-800 dark:text-white">{{ $stats['best_streak'] ?? 0 }}</span>
        </div>
        <h3 class="text-slate-600 dark:text-slate-400 font-semibold">Melhor Sequência</h3>
    </div>


<!-- Mensagens de Sucesso/Erro -->
@if (session()->has('message'))
    <div class="animate-bounce-in mb-6">
        <div
            class="bg-gradient-to-r {{ session('message_type') === 'error' ? 'from-red-500 to-red-600' : 'from-green-500 to-emerald-600' }} text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3">
            <i
                class="bi {{ session('message_type') === 'error' ? 'bi-x-circle-fill' : 'bi-check-circle-fill' }} text-2xl"></i>
            <span class="font-semibold">{{ session('message') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="animate-bounce-in mb-6">
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-2xl shadow-lg">
            <div class="flex items-center gap-3 mb-2">
                <i class="bi bi-exclamation-triangle-fill text-2xl"></i>
                <span class="font-bold text-lg">Erros de Validação:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 ml-8">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<!-- Hábitos Grid -->
@if (count($habits) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-8">
        @foreach ($habits as $habit)
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border-2 transition-all duration-300 hover:shadow-2xl hover:scale-105 {{ $habit['is_completed_today'] ? 'border-green-400' : 'border-slate-200 dark:border-slate-700' }}">
                <!-- Header do Card -->
                <div class="p-6"
                    style="background: linear-gradient(135deg, {{ $habit['color'] }}20, {{ $habit['color'] }}10);">
                    <div class="flex items-start justify-between mb-4">
                        <!-- Ícone e Nome -->
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl shadow-lg"
                                style="background: {{ $habit['color'] }};">
                                <i class="bi {{ $habit['icon'] }} text-3xl text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">{{ $habit['name'] }}
                                </h3>
                                @if ($habit['description'])
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $habit['description'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Botão Editar -->
                        <a href="{{ route('daily-habits.edit', ['habitId' => $habit['id']]) }}"
                            class="flex items-center justify-center w-10 h-10 bg-white/50 dark:bg-slate-700/50 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all duration-200">
                            <i class="bi bi-pencil text-slate-600 dark:text-slate-400"></i>
                        </a>
                    </div>

                    <!-- Botão de Conclusão GRANDE -->
                    <button wire:click="toggleHabit({{ $habit['id'] }})" wire:loading.attr="disabled"
                        wire:loading.class="loading opacity-50 cursor-not-allowed"
                        wire:target="toggleHabit({{ $habit['id'] }})"
                        onclick="console.log('🎯 Clicou em toggleHabit({{ $habit['id'] }})')"
                        class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-300 transform active:scale-95 shadow-lg {{ $habit['is_completed_today'] ? 'bg-gradient-to-r from-green-400 to-emerald-500 text-white' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' }}">
                        <span wire:loading.remove wire:target="toggleHabit({{ $habit['id'] }})">
                            @if ($habit['is_completed_today'])
                                <i class="bi bi-check-circle-fill text-2xl mr-2"></i>
                                Concluído Hoje! 🎉
                            @else
                                <i class="bi bi-circle text-2xl mr-2"></i>
                                Marcar como Concluído
                            @endif
                        </span>
                        <span wire:loading wire:target="toggleHabit({{ $habit['id'] }})"
                            class="flex items-center justify-center gap-2">
                            <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
                            Processando...
                        </span>
                    </button>
                </div>

                <!-- Stats do Hábito -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <!-- Sequência Atual -->
                        <div>
                            <div class="flex items-center justify-center gap-1 text-orange-500 mb-1">
                                <i class="bi bi-fire"></i>
                                <span class="text-2xl font-black">{{ $habit['current_streak'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 font-semibold">Dias seguidos</p>
                        </div>

                        <!-- Melhor Sequência -->
                        <div>
                            <div class="flex items-center justify-center gap-1 text-purple-500 mb-1">
                                <i class="bi bi-trophy"></i>
                                <span class="text-2xl font-black">{{ $habit['longest_streak'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 font-semibold">Recorde</p>
                        </div>

                        <!-- Taxa de Conclusão -->
                        <div>
                            <div class="flex items-center justify-center gap-1 text-blue-500 mb-1">
                                <i class="bi bi-graph-up"></i>
                                <span class="text-2xl font-black">{{ $habit['completion_rate'] }}%</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 font-semibold">30 dias</p>
                        </div>
                    </div>

                    <!-- Total de Conclusões -->
                    <div class="mt-4 text-center">
                        <p class="text-slate-600 dark:text-slate-400 text-sm">
                            <i class="bi bi-star-fill text-amber-400"></i>
                            <span class="font-bold">{{ $habit['total_completions'] }}</span> conclusões totais
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center py-20">
        <div
            class="flex items-center justify-center w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-full mb-6">
            <i class="bi bi-calendar-plus text-6xl text-indigo-400"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-700 dark:text-slate-300 mb-3">Nenhum hábito ainda</h2>
        <p class="text-slate-500 dark:text-slate-400 mb-6 text-center max-w-md">
            Comece sua jornada criando seu primeiro hábito diário. Pequenos passos levam a grandes conquistas!
        </p>
        <button wire:click="openCreateModal"
            class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-200">
            <i class="bi bi-plus-circle text-xl"></i>
            <span>Criar Primeiro Hábito</span>
        </button>
    </div>
@endif
</div>

<!-- Modal Criar Hábito -->
<!-- Modal de Exclusão Removido - Agora gerenciado na página de edição -->
</div>

@push('scripts')
    <script>
        console.log('🎯 [DailyHabits] Script carregado');

        // Log de cliques nos botões
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ [DailyHabits] DOM carregado');

            // Interceptar todos os cliques wire:click
            document.addEventListener('click', function(e) {
                const wireClick = e.target.closest('[wire\\:click]');
                if (wireClick) {
                    const action = wireClick.getAttribute('wire:click');
                    console.log('🖱️ [DailyHabits] Clique detectado:', {
                        action: action,
                        element: wireClick,
                        timestamp: new Date().toISOString()
                    });
                }
            });
        });

        // Listener para erros do Livewire
        window.addEventListener('livewire:error', function(event) {
            console.error('❌ [DailyHabits] Erro Livewire:', {
                error: event.detail,
                timestamp: new Date().toISOString()
            });
        });

        // Listener para atualizações do Livewire
        window.addEventListener('livewire:update', function(event) {
            console.log('🔄 [DailyHabits] Componente atualizado:', {
                component: event.detail.component.name,
                timestamp: new Date().toISOString()
            });
        });

        // Listener para requisições do Livewire
        window.addEventListener('livewire:request', function(event) {
            console.log('📡 [DailyHabits] Requisição iniciada:', {
                component: event.detail.component.name,
                timestamp: new Date().toISOString()
            });
        });

        // Listener para respostas do Livewire
        window.addEventListener('livewire:response', function(event) {
            console.log('✅ [DailyHabits] Resposta recebida:', {
                component: event.detail.component.name,
                duration: event.detail.duration + 'ms',
                timestamp: new Date().toISOString()
            });
        });

        // Validação do formulário antes de enviar
        Livewire.on('validationError', function(errors) {
            console.error('⚠️ [DailyHabits] Erros de validação:', errors);
            alert('❌ Erro de validação! Verifique o console (F12) para detalhes.');
        });

        // Confirmação de sucesso
        Livewire.on('habitCreated', function() {
            console.log('✨ [DailyHabits] Hábito criado com sucesso!');
        });

        Livewire.on('habitToggled', function() {
            console.log('🔄 [DailyHabits] Hábito alternado com sucesso!');
        });
    </script>

    <style>
        @keyframes bounce-in {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            50% {
                transform: translateY(5px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-bounce-in {
            animation: bounce-in 0.5s ease-out;
        }

        /* Loading spinner para botões */
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
</div>
