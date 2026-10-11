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

/** Pagamento do pedido pelo cliente no portal (Pix do Mercado Pago) e o aviso do Mercado Pago. */
class PortalPaymentController extends Controller
{
    public function __construct(private PortalCheckoutService $checkout) {}

    private function ownQuote(ClientQuoteRequest $quote): ClientQuoteRequest
    {
        abort_if((int) $quote->client_id !== (int) Auth::guard('portal')->id(), 403);

        return $quote;
    }

    /** Tela do Pix: gera (ou mostra) o QR code do pedido. */
    public function show(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $client = Auth::guard('portal')->user();
        $store = User::select('id', 'name', 'phone')->find($quote->user_id);
        $settings = PaymentSetting::for((int) $quote->user_id);

        if ($quote->payment_status === 'approved') {
            return redirect()->route('portal.quotes.show', $quote)->with('success', 'Pagamento aprovado! A loja já recebeu seu pedido.');
        }

        $payment = null;
        $error = null;
        if (! $settings?->pixReady()) {
            $error = 'A loja não está recebendo Pix pelo site agora.';
        } elseif ($quote->status === 'rejected' || $quote->sale) {
            $error = 'Este pedido não pode mais ser pago pelo site.';
        } else {
            try {
                $payment = $this->checkout->startPix($quote, $client, $settings);
            } catch (\Throwable $e) {
                Log::warning('Pix do portal não gerado', ['quote' => $quote->id, 'erro' => $e->getMessage()]);
                $error = str_starts_with($e->getMessage(), 'Sem estoque') || str_starts_with($e->getMessage(), 'Este pedido')
                    ? $e->getMessage()
                    : 'Não conseguimos gerar o Pix agora. Tente de novo em instantes.';
            }
        }

        return view('portal.quotes.pay', [
            'quote' => $quote, 'client' => $client, 'store' => $store, 'payment' => $payment,
            'error' => $error, 'settings' => $settings, 'orders' => app(\App\Services\Portal\PortalOrderService::class),
        ]);
    }

    /** Situação do pagamento (a tela pergunta a cada poucos segundos). */
    public function status(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        $payment = $quote->payments()->latest('id')->first();

        if ($payment && $payment->isPending() && $payment->updated_at->lt(now()->subSeconds(4))) {
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

    /** Desiste do Pix e manda o pedido para a loja combinar o pagamento. */
    public function manual(ClientQuoteRequest $quote)
    {
        $quote = $this->ownQuote($quote);
        abort_if($quote->payment_status === 'approved', 422);
        $settings = PaymentSetting::for((int) $quote->user_id);
        if ($settings?->pixReady() && ! $settings->allow_manual) {
            return back()->withErrors(['checkout' => 'Esta loja recebe os pedidos pelo Pix.']);
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
}
