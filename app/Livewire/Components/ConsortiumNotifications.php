<?php

namespace App\Livewire\Components;

use App\Models\ConsortiumNotification;
use App\Services\ConsortiumNotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class ConsortiumNotifications extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;
    public $showAll = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    #[On('notification-created')]
    public function loadNotifications()
    {
        $query = ConsortiumNotification::forUser(Auth::id())
            ->with(['consortium', 'participant.client'])
            ->orderBy('created_at', 'desc');

        if (!$this->showAll) {
            $query->limit(10);
        }

        $this->notifications = $query->get();
        $this->unreadCount = ConsortiumNotification::unreadCountForUser(Auth::id());
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;

        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function markAsRead($notificationId)
    {
        $notification = ConsortiumNotification::find($notificationId);

        if ($notification && $notification->user_id === Auth::id()) {
            $notification->markAsRead();
            $this->loadNotifications();

            // Redirecionar se tiver URL de ação
            if ($notification->action_url) {
                return redirect($notification->action_url);
            }
        }
    }

    public function markAsUnread($notificationId)
    {
        $notification = ConsortiumNotification::find($notificationId);

        if ($notification && $notification->user_id === Auth::id()) {
            $notification->markAsUnread();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        ConsortiumNotification::markAllAsReadForUser(Auth::id());
        $this->loadNotifications();

        session()->flash('success', 'Todas as notificações foram marcadas como lidas.');
    }

    public function delete($notificationId)
    {
        $notification = ConsortiumNotification::find($notificationId);

        if ($notification && $notification->user_id === Auth::id()) {
            $notification->delete();
            $this->loadNotifications();

            session()->flash('success', 'Notificação removida.');
        }
    }

    public function toggleShowAll()
    {
        $this->showAll = !$this->showAll;
        $this->loadNotifications();
    }

    public function refreshNotifications()
    {
        $service = new ConsortiumNotificationService();
        $stats = $service->checkAndCreateNotifications();

        $this->loadNotifications();
        $this->dispatch('notification-created');

        if ($stats['total'] > 0) {
            session()->flash('success', "{$stats['total']} nova(s) notificação(ões) criada(s)!");
        } else {
            session()->flash('info', 'Nenhuma nova notificação.');
        }
    }

    public function render()
    {
        return view('livewire.components.consortium-notifications');
    }
}
