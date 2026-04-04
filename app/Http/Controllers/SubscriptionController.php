<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(protected PaymentService $payment)
    {
    }

    /**
     * Exibe todos os planos disponíveis (pricing page).
     */
    public function plans()
    {
        $plans       = Plan::active()->ordered()->get();
        $currentSub  = Auth::user()->activeSubscription;
        $currentPlan = Auth::user()->plan();

        return view('subscription.plans', compact('plans', 'currentSub', 'currentPlan'));
    }

    /**
     * Exibe a página de checkout para o plano selecionado.
     */
    public function checkout(Request $request, Plan $plan)
    {
        $request->validate([
            'cycle'   => 'required|in:monthly,annual',
            'gateway' => 'required|string',
        ]);

        $billingCycle = $request->cycle;
        $gateway      = $request->gateway;
        $user         = Auth::user();

        $gatewayOptions = $this->payment->gatewayOptions();

        return view('subscription.checkout', compact('plan', 'billingCycle', 'gateway', 'gatewayOptions'));
    }

    /**
     * Processa a escolha do plano e redireciona para o gateway.
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'plan_id'       => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
            'gateway'       => 'required|in:stripe,pagseguro,mercadopago,manual',
        ]);

        $user  = Auth::user();
        $plan  = Plan::findOrFail($request->plan_id);

        // Plano gratuito — ativa diretamente sem pagamento
        if ($plan->isFree()) {
            return $this->activateFree($user, $plan);
        }

        $result = $this->payment->initiateCheckout(
            $user,
            $plan,
            $request->billing_cycle,
            $request->gateway
        );

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        // Se o gateway retornou URL externa (Stripe, MP, PS)
        if (isset($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }

        // Gateway manual (admin) — ativa direto
        if (isset($result['success']) && $result['success']) {
            return redirect()->route('subscription.success')
                ->with('activated_plan', $plan->name);
        }

        return back()->with('error', 'Não foi possível iniciar o pagamento. Tente novamente.');
    }

    /**
     * Ativa o plano gratuito imediatamente.
     */
    protected function activateFree($user, Plan $plan)
    {
        $this->payment->activateSubscription($user, $plan, 'monthly', 'manual', 0);

        return redirect()->route('subscription.success')
            ->with('activated_plan', $plan->name);
    }

    /**
     * Página de sucesso após pagamento.
     */
    public function success(Request $request)
    {
        $user        = Auth::user();
        $activeSub   = $user->fresh()->activeSubscription;
        $activePlan  = $user->fresh()->plan();
        $activatedPlan = session('activated_plan');

        return view('subscription.success', compact('activeSub', 'activePlan', 'activatedPlan'));
    }

    /**
     * Cancela a assinatura ativa do usuário.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $sub  = $user->activeSubscription;

        if (!$sub) {
            return back()->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        if ($sub->plan->isFree()) {
            return back()->with('error', 'O plano gratuito não pode ser cancelado.');
        }

        $this->payment->cancelSubscription($sub);

        // Ativa plano gratuito automaticamente
        $freePlan = Plan::getDefault();
        if ($freePlan) {
            $this->payment->activateSubscription($user, $freePlan, 'monthly', 'manual', 0);
        }

        return back()->with('success', 'Sua assinatura foi cancelada. Você continuará com acesso até o fim do período pago.');
    }

    /**
     * Webhook endpoint para Stripe.
     */
    public function webhookStripe(Request $request)
    {
        return $this->handleWebhook($request, 'stripe');
    }

    /**
     * Webhook endpoint para Mercado Pago.
     */
    public function webhookMercadoPago(Request $request)
    {
        return $this->handleWebhook($request, 'mercadopago');
    }

    /**
     * Webhook endpoint para PagSeguro.
     */
    public function webhookPagSeguro(Request $request)
    {
        return $this->handleWebhook($request, 'pagseguro');
    }

    protected function handleWebhook(Request $request, string $gateway)
    {
        $payload   = $request->all();
        $signature = $request->header('Stripe-Signature')
            ?? $request->header('X-Signature')
            ?? '';

        $result = $this->payment->handleWebhook($gateway, $payload, $signature);

        // Processar eventos comuns
        match ($result['event'] ?? '') {
            'payment_succeeded' => $this->onPaymentSucceeded($result['data'], $gateway),
            'payment_failed'    => $this->onPaymentFailed($result['data'], $gateway),
            'subscription_canceled' => $this->onSubscriptionCanceled($result['data'], $gateway),
            default => null,
        };

        return response()->json(['ok' => true]);
    }

    protected function onPaymentSucceeded(array $data, string $gateway): void
    {
        // Implementar lógica de renovação quando os pacotes dos gateways estiverem instalados
    }

    protected function onPaymentFailed(array $data, string $gateway): void
    {
        // Marcar assinatura como past_due
    }

    protected function onSubscriptionCanceled(array $data, string $gateway): void
    {
        // Marcar assinatura como canceled
    }
}
