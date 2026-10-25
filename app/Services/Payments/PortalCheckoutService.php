<?php

namespace App\Services\Payments;

use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\PaymentSetting;
use App\Models\PortalPayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use App\Notifications\PortalPaymentApproved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pagamento do pedido do portal pelo Mercado Pago (Pix, cartão ou boleto): gera a cobrança,
 * confere o pagamento na API e, quando aprovado, cria a venda paga (uma vez só) e baixa o
 * estoque. Estorno (pela loja ou pelo painel do Mercado Pago) cancela a venda e devolve o estoque.
 */
class PortalCheckoutService
{
    /** Status do Mercado Pago → status do pedido. */
    private const MAP = [
        'approved'     => 'approved',
        'authorized'   => 'pending',
        'pending'      => 'pending',
        'in_process'   => 'pending',
        'in_mediation' => 'pending',
        'rejected'     => 'rejected',
        'cancelled'    => 'cancelled',
        'refunded'     => 'refunded',
        'charged_back' => 'refunded',
    ];

    /** Motivos de recusa do cartão em português (status_detail do Mercado Pago). */
    private const CARD_REJECTIONS = [
        'cc_rejected_insufficient_amount'      => 'Saldo ou limite insuficiente.',
        'cc_rejected_bad_filled_security_code' => 'Código de segurança (CVV) incorreto.',
        'cc_rejected_bad_filled_date'          => 'Data de validade incorreta.',
        'cc_rejected_bad_filled_card_number'   => 'Número do cartão incorreto.',
        'cc_rejected_bad_filled_other'         => 'Confira os dados do cartão.',
        'cc_rejected_call_for_authorize'       => 'O banco pediu autorização: ligue para o seu banco e tente de novo.',
        'cc_rejected_card_disabled'            => 'Cartão bloqueado ou não ativado. Fale com o seu banco.',
        'cc_rejected_duplicated_payment'       => 'Você já fez um pagamento igual agora há pouco.',
        'cc_rejected_high_risk'                => 'Pagamento recusado por segurança. Tente o Pix ou outro cartão.',
        'cc_rejected_max_attempts'             => 'Muitas tentativas. Use outro cartão ou o Pix.',
        'cc_rejected_blacklist'                => 'Pagamento recusado. Tente o Pix ou outro cartão.',
        'cc_rejected_other_reason'             => 'O banco recusou o pagamento. Tente outro cartão ou o Pix.',
    ];

    public function api(PaymentSetting $settings): MercadoPagoService
    {
        return new MercadoPagoService($settings);
    }

    /** Valor a pagar recalculado no servidor: preço do pedido − cupom + entrega. */
    public function amount(ClientQuoteRequest $quote): float
    {
        $sum = collect($quote->items ?? [])->sum(fn ($i) => (float) ($i['price_ref'] ?? 0) * max(1, (int) ($i['quantity'] ?? 1)));
        $sum = max(0, $sum - (float) ($quote->discount ?? 0));
        // Kit montado: base + enfeites.
        $sum += \App\Services\Portal\KitService::total($quote);

        return round($sum + (float) ($quote->delivery_fee ?? 0), 2);
    }

    /** Produtos sem estoque suficiente para o pedido (nome => disponível). */
    public function stockProblems(ClientQuoteRequest $quote): array
    {
        $out = [];
        foreach ($quote->items ?? [] as $item) {
            $p = Product::withoutGlobalScopes()->where('user_id', $quote->user_id)->find($item['product_id'] ?? 0);
            $need = max(1, (int) ($item['quantity'] ?? 1));
            if (! $p || ! in_array($p->status, ['active', 'ativo'], true) || (int) $p->stock_quantity < $need) {
                $out[$item['name'] ?? ('#' . ($item['product_id'] ?? '?'))] = $p ? max(0, (int) $p->stock_quantity) : 0;
            }
        }

        return $out;
    }

