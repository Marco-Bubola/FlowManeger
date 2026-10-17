<?php

namespace App\Services\FinanceCapture;

use App\Models\FinanceCapture;
use Carbon\Carbon;

/**
 * Entende o texto das notificações dos bancos sem IA (Nubank, Inter, Mercado
 * Pago, Itaú, Bradesco...). Usado quando o texto é colado ou quando a IA não
 * estiver disponível. Devolve null quando não acha valor.
 */
class NotificationParser
{
    /** @return array{kind:?string,amount:float,description:string,date:string,installments_total:int,institution:?string,card_last4:?string}|null */
    public static function parse(string $text, ?Carbon $today = null): ?array
    {
        $today ??= now();
        $clean = trim(preg_replace('/\s+/u', ' ', $text));
        if ($clean === '') {
            return null;
        }

        if (! preg_match('/R\$\s*(-?\s*\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?|\d+(?:[.,]\d{1,2})?)/u', $clean, $m)) {
            return null;
        }
        $amount = self::money($m[1]);
        if ($amount <= 0) {
            return null;
        }

        $low = Institutions::normalize($clean);
        $kind = self::kind($low);

        $total = 1;
        if (preg_match('/\b(?:em\s+)?(\d{1,2})\s*x\b/iu', $clean, $p) || preg_match('/(\d{1,2})\s+parcelas/iu', $clean, $p)) {
            $total = max(1, min(48, (int) $p[1]));
        }

        $last4 = preg_match('/final\s*(?:de\s*)?(\d{4})/iu', $clean, $c) ? $c[1] : null;

        $description = self::description($clean, $kind);

        return [
            'kind' => $kind,
            'amount' => $amount,
            'description' => $description,
            'date' => self::date($clean, $today),
            'installments_total' => $kind === FinanceCapture::KIND_CARD ? $total : 1,
            // A loja ("MERCADO LIVRE") não é o banco: procura o banco fora da descrição
            'institution' => Institutions::detect(str_ireplace($description, ' ', $clean)),
            'card_last4' => $last4,
        ];
    }

    public static function money(string $raw): float
    {
        $raw = str_replace([' ', 'R$'], '', $raw);
        if (str_contains($raw, ',')) {
            $raw = str_replace(['.', ','], ['', '.'], $raw);
        }

        return abs(round((float) $raw, 2));
    }

    private static function kind(string $low): ?string
    {
        $has = fn (string $re) => (bool) preg_match($re, $low);

        if ($has('/ (voce )?recebeu | recebid[oa] | recebimento | caiu na sua conta | deposito | estorno | reembolso | rendimento | cashback /')) {
            return FinanceCapture::KIND_INCOME;
        }
        if ($has('/ debito /')) {
            return FinanceCapture::KIND_EXPENSE;
        }
        if ($has('/ compra | credito | cartao /') && ! $has('/ pix /')) {
            return FinanceCapture::KIND_CARD;
        }
        if ($has('/ pix | transferencia | ted | doc | pagou | pagamento | boleto | enviad[oa] | saque | debitad[oa] /')) {
            return FinanceCapture::KIND_EXPENSE;
        }

        return null;
    }

    private static function description(string $clean, ?string $kind): string
    {
        $stops = '(?=\s+(?:para o cart|no cart|no valor|no dia|valor|em \d{1,2}\/|às|as \d|hoje|ontem|com o cart|pelo pix|via pix|foi|em \d{1,2}x)|[.,;!]|\s+-\s|$)';
        $patterns = $kind === FinanceCapture::KIND_INCOME
            ? ["/\\b(?:de|do|da)\\s+(?!R\\$)([^\\d].{1,60}?){$stops}/iu"]
            : [
                "/\\bem\\s+(?!R\\$|\\d{1,2}\\s*x\\b)([A-Z0-9*][^\\d].{1,60}?){$stops}/u",   // "APROVADA em IFOOD *IFOOD"
                "/\\bem\\s+(?!R\\$|\\d{1,2}\\s*x\\b)([^\\d].{1,60}?){$stops}/iu",
                "/\\s-\\s+([A-Z0-9*].{1,60}?)\\s+(?:valor|R\\$)/u", // Itaú: "final 1234 - LOJA valor"
                "/\\b(?:para|a)\\s+(?!R\\$)([^\\d].{1,60}?){$stops}/iu",    // "Você pagou R$ 10 a FULANO"
            ];

        foreach ($patterns as $re) {
            if (preg_match($re, $clean, $m)) {
                $d = trim($m[1], " *-:\t");
                if (mb_strlen($d) >= 2 && ! preg_match('/^(seu|sua|um|uma|r\$)\b/iu', $d)) {
                    return mb_substr($d, 0, 120);
                }
            }
        }

        return match ($kind) {
            FinanceCapture::KIND_CARD => 'Compra no cartão',
            FinanceCapture::KIND_INCOME => 'Entrada',
            default => 'Pagamento',
        };
    }

    private static function date(string $clean, Carbon $today): string
    {
        if (preg_match('/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/', $clean, $d)) {
            $year = isset($d[3]) ? (int) (strlen($d[3]) === 2 ? '20' . $d[3] : $d[3]) : $today->year;
            if (checkdate((int) $d[2], (int) $d[1], $year)) {
                $date = Carbon::create($year, (int) $d[2], (int) $d[1]);
                // "12/12" lido em janeiro é do ano passado
                if (! isset($d[3]) && $date->gt($today->copy()->addDays(2))) {
                    $date->subYear();
                }

                return $date->toDateString();
            }
        }
        if (preg_match('/\bontem\b/iu', $clean)) {
            return $today->copy()->subDay()->toDateString();
        }

        return $today->toDateString();
    }
}
