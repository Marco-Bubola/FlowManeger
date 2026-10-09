<?php

namespace App\Notifications;

use App\Models\ClientQuoteRequest;
use App\Notifications\Channels\InAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PortalQuoteReceived extends Notification
{
    use Queueable;

    public function __construct(public ClientQuoteRequest $quote) {}

    public function via(object $notifiable): array
    {
        // database: histórico do Laravel; InAppChannel: aparece no sino/central.
        return ['database', InAppChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $client = $this->quote->client;
        return [
            'type'     => 'portal_quote',
            'quote_id' => $this->quote->id,
            'client'   => $client?->name ?? 'Cliente',
            'items'    => count($this->quote->items ?? []),
            'payment'  => $this->quote->payment_preference,
            'url'      => route('clients.portal.quotes', $this->quote->client_id),
        ];
    }

    public function toInApp(object $notifiable): array
    {
        $client = $this->quote->client?->name ?? 'Cliente';
        $items = count($this->quote->items ?? []);

        return [
            'module' => 'portal',
            'type' => 'portal_quote',
            'title' => 'Novo orçamento pelo portal',
            'message' => "{$client} pediu um orçamento com {$items} " . ($items === 1 ? 'item' : 'itens') . '.',
            'options' => [
                'priority' => 'high',
                'entity_type' => 'ClientQuoteRequest',
                'entity_id' => $this->quote->id,
                'action_url' => route('clients.portal.quotes', $this->quote->client_id, false),
                'data' => ['quote_id' => $this->quote->id, 'client_id' => $this->quote->client_id],
            ],
        ];
    }
}
