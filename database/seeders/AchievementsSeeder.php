<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            // BRONZE - Conquistas iniciais
            [
                'key' => 'first_habit',
                'name' => 'Primeiro Passo',
                'description' => 'Crie seu primeiro hábito diário',
                'category' => 'habits',
                'rarity' => 'bronze',
                'icon' => 'bi bi-bookmark-plus',
                'color' => '#CD7F32',
                'points' => 10,
                'criteria' => json_encode(['type' => 'first_habit']),
                'is_secret' => false,
                'order' => 1,
            ],
            [
                'key' => 'habit_streak_3',
                'name' => 'Consistência Inicial',
                'description' => 'Mantenha um hábito por 3 dias consecutivos',
                'category' => 'streak',
                'rarity' => 'bronze',
                'icon' => 'bi bi-fire',
                'color' => '#CD7F32',
                'points' => 15,
                'criteria' => json_encode(['type' => 'habit_streak_3', 'days' => 3]),
                'is_secret' => false,
                'order' => 2,
            ],
            [
                'key' => 'first_goal',
                'name' => 'Primeiro Objetivo',
                'description' => 'Crie sua primeira meta',
                'category' => 'goals',
                'rarity' => 'bronze',
                'icon' => 'bi bi-flag',
                'color' => '#CD7F32',
                'points' => 10,
                'criteria' => json_encode(['type' => 'first_goal']),
                'is_secret' => false,
                'order' => 3,
            ],
            [
                'key' => 'goal_completed_first',
                'name' => 'Conquistador Iniciante',
                'description' => 'Complete sua primeira meta',
                'category' => 'goals',
                'rarity' => 'bronze',
                'icon' => 'bi bi-check-circle',
                'color' => '#CD7F32',
                'points' => 20,
                'criteria' => json_encode(['type' => 'goal_completed_first']),
                'is_secret' => false,
                'order' => 4,
            ],

            // SILVER - Conquistas intermediárias
            [
                'key' => 'streak_week',
                'name' => 'Semana Perfeita',
                'description' => 'Mantenha um hábito por 7 dias consecutivos',
                'category' => 'streak',
                'rarity' => 'silver',
                'icon' => 'bi bi-calendar-week',
                'color' => '#C0C0C0',
                'points' => 50,
                'criteria' => json_encode(['type' => 'streak_week', 'days' => 7]),
                'is_secret' => false,
                'order' => 5,
            ],
            [
                'key' => 'habit_master',
                'name' => 'Mestre dos Hábitos',
                'description' => 'Crie 10 hábitos diferentes',
                'category' => 'habits',
                'rarity' => 'silver',
                'icon' => 'bi bi-list-stars',
                'color' => '#C0C0C0',
                'points' => 40,
                'criteria' => json_encode(['type' => 'habit_master', 'count' => 10]),
                'is_secret' => false,
                'order' => 6,
            ],
            [
                'key' => 'perfect_day',
                'name' => 'Dia Perfeito',
                'description' => 'Complete todos os seus hábitos em um único dia',
                'category' => 'habits',
                'rarity' => 'silver',
                'icon' => 'bi bi-star-fill',
                'color' => '#C0C0C0',
                'points' => 30,
                'criteria' => json_encode(['type' => 'perfect_day']),
                'is_secret' => false,
                'order' => 7,
            ],
            [
                'key' => 'goal_creator',
                'name' => 'Planejador',
                'description' => 'Crie 5 metas',
                'category' => 'goals',
                'rarity' => 'silver',
                'icon' => 'bi bi-clipboard-check',
                'color' => '#C0C0C0',
                'points' => 30,
                'criteria' => json_encode(['type' => 'goal_creator', 'count' => 5]),
                'is_secret' => false,
                'order' => 8,
            ],
            [
                'key' => 'goal_achiever',
                'name' => 'Realizador',
                'description' => 'Complete 5 metas',
                'category' => 'goals',
                'rarity' => 'silver',
                'icon' => 'bi bi-trophy',
                'color' => '#C0C0C0',
                'points' => 50,
                'criteria' => json_encode(['type' => 'goal_achiever', 'count' => 5]),
                'is_secret' => false,
                'order' => 9,
            ],

            // GOLD - Conquistas avançadas
            [
                'key' => 'streak_month',
                'name' => 'Mês Impecável',
                'description' => 'Mantenha um hábito por 30 dias consecutivos',
                'category' => 'streak',
                'rarity' => 'gold',
                'icon' => 'bi bi-calendar-month',
                'color' => '#FFD700',
                'points' => 100,
                'criteria' => json_encode(['type' => 'streak_month', 'days' => 30]),
                'is_secret' => false,
                'order' => 10,
            ],
            [
                'key' => 'consistency_king',
                'name' => 'Rei da Consistência',
                'description' => 'Mantenha uma sequência de 30 dias perfeitos',
                'category' => 'general',
                'rarity' => 'gold',
                'icon' => 'bi bi-crown',
                'color' => '#FFD700',
                'points' => 150,
                'criteria' => json_encode(['type' => 'consistency_king', 'days' => 30]),
                'is_secret' => false,
                'order' => 11,
            ],
            [
                'key' => 'early_bird',
                'name' => 'Madrugador',
                'description' => 'Complete seus hábitos antes das 8h por 7 dias seguidos',
                'category' => 'habits',
                'rarity' => 'gold',
                'icon' => 'bi bi-sunrise',
                'color' => '#FFD700',
                'points' => 80,
                'criteria' => json_encode(['type' => 'early_bird', 'days' => 7, 'hour' => 8]),
                'is_secret' => false,
                'order' => 12,
            ],
            [
                'key' => 'goal_master',
                'name' => 'Mestre das Metas',
                'description' => 'Complete 20 metas',
                'category' => 'goals',
                'rarity' => 'gold',
                'icon' => 'bi bi-bullseye',
                'color' => '#FFD700',
                'points' => 120,
                'criteria' => json_encode(['type' => 'goal_master', 'count' => 20]),
                'is_secret' => false,
                'order' => 13,
            ],

            // PLATINUM - Conquistas lendárias
            [
                'key' => 'streak_year',
                'name' => 'Disciplina Absoluta',
                'description' => 'Mantenha um hábito por 365 dias consecutivos',
                'category' => 'streak',
                'rarity' => 'platinum',
                'icon' => 'bi bi-gem',
                'color' => '#E5E4E2',
                'points' => 500,
                'criteria' => json_encode(['type' => 'streak_year', 'days' => 365]),
                'is_secret' => false,
                'order' => 14,
            ],
            [
                'key' => 'platinum_collector',
                'name' => 'Colecionador Supremo',
                'description' => 'Desbloqueie todas as conquistas',
                'category' => 'general',
                'rarity' => 'platinum',
                'icon' => 'bi bi-stars',
                'color' => '#E5E4E2',
                'points' => 1000,
                'criteria' => json_encode(['type' => 'all_achievements']),
                'is_secret' => true,
                'order' => 15,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['key' => $achievement['key']],
                $achievement
            );
        }
    }
}
