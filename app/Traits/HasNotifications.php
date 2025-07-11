<?php

namespace App\Traits;

trait HasNotifications
{
    /**
     * Envia uma notificação de sucesso
     */
    public function notifySuccess($message, $duration = 5000)
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
            'duration' => $duration
        ]);
    }

    /**
     * Envia uma notificação de erro
     */
    public function notifyError($message, $duration = 7000)
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message,
            'duration' => $duration
        ]);
    }

    /**
     * Envia uma notificação de aviso
     */
    public function notifyWarning($message, $duration = 6000)
    {
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => $message,
            'duration' => $duration
        ]);
    }

    /**
     * Envia uma notificação informativa
     */
    public function notifyInfo($message, $duration = 5000)
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => $message,
            'duration' => $duration
        ]);
    }

    /**
     * Redireciona após um delay com notificação
     */
    public function redirectWithNotification($route, $message, $type = 'success', $delay = 1500)
    {
        // Envia notificação
        $this->dispatch('notify', [
            'type' => $type,
            'message' => $message,
            'duration' => $delay
        ]);

        // Agenda o redirecionamento
        $this->dispatch('redirect-after-delay', [
            'url' => $route,
            'delay' => $delay
        ]);
    }
}
