<?php

namespace App\Livewire\Notifications;

use App\Livewire\Components\ConsortiumNotifications;
use App\Models\ConsortiumNotification;
use App\Traits\HasNotifications;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Central de notificações (/notificacoes): filtros por categoria, abas
 * não lidas/todas, agrupamento por dia (fuso America/Sao_Paulo) e ações.
 */
#[Title('Notificações')]
class NotificationsIndex extends Component
{
    use HasNotifications;

    public const PAGE = 30;

    #[Url(as: 'aba', except: 'all')]
    public string $tab = 'all';

    #[Url(as: 'categoria', except: '')]
    public string $category = '';

    public int $perPage = self::PAGE;

    public function mount(): void
    {
        $this->normalize();
    }

    public function updated(): void
    {
        $this->normalize();
    }

    protected function normalize(): void
    {
        if (!in_array($this->tab, ['all', 'unread'], true)) {
            $this->tab = 'all';
        }
        if ($this->category !== '' && !isset(ConsortiumNotification::CATEGORIES[$this->category])) {
            $this->category = '';
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->perPage = self::PAGE;
        $this->normalize();
    }

    public function setCategory(string $category): void
    {
        $this->category = $this->category === $category ? '' : $category;
        $this->perPage = self::PAGE;
        $this->normalize();
    }

    public function loadMore(): void
    {
        $this->perPage += self::PAGE;
    }

    #[On('notifications-updated')]
    public function refreshList(): void
    {
    }

    public function visit(int $id)
    {
        $n = $this->find($id);
        if (!$n) {
            return null;
        }
        $n->markAsRead();
        $this->syncBell();

        $link = $n->link;
        if ($link) {
            return $n->is_external_link ? redirect()->away($link) : $this->redirect($link, navigate: true);
        }

        return null;
    }

    public function markAsRead(int $id): void
    {
        $this->find($id)?->markAsRead();
        $this->syncBell();
    }

    public function markAsUnread(int $id): void
    {
        $this->find($id)?->markAsUnread();
        $this->syncBell();
    }

    public function delete(int $id): void
    {
        if ($n = $this->find($id)) {
            $n->delete();
            $this->syncBell();
        }
    }

    public function markAllAsRead(): void
    {
        $count = ConsortiumNotification::markAllAsReadForUser(Auth::id(), $this->category ?: null);
        $this->syncBell();
        $this->notifySuccess($count ? ($count === 1 ? '1 notificação marcada como lida.' : "{$count} notificações marcadas como lidas.") : 'Nada para marcar.');
    }

    /** Exclui as já lidas (da categoria filtrada, se houver). */
    public function clearRead(): void
    {
        $count = ConsortiumNotification::forUser(Auth::id())->read()
            ->when($this->category, fn ($q) => $q->inCategory($this->category))
            ->get(['id', 'user_id'])
            ->each->delete()
            ->count();
        $this->syncBell();
        $this->notifySuccess($count ? ($count === 1 ? '1 notificação lida excluída.' : "{$count} notificações lidas excluídas.") : 'Nenhuma notificação lida para excluir.');
    }

    protected function find(int $id): ?ConsortiumNotification
    {
        return ConsortiumNotification::forUser(Auth::id())->find($id);
    }

    protected function syncBell(): void
    {
        $this->dispatch('notifications-updated')->to(ConsortiumNotifications::class);
    }

    /** Contagens por categoria (total e não lidas) numa consulta só. */
    protected function categoryCounts(int $userId): array
    {
        $counts = array_fill_keys(array_keys(ConsortiumNotification::CATEGORIES), ['total' => 0, 'unread' => 0]);

        ConsortiumNotification::forUser($userId)
            ->selectRaw('module, type, is_read, COUNT(*) as c')
            ->groupBy('module', 'type', 'is_read')
            ->toBase()
            ->get()
            ->each(function ($row) use (&$counts) {
                $cat = ConsortiumNotification::categoryFor($row->module, $row->type);
                $counts[$cat]['total'] += (int) $row->c;
                if (!(bool) $row->is_read) {
                    $counts[$cat]['unread'] += (int) $row->c;
                }
            });

        return $counts;
    }

    public function render()
    {
        $userId = (int) Auth::id();
        $counts = $this->categoryCounts($userId);

        $items = ConsortiumNotification::forUser($userId)
            ->when($this->tab === 'unread', fn ($q) => $q->unread())
            ->when($this->category, fn ($q) => $q->inCategory($this->category))
            ->latest('created_at')->latest('id')
            ->limit($this->perPage + 1)
            ->get();

        $hasMore = $items->count() > $this->perPage;
        $items = $items->take($this->perPage);

        $today = Carbon::now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();
        $groups = $items->groupBy(function (ConsortiumNotification $n) use ($today) {
            $d = $n->local_created_at?->copy()->startOfDay() ?? $today;
            return match (true) {
                $d->gte($today) => 'Hoje',
                $d->gte($today->copy()->subDay()) => 'Ontem',
                $d->gte($today->copy()->subDays(6)) => 'Esta semana',
                default => 'Mais antigas',
            };
        });

        $scopeCounts = $this->category ? $counts[$this->category] : [
            'total' => array_sum(array_column($counts, 'total')),
            'unread' => array_sum(array_column($counts, 'unread')),
        ];

        return view('livewire.notifications.notifications-index', [
            'groups' => $groups,
            'hasMore' => $hasMore,
            'counts' => $counts,
            'scopeCounts' => $scopeCounts,
            'totalUnread' => array_sum(array_column($counts, 'unread')),
            'categories' => ConsortiumNotification::CATEGORIES,
        ]);
    }
}
