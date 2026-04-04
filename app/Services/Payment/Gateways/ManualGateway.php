<?php

namespace App\Services\Payment\Gateways;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Gateway Manual — admin concede/revoga acesso diretamente.
 * Usado para contas especiais, períodos de teste estendidos, etc.
 */
class ManualGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return Subscription::GATEWAY_MANUAL;
    }

    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle): array
    {
        // Cria assinatura diretamente, sem redirecionar para pagamento externo
        $endDate = $billingCycle === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $existing = $user->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if ($existing) {
            $existing->update([
                'plan_id'              => $plan->id,
                'status'               => Subscription::STATUS_ACTIVE,
                'billing_cycle'        => $billingCycle,
                'price_paid'           => 0,
                'gateway'              => $this->getName(),
                'current_period_start' => now(),
                'current_period_end'   => $endDate,
                'canceled_at'          => null,
            ]);
            $sub = $existing;
        } else {
            $sub = $user->subscriptions()->create([
                'plan_id'              => $plan->id,
                'status'               => Subscription::STATUS_ACTIVE,
                'billing_cycle'        => $billingCycle,
                'price_paid'           => 0,
                'gateway'              => $this->getName(),
                'current_period_start' => now(),
                'current_period_end'   => $endDate,
            ]);
        }

        Log::info("[ManualGateway] Assinatura criada/atualizada para user {$user->id}, plano {$plan->slug}");

        return [
            'success'         => true,
            'subscription_id' => $sub->id,
            'redirect'        => route('subscription.success'),
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        $subscription->update([
            'status'      => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);
        Log::info("[ManualGateway] Assinatura #{$subscription->id} cancelada.");
        return true;
    }

    public function getSubscriptionStatus(Subscription $subscription): string
    {
        return $subscription->status;
    }

    public function handleWebhook(array $payload, string $signature = ''): array
    {
        // Gateway manual não recebe webhooks
        return ['event' => 'noop', 'data' => []];
    }
}
