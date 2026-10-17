<?php

namespace App\Services\FinanceCapture;

use App\Models\FinanceCapture;
use App\Services\AI\GeminiClient;
use Illuminate\Support\Facades\Log;

/**
 * Lê o print de uma notificação (ou do comprovante) com o Gemini e devolve
 * uma lista de lançamentos. Um print da central de notificações pode ter vários.
 */
class PrintReader
{
    public function __construct(private ?GeminiClient $gemini = null)
    {
        $this->gemini ??= app(GeminiClient::class);
    }

    public function available(): bool
    {
        return $this->gemini->isConfigured();
    }

    /**
     * @return array{items: array<int, array>, raw_text: ?string, error: ?string}
     */
    public function readImage(string $bytes, string $mime, array $knownBanks = []): array
    {
        if (! $this->available()) {
            return ['items' => [], 'raw_text' => null, 'error' => 'A leitura de imagens precisa da chave do Gemini (GEMINI_API_KEY) no servidor.'];
        }

        $today = now()->format('d/m/Y');
        $banks = $knownBanks ? implode(', ', $knownBanks) : 'nenhum';
        $prompt = <<<TXT
Você lê prints de celular de notificações de banco, de cartão de crédito, de Pix e comprovantes, no Brasil. Hoje é {$today}.
Os bancos/cartões que a pessoa tem cadastrados são: {$banks}.

Para cada transação visível no print, devolva um item. Ignore propagandas, saldos e avisos sem valor.
Campos de cada item:
- kind: "card" para compra no cartão de CRÉDITO; "income" para dinheiro que ENTROU (Pix recebido, transferência recebida, depósito, estorno, reembolso); "expense" para dinheiro que SAIU da conta (Pix enviado, pagamento de boleto, compra no DÉBITO, transferência enviada, saque).
- amount: valor total em reais, número com ponto decimal (ex.: 1234.56), sempre positivo.
- description: nome da loja/estabelecimento ou da pessoa (curto, como aparece), sem o nome do banco.
- date: data no formato AAAA-MM-DD. Se o print mostra só a hora, "agora", "hoje" ou "ontem", calcule a partir de hoje.
- installments_total: número de parcelas (1 se à vista).
- institution: nome do banco/carteira que enviou a notificação ou emitiu o cartão (pelo nome ou pelo ícone do app: Nubank roxo, Inter laranja, Mercado Pago azul, Itaú, Bradesco, Banco do Brasil, Caixa, Santander, C6, PicPay, PagBank, Neon...). Se não der para saber, null.
- card_last4: 4 últimos dígitos do cartão se aparecerem, senão null.
- confidence: de 0 a 1, o quanto você tem certeza.

Responda SOMENTE com JSON no formato {"raw_text": "texto que você leu no print", "items": [ ... ]}.
TXT;

        $text = $this->gemini->generateText($prompt, [
            'feature' => 'finance_print',
            'images' => [['mime' => $mime, 'data' => $bytes]],
            'response_mime_type' => 'application/json',
            'temperature' => 0.1,
            'timeout' => 60,
        ]);

        if (! $text) {
            return ['items' => [], 'raw_text' => null, 'error' => 'Não consegui ler o print agora. Tente de novo ou cole o texto da notificação.'];
        }

        return self::decode($text);
    }

    /** Converte a resposta do Gemini para a lista de itens no formato do NotificationParser. */
    public static function decode(string $json): array
    {
        $json = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($json)));
        $data = json_decode($json, true);
        if (! is_array($data)) {
            Log::warning('PrintReader: resposta não é JSON', ['body' => mb_substr($json, 0, 500)]);

            return ['items' => [], 'raw_text' => null, 'error' => 'Não entendi o print. Cole o texto da notificação.'];
        }

        $rawText = is_string($data['raw_text'] ?? null) ? $data['raw_text'] : null;
        $items = [];
        foreach (($data['items'] ?? (isset($data['amount']) ? [$data] : [])) as $it) {
            $amount = is_numeric($it['amount'] ?? null) ? abs(round((float) $it['amount'], 2)) : NotificationParser::money((string) ($it['amount'] ?? '0'));
            if ($amount <= 0) {
                continue;
            }
            $kind = in_array($it['kind'] ?? null, [FinanceCapture::KIND_CARD, FinanceCapture::KIND_INCOME, FinanceCapture::KIND_EXPENSE], true) ? $it['kind'] : null;
            $date = (is_string($it['date'] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $it['date'])) ? $it['date'] : now()->toDateString();
            // Data no futuro distante é erro de leitura
            if ($date > now()->addDays(2)->toDateString()) {
                $date = now()->toDateString();
            }
            $institution = is_string($it['institution'] ?? null) && $it['institution'] !== ''
                ? (Institutions::detect($it['institution']) ?? mb_substr($it['institution'], 0, 60))
                : null;

            $items[] = [
                'kind' => $kind,
                'amount' => $amount,
                'description' => mb_substr(trim((string) ($it['description'] ?? '')) ?: 'Lançamento', 0, 120),
                'date' => $date,
                'installments_total' => max(1, min(48, (int) ($it['installments_total'] ?? 1))),
                'institution' => $institution,
                'card_last4' => preg_match('/^\d{4}$/', (string) ($it['card_last4'] ?? '')) ? (string) $it['card_last4'] : null,
                'confidence' => is_numeric($it['confidence'] ?? null) ? (float) $it['confidence'] : null,
            ];
        }

        return ['items' => $items, 'raw_text' => $rawText, 'error' => $items ? null : 'Não achei nenhum valor no print.'];
    }
}
