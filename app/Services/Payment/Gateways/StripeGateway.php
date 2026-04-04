<?php

namespace App\Services\Payment\Gateways;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Gateway Stripe — suporta cartão de crédito/débito internacional.
 * Requer: STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET no .env
 */
class StripeGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return Subscription::GATEWAY_STRIPE;
    }

    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle): array
    {
        $priceId = $this->getPriceId($plan, $billingCycle);

        if (!$priceId) {
            return ['error' => 'Este plano não possui um price_id configurado para Stripe.'];
        }

        try {
            // Quando o pacote stripe/stripe-php estiver instalado:
            // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            // $session = \Stripe\Checkout\Session::create([
            //     'customer_email'       => $user->email,
            //     'payment_method_types' => ['card'],
            //     'line_items'           => [['price' => $priceId, 'quantity' => 1]],
            //     'mode'                 => 'subscription',
            //     'success_url'  => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            //     'cancel_url'   => route('subscription.plans'),
            //     'metadata'     => ['user_id' => $user->id, 'plan_id' => $plan->id, 'billing_cycle' => $billingCycle],
            // ]);
            // return ['checkout_url' => $session->url, 'session_id' => $session->id];

            // Stub enquanto stripe/stripe-php não está instalado:
            return [
                'checkout_url' => route('subscription.checkout', ['plan' => $plan->slug, 'cycle' => $billingCycle, 'gateway' => 'stripe']),
                'session_id'   => 'stripe_mock_' . uniqid(),
            ];
        } catch (\Throwable $e) {
            Log::error('[Stripe] createCheckoutSession: ' . $e->getMessage());
            return ['error' => 'Erro ao criar sessão de pagamento Stripe.'];
        }
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        if (!$subscription->gateway_subscription_id) {
            return false;
        }
        try {
            // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            // \Stripe\Subscription::update($subscription->gateway_subscription_id, ['cancel_at_period_end' => true]);
            Log::info('[Stripe] cancelSubscription: ' . $subscription->gateway_subscription_id);
            return true;
        } catch (\Throwable $e) {
            Log::error('[Stripe] cancelSubscription: ' . $e->getMessage());
            return false;
        }
    }

    public function getSubscriptionStatus(Subscription $subscription): string
    {
        // Implementar quando stripe/stripe-php estiver instalado
        return $subscription->status;
    }

    public function handleWebhook(array $payload, string $signature = ''): array
    {
        $secret = config('services.stripe.webhook_secret');
        $eventType = $payload['type'] ?? 'unknown';

        // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // try {
        //     $event = \Stripe\Webhook::constructEvent(json_encode($payload), $signature, $secret);
        // } catch (\Stripe\Exception\SignatureVerificationException $e) {
        //     return ['error' => 'Assinatura inválida'];
        // }

        return match ($eventType) {
            'invoice.payment_succeeded'  => ['event' => 'payment_succeeded', 'data' => $payload['data'] ?? []],
            'invoice.payment_failed'     => ['event' => 'payment_failed',    'data' => $payload['data'] ?? []],
            'customer.subscription.deleted' => ['event' => 'subscription_canceled', 'data' => $payload['data'] ?? []],
            default                      => ['event' => $eventType, 'data' => $payload['data'] ?? []],
        };
    }

    private function getPriceId(Plan $plan, string $billingCycle): ?string
    {
        $features = $plan->features ?? [];
        $key = $billingCycle === 'annual' ? 'stripe_price_annual_id' : 'stripe_price_monthly_id';
        return $features[$key] ?? null;
    }
}
