<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoalBoard;
use App\Models\GoalList;
use Illuminate\Support\Facades\DB;

class GoalBoardSeeder extends Seeder
{
    public function run()
    {
        // Criar boards padrão para cada tipo
        $boards = [
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
            [
                'name' => 'Carreira Profissional',
                'description' => 'Planeje seu crescimento profissional e objetivos de carreira',
                'tipo' => 'profissional',
                'background_color' => '#3B82F6',
                'is_favorite' => false,
                'order' => 3,
                'lists' => [
                    ['name' => 'Objetivos', 'color' => '#94A3B8', 'order' => 0],
                    ['name' => 'Trabalhando', 'color' => '#3B82F6', 'order' => 1],
                    ['name' => 'Alcançadas', 'color' => '#10B981', 'order' => 2],
                ]
            ],
        ];

        // Buscar todos os usuários
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            foreach ($boards as $boardData) {
                $lists = $boardData['lists'];
                unset($boardData['lists']);

                // Criar o board para o usuário
                $board = GoalBoard::firstOrCreate([
                    'user_id' => $user->id,
                    'name' => $boardData['name'],
                ], array_merge($boardData, [
                    'user_id' => $user->id,
                ]));

                // Criar as listas do board
                foreach ($lists as $listData) {
                    GoalList::firstOrCreate([
                        'board_id' => $board->id,
                        'name' => $listData['name'],
                    ], array_merge($listData, [
                        'board_id' => $board->id,
                    ]));
                }
            }
        }
    }
}
