<?php

namespace App\Traits;

use App\Support\NotificationCenter;

trait HasNotifications
{
    /**
     * Envia uma notificação de sucesso
     */
    public function notifySuccess($message, $duration = 5000)
    {
        NotificationCenter::success($message, ['duration' => $duration]);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
            'duration' => $duration,
            'persistOnRedirect' => true,
        ]);
    }

    /**
     * Envia uma notificação de erro
     */
    public function notifyError($message, $duration = 7000)
    {
        NotificationCenter::error($message, ['duration' => $duration]);
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message,
            'duration' => $duration,
            'persistOnRedirect' => true,
        ]);
    }

    /**
     * Envia uma notificação de aviso
     */
    public function notifyWarning($message, $duration = 6000)
    {
        NotificationCenter::warning($message, ['duration' => $duration]);
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => $message,
            'duration' => $duration,
            'persistOnRedirect' => true,
        ]);
    }

    /**
     * Envia uma notificação informativa
     */
    public function notifyInfo($message, $duration = 5000)
    {
        NotificationCenter::info($message, ['duration' => $duration]);
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => $message,
            'duration' => $duration,
            'persistOnRedirect' => true,
        ]);
    }

    /**
     * Redireciona após um delay com notificação
     */
    public function redirectWithNotification($route, $message, $type = 'success', $delay = 1500)
    {
        NotificationCenter::flash($type, $message, ['duration' => $delay]);

        // Envia notificação
        $this->dispatch('notify', [
            'type' => $type,
            'message' => $message,
            'duration' => $delay,
            'persistOnRedirect' => true,
        ]);

        // Agenda o redirecionamento
        $this->dispatch('redirect-after-delay', [
            'url' => $route,
            'delay' => $delay
        ]);
    }

}
