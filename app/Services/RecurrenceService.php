<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RecurrenceService
{
    /**
     * Gerar próximas ocorrências para uma meta recorrente
     */
    public function generateOccurrencesForGoal(Goal $goal, int $count = 12): array
    {
        if ($goal->periodo === 'custom') {
            return [];
        }

        $occurrences = [];
        $startDate = $goal->data_inicio ? Carbon::parse($goal->data_inicio) : Carbon::now();

        for ($i = 0; $i < $count; $i++) {
            $nextDate = $this->calculateNextOccurrence($goal, $startDate, $i);
            if ($nextDate) {
                $occurrences[] = $nextDate->format('Y-m-d');
            }
        }

        return $occurrences;
    }

    /**
     * Calcular próxima ocorrência
     */
    public function calculateNextOccurrence(Goal $goal, Carbon $fromDate, int $iteration = 1): ?Carbon
    {
        $date = $fromDate->copy();

        switch ($goal->periodo) {
            case 'diario':
                return $date->addDays($iteration);

            case 'semanal':
                return $date->addWeeks($iteration);

            case 'mensal':
                $targetDay = $goal->recorrencia_dia ?? $date->day;
                $newDate = $date->copy()->addMonths($iteration);

                // Ajustar para o dia específico do mês
                if ($targetDay <= $newDate->daysInMonth) {
                    $newDate->day = $targetDay;
                } else {
                    $newDate->endOfMonth();
                }

                return $newDate;

            case 'trimestral':
                return $date->addMonths($iteration * 3);

            case 'semestral':
                return $date->addMonths($iteration * 6);

            case 'anual':
                return $date->addYears($iteration);

            default:
                return null;
        }
    }

    /**
     * Obter próxima data de vencimento
     */
    public function getNextDueDate(Goal $goal): ?Carbon
    {
        if ($goal->periodo === 'custom' || !$goal->data_inicio) {
            return $goal->data_vencimento ? Carbon::parse($goal->data_vencimento) : null;
        }

        $today = Carbon::today();
        $startDate = Carbon::parse($goal->data_inicio);

        // Se a data de início é futura, retornar ela
        if ($startDate->isFuture()) {
            return $startDate;
        }

        // Calcular próxima ocorrência após hoje
        $iteration = 0;
        $maxIterations = 365; // Proteção contra loop infinito

        do {
            $nextDate = $this->calculateNextOccurrence($goal, $startDate, $iteration);
            $iteration++;

            if (!$nextDate || $iteration > $maxIterations) {
                return null;
            }

        } while ($nextDate->isPast());

        return $nextDate;
    }

    /**
     * Verificar se a meta está atrasada
     */
    public function isOverdue(Goal $goal): bool
    {
        $dueDate = $this->getNextDueDate($goal);

        if (!$dueDate) {
            return false;
        }

        return $dueDate->isPast() && !$goal->completed_at;
    }

    /**
     * Obter período em dias úteis restantes
     */
    public function getBusinessDaysRemaining(Goal $goal): ?int
    {
        $dueDate = $this->getNextDueDate($goal);

        if (!$dueDate) {
            return null;
        }

        $today = Carbon::today();
        $businessDays = 0;

        foreach (CarbonPeriod::create($today, $dueDate) as $date) {
            if ($date->isWeekday()) {
                $businessDays++;
            }
        }

        return $businessDays;
    }

    /**
     * Gerar descrição legível da recorrência
     */
    public function getRecurrenceDescription(Goal $goal): string
    {
        switch ($goal->periodo) {
            case 'diario':
                return 'Todos os dias';

            case 'semanal':
                return 'Toda semana';

            case 'mensal':
                if ($goal->recorrencia_dia) {
                    return "Todo dia {$goal->recorrencia_dia} do mês";
                }
                return 'Todo mês';

            case 'trimestral':
                return 'A cada 3 meses';

            case 'semestral':
                return 'A cada 6 meses';

            case 'anual':
                return 'Todo ano';

            case 'custom':
            default:
                return 'Personalizado';
        }
    }

    /**
     * Regenerar ocorrências (quando período muda)
     */
    public function regenerateOccurrences(Goal $goal): array
    {
        // Limpar ocorrências antigas se existirem (futuro: tabela goal_occurrences)

        // Gerar novas
        return $this->generateOccurrencesForGoal($goal);
    }

    /**
     * Verificar se deve criar nova instância da meta recorrente
     */
    public function shouldCreateNewInstance(Goal $goal): bool
    {
        if ($goal->periodo === 'custom' || !$goal->completed_at) {
            return false;
        }

        $nextDate = $this->getNextDueDate($goal);

        return $nextDate && $nextDate->isToday();
    }

    /**
     * Criar nova instância da meta recorrente
     */
    public function createNewInstance(Goal $goal): ?Goal
    {
        if (!$this->shouldCreateNewInstance($goal)) {
            return null;
        }

        $nextDate = $this->getNextDueDate($goal);

        $newGoal = $goal->replicate();
        $newGoal->data_inicio = $nextDate;
        $newGoal->data_vencimento = $this->calculateNextOccurrence($goal, $nextDate, 1);
        $newGoal->progresso = 0;
        $newGoal->valor_atual = 0;
        $newGoal->completed_at = null;
        $newGoal->is_archived = false;
        $newGoal->save();

        return $newGoal;
    }
}
