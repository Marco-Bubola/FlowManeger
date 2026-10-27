<?php

namespace App\Services\Portal;

use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\Sale;
use App\Notifications\PortalOrderStatusChanged;
use App\Services\Collections\CollectionService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pedidos feitos pelo portal do cliente: status que o cliente vê, linha do
 * tempo (Enviado → Confirmado/Recusado → Pago → Entregue) e o aviso pronto
 * para o dono mandar no WhatsApp quando confirma ou recusa.
 */
class PortalOrderService
{
    public function __construct(private CollectionService $collections) {}

    // ─── Aviso ao cliente ─────────────────────────────────────────────────────

    /** Texto do aviso de pedido confirmado ou recusado, com o link do portal. */
    public function whatsappMessage(ClientQuoteRequest $quote, ?Client $client, ?string $storeName, ?Sale $sale = null): string
    {
        $first = $client?->name ? (string) Str::of($client->name)->trim()->explode(' ')->first() : '';
        $hello = $first !== '' ? "Oi, {$first}!" : 'Oi!';
        $from = filled($storeName) ? " Aqui é da {$storeName}." : '';
        $link = route('portal.quotes.show', $quote);
        $notes = trim((string) $quote->admin_notes);

        if ($quote->status === 'rejected') {
            $lines = [
                $hello . $from,
                '',
                "Infelizmente não conseguimos atender o seu pedido #{$quote->id} desta vez.",
            ];
            if ($notes !== '') {
                $lines[] = "Motivo: {$notes}";
            }
            $lines[] = '';
            $lines[] = 'Se quiser, me responda aqui que a gente procura uma alternativa.';
            $lines[] = "Detalhes do pedido: {$link}";

            return implode("\n", $lines);
        }

        $total = $sale?->total_price ?? $quote->quoted_total ?? $this->estimatedTotal($quote);
        $lines = [
            $hello . $from,
            '',
            "Seu pedido #{$quote->id} foi *confirmado*!",
        ];
        if ((float) $total > 0) {
            $lines[] = 'Total: *' . $this->money((float) $total) . '*';
        }
        if ($sale?->payment_method) {
            $lines[] = 'Pagamento: ' . $this->paymentLabel($sale->payment_method)
                . ($sale->tipo_pagamento === 'parcelado' && $sale->parcelas > 1 ? " em {$sale->parcelas}x" : '');
        }
        if ($notes !== '') {
            $lines[] = $notes;
        }
        $lines[] = '';
        $lines[] = "Acompanhe seu pedido: {$link}";
        $lines[] = 'Obrigado pela preferência!';

        return implode("\n", $lines);
    }

    /** Link wa.me com o aviso; sem telefone válido abre o WhatsApp para escolher o contato. */
    public function whatsappUrl(ClientQuoteRequest $quote, ?Client $client, ?string $storeName, ?Sale $sale = null): string
    {
        $text = $this->whatsappMessage($quote, $client, $storeName, $sale);
        $phone = $this->collections->whatsappPhone($client?->phone);

        return $phone
            ? $this->collections->whatsappUrl($phone, $text)
            : 'https://wa.me/?text=' . rawurlencode($text);
    }

    public function hasWhatsappPhone(?Client $client): bool
    {
        return $this->collections->whatsappPhone($client?->phone) !== null;
    }

