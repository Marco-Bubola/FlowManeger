<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Notifications extends Component
{
    public $notifications = [];

    protected $listeners = [
        'notify' => 'handleNotification',
        'clearNotifications' => 'clearAll'
    ];

    public function mount()
    {
        // Verifica se há mensagens flash da sessão
        if (session()->has('success')) {
            $this->addNotification('success', session('success'));
            session()->forget('success');
        }

        if (session()->has('error')) {
            $this->addNotification('error', session('error'));
            session()->forget('error');
        }

        if (session()->has('warning')) {
            $this->addNotification('warning', session('warning'));
            session()->forget('warning');
        }

        if (session()->has('info')) {
            $this->addNotification('info', session('info'));
            session()->forget('info');
        }
    }

    public function handleNotification($data)
    {
        if (is_array($data) && isset($data['type'], $data['message'])) {
            $this->addNotification(
                $data['type'],
                $data['message'],
                $data['duration'] ?? 5000
            );
        }
    }

    public function addNotification($data)
    {
        // Se recebeu um array com os dados da notificação
        if (is_array($data) && isset($data['type'], $data['message'])) {
            $notification = [
                'id' => uniqid(),
                'type' => $data['type'],
                'message' => $data['message'],
                'duration' => $data['duration'] ?? 5000,
                'timestamp' => now()->toISOString()
            ];
        } else {
            // Fallback para compatibilidade
            $notification = [
                'id' => uniqid(),
                'type' => $data ?? 'info',
                'message' => 'Notificação sem mensagem',
                'duration' => 5000,
                'timestamp' => now()->toISOString()
            ];
        }

        $this->notifications[] = $notification;
    }

    public function removeNotification($id)
    {
        $this->notifications = array_filter($this->notifications, function($notification) use ($id) {
            return $notification['id'] !== $id;
        });
    }

    public function clearAll()
    {
        $this->notifications = [];
    }

    public function render()
    {
        return view('livewire.components.notifications');
    }
}
