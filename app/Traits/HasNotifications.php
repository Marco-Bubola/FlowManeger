<?php

namespace App\Traits;

trait HasNotifications
{
    /**
     * Envia uma notificação de sucesso
     */
    public function notifySuccess($message, $duration = 5000)
    {
        // Notifications temporariamente desabilitadas
    }

    /**
     * Envia uma notificação de erro
     */
    public function notifyError($message, $duration = 7000)
    {
        // Notifications temporariamente desabilitadas
    }

    /**
     * Envia uma notificação de aviso
     */
    public function notifyWarning($message, $duration = 6000)
    {
        // Notifications temporariamente desabilitadas
    }

    /**
     * Envia uma notificação informativa
     */
    public function notifyInfo($message, $duration = 5000)
    {
        // Notifications temporariamente desabilitadas
    }

    /**
     * Redireciona após um delay com notificação
     */
    public function redirectWithNotification($route, $message, $type = 'success', $delay = 1500)
    {
        // Notifications temporariamente desabilitadas
    }

}
