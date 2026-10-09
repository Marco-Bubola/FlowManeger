<?php

namespace App\Notifications;

use App\Models\ClientQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail ao cliente do portal quando a loja confirma ou recusa o pedido.
 * Só é enviado quando o e-mail já está configurado (ver PortalOrderService).
 */
class PortalOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public ClientQuoteRequest $quote) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rejected = $this->quote->status === 'rejected';
        $first = trim(explode(' ', trim((string) ($notifiable->name ?? '')))[0] ?? '');

        $mail = (new MailMessage)
            ->subject($rejected ? "Pedido #{$this->quote->id} não pôde ser atendido" : "Pedido #{$this->quote->id} confirmado")
            ->greeting($first !== '' ? "Olá, {$first}!" : 'Olá!')
            ->line($rejected
                ? "Infelizmente a loja não conseguiu atender o seu pedido #{$this->quote->id} desta vez."
                : "Boa notícia: a loja confirmou o seu pedido #{$this->quote->id}.");

        if (filled($this->quote->admin_notes)) {
            $mail->line(($rejected ? 'Motivo: ' : 'Recado da loja: ') . $this->quote->admin_notes);
        }

        return $mail->action('Ver meu pedido', route('portal.quotes.show', $this->quote));
    }
}
