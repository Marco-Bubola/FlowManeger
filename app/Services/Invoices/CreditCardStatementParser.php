<?php

namespace App\Services\Invoices;

use Illuminate\Support\Facades\Log;

/**
 * Extrai lançamentos de faturas de cartão de crédito em PDF.
 *
 * Cada emissor tem um layout próprio, então há um parser dedicado por banco
 * (Inter, Nubank, Mercado Pago) mais um genérico de fallback. O emissor é
 * detectado pelo conteúdo do PDF — não pelo banco escolhido na tela — para que
 * um PDF do Nubank enviado no cartão errado ainda seja lido corretamente.
 *
 * Saída de cada lançamento:
 *   date         => 'Y-m-d'
 *   description  => string
 *   installments => '4 de 6' | '-'
 *   value        => float (sempre positivo; despesa)
 *   is_credit    => bool  (pagamento/estorno — não vira despesa)
 */
class CreditCardStatementParser
{
    public const BANK_INTER = 'inter';
    public const BANK_NUBANK = 'nubank';
    public const BANK_MERCADOPAGO = 'mercadopago';
    public const BANK_GENERIC = 'generic';

    private const MONTHS_PT = [
        'jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4,
        'mai' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8,
        'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12,
    ];

    /**
     * Lançamentos que não viram despesa mesmo quando o valor aparece positivo:
     * pagamentos, estornos e os pares de financiamento que se anulam na fatura
     * (ex.: "Saldo em atraso" + "Crédito de atraso" pelo mesmo valor).
     */
    private const CREDIT_KEYWORDS = [
        'pagamento de fatura', 'pagamento da fatura', 'pagamento recebido',
        'pagamento em', 'estorno', 'devolução', 'devolucao',
        'crédito de atraso', 'credito de atraso', 'saldo em atraso',
        'encerramento de dívida', 'encerramento de divida',
        'juros de dívida encerrada', 'juros de divida encerrada',
    ];

    /**
     * @return array{bank: string, transactions: array<int, array<string, mixed>>, credits: int}
     */
    public function parse(string $text): array
    {
        $bank = $this->detectBank($text);
        $lines = $this->normalizeLines($text);

        $transactions = match ($bank) {
            self::BANK_INTER => $this->parseInter($lines),
            self::BANK_NUBANK => $this->parseNubank($lines),
            self::BANK_MERCADOPAGO => $this->parseMercadoPago($lines),
            default => $this->parseGeneric($lines),
        };

        // O parser específico pode não achar nada se o layout mudou: cai no genérico.
        if (empty($transactions) && $bank !== self::BANK_GENERIC) {
            Log::warning('Parser específico não encontrou lançamentos, usando genérico', ['bank' => $bank]);
            $transactions = $this->parseGeneric($lines);
        }

        $credits = count(array_filter($transactions, fn ($t) => $t['is_credit']));

        Log::info('Fatura processada', [
            'bank' => $bank,
            'total' => count($transactions),
            'credits' => $credits,
        ]);

        return [
            'bank' => $bank,
            'transactions' => array_values($transactions),
            'credits' => $credits,
        ];
    }

    public function detectBank(string $text): string
    {
        $haystack = mb_strtolower($text);

        if (str_contains($haystack, 'banco inter') || str_contains($haystack, 'bancointer.com')
            || str_contains($haystack, 'despesas da fatura')) {
            return self::BANK_INTER;
        }

        if (str_contains($haystack, 'nubank') || str_contains($haystack, 'nu pagamentos')
            || str_contains($haystack, '18.236.120/0001-58')) {
            return self::BANK_NUBANK;
        }

        if (str_contains($haystack, 'mercado pago') || str_contains($haystack, 'mercadopago')
            || str_contains($haystack, 'mercadolivre*')) {
            return self::BANK_MERCADOPAGO;
        }

        return self::BANK_GENERIC;
    }

    // ==================================================================
    // INTER
    // ==================================================================

