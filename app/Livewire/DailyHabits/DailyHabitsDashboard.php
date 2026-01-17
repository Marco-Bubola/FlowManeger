<?php

namespace App\Livewire\DailyHabits;

use App\Models\DailyHabit;
use App\Models\DailyHabitCompletion;
use App\Models\DailyHabitStreak;
use App\Services\HabitService;
use App\Services\AchievementService;
use Carbon\Carbon;
use Livewire\Component;

class DailyHabitsDashboard extends Component
{
    public $habits = [];
    public $stats = [];
    public $achievementStats = [];
    public $recentAchievements = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedHabit = null;

    // Form fields
    public $name = '';
    public $description = '';
    public $icon = 'bi-check-circle';
    public $color = '#3B82F6';
    public $goal_frequency = 1;
    public $reminder_time = '';

    // Available icons
    public $availableIcons = [
        'bi-check-circle' => 'Check',
        'bi-heart' => 'Coração',
        'bi-droplet' => 'Água',
        'bi-trophy' => 'Troféu',
        'bi-book' => 'Livro',
        'bi-bicycle' => 'Exercício',
        'bi-moon-stars' => 'Sono',
        'bi-apple' => 'Alimentação',
        'bi-clipboard-check' => 'Tarefas',
        'bi-chat-dots' => 'Comunicação',
        'bi-brush' => 'Criatividade',
        'bi-graph-up' => 'Produtividade',
    ];

    // Available colors
    public $availableColors = [
        '#3B82F6' => 'Azul',
        '#10B981' => 'Verde',
        '#F59E0B' => 'Laranja',
        '#EF4444' => 'Vermelho',
        '#8B5CF6' => 'Roxo',
        '#EC4899' => 'Rosa',
        '#06B6D4' => 'Ciano',
        '#84CC16' => 'Lima',
    ];

    protected $habitService;
    protected $achievementService;

    public function boot(HabitService $habitService, AchievementService $achievementService)
    {
        $this->habitService = $habitService;
        $this->achievementService = $achievementService;
    }

    public function mount()
    {
        $this->loadHabits();
        $this->loadStats();
    }

