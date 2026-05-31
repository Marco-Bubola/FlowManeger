<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Notifications extends Component
{
    /**
     * Notificações iniciais vindas de flash messages da sessão.
     * São injetadas no cliente uma única vez (sem round-trips).
     */
    public array $flash = [];

    public function mount()
    {
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            if (session()->has($type)) {
                $this->flash[] = [
                    'type'    => $type,
                    'message' => session($type),
                ];
                session()->forget($type);
            }
        }
    }

    public function render()
    {
        return view('livewire.components.notifications');
    }
}
