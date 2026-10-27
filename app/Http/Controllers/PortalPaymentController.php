<?php

namespace App\Http\Controllers;

use App\Models\ClientQuoteRequest;
use App\Models\PaymentSetting;
use App\Models\PortalPayment;
use App\Models\User;
use App\Notifications\PortalQuoteReceived;
use App\Services\Payments\MercadoPagoService;
use App\Services\Payments\PortalCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/** Pagamento do pedido pelo cliente no portal (Pix, cartão e boleto do Mercado Pago) e o aviso do Mercado Pago. */
class PortalPaymentController extends Controller
{
    public function __construct(private PortalCheckoutService $checkout) {}

    private function ownQuote(ClientQuoteRequest $quote): ClientQuoteRequest
    {
        abort_if((int) $quote->client_id !== (int) Auth::guard('portal')->id(), 403);

        return $quote;
    }

    /**
     * Tela de pagamento do pedido: Pix (gera o QR na hora), cartão (formulário seguro do
     * Mercado Pago) ou boleto (gerado ao tocar no botão). ?metodo= escolhe a aba.
     */
    public function show(Request $request, ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $client = Auth::guard('portal')->user();
        $store = User::select('id', 'name', 'phone')->find($quote->user_id);
        $settings = PaymentSetting::for((int) $quote->user_id);

        if ($quote->payment_status === 'approved') {
            return redirect()->route('portal.quotes.show', $quote)->with('success', 'Pagamento aprovado! A loja já recebeu seu pedido.');
        }

        $methods = $settings?->methods() ?? [];
        $method = $request->query('metodo');
        if (! in_array($method, $methods, true)) {
            $last = $quote->payments()->latest('id')->value('method');
            $method = in_array($last, $methods, true) ? $last : ($methods[0] ?? null);
        }

        $payment = null;
        $error = null;
        if (! $method) {
            $error = 'A loja não está recebendo pagamentos pelo site agora.';
        } elseif ($quote->status === 'rejected' || $quote->sale) {
            $error = 'Este pedido não pode mais ser pago pelo site.';
        } elseif ($method === 'pix') {
            try {
                $payment = $this->checkout->startPix($quote, $client, $settings);
            } catch (\Throwable $e) {
                Log::warning('Pix do portal não gerado', ['quote' => $quote->id, 'erro' => $e->getMessage()]);
                $error = $this->friendly($e, 'Não conseguimos gerar o Pix agora. Tente de novo em instantes.');
            }
        } elseif ($method === 'boleto') {
            $payment = $this->checkout->openPayment($quote, 'boleto');
        } elseif ($problems = $this->checkout->stockProblems($quote)) {
            $error = 'Sem estoque suficiente agora: ' . implode(', ', array_keys($problems)) . '.';
        }

        return view('portal.quotes.pay', [
            'quote' => $quote, 'client' => $client, 'store' => $store, 'payment' => $payment,
            'error' => $error, 'settings' => $settings, 'orders' => app(\App\Services\Portal\PortalOrderService::class),
            'method' => $method, 'methods' => $methods,
            'amount' => $method === 'pix' ? $this->checkout->pixAmount($quote) : $this->checkout->amount($quote),
            'pixOff' => \App\Services\Portal\Rewards::quotePixOff($quote),
        ]);
    }

    private function friendly(\Throwable $e, string $fallback): string
    {
        $m = $e->getMessage();

        return str_starts_with($m, 'Sem estoque') || str_starts_with($m, 'Este pedido') || str_starts_with($m, 'Para gerar')
            ? $m : $fallback;
    }

    /** Gera o boleto do pedido. */
    public function boleto(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $settings = PaymentSetting::for((int) $quote->user_id);
        abort_unless($settings?->boletoReady() && ! $quote->sale && $quote->status !== 'rejected', 422);

        try {
            $this->checkout->startBoleto($quote, Auth::guard('portal')->user(), $settings);
        } catch (\Throwable $e) {
            Log::warning('Boleto do portal não gerado', ['quote' => $quote->id, 'erro' => $e->getMessage()]);

            return redirect()->route('portal.quotes.pay', [$quote, 'metodo' => 'boleto'])
                ->withErrors(['boleto' => $this->friendly($e, 'Não conseguimos gerar o boleto agora. Tente de novo em instantes.')]);
        }

        return redirect()->route('portal.quotes.pay', [$quote, 'metodo' => 'boleto']);
    }