    public function loadHabits()
    {
        $userId = auth()->id();

        $this->habits = DailyHabit::where('user_id', $userId)
            ->active()
            ->ordered()
            ->with(['streak', 'completions' => function($query) {
                $query->whereDate('completion_date', Carbon::today());
            }])
            ->get()
            ->map(function($habit) {
                $streak = $habit->streak ?? new DailyHabitStreak();
                $todayCompletion = $habit->completions->first();

                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'icon' => $habit->icon,
                    'color' => $habit->color,
                    'goal_frequency' => $habit->goal_frequency,
                    'is_completed_today' => $todayCompletion !== null,
                    'times_completed_today' => $todayCompletion?->times_completed ?? 0,
                    'current_streak' => $streak->current_streak ?? 0,
                    'longest_streak' => $streak->longest_streak ?? 0,
                    'total_completions' => $streak->total_completions ?? 0,
                    'completion_rate' => $habit->getCompletionRate(30),
                ];
            })
            ->toArray();
    }

    public function loadStats()
    {
        $userId = auth()->id();

        // Usar HabitService para KPIs
        $kpis = $this->habitService->getUserKPIs($userId);

        $this->stats = [
            'total_habits' => $kpis['total_habits'],
            'completed_today' => $kpis['completed_today'],
            'pending_today' => $kpis['pending_today'],
            'completion_percentage' => $kpis['completion_rate_today'],
            'total_goal_today' => $kpis['total_goal_today'],
        ];

        // Stats de achievements
        $this->achievementStats = $this->achievementService->getUserStats($userId);

        // Achievements recentes relacionados a hábitos
        $allAchievements = $this->achievementService->getUserAchievements($userId);
        $this->recentAchievements = $allAchievements
            ->filter(fn($ua) => in_array($ua->achievement->category, ['habits', 'streak']))
            ->sortByDesc('unlocked_at')
            ->take(3);
    }

    public function toggleHabit($habitId)
    {
        try {
            \Log::info('[DailyHabits] Iniciando toggleHabit', [
                'habit_id' => $habitId,
                'user_id' => auth()->id()
            ]);

            $habit = DailyHabit::where('id', $habitId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$habit) {
                \Log::warning('[DailyHabits] Hábito não encontrado', ['habit_id' => $habitId]);
                session()->flash('message', '❌ Hábito não encontrado');
                session()->flash('message_type', 'error');
                return;
            }

            $today = Carbon::today();
            $completion = DailyHabitCompletion::where('habit_id', $habitId)
                ->where('user_id', auth()->id())
                ->whereDate('completion_date', $today)
                ->first();

            if ($completion) {
                \Log::info('[DailyHabits] Desmarcando hábito', ['completion_id' => $completion->id]);
                // Usar HabitService para desmarcar
                $this->habitService->uncomplete($habit, auth()->id(), $today);
                $message = '⭕ Hábito desmarcado!';
            } else {
                \Log::info('[DailyHabits] Marcando hábito como concluído');
                // Usar HabitService para completar
                $this->habitService->complete($habit, auth()->id(), $today);

                // Verificar achievements
                $unlockedAchievements = $this->achievementService->checkAndUnlock(
                    auth()->id(),
                    'habit_completed',
                    ['habit_id' => $habitId]
                );

                // Emitir evento de achievement se algum foi desbloqueado
                if (!empty($unlockedAchievements)) {
                    foreach ($unlockedAchievements as $achievement) {
                        $this->dispatch('achievement-unlocked', [
                            'name' => $achievement->name,
                            'description' => $achievement->description,
                            'icon' => $achievement->icon,
                            'rarity' => $achievement->rarity,
                            'rarity_color' => $achievement->rarity_color,
                            'points' => $achievement->points,
                        ]);
                    }
                }

                $message = '🎉 Hábito concluído!';
            }

            $this->loadHabits();
            $this->loadStats();

            session()->flash('message', $message);
            session()->flash('message_type', 'success');

            \Log::info('[DailyHabits] Toggle concluído com sucesso');

        } catch (\Exception $e) {
            \Log::error('[DailyHabits] Erro em toggleHabit', [
                'message' => $e->getMessage(),
                'habit_id' => $habitId,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', '❌ Erro ao atualizar hábito: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }


        $today = Carbon::today();
        $lastCompletion = $streak->last_completion_date ? Carbon::parse($streak->last_completion_date) : null;

        if ($completed) {
            // Incrementar streak
            if ($lastCompletion && $lastCompletion->isYesterday()) {
                $streak->current_streak++;
            } elseif (!$lastCompletion || !$lastCompletion->isToday()) {
                $streak->current_streak = 1;
            }

            $streak->total_completions++;
            $streak->last_completion_date = $today;

            if ($streak->current_streak > $streak->longest_streak) {
                $streak->longest_streak = $streak->current_streak;
            }
        } else {
            // Decrementar (desmarcou)
            if ($lastCompletion && $lastCompletion->isToday()) {
                $streak->current_streak = max(0, $streak->current_streak - 1);
                $streak->total_completions = max(0, $streak->total_completions - 1);
                $streak->last_completion_date = $streak->total_completions > 0 ? $today->copy()->subDay() : null;
            }
        }

        $streak->save();
    }

    public function openCreateModal()
    {
        \Log::info('[DailyHabits] Abrindo modal de criação');
        try {
            $this->resetForm();
            $this->showCreateModal = true;
            \Log::info('[DailyHabits] Modal aberto com sucesso', ['showCreateModal' => $this->showCreateModal]);
        } catch (\Exception $e) {
            \Log::error('[DailyHabits] Erro ao abrir modal', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', '❌ Erro ao abrir modal: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function openEditModal($habitId)
    {
        $habit = DailyHabit::where('id', $habitId)
            ->where('user_id', auth()->id())
            ->first();

        if ($habit) {
            $this->selectedHabit = $habit->id;
            $this->name = $habit->name;
            $this->description = $habit->description;
            $this->icon = $habit->icon;
            $this->color = $habit->color;
            $this->goal_frequency = $habit->goal_frequency;
            $this->reminder_time = $habit->reminder_time ? $habit->reminder_time->format('H:i') : '';
            $this->showEditModal = true;
        }
    }

    public function createHabit()
    {
        try {
            \Log::info('[DailyHabits] Iniciando createHabit', [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'icon' => $this->icon,
                'color' => $this->color,
            ]);

            $validated = $this->validate([
                'name' => 'required|min:3|max:255',
                'description' => 'nullable|max:500',
                'icon' => 'required|in:' . implode(',', array_keys($this->availableIcons)),
                'color' => 'required',
                'goal_frequency' => 'required|integer|min:1|max:10',
                'reminder_time' => 'nullable|date_format:H:i',
            ], [
                'name.required' => 'O nome do hábito é obrigatório',
                'name.min' => 'O nome deve ter pelo menos 3 caracteres',
                'name.max' => 'O nome não pode ter mais de 255 caracteres',
                'icon.required' => 'Selecione um ícone',
                'icon.in' => 'Ícone inválido',
                'color.required' => 'Selecione uma cor',
                'goal_frequency.required' => 'A meta diária é obrigatória',
                'goal_frequency.integer' => 'A meta deve ser um número inteiro',
                'goal_frequency.min' => 'A meta deve ser pelo menos 1',
                'goal_frequency.max' => 'A meta não pode ser maior que 10',
                'reminder_time.date_format' => 'Formato de horário inválido (HH:MM)',
            ]);

            \Log::info('[DailyHabits] Validação passou');

            $maxOrder = DailyHabit::where('user_id', auth()->id())->max('order') ?? 0;

            $habit = DailyHabit::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'icon' => $this->icon,
                'color' => $this->color,
                'goal_frequency' => $this->goal_frequency,
                'reminder_time' => $this->reminder_time ?: null,
                'order' => $maxOrder + 1,
            ]);

            \Log::info('[DailyHabits] Hábito criado com sucesso', ['habit_id' => $habit->id]);

            $this->showCreateModal = false;
            $this->loadHabits();
            $this->loadStats();

            session()->flash('message', '✨ Hábito criado com sucesso!');
            session()->flash('message_type', 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[DailyHabits] Erro de validação', [
                'errors' => $e->errors(),
                'data' => [
                    'name' => $this->name,
                    'icon' => $this->icon,
                    'color' => $this->color,
                ]
            ]);
            session()->flash('message', '❌ Erro de validação. Verifique os campos.');
            session()->flash('message_type', 'error');
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[DailyHabits] Erro ao criar hábito', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', '❌ Erro ao criar hábito: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function updateHabit()
    {
        $validated = $this->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|max:500',
            'icon' => 'required|in:' . implode(',', array_keys($this->availableIcons)),
            'color' => 'required',
            'goal_frequency' => 'required|integer|min:1|max:10',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        $habit = DailyHabit::where('id', $this->selectedHabit)
            ->where('user_id', auth()->id())
            ->first();

        if ($habit) {
            $habit->update([
                'name' => $this->name,
                'description' => $this->description,
                'icon' => $this->icon,
                'color' => $this->color,
                'goal_frequency' => $this->goal_frequency,
                'reminder_time' => $this->reminder_time ?: null,
            ]);

            $this->showEditModal = false;
            $this->loadHabits();

            session()->flash('message', '✅ Hábito atualizado!');
        }
    }

    public function deleteHabit($habitId)
    {
        $habit = DailyHabit::where('id', $habitId)
            ->where('user_id', auth()->id())
            ->first();

        if ($habit) {
            $habit->update(['is_active' => false]);
            $this->loadHabits();
            $this->loadStats();

            session()->flash('message', '🗑️ Hábito arquivado!');
        }
    }

    private function resetForm()
    {
        $this->selectedHabit = null;
        $this->name = '';
        $this->description = '';
        $this->icon = 'bi-check-circle';
        $this->color = '#3B82F6';
        $this->goal_frequency = 1;
        $this->reminder_time = '';
    }

    public function render()
    {
        return view('livewire.daily-habits.daily-habits-dashboard');
    }
}
