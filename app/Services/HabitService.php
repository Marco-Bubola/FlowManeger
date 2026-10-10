<?php

namespace App\Services;

use App\Models\DailyHabit;
use App\Models\DailyHabitCompletion;
use App\Models\DailyHabitStreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HabitService
{
    /**
     * Criar novo hábito
     */
    public function create(array $data, int $userId): DailyHabit
    {
        $data['user_id'] = $userId;

        // Calcular order automático
        if (!isset($data['order'])) {
            $data['order'] = DailyHabit::where('user_id', $userId)->max('order') + 1;
        }

        $habit = DailyHabit::create($data);

        // Criar streak inicial
        DailyHabitStreak::create([
            'habit_id' => $habit->id,
            'user_id' => $userId,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_completion_date' => null,
        ]);

        return $habit->fresh();
    }

    /**
     * Atualizar hábito
     */
    public function update(DailyHabit $habit, array $data): DailyHabit
    {
        $habit->update($data);
        return $habit->fresh();
    }

    /**
     * Marcar conclusão do hábito
     */
    public function complete(DailyHabit $habit, int $userId, Carbon $date = null): DailyHabitCompletion
    {
        return DB::transaction(function () use ($habit, $userId, $date) {
            $date = $date ?? Carbon::today();

            // Verificar se já completou hoje
            $existing = DailyHabitCompletion::where('habit_id', $habit->id)
                ->where('user_id', $userId)
                ->whereDate('completion_date', $date)
                ->first();

            if ($existing) {
                // Incrementar contador
                $existing->increment('times_completed');
                $completion = $existing;
            } else {
                // Criar nova conclusão
                $completion = DailyHabitCompletion::create([
                    'habit_id' => $habit->id,
                    'user_id' => $userId,
                    'completion_date' => $date,
                    'times_completed' => 1,
                ]);
            }

            // Atualizar streak
            $this->updateStreak($habit, $userId);

            return $completion;
        });
    }

    /**
     * Desmarcar conclusão
     */
    public function uncomplete(DailyHabit $habit, int $userId, Carbon $date = null): void
    {
        DB::transaction(function () use ($habit, $userId, $date) {
            $date = $date ?? Carbon::today();

            $completion = DailyHabitCompletion::where('habit_id', $habit->id)
                ->where('user_id', $userId)
                ->whereDate('completion_date', $date)
                ->first();

            if ($completion) {
                if ($completion->times_completed > 1) {
                    $completion->decrement('times_completed');
                } else {
                    $completion->delete();
                }

                // Recalcular streak
                $this->updateStreak($habit, $userId);
            }
        });
    }

    /**
     * Marca ou desmarca o hábito de hoje com um toque. Usado pela tela Hoje e pela tela Hábitos,
     * para as duas darem o mesmo resultado: sequência, XP e progresso das metas ligadas ao hábito.
     */
    public function toggleToday(DailyHabit $habit, int $userId): array
    {
        return DB::transaction(fn () => $this->doToggleToday($habit, $userId));
    }

    protected function doToggleToday(DailyHabit $habit, int $userId): array
    {
        $today = Carbon::today();
        $gamification = app(GamificationService::class);

        $done = DailyHabitCompletion::where('habit_id', $habit->id)
            ->where('user_id', $userId)
            ->whereDate('completion_date', $today)
            ->exists();

        if ($done) {
            DailyHabitCompletion::where('habit_id', $habit->id)
                ->where('user_id', $userId)
                ->whereDate('completion_date', $today)
                ->delete();
            $this->updateStreak($habit, $userId);
            $gamification->removeXp($userId, GamificationService::XP_HABIT_COMPLETE, 'habit_uncomplete', $habit);
            $this->applyToGoals($habit, $userId, -1);

            return ['done' => false, 'result' => []];
        }

        DailyHabitCompletion::create([
            'habit_id' => $habit->id,
            'user_id' => $userId,
            'completion_date' => $today,
            'times_completed' => 1,
        ]);
        $this->updateStreak($habit, $userId);
        $result = $gamification->awardXp($userId, GamificationService::XP_HABIT_COMPLETE, 'habit_complete', $habit);
        $this->applyToGoals($habit, $userId, 1);

        return ['done' => true, 'result' => $result];
    }

    /**
     * Aplica o impacto do hábito nas metas vinculadas do tipo "habito".
     * $direction = +1 (concluiu) ou -1 (desmarcou).
     */
    protected function applyToGoals(DailyHabit $habit, int $userId, int $direction): void
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
                app(GamificationService::class)->awardXp($userId, GamificationService::XP_GOAL_COMPLETE, 'goal_complete', $goal);
            } elseif ($goal->progresso < 100 && $direction < 0) {
                $goal->completed_at = null;
            }

            $goal->save();
        }
    }

    /**
     * Atualizar streak do hábito
     */
    public function updateStreak(DailyHabit $habit, int $userId): DailyHabitStreak
    {
        $streak = DailyHabitStreak::firstOrCreate(
            [
                'habit_id' => $habit->id,
                'user_id' => $userId,
            ],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_completion_date' => null,
            ]
        );

        // Obter todas as conclusões ordenadas
        $completions = DailyHabitCompletion::where('habit_id', $habit->id)
            ->where('user_id', $userId)
            ->orderByDesc('completion_date')
            ->get();

        if ($completions->isEmpty()) {
            $streak->update([
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_completions' => 0,
                'last_completion_date' => null,
            ]);
            return $streak;
        }

        // Dias distintos com conclusão, do mais recente para o mais antigo
        $days = $completions
            ->map(fn ($c) => Carbon::parse($c->completion_date)->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->values();

        // Sequência atual: só conta se a última conclusão foi hoje ou ontem
        $currentStreak = 0;
        if ($days[0]->isToday() || $days[0]->isYesterday()) {
            $currentStreak = 1;
            for ($i = 1; $i < $days->count(); $i++) {
                if ((int) round($days[$i]->diffInDays($days[$i - 1], true)) !== 1) {
                    break;
                }
                $currentStreak++;
            }
        }

        // Recorde: maior sequência em todo o histórico
        $longest = 1;
        $run = 1;
        for ($i = 1; $i < $days->count(); $i++) {
            $run = (int) round($days[$i]->diffInDays($days[$i - 1], true)) === 1 ? $run + 1 : 1;
            $longest = max($longest, $run);
        }

        $streak->update([
            'current_streak' => $currentStreak,
            'longest_streak' => $longest,
            'total_completions' => $days->count(),
            'last_completion_date' => $days[0]->toDateString(),
        ]);

        return $streak->fresh();
    }

    /**
     * Obter estatísticas do hábito
     */
    public function getHabitStats(DailyHabit $habit, int $userId): array
    {
        $streak = $habit->streak;
        $today = $habit->getTodayCompletion();

        $completions = DailyHabitCompletion::where('habit_id', $habit->id)
            ->where('user_id', $userId)
            ->get();

        $totalDays = $completions->count();
        $totalCompletions = $completions->sum('times_completed');

        return [
            'current_streak' => $streak->current_streak ?? 0,
            'longest_streak' => $streak->longest_streak ?? 0,
            'total_days' => $totalDays,
            'total_completions' => $totalCompletions,
            'today_count' => $today ? $today->times_completed : 0,
            'goal_frequency' => $habit->goal_frequency,
            'today_progress' => $habit->goal_frequency > 0
                ? round((($today->times_completed ?? 0) / $habit->goal_frequency) * 100, 2)
                : 0,
            'is_completed_today' => $habit->isCompletedToday(),
        ];
    }

    /**
     * Obter hábitos do dia
     */
    public function getTodayHabits(int $userId)
    {
        return DailyHabit::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['completions' => function ($query) {
                $query->whereDate('completion_date', Carbon::today());
            }, 'streak'])
            ->orderBy('order')
            ->get();
    }

    /**
     * Obter KPIs do usuário
     */
    public function getUserKPIs(int $userId): array
    {
        $habits = DailyHabit::where('user_id', $userId)->where('is_active', true)->get();
        $today = Carbon::today();

        $completedToday = 0;
        $totalGoalToday = 0;

        foreach ($habits as $habit) {
            $todayCompletion = $habit->getTodayCompletion();
            $count = $todayCompletion ? $todayCompletion->times_completed : 0;

            if ($count >= $habit->goal_frequency) {
                $completedToday++;
            }

            $totalGoalToday += $habit->goal_frequency;
        }

        return [
            'total_habits' => $habits->count(),
            'completed_today' => $completedToday,
            'pending_today' => $habits->count() - $completedToday,
            'completion_rate_today' => $habits->count() > 0
                ? round(($completedToday / $habits->count()) * 100, 2)
                : 0,
            'total_goal_today' => $totalGoalToday,
        ];
    }
}
