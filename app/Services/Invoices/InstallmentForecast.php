<?php

namespace App\Services\Invoices;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Projeta as parcelas que ainda vão cair nas próximas faturas do cartão.
 *
 * As compras parceladas são gravadas com a data da compra e o texto
 * "4 de 6" (ou "4/6"). A parcela N cai na fatura do mês da compra + (N-1).
 * Para cada compra, usa a maior parcela já lançada e projeta as que faltam.
 */
class InstallmentForecast
{
    /**
     * @return array{0:int,1:int}|null [parcela atual, total de parcelas]
     */
    public static function parse(?string $label): ?array
    {
        if (! $label || ! preg_match('/(\d{1,2})\s*(?:de|\/)\s*(\d{1,2})/iu', $label, $m)) {
            return null;
        }

        [$current, $total] = [(int) $m[1], (int) $m[2]];

        return ($current >= 1 && $total > 1 && $current <= $total) ? [$current, $total] : null;
    }

    /**
     * @param  Collection  $invoices  itens com invoice_date, description, value, installments
     * @return array<string, array{month: string, total: float, items: array}>  chave Y-m, só meses depois de $after
     */
    public static function forecast(Collection $invoices, Carbon $after, int $months = 6): array
    {
        $limit = $after->copy()->startOfMonth()->addMonths($months);
        $after = $after->copy()->startOfMonth();

        // Uma linha por compra: mesma descrição, valor, data e total de parcelas
        $purchases = [];
        foreach ($invoices as $invoice) {
            $parsed = self::parse($invoice->installments ?? null);
            if (! $parsed) {
                continue;
            }

            [$current, $total] = $parsed;
            $date = Carbon::parse($invoice->invoice_date)->startOfMonth();
            $key = mb_strtolower(trim($invoice->description)).'|'.number_format((float) $invoice->value, 2, '.', '').'|'.$date->format('Y-m').'|'.$total;

            if (! isset($purchases[$key]) || $purchases[$key]['current'] < $current) {
                $purchases[$key] = [
                    'description' => $invoice->description,
                    'value' => (float) $invoice->value,
                    'date' => $date,
                    'current' => $current,
                    'total' => $total,
                ];
            }
        }

        $result = [];
        foreach ($purchases as $purchase) {
            for ($n = $purchase['current'] + 1; $n <= $purchase['total']; $n++) {
                $month = $purchase['date']->copy()->addMonthsNoOverflow($n - 1);
                if ($month->lte($after) || $month->gt($limit)) {
                    continue;
                }

                $key = $month->format('Y-m');
                $result[$key] ??= ['month' => $key, 'total' => 0.0, 'items' => []];
                $result[$key]['total'] += $purchase['value'];
                $result[$key]['items'][] = [
                    'description' => $purchase['description'],
                    'value' => $purchase['value'],
                    'installment' => "{$n} de {$purchase['total']}",
                ];
            }
        }

        ksort($result);

        return $result;
    }
}
