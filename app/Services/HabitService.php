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
                $existing->increment('completion_count');
                $completion = $existing;
            } else {
                // Criar nova conclusão
                $completion = DailyHabitCompletion::create([
                    'habit_id' => $habit->id,
                    'user_id' => $userId,
                    'completion_date' => $date,
                    'completion_count' => 1,
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
                if ($completion->completion_count > 1) {
                    $completion->decrement('completion_count');
                } else {
                    $completion->delete();
                }

                // Recalcular streak
                $this->updateStreak($habit, $userId);
            }
        });
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
                'last_completion_date' => null,
            ]);
            return $streak;
        }

        // Calcular streak atual
        $currentStreak = 0;
        $lastDate = null;

        foreach ($completions as $completion) {
            $compDate = Carbon::parse($completion->completion_date);

            if ($lastDate === null) {
                // Primeiro dia
                if ($compDate->isToday() || $compDate->isYesterday()) {
                    $currentStreak = 1;
                    $lastDate = $compDate;
                } else {
                    break; // Quebrou a sequência
                }
            } else {
                // Verificar se é consecutivo
                if ($compDate->diffInDays($lastDate) === 1) {
                    $currentStreak++;
                    $lastDate = $compDate;
                } else {
                    break; // Quebrou a sequência
                }
            }
        }

        // Atualizar
        $streak->update([
            'current_streak' => $currentStreak,
            'longest_streak' => max($streak->longest_streak, $currentStreak),
            'last_completion_date' => $completions->first()->completion_date,
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
        $totalCompletions = $completions->sum('completion_count');

        return [
            'current_streak' => $streak->current_streak ?? 0,
            'longest_streak' => $streak->longest_streak ?? 0,
            'total_days' => $totalDays,
            'total_completions' => $totalCompletions,
            'today_count' => $today ? $today->completion_count : 0,
            'goal_frequency' => $habit->goal_frequency,
            'today_progress' => $habit->goal_frequency > 0
                ? round((($today->completion_count ?? 0) / $habit->goal_frequency) * 100, 2)
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
            $count = $todayCompletion ? $todayCompletion->completion_count : 0;

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
