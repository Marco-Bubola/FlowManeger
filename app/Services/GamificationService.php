<?php

namespace App\Services;

use App\Models\UserLevel;
use App\Models\XpLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public const XP_HABIT_COMPLETE   = 10;
    public const XP_GOAL_COMPLETE     = 50;
    public const XP_MILESTONE_REACHED = 25;
    public const XP_STREAK_BONUS      = 5; // por dia de streak mantido

    /**
     * Garante o registro de nível do usuário.
     */
    public function levelFor(int $userId): UserLevel
    {
        return UserLevel::firstOrCreate(
            ['user_id' => $userId],
            ['xp' => 0, 'level' => 1, 'current_streak' => 0, 'best_streak' => 0]
        );
    }

    /**
     * Concede XP, registra log, recalcula nível e streak diário.
     * Retorna ['level' => UserLevel, 'leveledUp' => bool, 'gained' => int].
     */
    public function awardXp(int $userId, int $amount, string $reason, $source = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $reason, $source) {
            $level = $this->levelFor($userId);
            $oldLevel = $level->level;

            $level->xp = max(0, $level->xp + $amount);

            // Recalcula nível com base na curva de XP
            $newLevel = 1;
            while (UserLevel::xpForLevel($newLevel + 1) <= $level->xp) {
                $newLevel++;
                if ($newLevel > 999) break;
            }
            $level->level = $newLevel;

            $this->touchDailyStreak($level);
            $level->save();

            XpLog::create([
                'user_id'     => $userId,
                'amount'      => $amount,
                'reason'      => $reason,
                'source_type' => $source ? get_class($source) : null,
                'source_id'   => $source->id ?? null,
            ]);

            return [
                'level'     => $level,
                'leveledUp' => $newLevel > $oldLevel,
                'gained'    => $amount,
            ];
        });
    }

    /**
     * Atualiza streak global de atividade (dias consecutivos com alguma conclusão).
     */
    protected function touchDailyStreak(UserLevel $level): void
    {
        $today = Carbon::today();
        $last  = $level->last_activity_date ? $level->last_activity_date->copy()->startOfDay() : null;

        if (!$last) {
            $level->current_streak = 1;
        } elseif ($last->isSameDay($today)) {
            // já contou hoje
        } elseif ($last->copy()->addDay()->isSameDay($today)) {
            $level->current_streak += 1;
        } else {
            $level->current_streak = 1;
        }

        $level->best_streak = max($level->best_streak, $level->current_streak);
        $level->last_activity_date = now();
    }

    public function removeXp(int $userId, int $amount, string $reason, $source = null): void
    {
        $this->awardXp($userId, -abs($amount), $reason, $source);
    }

    public function summary(int $userId): array
    {
        $level = $this->levelFor($userId);
        return [
            'xp'             => $level->xp,
            'level'          => $level->level,
            'current_streak' => $level->current_streak,
            'best_streak'    => $level->best_streak,
            'xp_floor'       => $level->xp_floor,
            'xp_ceiling'     => $level->xp_ceiling,
            'progress'       => round($level->progress_to_next_level, 1),
        ];
    }
}
