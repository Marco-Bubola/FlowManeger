<?php

namespace App\Livewire\Conquistas;

use App\Models\DailyHabit;
use App\Models\DailyHabitCompletion;
use App\Models\DailyHabitStreak;
use App\Models\Goal;
use App\Services\GamificationService;
use App\Services\QuoteService;
use App\Services\AiSuggestionService;
use App\Traits\HasNotifications;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class ConquistasHub extends Component
{
    use HasNotifications;

    #[Url(as: 'aba')]
    public string $activeTab = 'hoje';

    public array $level = [];
    public array $quote = [];
    public array $todayHabits = [];
    public array $urgentGoals = [];
    public array $dayProgress = ['done' => 0, 'total' => 0, 'percent' => 0];

    // IA
    public bool $aiConfigured = false;
    public array $aiHabitSuggestions = [];
    public ?int $aiForGoalId = null;
    public bool $aiLoading = false;

    protected GamificationService $gamification;
    protected QuoteService $quotes;
    protected AiSuggestionService $ai;

    public function boot(GamificationService $gamification, QuoteService $quotes, AiSuggestionService $ai): void
    {
        $this->gamification = $gamification;
        $this->quotes = $quotes;
        $this->ai = $ai;
    }

    public function mount(): void
    {
        $this->aiConfigured = $this->ai->isConfigured();
        $this->loadToday();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab === 'hoje') {
            $this->loadToday();
        }
    }

    public function loadToday(): void
    {
        $userId = Auth::id();

        $this->level = $this->gamification->summary($userId);
        $this->quote = $this->quotes->quoteOfTheDay();

        $today = Carbon::today();

        $habits = DailyHabit::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_archived', false)->orWhereNull('is_archived');
            })
            ->orderBy('order')->orderBy('created_at')
            ->get();

        $completionIds = DailyHabitCompletion::where('user_id', $userId)
            ->whereDate('completion_date', $today)
            ->pluck('habit_id')->all();

        $scheduled = $habits->filter(fn ($h) => $h->isScheduledFor($today));

        $done = 0;
        $this->todayHabits = $scheduled->map(function ($h) use ($completionIds, &$done) {
            $isDone = in_array($h->id, $completionIds);
            if ($isDone) $done++;
            return [
                'id'          => $h->id,
                'name'        => $h->name,
                'icon'        => $h->icon ?: 'bi-check2-circle',
                'color'       => $h->color ?: '#a490c2',
                'type'        => $h->type ?? 'boolean',
                'unit'        => $h->unit,
                'target'      => $h->target_value,
                'done'        => $isDone,
            ];
        })->values()->toArray();

        $total = count($this->todayHabits);
        $this->dayProgress = [
            'done'    => $done,
            'total'   => $total,
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];

        // Metas vencendo em 7 dias
        $this->urgentGoals = Goal::where('user_id', $userId)
            ->whereNull('completed_at')
            ->where('is_archived', false)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', $today)
            ->whereDate('data_vencimento', '<=', $today->copy()->addDays(7))
            ->orderBy('data_vencimento')
            ->limit(6)
            ->get()
            ->map(fn ($g) => [
                'id'         => $g->id,
                'title'      => $g->title,
                'progresso'  => (float) $g->progresso,
                'vencimento' => optional($g->data_vencimento)->format('d/m'),
                'prioridade' => $g->prioridade,
                'cor'        => $g->cor ?: '#4a4e8f',
            ])->toArray();
    }

    public function toggleHabit(int $habitId): void
    {
        $userId = Auth::id();
        $habit = DailyHabit::where('id', $habitId)->where('user_id', $userId)->first();
        if (!$habit) {
            $this->notifyError('Hábito não encontrado.');
            return;
        }

        $today = Carbon::today();
        $completion = DailyHabitCompletion::where('habit_id', $habitId)
            ->where('user_id', $userId)
            ->whereDate('completion_date', $today)
            ->first();

        if ($completion) {
            // Desmarcar
            $completion->delete();
            $this->updateStreak($habit, $userId, false);
            $this->gamification->removeXp($userId, GamificationService::XP_HABIT_COMPLETE, 'habit_uncomplete', $habit);
            $this->applyHabitToGoals($habit, -1);
            $this->dispatch('habit-toggled', done: false);
        } else {
            // Marcar
            DailyHabitCompletion::create([
                'habit_id'        => $habitId,
                'user_id'         => $userId,
                'completion_date' => $today,
                'times_completed' => 1,
            ]);
            $this->updateStreak($habit, $userId, true);
            $result = $this->gamification->awardXp($userId, GamificationService::XP_HABIT_COMPLETE, 'habit_complete', $habit);
            $this->applyHabitToGoals($habit, 1);

            $this->dispatch('habit-toggled', done: true, xp: GamificationService::XP_HABIT_COMPLETE, habitId: $habitId);
            if (!empty($result['leveledUp'])) {
                $this->dispatch('level-up', level: $result['level']->level);
                $this->notifySuccess('🎉 Subiu para o nível ' . $result['level']->level . '!');
            }
        }

        $this->loadToday();
    }

    protected function updateStreak(DailyHabit $habit, int $userId, bool $completed): void
    {
        $streak = DailyHabitStreak::firstOrCreate(
            ['habit_id' => $habit->id, 'user_id' => $userId],
            ['current_streak' => 0, 'longest_streak' => 0, 'total_completions' => 0]
        );

        $today = Carbon::today();
        $last = $streak->last_completion_date ? Carbon::parse($streak->last_completion_date) : null;

        if ($completed) {
            if ($last && $last->isYesterday()) {
                $streak->current_streak++;
            } elseif (!$last || !$last->isToday()) {
                $streak->current_streak = 1;
            }
            $streak->total_completions++;
            $streak->last_completion_date = $today;
            $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        } else {
            if ($last && $last->isToday()) {
                $streak->current_streak = max(0, $streak->current_streak - 1);
                $streak->total_completions = max(0, $streak->total_completions - 1);
                $streak->last_completion_date = $streak->total_completions > 0 ? $today->copy()->subDay() : null;
            }
        }

        $streak->save();
    }

    /**
     * Aplica o impacto do hábito nas metas vinculadas do tipo "habito".
     * $direction = +1 (concluiu) ou -1 (desmarcou).
     */
    protected function applyHabitToGoals(DailyHabit $habit, int $direction): void
    {
        $goals = $habit->metaGoals()->where('goals.tipo_meta', 'habito')->get();

        foreach ($goals as $goal) {
            $peso = (float) ($goal->pivot->peso ?? 5);

            if ($goal->valor_meta > 0) {
                $novoValor = max(0, (float) $goal->valor_atual + ($peso * $direction));
                $goal->valor_atual = $novoValor;
                $goal->progresso = min(100, ($novoValor / (float) $goal->valor_meta) * 100);
            } else {
                $goal->progresso = max(0, min(100, (float) $goal->progresso + ($peso * $direction)));
            }

            if ($goal->progresso >= 100 && is_null($goal->completed_at)) {
                $goal->completed_at = now();
                $this->gamification->awardXp(Auth::id(), GamificationService::XP_GOAL_COMPLETE, 'goal_complete', $goal);
            } elseif ($goal->progresso < 100 && $direction < 0) {
                $goal->completed_at = null;
            }

            $goal->save();
        }
    }

    /**
     * IA: sugerir hábitos para uma meta (Claude API).
     */
    public function suggestHabits(int $goalId): void
    {
        $goal = Goal::where('id', $goalId)->where('user_id', Auth::id())->first();
        if (!$goal) return;

        $this->aiForGoalId = $goalId;
        $this->aiHabitSuggestions = $this->ai->suggestHabitsForGoal($goal->title, $goal->description);

        if (empty($this->aiHabitSuggestions)) {
            $this->notifyWarning('Não consegui gerar sugestões agora.');
        } else {
            $msg = $this->aiConfigured ? 'Sugestões geradas pela IA!' : 'Sugestões (modo local — configure ANTHROPIC_API_KEY para IA real).';
            $this->notifyInfo($msg);
        }
    }

    /**
     * Cria um hábito a partir de uma sugestão da IA.
     */
    public function createHabitFromSuggestion(int $index): void
    {
        $s = $this->aiHabitSuggestions[$index] ?? null;
        if (!$s) return;

        $habit = DailyHabit::create([
            'user_id'        => Auth::id(),
            'name'           => $s['name'],
            'description'    => $s['description'] ?? null,
            'icon'           => $s['icon'] ?? 'bi-check2-circle',
            'color'          => '#a490c2',
            'goal_frequency' => 1,
            'frequency_type' => 'daily',
            'type'           => 'boolean',
            'is_active'      => true,
            'is_archived'    => false,
            'order'          => 0,
        ]);

        // Vincula à meta (se houver) com peso padrão
        if ($this->aiForGoalId) {
            $habit->metaGoals()->syncWithoutDetaching([$this->aiForGoalId => ['peso' => 5]]);
        }

        unset($this->aiHabitSuggestions[$index]);
        $this->aiHabitSuggestions = array_values($this->aiHabitSuggestions);
        $this->notifySuccess('Hábito "' . $habit->name . '" criado!');
        $this->loadToday();
    }

    public function render()
    {
        return view('livewire.conquistas.conquistas-hub')
            ->layout('components.layouts.app');
    }
}
