<?php

namespace App\Notifications;

use App\Models\ClientQuoteRequest;
use App\Models\PortalPayment;
use App\Notifications\Channels\InAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Pedido do portal pago pelo Pix: a venda já foi criada como paga. */
class PortalPaymentApproved extends Notification
{
    use Queueable;

    public function __construct(public ClientQuoteRequest $quote, public PortalPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', InAppChannel::class];
    }

    private function money(): string
    {
        return 'R$ ' . number_format((float) $this->payment->amount, 2, ',', '.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'portal_payment',
            'quote_id' => $this->quote->id,
            'sale_id'  => $this->payment->sale_id,
            'client'   => $this->quote->client?->name ?? 'Cliente',
            'amount'   => (float) $this->payment->amount,
            'url'      => route('clients.portal.quotes', $this->quote->client_id),
        ];
    }

    public function toInApp(object $notifiable): array
    {
        $client = $this->quote->client?->name ?? 'Cliente';
        $delivery = $this->quote->delivery_method === 'entrega' ? ' Entrega no endereço do cliente.' : ($this->quote->delivery_method === 'retirada' ? ' Cliente vai retirar.' : '');

        return [
            'module' => 'portal',
            'type' => 'portal_payment',
            'title' => 'Pedido pago pelo Pix',
            'message' => "{$client} pagou {$this->money()} no pedido #{$this->quote->id}. A venda já foi criada como paga.{$delivery}",
            'options' => [
                'priority' => 'high',
                'entity_type' => 'ClientQuoteRequest',
                'entity_id' => $this->quote->id,
                'action_url' => route('clients.portal.quotes', $this->quote->client_id, false),
                'data' => ['quote_id' => $this->quote->id, 'client_id' => $this->quote->client_id, 'sale_id' => $this->payment->sale_id],
            ],
        ];
    }
}
