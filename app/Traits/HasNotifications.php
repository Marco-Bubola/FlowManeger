<?php

namespace App\Traits;

trait HasNotifications
{
    /**
     * Envia uma notificação de sucesso
     */
    public function notifySuccess($message, $duration = 5000)
    {
        // Remove session()->flash() para evitar duplicação de notificações
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
        // Remove session()->flash() para evitar duplicação de notificações
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
        // Remove session()->flash() para evitar duplicação de notificações
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
        // Remove session()->flash() para evitar duplicação de notificações
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
        if ($type === 'success') {
            $this->notifySuccess($message);
        } elseif ($type === 'error') {
            $this->notifyError($message);
        } elseif ($type === 'warning') {
            $this->notifyWarning($message);
        } else {
            $this->notifyInfo($message);
        }

        return redirect()->route($route);
    }

}
