<?php

namespace App\Notifications\Channels;

use App\Models\ConsortiumNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Canal que leva uma Notification do Laravel para o sino/central in-app
 * (tabela consortium_notifications). A notificação implementa
 * toInApp($notifiable): array{module,type,title,message,options?}.
 */
class InAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toInApp') || empty($notifiable->id)) {
            return;
        }

        try {
            $p = $notification->toInApp($notifiable);
            ConsortiumNotification::createGeneric(
                $p['module'],
                $p['type'],
                (int) $notifiable->id,
                $p['title'],
                $p['message'],
                $p['options'] ?? []
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar notificação in-app', ['error' => $e->getMessage()]);
        }
    }
}
