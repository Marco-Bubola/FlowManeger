<?php

namespace App\Services\FinanceCapture;

/**
 * Bancos e carteiras mais usados no Brasil, com os nomes que aparecem nas
 * notificações e nos cadastros. Usado para achar o cartão/conta certos.
 */
class Institutions
{
    /** nome padrão => apelidos (minúsculos, sem acento) */
    public const ALL = [
        'Nubank' => ['nubank', 'nu pagamentos', 'nu bank', 'roxinho'],
        'Inter' => ['banco inter', 'inter'],
        'Mercado Pago' => ['mercado pago', 'mercadopago', 'mercado livre'],
        'Itaú' => ['itau', 'itaucard', 'iti', 'personnalite', 'uniclass'],
        'Bradesco' => ['bradesco', 'bradescard', 'next'],
        'Banco do Brasil' => ['banco do brasil', 'bb', 'ourocard'],
        'Caixa' => ['caixa', 'cef', 'caixa economica'],
        'Santander' => ['santander'],
        'C6 Bank' => ['c6 bank', 'c6'],
        'PicPay' => ['picpay', 'pic pay'],
        'PagBank' => ['pagbank', 'pagseguro'],
        'Neon' => ['neon'],
        'BTG Pactual' => ['btg'],
        'XP' => ['xp investimentos', 'xp'],
        'Sicoob' => ['sicoob'],
        'Sicredi' => ['sicredi'],
        'Banco Original' => ['banco original', 'original'],
        'Banrisul' => ['banrisul'],
        'Will Bank' => ['will bank', 'willbank'],
        'Porto Bank' => ['porto bank', 'porto seguro', 'portocard'],
        'Banco Pan' => ['banco pan', 'pan'],
        'Ame Digital' => ['ame digital'],
        'RecargaPay' => ['recargapay'],
        'Carrefour' => ['carrefour', 'atacadao'],
    ];

    public static function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;

        return ' ' . preg_replace('/[^a-z0-9]+/', ' ', $text) . ' ';
    }

    /** Primeiro banco citado no texto (apelido mais longo vence: "banco inter" antes de "inter"). */
    public static function detect(?string $text): ?string
    {
        $haystack = self::normalize($text);
        $best = null;
        $bestLen = 0;
        $bestPos = PHP_INT_MAX;
        foreach (self::ALL as $name => $aliases) {
            foreach ($aliases as $alias) {
                $pos = strpos($haystack, ' ' . $alias . ' ');
                if ($pos === false) {
                    continue;
                }
                // Mais cedo no texto vence; empate, o apelido mais longo
                if ($pos < $bestPos || ($pos === $bestPos && strlen($alias) > $bestLen)) {
                    [$best, $bestLen, $bestPos] = [$name, strlen($alias), $pos];
                }
            }
        }

        return $best;
    }

    /** O nome cadastrado ("Nubank Roxinho", "Cartão Inter") é desse banco? */
    public static function matches(?string $registeredName, ?string $institution): bool
    {
        if (! $institution || ! $registeredName) {
            return false;
        }
        $haystack = self::normalize($registeredName);
        $aliases = self::ALL[$institution] ?? [mb_strtolower($institution)];
        $aliases[] = trim(self::normalize($institution));
        foreach ($aliases as $alias) {
            if ($alias !== '' && str_contains($haystack, ' ' . $alias . ' ')) {
                return true;
            }
        }

        return false;
    }
}
