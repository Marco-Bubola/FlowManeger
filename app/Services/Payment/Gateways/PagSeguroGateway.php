<?php

namespace App\Services\Payment\Gateways;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Gateway PagSeguro — popular no Brasil, aceita Pix, boleto e cartão.
 * Requer: PAGSEGURO_TOKEN, PAGSEGURO_EMAIL no .env
 */
class PagSeguroGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return Subscription::GATEWAY_PAGSEGURO;
    }

    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle): array
    {
        $price = $billingCycle === 'annual'
            ? ($plan->price_annual * 12)
            : $plan->price_monthly;

        try {
            // Quando pacote PagSeguro estiver instalado:
            // Endpoint: https://api.pagseguro.com/orders (PagBank API v4)
            // Headers: Authorization: Bearer PAGSEGURO_TOKEN
            // Body: { reference_id, customer, items, payment_methods, redirect_url, notification_urls }
            //
            // Stub:
            return [
                'checkout_url' => route('subscription.checkout', ['plan' => $plan->slug, 'cycle' => $billingCycle, 'gateway' => 'pagseguro']),
                'charge_id'    => 'ps_mock_' . uniqid(),
            ];
        } catch (\Throwable $e) {
            Log::error('[PagSeguro] createCheckoutSession: ' . $e->getMessage());
            return ['error' => 'Erro ao criar sessão PagSeguro.'];
        }
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        Log::info('[PagSeguro] cancelSubscription: ' . ($subscription->gateway_subscription_id ?? 'n/a'));
        return true;
    }

    public function getSubscriptionStatus(Subscription $subscription): string
    {
        return $subscription->status;
    }

    public function handleWebhook(array $payload, string $signature = ''): array
    {
        $type = $payload['type'] ?? 'unknown';

        return match ($type) {
            'charge.paid'     => ['event' => 'payment_succeeded', 'data' => $payload],
            'charge.declined' => ['event' => 'payment_failed',    'data' => $payload],
            default           => ['event' => $type,               'data' => $payload],
        };
    }
}