    /**
     * E-mail ao cliente, só quando o envio de e-mail já está configurado
     * (o mailer padrão "log"/"array" não entrega nada) e o cliente tem e-mail.
     */
    public function mailClient(ClientQuoteRequest $quote, ?Client $client): bool
    {
        if (! $client || blank($client->email) || in_array(config('mail.default'), ['log', 'array', null], true)) {
            return false;
        }

        try {
            $client->notify(new PortalOrderStatusChanged($quote));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Portal: não foi possível enviar o e-mail do pedido', ['quote' => $quote->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /** Andamento da entrega que a loja marca em Pedidos do portal: [rótulo, texto para o cliente, ícone]. */
    public const DELIVERY_STAGES = [
        'separando' => ['Separando o pedido', 'A loja está separando e embalando os seus produtos.', 'bi-box-seam'],
        'pronto'    => ['Pronto para retirar', 'Seu pedido está pronto. Pode vir buscar!', 'bi-shop'],
        'enviado'   => ['Enviado', 'Seu pedido foi enviado.', 'bi-truck'],
        'saiu'      => ['Saiu para entrega', 'Seu pedido saiu para entrega e chega hoje.', 'bi-geo-alt'],
        'entregue'  => ['Entregue', 'Pedido entregue. Obrigado pela compra!', 'bi-house-check'],
    ];

    public const CARRIERS = ['correios' => 'Correios', 'jadlog' => 'Jadlog', 'loggi' => 'Loggi', 'motoboy' => 'Motoboy / entrega própria', 'outro' => 'Outra transportadora'];

    /** Passos da entrega para o tipo do pedido (retirada ou entrega). */
    public function deliveryFlow(ClientQuoteRequest $quote): array
    {
        return $quote->delivery_method === 'retirada' ? ['separando', 'pronto', 'entregue'] : ['separando', 'enviado', 'saiu', 'entregue'];
    }

    /** Link para rastrear: o código (ou link) que a loja colocou. */
    public function trackingUrl(ClientQuoteRequest $quote): ?string
    {
        $code = trim((string) $quote->tracking_code);
        if ($code === '') {
            return null;
        }
        if (preg_match('~^https?://~i', $code)) {
            return $code;
        }
        if ($quote->tracking_carrier === 'correios' || preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/i', $code)) {
            return 'https://rastreamento.correios.com.br/app/index.php?objetos=' . rawurlencode(strtoupper($code));
        }
        if ($quote->tracking_carrier === 'jadlog') {
            return 'https://www.jadlog.com.br/jadlog/tracking?cte=' . rawurlencode($code);
        }

        return null;
    }

    /** Quando a loja marcou cada passo da entrega: [stage => Carbon]. */
    public function deliveryDates(ClientQuoteRequest $quote): array
    {
        $out = [];
        foreach ((array) $quote->delivery_events as $e) {
            if (! empty($e['stage']) && ! empty($e['at'])) {
                $out[$e['stage']] = \Illuminate\Support\Carbon::parse($e['at']);
            }
        }

        return $out;
    }

    /** Mensagem do WhatsApp para avisar o cliente do andamento da entrega. */
    public function deliveryMessage(ClientQuoteRequest $quote, ?Client $client, ?string $storeName): string
    {
        [$label, $text] = self::DELIVERY_STAGES[$quote->delivery_stage] ?? ['Atualizado', 'Seu pedido teve novidade.'];
        $first = $client?->name ? explode(' ', trim($client->name))[0] : '';
        $msg = 'Oi' . ($first ? ', ' . $first : '') . '! ' . ($storeName ? "Aqui é da {$storeName}. " : '') . "Seu pedido #{$quote->id}: *{$label}*. {$text}";
        if ($quote->tracking_code) {
            $msg .= "\nCódigo de rastreio: {$quote->tracking_code}" . (($u = $this->trackingUrl($quote)) && ! str_starts_with($quote->tracking_code, 'http') ? "\n{$u}" : '');
        }
        if ($quote->delivery_eta && $quote->delivery_stage !== 'entregue') {
            $msg .= "\nPrevisão: " . $quote->delivery_eta->format('d/m');
        }

        return $msg . "\nAcompanhe em: " . route('portal.quotes.show', $quote);
    }

    // ─── O que o cliente vê ───────────────────────────────────────────────────

    /**
     * Status resumido do pedido para o cliente: [key, label, tone, hint].
     * tone: amber | violet | green | red | gray.
     */
    public function clientStatus(ClientQuoteRequest $quote, ?Sale $sale = null): array
    {
        if ($sale && in_array($sale->status, ['cancelada', 'cancelado'], true)) {
            return ['cancelled', 'Cancelado', 'gray', 'A venda deste pedido foi cancelada pela loja.'];
        }
        if (($sale && $this->isDelivered($sale)) || $quote->delivery_stage === 'entregue') {
            return ['delivered', 'Entregue', 'green', 'Pedido concluído. Obrigado pela compra!'];
        }
        if ($quote->delivery_stage && isset(self::DELIVERY_STAGES[$quote->delivery_stage]) && $quote->status === 'approved') {
            [$dl, $dt] = self::DELIVERY_STAGES[$quote->delivery_stage];

            return [$quote->delivery_stage, $dl, 'violet', $dt . ($quote->delivery_eta ? ' Previsão: ' . $quote->delivery_eta->format('d/m') . '.' : '')];
        }
        if ($sale && $this->isPaid($sale)) {
            return ['paid', 'Pago', 'green', 'Pagamento recebido pela loja.'];
        }

        if (! $sale && $quote->status === 'pending' && in_array($quote->payment_status, ['pending', 'expired', 'cancelled', 'rejected'], true)) {
            return ['awaiting_payment', 'Aguardando pagamento', 'amber', 'Conclua o pagamento para a loja receber o seu pedido.'];
        }

        return match ($quote->status) {
            'approved' => ['confirmed', 'Confirmado', 'green', $sale ? 'A loja confirmou o seu pedido.' : 'Você aceitou a proposta. A loja vai finalizar o pedido.'],
            'rejected' => ['rejected', 'Recusado', 'red', 'A loja não pôde atender este pedido.'],
            'quoted'   => ['quoted', 'Proposta recebida', 'violet', 'A loja mandou uma proposta. Aceite ou recuse.'],
            'reviewing' => ['reviewing', 'Em análise', 'amber', 'A loja está conferindo o seu pedido.'],
            default    => ['sent', 'Aguardando confirmação', 'amber', 'Pedido enviado. A loja vai confirmar em breve.'],
        };
    }

    /**
     * Passos da linha do tempo: cada um com key, label, state
     * (done | current | todo | failed), date (?Carbon) e text.
     */
    public function timeline(ClientQuoteRequest $quote, ?Sale $sale = null): array
    {
        $responded = $quote->responded_at ?? ($quote->status !== 'pending' ? $quote->updated_at : null);
        $steps = [[
            'key' => 'sent', 'label' => 'Pedido enviado', 'state' => 'done',
            'date' => $quote->created_at, 'text' => 'Recebemos o seu pedido.',
        ]];

        if ($quote->status === 'rejected') {
            $steps[] = ['key' => 'rejected', 'label' => 'Recusado', 'state' => 'failed', 'date' => $responded, 'text' => 'A loja não pôde atender este pedido.'];

            return $steps;
        }

        if ($quote->status === 'quoted') {
            $steps[] = ['key' => 'quoted', 'label' => 'Proposta recebida', 'state' => 'current', 'date' => $responded, 'text' => 'Confira a proposta e responda.'];
        }

        $confirmed = $quote->status === 'approved';
        $steps[] = [
            'key' => 'confirmed', 'label' => 'Confirmado',
            'state' => $confirmed ? 'done' : ($quote->status === 'quoted' ? 'todo' : 'current'),
            'date' => $confirmed ? ($sale?->created_at ?? $responded) : null,
            'text' => $confirmed ? 'A loja confirmou o seu pedido.' : 'Aguardando a loja confirmar.',
        ];

        $cancelled = $sale && in_array($sale->status, ['cancelada', 'cancelado'], true);
        if ($cancelled) {
            $steps[] = ['key' => 'cancelled', 'label' => 'Cancelado', 'state' => 'failed', 'date' => $sale->updated_at, 'text' => 'A venda foi cancelada pela loja.'];

            return $steps;
        }

        $paid = $sale && $this->isPaid($sale);
        $steps[] = [
            'key' => 'paid', 'label' => 'Pago',
            'state' => $paid ? 'done' : ($confirmed && $sale ? 'current' : 'todo'),
            'date' => $paid ? ($quote->paid_at ?? $this->paidAt($sale)) : null,
            'text' => $paid ? 'Pagamento recebido.' : ($sale && (float) $sale->total_paid > 0
                ? 'Pago ' . $this->money((float) $sale->total_paid) . ' de ' . $this->money((float) $sale->total_price) . '.'
                : 'Aguardando pagamento.'),
        ];

        $delivered = ($sale && $this->isDelivered($sale)) || $quote->delivery_stage === 'entregue';
        $stage = $quote->delivery_stage && isset(self::DELIVERY_STAGES[$quote->delivery_stage]) ? $quote->delivery_stage : null;
        if (! $stage) {
            $steps[] = [
                'key' => 'delivered', 'label' => 'Entregue',
                'state' => $delivered ? 'done' : ($paid ? 'current' : 'todo'),
                'date' => $delivered ? $sale?->updated_at : null,
                'text' => $delivered ? 'Pedido entregue.' : 'A loja combina a entrega com você.',
            ];

            return $steps;
        }

        // A loja está marcando a entrega: um passo para cada etapa.
        $flow = $this->deliveryFlow($quote);
        if (! in_array($stage, $flow, true)) {
            array_splice($flow, count($flow) - 1, 0, [$stage]);
        }
        $at = array_search($delivered ? 'entregue' : $stage, $flow, true);
        $dates = $this->deliveryDates($quote);
        foreach ($flow as $i => $key) {
            [$label, $text] = self::DELIVERY_STAGES[$key];
            $state = $i < $at || ($i === $at && $key === 'entregue') ? 'done' : ($i === $at ? 'current' : 'todo');
            $steps[] = [
                'key' => $key === 'entregue' ? 'delivered' : $key, 'label' => $key === 'entregue' && $quote->delivery_method === 'retirada' ? 'Retirado' : $label,
                'state' => $state, 'date' => $state !== 'todo' ? ($dates[$key] ?? null) : null,
                'text' => $state === 'todo' ? '' : $text,
            ];
        }

        return $steps;
    }

    /**
     * Itens para mostrar ao cliente: os da venda (preço final) quando já
     * confirmada, senão os do pedido (preço de referência). Nunca o estoque.
     * Cada item: name, quantity, unit, total, image, notes, product_id.
     */
    public function items(ClientQuoteRequest $quote, ?Sale $sale, $products): array
    {
        if ($sale && $sale->relationLoaded('saleItems') && $sale->saleItems->isNotEmpty()) {
            return $sale->saleItems->map(function ($item) use ($products) {
                $product = $products->get($item->product_id) ?? $item->product;
                $unit = (float) ($item->price_sale ?? $item->price ?? 0);

                return [
                    'product_id' => $item->product_id,
                    'name' => $product?->name ?? 'Produto',
                    'quantity' => (int) $item->quantity,
                    'unit' => $unit,
                    'original' => $item->original_price ? (float) $item->original_price : null,
                    'total' => $unit * (int) $item->quantity,
                    'image' => $this->imageOf($product),
                    'notes' => null,
                ];
            })->all();
        }

        return collect($quote->items ?? [])->map(function ($item) use ($products) {
            $product = isset($item['product_id']) ? $products->get($item['product_id']) : null;
            $unit = (float) ($item['price_ref'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);

            return [
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? $product?->name ?? 'Produto',
                'quantity' => $qty,
                'unit' => $unit,
                'original' => null,
                'total' => $unit * $qty,
                'image' => $this->imageOf($product),
                'notes' => $item['notes'] ?? null,
            ];
        })->all();
    }

    public function total(ClientQuoteRequest $quote, ?Sale $sale = null): float
    {
        return (float) ($sale?->total_price ?? $quote->quoted_total ?? $this->estimatedTotal($quote));
    }

    public function estimatedTotal(ClientQuoteRequest $quote): float
    {
        $sum = (float) collect($quote->items ?? [])->sum(fn ($i) => (float) ($i['price_ref'] ?? 0) * (int) ($i['quantity'] ?? 1));

        // Cupom do catálogo sai do valor dos produtos.
        // Kit montado: a base e os enfeites somam ao valor.
        return max(0, $sum - (float) ($quote->discount ?? 0)) + KitService::total($quote);
    }

    public function isPaid(Sale $sale): bool
    {
        if (in_array($sale->status, ['pago', 'paga', 'concluida', 'concluido', 'entregue'], true)) {
            return true;
        }

        return (float) $sale->total_price > 0 && (float) $sale->total_paid >= (float) $sale->total_price - 0.009;
    }

    public function isDelivered(Sale $sale): bool
    {
        return in_array($sale->status, ['concluida', 'concluido', 'entregue'], true);
    }

    public function paymentLabel(?string $method): string
    {
        return [
            'pix' => 'PIX', 'dinheiro' => 'Dinheiro', 'credito' => 'Cartão de crédito', 'debito' => 'Cartão de débito',
            'cartao_credito' => 'Cartão de crédito', 'cartao_debito' => 'Cartão de débito', 'boleto' => 'Boleto',
            'transferencia' => 'Transferência', 'outro' => 'Outro',
        ][$method ?? ''] ?? ucfirst((string) $method);
    }

    /**
     * Situação de uma compra para o cliente: [rótulo, tom, dica]. Tons iguais aos de "Meus pedidos".
     */
    public function saleStatus(Sale $sale): array
    {
        if (in_array($sale->status, ['cancelada', 'cancelado', 'cancelled'], true)) {
            return ['Cancelada', 'gray', 'Esta compra foi cancelada pela loja.'];
        }
        $remaining = max(0, (float) $sale->total_price - (float) $sale->total_paid);
        if ($this->isDelivered($sale)) {
            return ['Entregue', 'green', $remaining > 0.009 ? 'Falta pagar ' . $this->money($remaining) . '.' : 'Compra concluída e paga.'];
        }
        if ($this->isPaid($sale)) {
            return ['Paga', 'green', 'Pagamento recebido. Obrigado!'];
        }
        if ((float) $sale->total_paid > 0) {
            return ['Paga em parte', 'amber', 'Falta pagar ' . $this->money($remaining) . '.'];
        }

        return ['Aguardando pagamento', 'amber', $remaining > 0 ? 'Total a pagar: ' . $this->money($remaining) . '.' : 'A loja vai combinar o pagamento com você.'];
    }

    /** Primeira foto de um produto do pedido (ou null). */
    public function photo($product): ?string
    {
        return $this->imageOf($product);
    }

    public function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function imageOf($product): ?string
    {
        if (! $product || blank($product->image) || $product->image === 'product-placeholder.png') {
            return null;
        }

        return $product->image_url;
    }

    private function paidAt(Sale $sale): ?CarbonInterface
    {
        $last = $sale->relationLoaded('payments')
            ? $sale->payments->max('payment_date')
            : $sale->payments()->max('payment_date');

        return $last ? \Illuminate\Support\Carbon::parse($last) : $sale->updated_at;
    }
}
