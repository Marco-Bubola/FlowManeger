<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\Gateways\ManualGateway;
use App\Services\Payment\Gateways\MercadoPagoGateway;
use App\Services\Payment\Gateways\PagSeguroGateway;
use App\Services\Payment\Gateways\StripeGateway;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected array $gateways;

    public function __construct()
    {
        $this->gateways = [
            Subscription::GATEWAY_STRIPE      => new StripeGateway(),
            Subscription::GATEWAY_PAGSEGURO   => new PagSeguroGateway(),
            Subscription::GATEWAY_MERCADOPAGO => new MercadoPagoGateway(),
            Subscription::GATEWAY_MANUAL      => new ManualGateway(),
        ];
    }

    public function gateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new \InvalidArgumentException("Gateway '{$name}' não reconhecido.");
        }
        return $this->gateways[$name];
    }

    public function availableGateways(): array
    {
        return array_keys($this->gateways);
    }

    // ── Checkout ──────────────────────────────────────────────────

    /**
     * Inicia o fluxo de pagamento. Retorna URL de checkout ou dados de redirecionamento.
     */
    public function initiateCheckout(User $user, Plan $plan, string $billingCycle, string $gatewayName): array
    {
        $gw = $this->gateway($gatewayName);
        $result = $gw->createCheckoutSession($user, $plan, $billingCycle);

        if (isset($result['error'])) {
            Log::error("[PaymentService] Erro no checkout: {$result['error']}", compact('gatewayName', 'billingCycle'));
        }

        return $result;
    }

    // ── Ativar assinatura após pagamento confirmado ───────────────

    public function activateSubscription(
        User $user,
        Plan $plan,
        string $billingCycle,
        string $gateway,
        float $pricePaid = 0,
        ?string $gatewaySubId = null,
        ?string $gatewayCustomerId = null,
        ?string $gatewayPaymentId = null
    ): Subscription {
        return DB::transaction(function () use (
            $user, $plan, $billingCycle, $gateway,
            $pricePaid, $gatewaySubId, $gatewayCustomerId, $gatewayPaymentId
        ) {
            // Cancela assinaturas anteriores ativas
            $user->subscriptions()
                ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
                ->where('id', '!=', 0) // dummy para update all
                ->update(['status' => Subscription::STATUS_CANCELED, 'canceled_at' => now()]);

            $end = $billingCycle === 'annual' ? now()->addYear() : now()->addMonth();

            return $user->subscriptions()->create([
                'plan_id'                  => $plan->id,
                'status'                   => Subscription::STATUS_ACTIVE,
                'billing_cycle'            => $billingCycle,
                'price_paid'               => $pricePaid,
                'gateway'                  => $gateway,
                'gateway_subscription_id'  => $gatewaySubId,
                'gateway_customer_id'      => $gatewayCustomerId,
                'gateway_payment_id'       => $gatewayPaymentId,
                'current_period_start'     => now(),
                'current_period_end'       => $end,
            ]);
        });
    }

    // ── Trial ─────────────────────────────────────────────────────

    public function startTrial(User $user, Plan $plan): Subscription
    {
        $days = $plan->trial_days ?: 7;

        return $user->subscriptions()->create([
            'plan_id'           => $plan->id,
            'status'            => Subscription::STATUS_TRIALING,
            'billing_cycle'     => 'monthly',
            'price_paid'        => 0,
            'gateway'           => Subscription::GATEWAY_MANUAL,
            'trial_ends_at'     => now()->addDays($days),
            'current_period_end'=> now()->addDays($days),
        ]);
    }

    // ── Cancelar ──────────────────────────────────────────────────

    public function cancelSubscription(Subscription $subscription): bool
    {
        if ($subscription->gateway) {
            try {
                $this->gateway($subscription->gateway)->cancelSubscription($subscription);
            } catch (\Throwable $e) {
                Log::warning("[PaymentService] Erro ao cancelar no gateway: " . $e->getMessage());
            }
        }

        $subscription->update([
            'status'      => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);

        return true;
    }

    // ── Webhook dispatcher ────────────────────────────────────────

    public function handleWebhook(string $gatewayName, array $payload, string $signature = ''): array
    {
        $gw = $this->gateway($gatewayName);
        $result = $gw->handleWebhook($payload, $signature);

        Log::info("[PaymentService] Webhook {$gatewayName}: {$result['event']}");

        return $result;
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function gatewayOptions(): array
    {
        return [
            Subscription::GATEWAY_STRIPE      => ['label' => 'Stripe',        'icon' => '💳', 'desc' => 'Cartão de crédito/débito internacional'],
            Subscription::GATEWAY_MERCADOPAGO => ['label' => 'Mercado Pago',  'icon' => '🛒', 'desc' => 'Pix, boleto e cartão (Brasil)'],
            Subscription::GATEWAY_PAGSEGURO   => ['label' => 'PagSeguro',     'icon' => '🏦', 'desc' => 'Pix, boleto e cartão (PagBank)'],
            Subscription::GATEWAY_MANUAL      => ['label' => 'Manual (Admin)','icon' => '🔑', 'desc' => 'Acesso concedido pelo administrador'],
        ];
    }
}