    /** Cobrança em aberto (não vencida) do pedido para um meio, se houver. */
    public function openPayment(ClientQuoteRequest $quote, string $method): ?PortalPayment
    {
        return $quote->payments()->where('method', $method)->whereIn('status', ['pending', 'in_process'])
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')->first();
    }

    public function openPix(ClientQuoteRequest $quote): ?PortalPayment
    {
        return $this->openPayment($quote, 'pix');
    }

    /** Antes de cobrar: o pedido não pode estar pago e precisa ter estoque e valor. */
    private function guard(ClientQuoteRequest $quote): float
    {
        if ($quote->payment_status === 'approved') {
            throw new RuntimeException('Este pedido já está pago.');
        }
        if ($problems = $this->stockProblems($quote)) {
            throw new RuntimeException('Sem estoque suficiente agora: ' . implode(', ', array_keys($problems)) . '.');
        }
        $amount = $this->amount($quote);
        if ($amount < 0.01) {
            throw new RuntimeException('Pedido sem valor para pagar.');
        }

        return $amount;
    }

    private function payer(Client $client, bool $withAddress = false): array
    {
        [$first, $last] = array_pad(explode(' ', trim((string) $client->name), 2), 2, '');
        $payer = [
            'email'      => $client->email ?: ('cliente' . $client->id . '@flowmaneger.com'),
            'first_name' => $first ?: 'Cliente',
            'last_name'  => $last ?: $first,
            'cpf'        => preg_replace('/\D+/', '', (string) $client->cpf_cnpj),
        ];
        if ($withAddress) {
            $payer['address'] = [
                'zip_code'      => preg_replace('/\D+/', '', (string) $client->cep),
                'street_name'   => (string) $client->street,
                'street_number' => (string) $client->number,
                'neighborhood'  => (string) $client->neighborhood,
                'city'          => (string) $client->city,
                'federal_unit'  => strtoupper(substr((string) $client->state, 0, 2)),
            ];
        }

        return $payer;
    }

    private function description(ClientQuoteRequest $quote): string
    {
        $store = User::whereKey($quote->user_id)->value('name');

        return 'Pedido #' . $quote->id . ($store ? ' · ' . $store : '');
    }

    private function newPayment(ClientQuoteRequest $quote, string $method, float $amount, ?\DateTimeInterface $expires = null): PortalPayment
    {
        return PortalPayment::create([
            'quote_id' => $quote->id, 'user_id' => $quote->user_id, 'method' => $method, 'status' => 'creating',
            'amount' => $amount, 'expires_at' => $expires, 'idempotency_key' => (string) Str::uuid(),
        ]);
    }

    private function reference(ClientQuoteRequest $quote, PortalPayment $payment): string
    {
        return 'quote:' . $quote->id . ':pay:' . $payment->id;
    }

    private function webhookUrl(ClientQuoteRequest $quote): string
    {
        return route('payments.mercadopago.webhook', ['user' => $quote->user_id]);
    }