    /**
     * Layout:
     *   Despesas da fatura
     *   CARTÃO 2306****0895
     *   Data\tMovimentação\tBeneficiário\tValor
     *   25 de ago. 2025 AIRBNB * HM5DXH33QC (Parcela 04 de 06)\t-\tR$ 657,73
     *   Total CARTÃO 2306****0895\tR$ 715,80
     */
    private function parseInter(array $lines): array
    {
        $transactions = [];
        $inSection = false;

        foreach ($lines as $line) {
            if (preg_match('/^Despesas da fatura/iu', $line)) {
                $inSection = true;
                continue;
            }

            if (! $inSection) {
                continue;
            }

            // "Próxima fatura" lista parcelas que ainda NÃO foram cobradas.
            if (preg_match('/^(Limite de crédito total|Próxima fatura|Fale com a gente)/iu', $line)) {
                break;
            }

            // 25 de ago. 2025 DESCRIÇÃO ... R$ 657,73
            if (! preg_match('/^(\d{1,2})\s+de\s+([a-zçãé]{3,})\.?\s+(\d{4})\s+(.+)$/iu', $line, $m)) {
                continue;
            }

            $month = self::MONTHS_PT[mb_strtolower(mb_substr($m[2], 0, 3))] ?? null;
            if (! $month) {
                continue;
            }

            $rest = $m[4];
            $amount = $this->extractAmount($rest);
            if ($amount === null) {
                continue;
            }

            // A descrição é o trecho antes do primeiro separador de coluna.
            $description = trim(preg_split('/\t/', $rest)[0]);
            $description = trim(preg_replace('/R\$\s*[\d.]*\d,\d{2}.*$/u', '', $description));
            $description = trim($description, " \t-");

            $installments = $this->extractInstallments($description);
            $description = $installments['description'];

            if ($description === '') {
                continue;
            }

            $transactions[] = $this->makeTransaction(
                sprintf('%04d-%02d-%02d', (int) $m[3], $month, (int) $m[1]),
                $description,
                $installments['label'],
                $amount['value'],
                $amount['is_credit'] || $this->looksLikeCredit($description)
            );
        }

        return $transactions;
    }

    // ==================================================================
    // NUBANK
    // ==================================================================

    /**
     * Layout:
     *   TRANSAÇÕES DE 06 JUN A 06 JUL
     *   06 JUN  •••• 3910Central da Borracha - Parcela 2/2\tR$ 84,00
     *   07 JUN  Plano NuCel\tR$ 25,00
     *   16 JUN Pagamento em 16 JUN\t−R$ 4.356,02
     *
     * A data não traz o ano: ele vem do cabeçalho "FATURA 13 JUL 2026".
     */
    private function parseNubank(array $lines): array
    {
        $transactions = [];
        $reference = $this->nubankReference($lines);
        $inSection = false;
        $pending = null; // lançamento cujo valor está nas linhas seguintes

        foreach ($lines as $line) {
            if (preg_match('/^(TRANSAÇÕES DE|Pagamentos e Financiamentos)/iu', $line)) {
                $inSection = true;
                $pending = null;
                continue;
            }

            if (! $inSection) {
                continue;
            }

            if (preg_match('/^(Em cumprimento|Como assegurado|RESUMO DA FATURA|LIMITES DISPONÍVEIS)/iu', $line)) {
                $inSection = false;
                $pending = null;
                continue;
            }

            // Rodapé de página ("5 de 7", "MARCO ANTONIO BUBOLA", "FATURA 13 JUL 2026 ...")
            if (preg_match('/^\d+ de \d+$/u', $line) || preg_match('/^FATURA\s+\d{1,2}\s+[A-ZÇ]{3}/iu', $line)) {
                continue;
            }

            // 06 JUN  descrição ... R$ 84,00
            if (preg_match('/^(\d{1,2})\s+([A-Za-zÇç]{3})\s+(.*)$/u', $line, $m)) {
                $month = self::MONTHS_PT[mb_strtolower($m[2])] ?? null;
                if (! $month) {
                    continue;
                }

                $date = $this->resolveYear((int) $m[1], $month, $reference);
                $rest = trim($m[3]);
                $amount = $this->extractAmount($rest);

                $description = $this->cleanNubankDescription($rest);
                if ($description === '') {
                    continue;
                }

                if ($amount === null) {
                    // Ex.: "16 JUN IOF de atraso" com o valor 2 linhas abaixo.
                    $pending = ['date' => $date, 'description' => $description, 'ttl' => 4];
                    continue;
                }

                $pending = null;
                $installments = $this->extractInstallments($description);

                $transactions[] = $this->makeTransaction(
                    $date,
                    $installments['description'],
                    $installments['label'],
                    $amount['value'],
                    $amount['is_credit'] || $this->looksLikeCredit($description)
                );

                continue;
            }

            if ($pending !== null) {
                if (preg_match('/^[−-]?\s*R\$\s*[\d.]*\d,\d{2}$/u', $line)) {
                    $amount = $this->extractAmount($line);
                    $installments = $this->extractInstallments($pending['description']);

                    $transactions[] = $this->makeTransaction(
                        $pending['date'],
                        $installments['description'],
                        $installments['label'],
                        $amount['value'],
                        $amount['is_credit'] || $this->looksLikeCredit($pending['description'])
                    );

                    $pending = null;
                } elseif (--$pending['ttl'] <= 0) {
                    $pending = null;
                }
            }
        }

        return $transactions;
    }