    /** Cobra o cartão (dados vindos do formulário seguro do Mercado Pago, em JSON). */
    public function card(Request $request, ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $settings = PaymentSetting::for((int) $quote->user_id);
        if (! $settings?->cardReady() || $quote->sale || $quote->status === 'rejected') {
            return response()->json(['status' => 'error', 'message' => 'Este pedido não pode ser pago com cartão agora.'], 422);
        }
        $data = $request->validate([
            'token'             => ['required', 'string', 'max:100'],
            'payment_method_id' => ['required', 'string', 'max:40'],
            'issuer_id'         => ['nullable'],
            'installments'      => ['required', 'integer', 'min:1', 'max:24'],
            'payer'             => ['nullable', 'array'],
            'payer.email'       => ['nullable', 'email'],
            'payer.identification.number' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            [$payment, $message] = $this->checkout->payCard($quote, Auth::guard('portal')->user(), $settings, $data);
        } catch (\Throwable $e) {
            Log::warning('Cartão do portal não cobrado', ['quote' => $quote->id, 'erro' => $e->getMessage()]);

            return response()->json(['status' => 'error', 'message' => $this->friendly($e, 'Não conseguimos processar o cartão. Confira os dados ou tente o Pix.')], 422);
        }
        $quote->refresh();

        return response()->json([
            'status'   => $quote->payment_status === 'approved' ? 'approved' : $payment->status,
            'message'  => $message,
            'redirect' => $quote->payment_status === 'approved' ? route('portal.quotes.show', $quote) : null,
        ]);
    }

    /** Situação do pagamento (a tela pergunta a cada poucos segundos). */
    public function status(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $payment = $quote->payments()->latest('id')->first();

        $wait = $payment?->method === 'boleto' ? 60 : 4;
        if ($payment && $payment->isPending() && $payment->updated_at->lt(now()->subSeconds($wait))) {
            try {
                $payment = $this->checkout->refresh($payment);
            } catch (\Throwable $e) {
                Log::info('Consulta do Pix falhou', ['payment' => $payment->id, 'erro' => $e->getMessage()]);
                $payment->touch();
            }
        }
        $quote->refresh();

        return response()->json([
            'status'  => $quote->payment_status === 'approved' ? 'approved' : ($payment?->isExpired() ? 'expired' : ($payment->status ?? 'none')),
            'redirect'=> $quote->payment_status === 'approved' ? route('portal.quotes.show', $quote) : null,
        ]);
    }

    /** Desiste de pagar pelo site e manda o pedido para a loja combinar o pagamento. */
    public function manual(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        abort_if($quote->payment_status === 'approved', 422);
        $settings = PaymentSetting::for((int) $quote->user_id);
        if ($settings?->onlineReady() && ! $settings->allow_manual) {
            return back()->withErrors(['checkout' => 'Esta loja recebe os pedidos com pagamento pelo site.']);
        }

        foreach ($quote->payments()->whereIn('status', ['pending', 'in_process'])->get() as $p) {
            try {
                if ($p->provider_id && $settings?->access_token) {
                    $this->checkout->api($settings)->cancel($p->provider_id);
                }
            } catch (\Throwable $e) {
                // vence sozinho
            }
            $p->update(['status' => 'cancelled']);
        }
        $wasPix = $quote->payment_status !== null;
        $quote->update(['payment_status' => null, 'payment_preference' => null]);
        if ($wasPix) {
            User::find($quote->user_id)?->notify(new PortalQuoteReceived($quote));
        }

        return redirect()->route('portal.quotes.show', $quote)->with('success', 'Pedido enviado! A loja vai combinar o pagamento com você.');
    }

    /**
     * Aviso do Mercado Pago (notification_url). Sempre confere o pagamento na API
     * antes de mudar qualquer coisa; com chave secreta salva, confere a assinatura.
     */
    public function webhook(Request $request, int $user)
    {
        $settings = PaymentSetting::for($user);
        if (! $settings || blank($settings->access_token)) {
            return response()->json(['ok' => true]);
        }

        $type = $request->input('type') ?? $request->query('type') ?? $request->query('topic');
        $dataId = (string) ($request->input('data.id') ?? $request->query('data_id') ?? $request->query('data.id') ?? $request->query('id') ?? '');
        if ($type && ! in_array($type, ['payment'], true)) {
            return response()->json(['ok' => true]);
        }
        if ($dataId === '') {
            return response()->json(['ok' => true]);
        }

        if (filled($settings->webhook_secret)
            && ! MercadoPagoService::validSignature($request->header('x-signature'), $request->header('x-request-id'), $request->query('data_id', $dataId), $settings->webhook_secret)) {
            Log::warning('Mercado Pago: aviso com assinatura inválida', ['user' => $user, 'id' => $dataId]);

            return response()->json(['ok' => false], 401);
        }

        try {
            $mp = $this->checkout->api($settings)->getPayment($dataId);
            $payment = PortalPayment::where('user_id', $user)->where('provider_id', $dataId)->first();
            if (! $payment && preg_match('/^quote:(\d+):pay:(\d+)$/', (string) ($mp['external_reference'] ?? ''), $m)) {
                $payment = PortalPayment::where('user_id', $user)->where('quote_id', (int) $m[1])->find((int) $m[2]);
                $payment?->update(['provider_id' => $dataId]);
            }
            if ($payment) {
                $this->checkout->apply($payment, $mp);
            }
        } catch (\Throwable $e) {
            Log::warning('Mercado Pago: aviso não processado', ['user' => $user, 'id' => $dataId, 'erro' => $e->getMessage()]);

            return response()->json(['ok' => false], 500); // o Mercado Pago tenta de novo
        }

        return response()->json(['ok' => true]);
    }

    /** A loja devolve ao cliente um pagamento aprovado pelo site (cancela a venda e devolve o estoque). */
    public function refund(ClientQuoteRequest $quote)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && ($user->isAdmin() || (int) $quote->user_id === (int) $user->id), 403);

        $payment = $quote->payments()->where('status', 'approved')->whereNotNull('sale_id')->latest('id')->first();
        if (! $payment) {
            return back()->with('error', 'Não há pagamento aprovado para devolver neste pedido.');
        }
        try {
            $this->checkout->refund($payment);
        } catch (\Throwable $e) {
            Log::warning('Estorno do portal falhou', ['payment' => $payment->id, 'erro' => $e->getMessage()]);

            return back()->with('error', 'O Mercado Pago não devolveu o pagamento: ' . $e->getMessage());
        }

        return back()->with('success', 'Pagamento de ' . 'R$ ' . number_format((float) $payment->amount, 2, ',', '.') . ' devolvido ao cliente. A venda foi cancelada e o estoque voltou.');
    }
}
