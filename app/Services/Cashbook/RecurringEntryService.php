<?php

namespace App\Services\Cashbook;

use App\Models\Cashbook;
use App\Models\LancamentoRecorrente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Gera no livro-caixa os lançamentos recorrentes que já venceram.
 */
class RecurringEntryService
{
    public const FREQUENCIES = [
        'diaria' => 'Todo dia',
        'semanal' => 'Toda semana',
        'mensal' => 'Todo mês',
        'anual' => 'Todo ano',
    ];

    /**
     * Próxima data. $anchorDay mantém o dia original nos meses seguintes
     * (dia 31 vira 28 em fevereiro e volta a 31 em março).
     */
    public static function nextDate(Carbon $date, string $frequency, ?int $anchorDay = null): Carbon
    {
        $next = match ($frequency) {
            'diaria' => $date->copy()->addDay(),
            'semanal' => $date->copy()->addWeek(),
            'anual' => $date->copy()->addYearNoOverflow(),
            default => $date->copy()->addMonthNoOverflow(),
        };

        if ($anchorDay && in_array($frequency, ['mensal', 'anual'], true)) {
            $next->day(min($anchorDay, $next->daysInMonth));
        }

        return $next;
    }

    /**
     * Cria os lançamentos vencidos até $today e avança o próximo vencimento.
     * Retorna quantos lançamentos foram criados.
     */
    public function generate(LancamentoRecorrente $recurring, ?Carbon $today = null): int
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $created = 0;

        DB::transaction(function () use ($recurring, $today, &$created) {
            $next = Carbon::parse($recurring->proximo_vencimento)->startOfDay();
            $end = $recurring->data_fim ? Carbon::parse($recurring->data_fim)->endOfDay() : null;
            $anchorDay = Carbon::parse($recurring->data_inicio)->day;

            // Trava de segurança: no máximo 400 lançamentos por vez
            while ($next->lte($today) && (! $end || $next->lte($end)) && $created < 400) {
                Cashbook::create([
                    'user_id' => $recurring->user_id,
                    'value' => $recurring->valor,
                    'description' => $recurring->descricao,
                    'date' => $next->toDateString(),
                    'is_pending' => false,
                    'category_id' => $recurring->category_id,
                    'type_id' => $recurring->type_id,
                    'note' => 'Lançamento recorrente',
                    'inc_datetime' => now(),
                ]);

                $created++;
                $next = self::nextDate($next, $recurring->frequencia, $anchorDay);
            }

            $recurring->proximo_vencimento = $next->toDateString();
            if ($end && $next->gt($end)) {
                $recurring->ativo = false;
            }
            $recurring->save();
        });

        return $created;
    }

    public function generateAll(?Carbon $today = null): int
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $total = 0;

        LancamentoRecorrente::where('ativo', true)
            ->whereDate('proximo_vencimento', '<=', $today)
            ->each(function (LancamentoRecorrente $recurring) use ($today, &$total) {
                $total += $this->generate($recurring, $today);
            });

        return $total;
    }
}
