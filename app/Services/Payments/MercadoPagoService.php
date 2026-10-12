<?php

namespace App\Services\Payments;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Chamadas à API do Mercado Pago com o Access Token de cada loja.
 * Docs: https://www.mercadopago.com.br/developers/pt/reference
 */
class MercadoPagoService
{
    public const BASE = 'https://api.mercadopago.com';

    public function __construct(private PaymentSetting $settings)
    {
        if (blank($settings->access_token)) {
            throw new RuntimeException('Mercado Pago sem Access Token.');
        }
    }

    private function http(?string $idempotencyKey = null)
    {
        $req = Http::baseUrl(config('services.mercadopago.base_url', self::BASE))
            ->withToken($this->settings->access_token)
            ->acceptJson()
            ->timeout(20);

        return $idempotencyKey ? $req->withHeaders(['X-Idempotency-Key' => $idempotencyKey]) : $req;
    }

    /** Dados do comprador no formato do Mercado Pago. $payer: email, first_name, last_name, cpf, address[]. */
    private function payer(array $payer): array
    {
        $cpf = preg_replace('/\D+/', '', (string) ($payer['cpf'] ?? ''));

        return array_filter([
            'email'          => $payer['email'],
            'first_name'     => $payer['first_name'] ?? null,
            'last_name'      => $payer['last_name'] ?? null,
            'identification' => $cpf !== '' ? ['type' => strlen($cpf) > 11 ? 'CNPJ' : 'CPF', 'number' => $cpf] : null,
            'address'        => $payer['address'] ?? null,
        ]);
    }

    /** POST /v1/payments com a chave de idempotência (repetir a chamada nunca cobra duas vezes). */
    private function createPayment(array $body, string $notificationUrl, string $idempotencyKey, string $what): array
    {
        // O Mercado Pago só aceita URL pública com https para avisar do pagamento.
        if (str_starts_with($notificationUrl, 'https://')) {
            $body['notification_url'] = $notificationUrl;
        }

        $res = $this->http($idempotencyKey)->post('/v1/payments', $body);
        if (! $res->successful()) {
            Log::warning('Mercado Pago: erro ao criar ' . $what, ['status' => $res->status(), 'body' => $res->json() ?? $res->body()]);
            throw new RuntimeException($this->errorMessage($res->json() ?? []));
        }

        return $res->json();
    }

    /**
     * Cria um pagamento Pix.
     * Retorna o JSON do pagamento (id, status, point_of_interaction.transaction_data...).
     */
    public function createPix(float $amount, string $description, array $payer, string $externalReference, string $notificationUrl, \DateTimeInterface $expiresAt, string $idempotencyKey): array
    {
        return $this->createPayment([
            'transaction_amount' => round($amount, 2),
            'description'        => mb_substr($description, 0, 250),
            'payment_method_id'  => 'pix',
            'external_reference' => $externalReference,
            'date_of_expiration' => $expiresAt->format('Y-m-d\TH:i:s.vP'),
            'payer'              => $this->payer($payer),
        ], $notificationUrl, $idempotencyKey, 'Pix');
    }

    /**
     * Cobra um cartão (crédito ou débito). $card vem do formulário seguro do Mercado Pago no
     * navegador: token (o número do cartão nunca passa pelo nosso servidor), payment_method_id,
     * issuer_id, installments e payer.identification.
     */
    public function createCard(float $amount, string $description, array $card, array $payer, string $externalReference, string $notificationUrl, string $idempotencyKey): array
    {
        return $this->createPayment(array_filter([
            'transaction_amount'   => round($amount, 2),
            'description'          => mb_substr($description, 0, 250),
            'token'                => $card['token'],
            'installments'         => max(1, (int) ($card['installments'] ?? 1)),
            'payment_method_id'    => $card['payment_method_id'],
            'issuer_id'            => $card['issuer_id'] ?? null,
            'external_reference'   => $externalReference,
            'statement_descriptor' => $card['statement_descriptor'] ?? null,
            'payer'                => $this->payer($payer),
        ], fn ($v) => $v !== null && $v !== ''), $notificationUrl, $idempotencyKey, 'pagamento com cartão');
    }

    /** Gera um boleto (precisa de nome, CPF e endereço completo do comprador). */
    public function createBoleto(float $amount, string $description, array $payer, string $externalReference, string $notificationUrl, \DateTimeInterface $expiresAt, string $idempotencyKey): array
    {
        return $this->createPayment([
            'transaction_amount' => round($amount, 2),
            'description'        => mb_substr($description, 0, 250),
            'payment_method_id'  => 'bolbradesco',
            'external_reference' => $externalReference,
            'date_of_expiration' => $expiresAt->format('Y-m-d\TH:i:s.vP'),
            'payer'              => $this->payer($payer),
        ], $notificationUrl, $idempotencyKey, 'boleto');
    }

    /** Consulta um pagamento (fonte da verdade: nunca confiar só no aviso ou no navegador). */
    public function getPayment(string $id): array
    {
        $res = $this->http()->get('/v1/payments/' . rawurlencode($id));
        if (! $res->successful()) {
            throw new RuntimeException('Mercado Pago: pagamento ' . $id . ' não encontrado (' . $res->status() . ').');
        }

        return $res->json();
    }

    /** Cancela um Pix que ainda não foi pago. */
    public function cancel(string $id): void
    {
        $this->http()->put('/v1/payments/' . rawurlencode($id), ['status' => 'cancelled']);
    }

    /** Devolve o dinheiro de um pagamento aprovado (estorno total). */
    public function refund(string $id, string $idempotencyKey): array
    {
        $res = $this->http($idempotencyKey)->post('/v1/payments/' . rawurlencode($id) . '/refunds', (object) []);
        if (! $res->successful()) {
            throw new RuntimeException($this->errorMessage($res->json() ?? []));
        }

        return $res->json();
    }

    /** Confere as credenciais: devolve o nome/apelido da conta ou lança erro. */
    public function account(): array
    {
        $res = $this->http()->get('/users/me');
        if (! $res->successful()) {
            throw new RuntimeException('Access Token recusado pelo Mercado Pago (' . $res->status() . ').');
        }

        return $res->json();
    }

    /**
     * Confere a assinatura do aviso (cabeçalho x-signature: "ts=...,v1=...").
     * Modelo: "id:{data.id};request-id:{x-request-id};ts:{ts};" com HMAC SHA256 da chave secreta.
     */
    public static function validSignature(?string $signature, ?string $requestId, ?string $dataId, string $secret): bool
    {
        if (! $signature) {
            return false;
        }
        $parts = [];
        foreach (explode(',', $signature) as $piece) {
            [$k, $v] = array_pad(explode('=', trim($piece), 2), 2, null);
            if ($k !== null && $v !== null) {
                $parts[trim($k)] = trim($v);
            }
        }
        if (empty($parts['ts']) || empty($parts['v1'])) {
            return false;
        }
        $manifest = '';
        if ($dataId !== null && $dataId !== '') {
            $manifest .= 'id:' . (ctype_alnum($dataId) ? strtolower($dataId) : $dataId) . ';';
        }
        if ($requestId) {
            $manifest .= 'request-id:' . $requestId . ';';
        }
        $manifest .= 'ts:' . $parts['ts'] . ';';

        return hash_equals(hash_hmac('sha256', $manifest, $secret), $parts['v1']);
    }

    private function errorMessage(array $json): string
    {
        $cause = $json['cause'][0]['description'] ?? null;
        $msg = $json['message'] ?? 'erro desconhecido';

        return 'Mercado Pago: ' . ($cause ?: $msg);
    }
}
