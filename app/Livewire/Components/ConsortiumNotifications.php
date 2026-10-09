<?php

namespace App\Livewire\Components;

use App\Models\ConsortiumNotification;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Sino de notificações (rodapé da sidebar). Mostra as 8 mais recentes e leva à
 * central completa (/notificacoes). Atualiza por polling e pelo evento
 * "notifications-updated" disparado pela central.
 */
class ConsortiumNotifications extends Component
{
    use HasNotifications;

    public const LIMIT = 8;

    #[On('notification-created')]
    #[On('notifications-updated')]
    public function refreshList(): void
    {
        // Só re-renderiza.
    }

    /** Abre a ação da notificação e a marca como lida. */
    public function visit(int $id)
    {
        $notification = $this->find($id);
        if (!$notification) {
            return null;
        }

        $notification->markAsRead();
        $this->dispatch('notifications-updated')->to(\App\Livewire\Notifications\NotificationsIndex::class);

        $link = $notification->link;
        if ($link) {
            return $notification->is_external_link ? redirect()->away($link) : $this->redirect($link, navigate: true);
        }

        return null;
    }

    public function markAsRead(int $id): void
    {
        $this->find($id)?->markAsRead();
        $this->dispatch('notifications-updated')->to(\App\Livewire\Notifications\NotificationsIndex::class);
    }

    public function markAsUnread(int $id): void
    {
        $this->find($id)?->markAsUnread();
        $this->dispatch('notifications-updated')->to(\App\Livewire\Notifications\NotificationsIndex::class);
    }

    public function markAllAsRead(): void
    {
        $count = ConsortiumNotification::markAllAsReadForUser(Auth::id());
        $this->dispatch('notifications-updated')->to(\App\Livewire\Notifications\NotificationsIndex::class);
        if ($count > 0) {
            $this->notifySuccess($count === 1 ? '1 notificação marcada como lida.' : "{$count} notificações marcadas como lidas.");
        }
    }

    public function delete(int $id): void
    {
        $notification = $this->find($id);
        if ($notification) {
            $notification->delete();
            $this->dispatch('notifications-updated')->to(\App\Livewire\Notifications\NotificationsIndex::class);
        }
    }

    protected function find(int $id): ?ConsortiumNotification
    {
        return ConsortiumNotification::forUser(Auth::id())->find($id);
    }

    public function render()
    {
        $userId = Auth::id();

        return view('livewire.components.consortium-notifications', [
            'notifications' => ConsortiumNotification::forUser($userId)
                ->latest('created_at')->latest('id')
                ->limit(self::LIMIT)
                ->get(),
            'unreadCount' => ConsortiumNotification::unreadCountForUser($userId),
        ]);
    }
}
