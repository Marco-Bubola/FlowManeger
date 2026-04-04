<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('portal.password.reset', [
            'token' => $this->token,
            'login' => $notifiable->portal_login,
        ]);

        return (new MailMessage)
            ->subject('Redefinir acesso ao portal do cliente')
            ->greeting('Ola, ' . $notifiable->name . '!')
            ->line('Recebemos um pedido para redefinir a senha do seu portal do cliente.')
            ->line('Seu login do portal: ' . $notifiable->portal_login)
            ->action('Redefinir Senha', $url)
            ->line('Esse link expira em 60 minutos.')
            ->line('Se voce nao solicitou isso, ignore esta mensagem.');
    }
}