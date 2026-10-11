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

    /**
     * Cria um pagamento Pix. $payer: email, first_name, last_name, cpf (só dígitos).
     * Retorna o JSON do pagamento (id, status, point_of_interaction.transaction_data...).
     */
    public function createPix(float $amount, string $description, array $payer, string $externalReference, string $notificationUrl, \DateTimeInterface $expiresAt, string $idempotencyKey): array
    {
        $body = [
            'transaction_amount' => round($amount, 2),
            'description'        => mb_substr($description, 0, 250),
            'payment_method_id'  => 'pix',
            'external_reference' => $externalReference,
            'date_of_expiration' => $expiresAt->format('Y-m-d\TH:i:s.vP'),
            'payer'              => array_filter([
                'email'      => $payer['email'],
                'first_name' => $payer['first_name'] ?? null,
                'last_name'  => $payer['last_name'] ?? null,
                'identification' => ! empty($payer['cpf'])
                    ? ['type' => strlen($payer['cpf']) > 11 ? 'CNPJ' : 'CPF', 'number' => $payer['cpf']]
                    : null,
            ]),
        ];
        // O Mercado Pago só aceita URL pública com https para avisar do pagamento.
        if (str_starts_with($notificationUrl, 'https://')) {
            $body['notification_url'] = $notificationUrl;
        }

        $res = $this->http($idempotencyKey)->post('/v1/payments', $body);
        if (! $res->successful()) {
            Log::warning('Mercado Pago: erro ao criar Pix', ['status' => $res->status(), 'body' => $res->json() ?? $res->body()]);
            throw new RuntimeException($this->errorMessage($res->json() ?? []));
        }

        return $res->json();
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
