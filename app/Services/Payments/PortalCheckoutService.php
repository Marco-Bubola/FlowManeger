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
 * Pagamento do pedido do portal pelo Mercado Pago (Pix): gera a cobrança, confere o
 * pagamento na API e, quando aprovado, cria a venda paga (uma vez só) e baixa o estoque.
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

    public function api(PaymentSetting $settings): MercadoPagoService
    {
        return new MercadoPagoService($settings);
    }

    /** Valor a pagar recalculado no servidor: preço atual (com promoção) + entrega. */
    public function amount(ClientQuoteRequest $quote): float
    {
        $sum = collect($quote->items ?? [])->sum(fn ($i) => (float) ($i['price_ref'] ?? 0) * max(1, (int) ($i['quantity'] ?? 1)));

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

    /** Pix em aberto (não vencido) do pedido, se houver. */
    public function openPix(ClientQuoteRequest $quote): ?PortalPayment
    {
        return $quote->payments()->where('method', 'pix')->whereIn('status', ['pending', 'in_process'])
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')->first();
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
        if ($problems = $this->stockProblems($quote)) {
            throw new RuntimeException('Sem estoque suficiente agora: ' . implode(', ', array_keys($problems)) . '.');
        }

        $amount = $this->amount($quote);
        if ($amount < 0.01) {
            throw new RuntimeException('Pedido sem valor para pagar.');
        }

        $minutes = max(10, min(1440, (int) ($settings->pix_minutes ?: 30)));
        $expires = now()->addMinutes($minutes);
        $key = (string) Str::uuid();
        $name = trim((string) $client->name);
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
        $store = User::whereKey($quote->user_id)->value('name');

        $payment = PortalPayment::create([
            'quote_id' => $quote->id, 'user_id' => $quote->user_id, 'method' => 'pix', 'status' => 'creating',
            'amount' => $amount, 'expires_at' => $expires, 'idempotency_key' => $key,
        ]);

        try {
            $mp = $this->api($settings)->createPix(
                $amount,
                'Pedido #' . $quote->id . ($store ? ' · ' . $store : ''),
                [
                    'email'      => $client->email ?: ('cliente' . $client->id . '@flowmaneger.com'),
                    'first_name' => $first ?: 'Cliente',
                    'last_name'  => $last,
                    'cpf'        => preg_replace('/\D+/', '', (string) $client->cpf_cnpj),
                ],
                'quote:' . $quote->id . ':pay:' . $payment->id,
                route('payments.mercadopago.webhook', ['user' => $quote->user_id]),
                $expires,
                $key
            );
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

        return DB::transaction(function () use ($payment, $mp, $status) {
            /** @var PortalPayment $payment */
            $payment = PortalPayment::whereKey($payment->id)->lockForUpdate()->first();
            $quote = ClientQuoteRequest::whereKey($payment->quote_id)->lockForUpdate()->first();

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
                    // Aviso do Mercado Pago e tela do cliente não têm a loja logada: sem isso o
                    // filtro de equipe esconde os produtos e o estoque não baixa.
                    $sale = $this->asOwner((int) $quote->user_id, fn () => $quote->sale
                        ? $this->addPaymentToSale($quote->sale, $payment)
                        : $this->createPaidSale($quote, $payment));
                    $payment->update(['sale_id' => $sale->id]);
                    $this->notifyOwner($quote, $payment);
                }
                $quote->update(['payment_status' => 'approved', 'paid_at' => $payment->approved_at ?? now()]);
            } elseif ($quote->payment_status !== 'approved') {
                // Pix vencido/recusado: o pedido continua aberto para tentar de novo.
                $quote->update(['payment_status' => $quoteStatus]);
            } elseif (in_array($quoteStatus, ['refunded'], true)) {
                $quote->update(['payment_status' => 'refunded']);
            }

            return $payment;
        });
    }

    /** Venda paga com os itens do pedido; baixa o estoque como as outras vendas. */
    private function createPaidSale(ClientQuoteRequest $quote, PortalPayment $payment): Sale
    {
        $sale = Sale::create([
            'client_id'       => $quote->client_id,
            'user_id'         => $quote->user_id,
            'total_price'     => $payment->amount,
            'amount_paid'     => $payment->amount,
            'status'          => 'pago',
            'payment_method'  => 'pix',
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
            SaleItem::create([
                'sale_id'        => $sale->id,
                'product_id'     => $product->id,
                'quantity'       => max(1, (int) ($item['quantity'] ?? 1)),
                // O preço que o cliente pagou (o do pedido), mesmo que a promoção já tenha acabado.
                'price_sale'     => (float) ($item['price_ref'] ?? $product->price_sale),
                'price'          => (float) ($product->price_sale ?? $item['price_ref'] ?? 0),
                'original_price' => $product->livePromotion() ? $product->livePromotion()->original_price : null,
                'promotion_id'   => $product->livePromotion()?->id,
            ]);
        }

        SalePayment::create([
            'sale_id'        => $sale->id,
            'amount_paid'    => $payment->amount,
            'payment_method' => 'pix',
            'payment_date'   => ($payment->approved_at ?? now())->toDateString(),
        ]);

        $sale->applyStockDecrement();

        $quote->update([
            'status'       => 'approved',
            'quoted_total' => $payment->amount,
            'responded_at' => now(),
        ]);

        return $sale;
    }

    /** A loja já tinha confirmado o pedido como venda: o Pix entra como pagamento dela. */
    private function addPaymentToSale(Sale $sale, PortalPayment $payment): Sale
    {
        SalePayment::create([
            'sale_id'        => $sale->id,
            'amount_paid'    => $payment->amount,
            'payment_method' => 'pix',
            'payment_date'   => ($payment->approved_at ?? now())->toDateString(),
        ]);
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
