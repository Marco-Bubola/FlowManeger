<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Verificar e desbloquear conquistas para um usuário
     */
    public function checkAndUnlock(int $userId, string $context, array $data = []): array
    {
        $unlockedAchievements = [];

        // Obter conquistas ainda não desbloqueadas
        $achievements = Achievement::whereNotIn('id', function($query) use ($userId) {
            $query->select('achievement_id')
                  ->from('user_achievements')
                  ->where('user_id', $userId);
        })->get();

        foreach ($achievements as $achievement) {
            if ($this->checkCriteria($achievement, $userId, $context, $data)) {
                $unlocked = $this->unlock($userId, $achievement->id);
                if ($unlocked) {
                    $unlockedAchievements[] = $achievement;
                }
            }
        }

        return $unlockedAchievements;
    }

    /**
     * Verificar critérios de uma conquista
     */
    protected function checkCriteria(Achievement $achievement, int $userId, string $context, array $data): bool
    {
        $criteria = $achievement->criteria;

        // Se não tem critérios, não desbloqueia
        if (empty($criteria)) {
            return false;
        }

        // Verificar contexto
        if (isset($criteria['context']) && $criteria['context'] !== $context) {
            return false;
        }

        // Verificar cada tipo de critério
        switch ($achievement->key) {
            case 'first_habit':
                return $context === 'habit_created' && $this->getHabitsCount($userId) === 1;

            case 'habit_master':
                return $this->getHabitsCount($userId) >= ($criteria['count'] ?? 10);

            case 'streak_week':
                return $context === 'habit_completed' && ($data['current_streak'] ?? 0) >= 7;

            case 'streak_month':
                return $context === 'habit_completed' && ($data['current_streak'] ?? 0) >= 30;

            case 'streak_year':
                return $context === 'habit_completed' && ($data['current_streak'] ?? 0) >= 365;

            case 'first_goal':
                return $context === 'goal_created' && $this->getGoalsCount($userId) === 1;

            case 'goal_achiever':
                return $context === 'goal_completed' && $this->getCompletedGoalsCount($userId) >= ($criteria['count'] ?? 10);

            case 'perfect_day':
                return $context === 'habits_checked' && $this->allHabitsCompletedToday($userId);

            case 'consistency_king':
                return $this->has30ConsecutiveDays($userId);

            case 'early_bird':
                return $context === 'habit_completed' && now()->hour < 7;

            default:
                return false;
        }
    }

    /**
     * Desbloquear conquista
     */
    public function unlock(int $userId, int $achievementId): ?UserAchievement
    {
        // Verificar se já tem
        $existing = UserAchievement::where('user_id', $userId)
                                   ->where('achievement_id', $achievementId)
                                   ->first();

        if ($existing) {
            return null;
        }

        return UserAchievement::create([
            'user_id' => $userId,
            'achievement_id' => $achievementId,
            'unlocked_at' => now(),
        ]);
    }

    /**
     * Obter conquistas do usuário
     */
    public function getUserAchievements(int $userId)
    {
        return UserAchievement::where('user_id', $userId)
                             ->with('achievement')
                             ->orderByDesc('unlocked_at')
                             ->get();
    }

    /**
     * Obter estatísticas de conquistas
     */
    public function getUserStats(int $userId): array
    {
        $total = Achievement::count();
        $unlocked = UserAchievement::where('user_id', $userId)->count();
        $points = UserAchievement::where('user_id', $userId)
                                ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
                                ->sum('achievements.points');

        $byRarity = UserAchievement::where('user_id', $userId)
                                  ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
                                  ->select('achievements.rarity', DB::raw('count(*) as count'))
                                  ->groupBy('achievements.rarity')
                                  ->pluck('count', 'rarity')
                                  ->toArray();

        return [
            'total' => $total,
            'unlocked' => $unlocked,
            'locked' => $total - $unlocked,
            'completion_rate' => $total > 0 ? round(($unlocked / $total) * 100, 2) : 0,
            'total_points' => $points,
            'by_rarity' => [
                'bronze' => $byRarity['bronze'] ?? 0,
                'silver' => $byRarity['silver'] ?? 0,
                'gold' => $byRarity['gold'] ?? 0,
                'platinum' => $byRarity['platinum'] ?? 0,
            ],
        ];
    }

    // Métodos auxiliares
    protected function getHabitsCount(int $userId): int
    {
        return \App\Models\DailyHabit::where('user_id', $userId)->count();
    }

    protected function getGoalsCount(int $userId): int
    {
        return \App\Models\Goal::where('user_id', $userId)->count();
    }

    protected function getCompletedGoalsCount(int $userId): int
    {
        return \App\Models\Goal::where('user_id', $userId)
                              ->whereNotNull('completed_at')
                              ->count();
    }

    protected function allHabitsCompletedToday(int $userId): bool
    {
        $habits = \App\Models\DailyHabit::where('user_id', $userId)
                                       ->where('is_active', true)
                                       ->get();

        if ($habits->isEmpty()) {
            return false;
        }

        foreach ($habits as $habit) {
            if (!$habit->isCompletedToday()) {
                return false;
            }
        }

        return true;
    }

    protected function has30ConsecutiveDays(int $userId): bool
    {
        $streak = \App\Models\DailyHabitStreak::where('user_id', $userId)
                                             ->max('current_streak');

        return $streak >= 30;
    }
}