    /**
     * Mês/ano de referência da fatura do Nubank.
     *
     * @return array{month: int, year: int}
     */
    private function nubankReference(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/FATURA\s+\d{1,2}\s+([A-Za-zÇç]{3})\s+(\d{4})/u', $line, $m)) {
                $month = self::MONTHS_PT[mb_strtolower($m[1])] ?? null;
                if ($month) {
                    return ['month' => $month, 'year' => (int) $m[2]];
                }
            }
        }

        return ['month' => (int) date('n'), 'year' => (int) date('Y')];
    }

    private function cleanNubankDescription(string $rest): string
    {
        // Remove o valor e a máscara do cartão colada na descrição ("•••• 3910Padaria").
        $description = preg_replace('/[−+-]?\s*R\$\s*[\d.]*\d,\d{2}.*$/u', '', $rest);
        $description = preg_replace('/^[•\x{2022}\s]*\d{4}\s*/u', '', trim($description));

        return trim($description, " \t-");
    }

    // ==================================================================
    // MERCADO PAGO
    // ==================================================================

    /**
     * Layout:
     *   Data Movimentações\tValor em R$
     *   17/02 MERCADOLIVRE*2PRODUTOS\tParcela 4 de 4 R$ 54,85
     *   04/06 MERCADOLIVRE*FILIPEFLO\tR$ 54,21
     *   Total\tR$ 828,70
     *
     * A data traz só dia/mês: o ano vem de "Vencimento: 07/07/2026".
     */
    private function parseMercadoPago(array $lines): array
    {
        $transactions = [];
        $reference = $this->mercadoPagoReference($lines);
        $inSection = false;

        foreach ($lines as $line) {
            if (preg_match('/^(Movimentações na fatura|Cartão\s+\w+\s*\[|Data\s+Movimentações)/iu', $line)) {
                $inSection = true;
                continue;
            }

            if (! $inSection) {
                continue;
            }

            if (preg_match('/^(Total\b|Parcele a fatura|Seu cartão de crédito|Lançamentos futuros)/iu', $line)) {
                $inSection = false;
                continue;
            }

            // 17/02 DESCRIÇÃO\tParcela 4 de 4 R$ 54,85
            if (! preg_match('/^(\d{1,2})\/(\d{1,2})\s+(.+)$/u', $line, $m)) {
                continue;
            }

            $rest = trim($m[3]);
            $amount = $this->extractAmount($rest);
            if ($amount === null) {
                continue;
            }

            $description = trim(preg_replace('/[+-]?\s*R\$\s*[\d.]*\d,\d{2}.*$/u', '', $rest));
            $installments = $this->extractInstallments($description);
            $description = trim($installments['description'], " \t-");

            if ($description === '') {
                continue;
            }

            $transactions[] = $this->makeTransaction(
                $this->resolveYear((int) $m[1], (int) $m[2], $reference),
                $description,
                $installments['label'],
                $amount['value'],
                $amount['is_credit'] || $this->looksLikeCredit($description)
            );
        }

        return $transactions;
    }

    /**
     * @return array{month: int, year: int}
     */
    private function mercadoPagoReference(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:Vencimento|Vence em|Emitida em)[:\s]+(\d{1,2})\/(\d{1,2})\/(\d{4})/iu', $line, $m)) {
                return ['month' => (int) $m[2], 'year' => (int) $m[3]];
            }
        }

        // "Vence em" e a data podem estar em linhas separadas.
        foreach ($lines as $line) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/u', trim($line), $m)) {
                return ['month' => (int) $m[2], 'year' => (int) $m[3]];
            }
        }

        return ['month' => (int) date('n'), 'year' => (int) date('Y')];
    }

    // ==================================================================
    // GENÉRICO
    // ==================================================================

    /**
     * Fallback para layouts não reconhecidos: aceita dd/mm/aaaa, dd/mm e
     * "dd de mmm. aaaa" na mesma linha de um valor em R$.
     */
    private function parseGeneric(array $lines): array
    {
        $transactions = [];
        $reference = ['month' => (int) date('n'), 'year' => (int) date('Y')];

        foreach ($lines as $line) {
            $amount = $this->extractAmount($line);
            if ($amount === null) {
                continue;
            }

            $date = null;
            $rest = null;

            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(.+)$/u', $line, $m)) {
                $date = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
                $rest = $m[4];
            } elseif (preg_match('/^(\d{1,2})\s+de\s+([a-zçãé]{3,})\.?\s+(\d{4})\s+(.+)$/iu', $line, $m)) {
                $month = self::MONTHS_PT[mb_strtolower(mb_substr($m[2], 0, 3))] ?? null;
                if (! $month) {
                    continue;
                }
                $date = sprintf('%04d-%02d-%02d', (int) $m[3], $month, (int) $m[1]);
                $rest = $m[4];
            } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\s+(.+)$/u', $line, $m)) {
                $date = $this->resolveYear((int) $m[1], (int) $m[2], $reference);
                $rest = $m[3];
            }

            if ($date === null) {
                continue;
            }

            $description = trim(preg_replace('/[+-]?\s*R\$\s*[\d.]*\d,\d{2}.*$/u', '', preg_split('/\t/', $rest)[0]));
            $installments = $this->extractInstallments($description);
            $description = trim($installments['description'], " \t-");

            if ($description === '' || mb_strlen($description) > 200) {
                continue;
            }

            $transactions[] = $this->makeTransaction(
                $date,
                $description,
                $installments['label'],
                $amount['value'],
                $amount['is_credit'] || $this->looksLikeCredit($description)
            );
        }

        return $transactions;
    }

    // ==================================================================
    // Helpers
    // ==================================================================

    private function normalizeLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $lines = [];
        foreach (explode("\n", $text) as $line) {
            // Espaço fino/não separável usado por alguns emissores atrapalha as regex.
            $line = str_replace(["\u{00A0}", "\u{2009}", "\u{202F}"], ' ', $line);
            $line = rtrim($line);

            if (trim($line) !== '') {
                $lines[] = ltrim($line);
            }
        }

        return $lines;
    }

    /**
     * Último valor monetário da linha, com o sinal.
     *
     * @return array{value: float, is_credit: bool}|null
     */
    private function extractAmount(string $line): ?array
    {
        // O valor fica sempre na última coluna. Isolá-la evita ler o "-" da
        // coluna Beneficiário do Inter ("...\t-\tR$ 657,73") como sinal negativo.
        $cells = array_values(array_filter(array_map('trim', explode("\t", $line)), fn ($c) => $c !== ''));
        $scope = count($cells) > 1 ? end($cells) : $line;

        if (! preg_match_all('/([−+-])?\s?R\$\s*([\d.]*\d,\d{2})/u', $scope, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $last = end($matches);
        $sign = $last[1] ?? '';
        $raw = str_replace(['.', ','], ['', '.'], $last[2]);

        return [
            'value' => abs((float) $raw),
            // "−" (U+2212) e "-" são estorno/pagamento; "+" no Inter também é crédito.
            'is_credit' => in_array($sign, ['−', '-', '+'], true),
        ];
    }

    /**
     * Extrai "Parcela 4 de 6", "Parcela 2/2", "(4 de 6)" da descrição.
     *
     * @return array{description: string, label: string}
     */
    private function extractInstallments(string $description): array
    {
        $patterns = [
            '/\(?\s*Parcela\s*(\d{1,2})\s*(?:de|\/)\s*(\d{1,2})\s*\)?/iu',
            '/\(\s*(\d{1,2})\s*(?:de|\/)\s*(\d{1,2})\s*\)/u',
            '/\s-\s(\d{1,2})\/(\d{1,2})\s*$/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $m)) {
                $clean = trim(preg_replace($pattern, '', $description));
                $clean = trim(preg_replace('/\s{2,}/u', ' ', $clean), " \t-");

                return [
                    'description' => $clean !== '' ? $clean : $description,
                    'label' => sprintf('%d de %d', (int) $m[1], (int) $m[2]),
                ];
            }
        }

        return ['description' => $description, 'label' => '-'];
    }

    private function looksLikeCredit(string $description): bool
    {
        $haystack = mb_strtolower($description);

        foreach (self::CREDIT_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve o ano de uma data sem ano: um mês maior que o da fatura pertence
     * ao ano anterior (compra parcelada de dez/2025 numa fatura de jul/2026).
     *
     * @param array{month: int, year: int} $reference
     */
    private function resolveYear(int $day, int $month, array $reference): string
    {
        $year = $month > $reference['month'] ? $reference['year'] - 1 : $reference['year'];

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function makeTransaction(string $date, string $description, string $installments, float $value, bool $isCredit): array
    {
        return [
            'date' => $date,
            'description' => preg_replace('/\s{2,}/u', ' ', trim($description)),
            'installments' => $installments,
            'value' => round($value, 2),
            'is_credit' => $isCredit,
        ];
    }
}
