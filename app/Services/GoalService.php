<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalList;
use App\Models\GoalBoard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GoalService
{
    /**
     * Criar nova meta
     */
    public function create(array $data, int $userId): Goal
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['user_id'] = $userId;

            // Definir data_inicio se não fornecida
            if (!isset($data['data_inicio'])) {
                $data['data_inicio'] = now();
            }

            // Calcular order automático
            if (!isset($data['order'])) {
                $data['order'] = Goal::where('list_id', $data['list_id'])->max('order') + 1;
            }

            $goal = Goal::create($data);

            // Se tem recorrência, gerar próximas ocorrências
            if ($data['periodo'] !== 'custom') {
                app(RecurrenceService::class)->generateOccurrencesForGoal($goal);
            }

            return $goal->fresh();
        });
    }

    /**
     * Atualizar meta existente
     */
    public function update(Goal $goal, array $data): Goal
    {
        return DB::transaction(function () use ($goal, $data) {
            $goal->update($data);

            // Se mudou o período, recriar ocorrências
            if (isset($data['periodo']) && $data['periodo'] !== 'custom') {
                app(RecurrenceService::class)->regenerateOccurrences($goal);
            }

            return $goal->fresh();
        });
    }

    /**
     * Calcular progresso da meta
     */
    public function calculateProgress(Goal $goal): float
    {
        // Se tem valor meta e valor atual
        if ($goal->valor_meta > 0 && $goal->valor_atual !== null) {
            return min(($goal->valor_atual / $goal->valor_meta) * 100, 100);
        }

        // Se tem checklists, calcular por items completados
        if ($goal->checklists()->exists()) {
            $totalItems = $goal->checklistItems()->count();
            $completedItems = $goal->checklistItems()->where('is_completed', true)->count();

            return $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
        }

        // Se tem hábitos vinculados, calcular por completion
        if ($goal->habits()->exists()) {
            $habits = $goal->habits;
            $totalExpected = $habits->sum('goal_frequency');
            $totalCompleted = $habits->sum(function ($habit) {
                return $habit->getTodayCompletionCount();
            });

            return $totalExpected > 0 ? ($totalCompleted / $totalExpected) * 100 : 0;
        }

        return $goal->progresso ?? 0;
    }

    /**
     * Atualizar progresso automaticamente
     */
    public function updateProgress(Goal $goal): Goal
    {
        $goal->progresso = $this->calculateProgress($goal);
        $goal->save();

        // Se chegou a 100%, marcar como completa
        if ($goal->progresso >= 100 && !$goal->completed_at) {
            $goal->completed_at = now();
            $goal->save();
        }

        return $goal;
    }

    /**
     * Mover meta para outra lista
     */
    public function moveToList(Goal $goal, int $newListId, int $newOrder = null): Goal
    {
        return DB::transaction(function () use ($goal, $newListId, $newOrder) {
            $oldListId = $goal->list_id;

            // Atualizar ordem na lista antiga
            Goal::where('list_id', $oldListId)
                ->where('order', '>', $goal->order)
                ->decrement('order');

            // Calcular nova ordem se não especificada
            if ($newOrder === null) {
                $newOrder = Goal::where('list_id', $newListId)->max('order') + 1;
            }

            // Mover
            $goal->list_id = $newListId;
            $goal->order = $newOrder;
            $goal->save();

            return $goal;
        });
    }

    /**
     * Arquivar meta
     */
    public function archive(Goal $goal): Goal
    {
        $goal->is_archived = true;
        $goal->save();

        return $goal;
    }

    /**
     * Restaurar meta arquivada
     */
    public function restore(Goal $goal): Goal
    {
        $goal->is_archived = false;
        $goal->save();

        return $goal;
    }

    /**
     * Obter metas atrasadas
     */
    public function getOverdueGoals(int $userId)
    {
        return Goal::where('user_id', $userId)
            ->whereNotNull('data_vencimento')
            ->where('data_vencimento', '<', now())
            ->where('is_archived', false)
            ->whereNull('completed_at')
            ->with(['list.board'])
            ->get();
    }

    /**
     * Obter metas próximas do vencimento (7 dias)
     */
    public function getUpcomingGoals(int $userId, int $days = 7)
    {
        return Goal::where('user_id', $userId)
            ->whereNotNull('data_vencimento')
            ->whereBetween('data_vencimento', [now(), now()->addDays($days)])
            ->where('is_archived', false)
            ->whereNull('completed_at')
            ->with(['list.board'])
            ->orderBy('data_vencimento')
            ->get();
    }

    /**
     * Obter KPIs do usuário
     */
    public function getUserKPIs(int $userId): array
    {
        $goals = Goal::where('user_id', $userId)->where('is_archived', false);

        return [
            'total' => $goals->count(),
            'completed' => $goals->clone()->whereNotNull('completed_at')->count(),
            'in_progress' => $goals->clone()->whereNull('completed_at')->where('progresso', '>', 0)->count(),
            'pending' => $goals->clone()->whereNull('completed_at')->where('progresso', 0)->count(),
            'overdue' => $this->getOverdueGoals($userId)->count(),
            'upcoming' => $this->getUpcomingGoals($userId)->count(),
            'completion_rate' => $goals->count() > 0
                ? round(($goals->clone()->whereNotNull('completed_at')->count() / $goals->count()) * 100, 2)
                : 0,
        ];
    }
}
