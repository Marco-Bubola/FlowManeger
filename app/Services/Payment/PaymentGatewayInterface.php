<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * Cria uma sessão de checkout e retorna a URL de pagamento.
     */
    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle): array;

    /**
     * Cancela a assinatura no gateway.
     */
    public function cancelSubscription(Subscription $subscription): bool;

    /**
     * Verifica o status de uma assinatura no gateway.
     */
    public function getSubscriptionStatus(Subscription $subscription): string;

    /**
     * Processa um webhook recebido do gateway.
     * Retorna array com tipo do evento e dados relevantes.
     */
    public function handleWebhook(array $payload, string $signature = ''): array;

    /**
     * Nome identificador do gateway.
     */
    public function getName(): string;
}
