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

    public function mount()
    {
        // As abas Metas, Hábitos e Conquistas agora são as próprias telas (cabeçalho único da área Pessoal).
        $pages = ['metas' => 'goals.dashboard', 'habitos' => 'daily-habits.dashboard', 'conquistas' => 'achievements.index'];
        if (isset($pages[$this->activeTab])) {
            return $this->redirectRoute($pages[$this->activeTab], navigate: true);
        }
        if (! in_array($this->activeTab, ['hoje', 'insights'], true)) {
            $this->activeTab = 'hoje';
        }

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

        $toggle = app(\App\Services\HabitService::class)->toggleToday($habit, $userId);

        if ($toggle['done']) {
            $result = $toggle['result'];
            $this->dispatch('habit-toggled', done: true, xp: GamificationService::XP_HABIT_COMPLETE, habitId: $habitId);
            if (!empty($result['leveledUp'])) {
                $this->dispatch('level-up', level: $result['level']->level);
                $this->notifySuccess('🎉 Subiu para o nível ' . $result['level']->level . '!');
            }
        } else {
            $this->dispatch('habit-toggled', done: false);
        }

        $this->loadToday();
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
