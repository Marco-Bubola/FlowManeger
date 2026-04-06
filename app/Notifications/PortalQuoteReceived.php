<?php

namespace App\Notifications;

use App\Models\ClientQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PortalQuoteReceived extends Notification
{
    use Queueable;

    public function __construct(public ClientQuoteRequest $quote) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