    /** Gera (ou reaproveita) o Pix do pedido. */
    public function startPix(ClientQuoteRequest $quote, Client $client, PaymentSetting $settings): PortalPayment
    {
        if ($quote->payment_status === 'approved') {
            throw new RuntimeException('Este pedido já está pago.');
        }
        if ($open = $this->openPix($quote)) {
            return $open;
        }
        $amount = $this->guard($quote);
        $expires = now()->addMinutes(max(10, min(1440, (int) ($settings->pix_minutes ?: 30))));
        $payment = $this->newPayment($quote, 'pix', $amount, $expires);

        try {
            $mp = $this->api($settings)->createPix($amount, $this->description($quote), $this->payer($client),
                $this->reference($quote, $payment), $this->webhookUrl($quote), $expires, $payment->idempotency_key);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'error', 'status_detail' => Str::limit($e->getMessage(), 75)]);
            throw $e;
        }

        $tx = $mp['point_of_interaction']['transaction_data'] ?? [];
        $payment->update([
            'provider_id'    => (string) ($mp['id'] ?? ''),
            'status'         => $mp['status'] ?? 'pending',
            'status_detail'  => $mp['status_detail'] ?? null,
            'qr_code'        => $tx['qr_code'] ?? null,
            'qr_code_base64' => $tx['qr_code_base64'] ?? null,
            'ticket_url'     => $tx['ticket_url'] ?? null,
        ]);
        $quote->update(['payment_status' => 'pending', 'payment_amount' => $amount, 'payment_preference' => 'pix']);

        return $payment->fresh();
    }

    /** Gera (ou reaproveita) o boleto do pedido. Vence no fim do dia, depois do prazo da loja. */
    public function startBoleto(ClientQuoteRequest $quote, Client $client, PaymentSetting $settings): PortalPayment
    {
        if ($quote->payment_status === 'approved') {
            throw new RuntimeException('Este pedido já está pago.');
        }
        if ($open = $this->openPayment($quote, 'boleto')) {
            return $open;
        }
        if (! $client->hasRequiredPortalProfileData()) {
            throw new RuntimeException('Para gerar o boleto, complete seu CPF e endereço em Meus dados.');
        }
        $amount = $this->guard($quote);
        $expires = now()->addDays(max(1, min(10, (int) ($settings->boleto_days ?: 3))))->endOfDay();
        $payment = $this->newPayment($quote, 'boleto', $amount, $expires);

        try {
            $mp = $this->api($settings)->createBoleto($amount, $this->description($quote), $this->payer($client, true),
                $this->reference($quote, $payment), $this->webhookUrl($quote), $expires, $payment->idempotency_key);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'error', 'status_detail' => Str::limit($e->getMessage(), 75)]);
            throw $e;
        }

        $payment->update([
            'provider_id'   => (string) ($mp['id'] ?? ''),
            'status'        => $mp['status'] ?? 'pending',
            'status_detail' => $mp['status_detail'] ?? null,
            'ticket_url'    => $mp['transaction_details']['external_resource_url'] ?? null,
            'barcode'       => $mp['barcode']['content'] ?? ($mp['transaction_details']['digitable_line'] ?? null),
        ]);
        $quote->update(['payment_status' => 'pending', 'payment_amount' => $amount, 'payment_preference' => 'boleto']);

        return $payment->fresh();
    }

    /**
     * Cobra o cartão com os dados do formulário seguro do Mercado Pago. O valor é sempre o do
     * servidor. Aprovado na hora: a venda paga já sai daqui. Retorna o pagamento e uma mensagem.
     */
    public function payCard(ClientQuoteRequest $quote, Client $client, PaymentSetting $settings, array $card): array
    {
        // Um Pix/boleto em aberto pode ter sido pago agora: confere antes de cobrar o cartão.
        foreach ($quote->payments()->whereIn('status', ['pending', 'in_process'])->whereNotNull('provider_id')->get() as $open) {
            try {
                $this->refresh($open, $settings);
            } catch (\Throwable $e) {
                // segue: a cobrança em dobro, se acontecer, é devolvida sozinha em apply()
            }
        }
        $quote->refresh();
        $amount = $this->guard($quote);
        $installments = max(1, min((int) ($settings->max_installments ?: 1), (int) ($card['installments'] ?? 1)));
        $payment = $this->newPayment($quote, 'cartao', $amount);

        $payer = $this->payer($client);
        $cardDoc = preg_replace('/\D+/', '', (string) ($card['payer']['identification']['number'] ?? ''));
        if ($cardDoc !== '') {
            $payer['cpf'] = $cardDoc; // documento do titular do cartão (pode ser outra pessoa)
        }
        if (filled($card['payer']['email'] ?? null)) {
            $payer['email'] = $card['payer']['email'];
        }

        try {
            $mp = $this->api($settings)->createCard($amount, $this->description($quote), [
                'token'             => (string) $card['token'],
                'payment_method_id' => (string) $card['payment_method_id'],
                'issuer_id'         => $card['issuer_id'] ?? null,
                'installments'      => $installments,
            ], $payer, $this->reference($quote, $payment), $this->webhookUrl($quote), $payment->idempotency_key);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'error', 'status_detail' => Str::limit($e->getMessage(), 75)]);
            throw $e;
        }

        $payment->update([
            'provider_id'  => (string) ($mp['id'] ?? ''),
            'installments' => (int) ($mp['installments'] ?? $installments),
            'card_brand'   => $mp['payment_method_id'] ?? ($card['payment_method_id'] ?? null),
            'card_last4'   => $mp['card']['last_four_digits'] ?? null,
        ]);
        $quote->update(['payment_amount' => $amount, 'payment_preference' => ($mp['payment_type_id'] ?? '') === 'debit_card' ? 'debito' : 'credito']);
        $payment = $this->apply($payment, $mp);

        return [$payment, $this->cardMessage($payment)];
    }

    public function cardMessage(PortalPayment $payment): string
    {
        return match (true) {
            $payment->status === 'approved' => 'Pagamento aprovado!',
            $payment->isPending()           => 'Pagamento em análise. Você recebe a confirmação aqui em instantes.',
            default                         => self::CARD_REJECTIONS[$payment->status_detail] ?? 'Pagamento recusado. Confira os dados ou use outro cartão.',
        };
    }

    /** Consulta o Mercado Pago e aplica o resultado. */
    public function refresh(PortalPayment $payment, ?PaymentSetting $settings = null): PortalPayment
    {
        if (! $payment->provider_id) {
            return $payment;
        }
        $settings ??= PaymentSetting::for($payment->user_id);
        if (! $settings || blank($settings->access_token)) {
            return $payment;
        }

        return $this->apply($payment, $this->api($settings)->getPayment($payment->provider_id));
    }

    /** Aplica o JSON do pagamento (vindo da API) no pedido. Idempotente. */
    public function apply(PortalPayment $payment, array $mp): PortalPayment
    {
        $status = (string) ($mp['status'] ?? $payment->status);
        $duplicate = null;

        $payment = DB::transaction(function () use ($payment, $mp, $status, &$duplicate) {
            /** @var PortalPayment $payment */
            $payment = PortalPayment::whereKey($payment->id)->lockForUpdate()->first();
            $quote = ClientQuoteRequest::whereKey($payment->quote_id)->lockForUpdate()->first();
            $wasApproved = $payment->status === 'approved' || $payment->sale_id;

            // Valor pago tem que bater com o cobrado (nunca confiar em valor menor).
            $paidAmount = (float) ($mp['transaction_amount'] ?? $payment->amount);
            if ($status === 'approved' && $paidAmount + 0.009 < (float) $payment->amount) {
                Log::warning('Mercado Pago: valor aprovado menor que o cobrado', ['payment' => $payment->id, 'mp' => $paidAmount]);
                $status = 'in_mediation';
            }

            $payment->fill([
                'status'        => $status,
                'status_detail' => $mp['status_detail'] ?? $payment->status_detail,
            ]);
            if ($status === 'approved' && ! $payment->approved_at) {
                $payment->approved_at = isset($mp['date_approved']) ? \Illuminate\Support\Carbon::parse($mp['date_approved']) : now();
            }
            $payment->save();

            if (! $quote) {
                return $payment;
            }

            $quoteStatus = self::MAP[$status] ?? 'pending';
            if ($quoteStatus === 'approved') {
                if (! $payment->sale_id) {
                    // Outro pagamento do mesmo pedido já foi aprovado (ex.: pagou Pix e cartão):
                    // este é devolvido, a venda não é duplicada.
                    $other = $quote->payments()->where('id', '<>', $payment->id)->whereNotNull('sale_id')
                        ->where('status', 'approved')->exists();
                    if ($other) {
                        $duplicate = $payment;

                        return $payment;
                    }
                    // Aviso do Mercado Pago e tela do cliente não têm a loja logada: sem isso o
                    // filtro de equipe esconde os produtos e o estoque não baixa.
                    $sale = $this->asOwner((int) $quote->user_id, fn () => $quote->sale
                        ? $this->addPaymentToSale($quote->sale, $payment, $mp)
                        : $this->createPaidSale($quote, $payment, $mp));
                    $payment->update(['sale_id' => $sale->id]);
                    $this->notifyOwner($quote, $payment);
                }
                $quote->update(['payment_status' => 'approved', 'paid_at' => $payment->approved_at ?? now()]
                    + (in_array($quote->status, ['pending', 'reviewing', 'quoted'], true) ? ['status' => 'approved', 'responded_at' => now()] : []));
            } elseif ($quoteStatus === 'refunded') {
                if ($wasApproved && ! $payment->refunded_at) {
                    $this->asOwner((int) $quote->user_id, fn () => $this->undoSale($payment));
                }
                if (! $quote->payments()->where('status', 'approved')->exists()) {
                    $quote->update(['payment_status' => 'refunded']);
                }
            } elseif ($quote->payment_status !== 'approved') {
                // Pix/boleto vencido ou cartão recusado: o pedido continua aberto para tentar de novo.
                $quote->update(['payment_status' => $quoteStatus]);
            }

            return $payment;
        });

        if ($duplicate) {
            $this->refundDuplicate($duplicate);
            $payment->refresh();
        }

        return $payment;
    }

    /** Devolve um pagamento aprovado (pela loja). Cancela a venda e devolve o estoque. */
    public function refund(PortalPayment $payment): PortalPayment
    {
        if ($payment->status !== 'approved' || ! $payment->provider_id) {
            throw new RuntimeException('Só dá para devolver um pagamento aprovado.');
        }
        $settings = PaymentSetting::for($payment->user_id);
        if (! $settings || blank($settings->access_token)) {
            throw new RuntimeException('Mercado Pago não está conectado.');
        }
        $this->api($settings)->refund($payment->provider_id, 'refund-' . $payment->idempotency_key);

        return $this->apply($payment, ['status' => 'refunded', 'status_detail' => 'refunded', 'transaction_amount' => (float) $payment->amount]);
    }

    private function refundDuplicate(PortalPayment $payment): void
    {
        try {
            $settings = PaymentSetting::for($payment->user_id);
            $this->api($settings)->refund($payment->provider_id, 'refund-' . $payment->idempotency_key);
            $payment->update(['status' => 'refunded', 'status_detail' => 'pagamento em dobro devolvido', 'refunded_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Pagamento em dobro do portal NÃO devolvido', ['payment' => $payment->id, 'erro' => $e->getMessage()]);
            $payment->update(['status_detail' => 'em dobro: devolver no Mercado Pago']);
        }
    }

    /** Desfaz a venda paga por este pagamento (estorno ou contestação). */
    private function undoSale(PortalPayment $payment): void
    {
        $payment->update(['refunded_at' => now()]);
        $sale = $payment->sale_id ? Sale::withoutGlobalScopes()->find($payment->sale_id) : null;
        if (! $sale) {
            return;
        }
        if ($payment->sale_payment_id) {
            SalePayment::whereKey($payment->sale_payment_id)->delete();
        }
        $paid = (float) $sale->payments()->where('payment_method', '<>', 'desconto')->sum('amount_paid');
        if ($paid < 0.01) {
            // A venda só existia por causa deste pagamento: cancela e devolve o estoque.
            $sale->restoreStock();
            $sale->update(['status' => 'cancelada', 'amount_paid' => 0]);
        } else {
            $sale->update(['status' => 'pendente', 'amount_paid' => $paid]);
        }
    }

    private function salePaymentMethod(PortalPayment $payment, array $mp): string
    {
        return match ($payment->method) {
            'cartao' => ($mp['payment_type_id'] ?? '') === 'debit_card' ? 'debito' : 'credito',
            'boleto' => 'boleto',
            default  => 'pix',
        };
    }

    /** Venda paga com os itens do pedido; baixa o estoque como as outras vendas. */
    private function createPaidSale(ClientQuoteRequest $quote, PortalPayment $payment, array $mp): Sale
    {
        $method = $this->salePaymentMethod($payment, $mp);
        $sale = Sale::create([
            'client_id'       => $quote->client_id,
            'user_id'         => $quote->user_id,
            'total_price'     => $payment->amount,
            'amount_paid'     => $payment->amount,
            'status'          => 'pago',
            'payment_method'  => $method,
            'tipo_pagamento'  => 'a_vista',
            'parcelas'        => 1,
            'source'          => 'portal',
            'portal_quote_id' => $quote->id,
        ]);

        foreach ($quote->items ?? [] as $item) {
            $product = Product::withoutGlobalScopes()->where('user_id', $quote->user_id)->find($item['product_id'] ?? 0);
            if (! $product) {
                continue;
            }
            $promo = $product->livePromotion();
            SaleItem::create([
                'sale_id'        => $sale->id,
                'product_id'     => $product->id,
                'quantity'       => max(1, (int) ($item['quantity'] ?? 1)),
                // O preço que o cliente pagou (o do pedido), mesmo que a promoção já tenha acabado.
                'price_sale'     => (float) ($item['price_ref'] ?? $product->price_sale),
                'price'          => (float) ($product->price_sale ?? $item['price_ref'] ?? 0),
                'original_price' => $promo?->original_price,
                'promotion_id'   => $promo?->id,
            ]);
        }

        $sp = SalePayment::create([
            'sale_id'        => $sale->id,
            'amount_paid'    => $payment->amount,
            'payment_method' => $method,
            'payment_date'   => ($payment->approved_at ?? now())->toDateString(),
        ]);
        $payment->update(['sale_payment_id' => $sp->id]);

        $sale->applyStockDecrement();

        $quote->update([
            'status'       => 'approved',
            'quoted_total' => $payment->amount,
            'responded_at' => now(),
        ]);

        return $sale;
    }

    /** A loja já tinha confirmado o pedido como venda: o pagamento entra nela. */
    private function addPaymentToSale(Sale $sale, PortalPayment $payment, array $mp): Sale
    {
        $sp = SalePayment::create([
            'sale_id'        => $sale->id,
            'amount_paid'    => $payment->amount,
            'payment_method' => $this->salePaymentMethod($payment, $mp),
            'payment_date'   => ($payment->approved_at ?? now())->toDateString(),
        ]);
        $payment->update(['sale_payment_id' => $sp->id]);
        $paid = (float) $sale->fresh()->total_paid;
        $sale->update(['amount_paid' => $paid] + ($paid + 0.009 >= (float) $sale->total_price ? ['status' => 'pago'] : []));
        $sale->applyStockIfPaid();

        return $sale;
    }

    /** Roda $fn com a loja logada (só nesta requisição), como no aviso da Shopee/ML. */
    private function asOwner(int $userId, \Closure $fn)
    {
        $guard = Auth::guard('web');
        $previous = $guard->user();
        if ($previous && (int) $previous->getAuthIdentifier() === $userId) {
            return $fn();
        }
        try {
            $guard->onceUsingId($userId);

            return $fn();
        } finally {
            if ($previous) {
                $guard->setUser($previous);
            } else {
                $guard->forgetUser();
            }
        }
    }

    private function notifyOwner(ClientQuoteRequest $quote, PortalPayment $payment): void
    {
        try {
            // Sem a loja logada (aviso do Mercado Pago) o filtro de equipe esconderia o cliente.
            $quote->setRelation('client', Client::withoutGlobalScopes()->find($quote->client_id));
            User::find($quote->user_id)?->notify(new PortalPaymentApproved($quote, $payment));
        } catch (\Throwable $e) {
            Log::warning('Aviso de pagamento aprovado falhou', ['quote' => $quote->id, 'erro' => $e->getMessage()]);
        }
    }
}
