<?php

namespace App\Services\Payment\Gateways;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Gateway Mercado Pago — excelente para o Brasil, com Pix, boleto e cartão.
 * Requer: MERCADOPAGO_ACCESS_TOKEN, MERCADOPAGO_PUBLIC_KEY no .env
 * Pacote: composer require mercadopago/dx-php
 */
class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return Subscription::GATEWAY_MERCADOPAGO;
    }

    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle): array
    {
        $price = $billingCycle === 'annual'
            ? ($plan->price_annual * 12)
            : $plan->price_monthly;

        try {
            // Quando mercadopago/dx-php estiver instalado:
            // \MercadoPago\SDK::setAccessToken(config('services.mercadopago.access_token'));
            //
            // $preference = new \MercadoPago\Preference();
            // $item = new \MercadoPago\Item();
            // $item->title       = "FlowManager — Plano {$plan->name} ({$this->billingLabel($billingCycle)})";
            // $item->quantity    = 1;
            // $item->unit_price  = (float) $price;
            // $item->currency_id = 'BRL';
            // $preference->items   = [$item];
            // $preference->payer   = ['email' => $user->email];
            // $preference->back_urls = [
            //     'success' => route('subscription.success'),
            //     'failure' => route('subscription.plans'),
            //     'pending' => route('subscription.plans'),
            // ];
            // $preference->auto_return = 'approved';
            // $preference->external_reference = "user_{$user->id}_plan_{$plan->id}";
            // $preference->save();
            // return ['checkout_url' => $preference->init_point, 'preference_id' => $preference->id];

            // Stub:
            return [
                'checkout_url' => route('subscription.checkout', ['plan' => $plan->slug, 'cycle' => $billingCycle, 'gateway' => 'mercadopago']),
                'preference_id' => 'mp_mock_' . uniqid(),
            ];
        } catch (\Throwable $e) {
            Log::error('[MercadoPago] createCheckoutSession: ' . $e->getMessage());
            return ['error' => 'Erro ao criar preferência Mercado Pago.'];
        }
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        Log::info('[MercadoPago] cancelSubscription: ' . ($subscription->gateway_subscription_id ?? 'n/a'));
        return true;
    }

    public function getSubscriptionStatus(Subscription $subscription): string
    {
        return $subscription->status;
    }

    public function handleWebhook(array $payload, string $signature = ''): array
    {
        $type = $payload['type'] ?? $payload['action'] ?? 'unknown';

        return match ($type) {
            'payment'                   => ['event' => 'payment', 'data' => $payload['data'] ?? []],
            'subscription_preapproval'  => ['event' => 'subscription_update', 'data' => $payload['data'] ?? []],
            default                     => ['event' => $type, 'data' => $payload],
        };
    }

    private function billingLabel(string $cycle): string
    {
        return $cycle === 'annual' ? 'Anual' : 'Mensal';
    }
}
