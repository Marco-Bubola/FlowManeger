<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use App\Models\GoalBoard;
use App\Models\GoalList;
use App\Services\GoalService;
use App\Services\AchievementService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GoalsDashboard extends Component
{
    public $stats = [];
    public $boards = [];
    public $urgentGoals = [];
    public $recentActivities = [];
    public $goalsByPeriodo = [];
    public $goalsByPrioridade = [];
    public $progressStats = [];
    public $achievementStats = [];
    public $recentAchievements = [];
    protected $habitService;
    public $habitStats = [];
    public $habits = [];

    protected $goalService;
    protected $achievementService;

    public function boot(GoalService $goalService, AchievementService $achievementService, \App\Services\HabitService $habitService)
    {
        $this->goalService = $goalService;
        $this->achievementService = $achievementService;
        $this->habitService = $habitService;
    }

    public function mount()
    {
        // Criar boards padrão se não existirem
        $this->createDefaultBoardsIfNeeded();

        $this->loadDashboardData();
    }

    private function createDefaultBoardsIfNeeded()
    {
        $userId = Auth::id();
        $boardsCount = GoalBoard::where('user_id', $userId)->count();

        if ($boardsCount === 0) {
            // Criar boards padrão
            $defaultBoards = [
                [
                    'name' => 'Metas Financeiras',
                    'description' => 'Organize suas metas de economia, investimentos e controle financeiro',
                    'tipo' => 'financeiro',
                    'background_color' => '#10B981',
                    'is_favorite' => true,
                    'order' => 1,
                    'lists' => [
                        ['name' => 'Planejamento', 'color' => '#94A3B8', 'order' => 0],
                        ['name' => 'Em Andamento', 'color' => '#3B82F6', 'order' => 1],
                        ['name' => 'Próximo da Meta', 'color' => '#F59E0B', 'order' => 2],
                        ['name' => 'Concluídas', 'color' => '#10B981', 'order' => 3],
                    ]
                ],
                [
                    'name' => 'Desenvolvimento Pessoal',
                    'description' => 'Acompanhe seu crescimento pessoal, hábitos e bem-estar',
                    'tipo' => 'pessoal',
                    'background_color' => '#F59E0B',
                    'is_favorite' => false,
                    'order' => 2,
                    'lists' => [
                        ['name' => 'Novos Hábitos', 'color' => '#8B5CF6', 'order' => 0],
                        ['name' => 'Em Progresso', 'color' => '#3B82F6', 'order' => 1],
                        ['name' => 'Concluídas', 'color' => '#10B981', 'order' => 2],
                    ]
                ],
            ];

            foreach ($defaultBoards as $boardData) {
                $lists = $boardData['lists'];
                unset($boardData['lists']);

                $board = GoalBoard::create(array_merge($boardData, ['user_id' => $userId]));

                foreach ($lists as $listData) {
                    GoalList::create(array_merge($listData, ['board_id' => $board->id]));
                }
            }
        }
    }

    public function loadHabitsData()
    {
        $userId = Auth::id();
        $kpis = $this->habitService->getUserKPIs($userId) ?? [];
        $this->habitStats = array_merge([
            'total_habits' => 0,
            'completed_today' => 0,
            'pending_today' => 0,
            'completion_rate_today' => 0,
            'total_goal_today' => 0,
        ], $kpis);
        $this->habits = \App\Models\DailyHabit::where('user_id', $userId)->active()->ordered()->get();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Usar GoalService para KPIs
        $kpis = $this->goalService->getUserKPIs($userId);

        $this->stats = [
            'total' => $kpis['total'],
            'active' => $kpis['in_progress'],
            'completed' => $kpis['completed'],
            'archived' => 0, // Pode adicionar no service se necessário
            'avgProgress' => $kpis['completion_rate'],
            'delayed' => $kpis['overdue'],
            'upcoming' => $kpis['upcoming'],
            'pending' => $kpis['pending'],
        ];

        // Stats de achievements
        $this->achievementStats = $this->achievementService->getUserStats($userId);

        // Achievements recentes (últimas 3)
        $this->recentAchievements = $this->achievementService->getUserAchievements($userId)
            ->sortByDesc('unlocked_at')
            ->take(3);

        // Boards do usuário
        $this->boards = GoalBoard::where('user_id', $userId)
            ->withCount(['lists', 'lists as active_goals_count' => function($query) {
                $query->join('goals', 'goal_lists.id', '=', 'goals.list_id')
                      ->where('goals.is_archived', false)
                      ->whereNull('goals.completed_at');
            }])
            ->orderBy('order')
            ->get()
            ->map(function($board) {
                return [
                    'id' => $board->id,
                    'name' => $board->name,
                    'tipo' => $board->tipo,
                    'tipo_label' => $this->getTipoLabel($board->tipo),
                    'color' => $board->background_color ?? $this->getDefaultColor($board->tipo),
                    'icon' => $this->getTipoIcon($board->tipo),
                    'lists_count' => $board->lists_count,
                    'active_goals' => $board->active_goals_count ?? 0,
                    'is_favorite' => $board->is_favorite,
                ];
            });

        // Metas urgentes usando GoalService
        $overdueGoals = $this->goalService->getOverdueGoals($userId);
        $upcomingGoals = $this->goalService->getUpcomingGoals($userId, 3);

        $this->urgentGoals = $overdueGoals->merge($upcomingGoals)
            ->unique('id')
            ->sortBy('data_vencimento')
            ->take(10)
            ->map(function($goal) {
                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'board' => $goal->list->board->name,
                    'list' => $goal->list->name,
                    'board_color' => $goal->list->board->background_color,
                    'data_vencimento' => $goal->data_vencimento,
                    'days_left' => now()->diffInDays($goal->data_vencimento, false),
                    'is_atrasada' => $goal->is_atrasada,
                    'progresso' => $goal->progresso_percentual,
                    'prioridade' => $goal->prioridade,
                    'labels' => $goal->labels ?? [],
                ];
            });

        // Metas por período
        $this->goalsByPeriodo = Goal::where('user_id', $userId)
            ->active()
            ->get()
            ->groupBy('periodo')
            ->map(function($goals, $periodo) {
                return [
                    'label' => $this->getPeriodoLabel($periodo),
                    'count' => $goals->count(),
                    'avgProgress' => round($goals->avg('progresso'), 2),
                ];
            });

        // Metas por prioridade
        $this->goalsByPrioridade = Goal::where('user_id', $userId)
            ->active()
            ->get()
            ->groupBy('prioridade')
            ->map(function($goals, $prioridade) {
                return [
                    'label' => ucfirst($prioridade),
                    'count' => $goals->count(),
                    'color' => $this->getPrioridadeColor($prioridade),
                ];
            });

        // Estatísticas de progresso
        $this->progressStats = [
            '0-25' => Goal::where('user_id', $userId)->active()->whereBetween('progresso', [0, 25])->count(),
            '26-50' => Goal::where('user_id', $userId)->active()->whereBetween('progresso', [26, 50])->count(),
            '51-75' => Goal::where('user_id', $userId)->active()->whereBetween('progresso', [51, 75])->count(),
            '76-99' => Goal::where('user_id', $userId)->active()->whereBetween('progresso', [76, 99])->count(),
            '100' => Goal::where('user_id', $userId)->whereNotNull('completed_at')->count(),
        ];

        // Atividades recentes
        $this->recentActivities = \App\Models\GoalActivity::whereHas('goal', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['goal', 'user'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function($activity) {
                return [
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'user_name' => $activity->user->name ?? 'Sistema',
                    'goal_title' => $activity->goal->title ?? '',
                    'icon' => $activity->action_icon,
                    'color' => $activity->action_color,
                    'time_ago' => $activity->created_at->diffForHumans(),
                ];
            });
    }

    private function getTipoLabel($tipo)
    {
        $labels = [
            'financeiro' => 'Financeiro',
            'pessoal' => 'Pessoal',
            'profissional' => 'Profissional',
            'saude' => 'Saúde',
            'estudos' => 'Estudos',
        ];
        return $labels[$tipo] ?? 'Outros';
    }

    private function getDefaultColor($tipo)
    {
        $colors = [
            'financeiro' => '#10B981',
            'pessoal' => '#F59E0B',
            'profissional' => '#3B82F6',
            'saude' => '#EF4444',
            'estudos' => '#8B5CF6',
        ];
        return $colors[$tipo] ?? '#6B7280';
    }

    private function getTipoIcon($tipo)
    {
        $icons = [
            'financeiro' => 'bi-currency-dollar',
            'pessoal' => 'bi-person-heart',
            'profissional' => 'bi-briefcase',
            'saude' => 'bi-heart-pulse',
            'estudos' => 'bi-book',
        ];
        return $icons[$tipo] ?? 'bi-flag';
    }

    private function getPeriodoLabel($periodo)
    {
        $labels = [
            'diario' => 'Diário',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
            'custom' => 'Personalizado',
        ];
        return $labels[$periodo] ?? 'Outros';
    }

    private function getPrioridadeColor($prioridade)
    {
        $colors = [
            'baixa' => '#10B981',
            'media' => '#F59E0B',
            'alta' => '#EF4444',
            'urgente' => '#DC2626',
        ];
        return $colors[$prioridade] ?? '#6B7280';
    }

    public function render()
    {
        return view('livewire.goals.goals-dashboard');
    }
}
