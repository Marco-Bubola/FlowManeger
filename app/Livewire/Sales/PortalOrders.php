<?php

namespace App\Livewire\Sales;

use App\Models\ClientQuoteRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\Portal\PortalOrderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Pedidos que os clientes fizeram pelo portal (catálogo), de todos os clientes
 * da loja numa tela só: novos, aguardando, confirmados e recusados.
 *
 * Confirmar como venda e devolver pagamento continuam nas rotas do
 * ClientPortalController / PortalPaymentController (mesma regra de estoque e de
 * venda); aqui ficam a lista, os filtros e as respostas rápidas.
 */
class PortalOrders extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'aba', except: 'abertos')]
    public string $tab = 'abertos'; // abertos | pagos | confirmados | recusados | todos

    #[Url(as: 'ordem', except: 'recentes')]
    public string $sort = 'recentes'; // recentes | antigos | valor

    #[Url(as: 'pedido', except: null)]
    public ?int $focus = null;

    public ?int $rejectingId = null;
    public string $rejectReason = '';

    /** Aviso ao cliente depois de recusar (WhatsApp em um clique). */
    public ?array $notify = null;

    public const TABS = [
        'abertos' => ['Novos e em aberto', 'bi-inbox'],
        'pagos' => ['Pagos pelo site', 'bi-patch-check'],
        'confirmados' => ['Confirmados', 'bi-check2-circle'],
        'recusados' => ['Recusados', 'bi-x-circle'],
        'todos' => ['Todos', 'bi-list-ul'],
    ];

    public const STATUS = [
        'pending' => ['Novo', 'amber', 'bi-bell'],
        'reviewing' => ['Em análise', 'sky', 'bi-search'],
        'quoted' => ['Orçamento enviado', 'violet', 'bi-tag'],
        'approved' => ['Confirmado', 'emerald', 'bi-check2-circle'],
        'rejected' => ['Recusado', 'rose', 'bi-x-circle'],
    ];

    public function mount(): void
    {
        if ($this->focus && ! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'todos';
        }
    }

    public function updatedTab(): void
    {
        $this->focus = null;
    }

    public function setStatus(int $id, string $status): void
    {
        if (! in_array($status, ['pending', 'reviewing', 'quoted'], true)) {
            return;
        }
        $quote = $this->quote($id);
        if (! $quote || in_array($quote->status, ['approved', 'rejected'], true)) {
            return;
        }
        $data = ['status' => $status];
        if ($status === 'quoted' && $quote->status !== 'quoted') {
            $data['responded_at'] = now();
            $data['quoted_total'] = $quote->quoted_total ?? app(PortalOrderService::class)->estimatedTotal($quote);
        }
        $quote->update($data);
        $this->dispatch('notify', type: 'success', message: 'Pedido #' . $quote->id . ': ' . self::STATUS[$status][0] . '.');
    }

    public function saveNotes(int $id, string $notes): void
    {
        $quote = $this->quote($id);
        if (! $quote) {
            return;
        }
        $quote->update(['admin_notes' => mb_substr(trim($notes), 0, 2000) ?: null]);
        $this->dispatch('notify', type: 'success', message: 'Resposta do pedido #' . $quote->id . ' salva.');
    }

    public function askReject(int $id): void
    {
        $quote = $this->quote($id);
        if (! $quote || in_array($quote->status, ['approved', 'rejected'], true)) {
            return;
        }
        $this->rejectingId = $quote->id;
        $this->rejectReason = (string) $quote->admin_notes;
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectReason = '';
    }

    public function reject(): void
    {
        $quote = $this->rejectingId ? $this->quote($this->rejectingId) : null;
        $this->rejectingId = null;
        if (! $quote || in_array($quote->status, ['approved', 'rejected'], true)) {
            return;
        }
        $quote->update([
            'status' => 'rejected',
            'admin_notes' => mb_substr(trim($this->rejectReason), 0, 2000) ?: $quote->admin_notes,
            'responded_at' => now(),
        ]);
        $this->rejectReason = '';

        $orders = app(PortalOrderService::class);
        $client = $quote->client;
        $store = User::whereKey($quote->user_id)->value('name');
        $this->notify = [
            'quote_id' => $quote->id,
            'status' => 'rejected',
            'sale_id' => null,
            'client' => $client?->name,
            'has_phone' => $orders->hasWhatsappPhone($client),
            'mailed' => $orders->mailClient($quote, $client),
            'url' => route('clients.portal.quotes.whatsapp', $quote),
            'message' => $orders->whatsappMessage($quote, $client, $store),
        ];
    }

    /** Andamento da entrega (o cliente vê em Meus pedidos); depois oferece avisar no WhatsApp. */
    public function setDelivery(int $id, string $stage, ?string $code = null, ?string $carrier = null, ?string $eta = null): void
    {
        $quote = $this->quote($id);
        if (! $quote || $quote->status !== 'approved' || ! isset(PortalOrderService::DELIVERY_STAGES[$stage])) {
            return;
        }
        $code = mb_substr(trim((string) $code), 0, 60) ?: null;
        $carrier = isset(PortalOrderService::CARRIERS[$carrier]) ? $carrier : null;
        try {
            $eta = $eta ? \Illuminate\Support\Carbon::parse($eta)->toDateString() : null;
        } catch (\Throwable $e) {
            $eta = null;
        }
        $events = (array) ($quote->delivery_events ?? []);
        if ($quote->delivery_stage !== $stage) {
            $events[] = ['stage' => $stage, 'at' => now()->toIso8601String()];
        }
        $quote->update(['delivery_stage' => $stage, 'delivery_events' => array_slice($events, -20), 'tracking_code' => $code, 'tracking_carrier' => $carrier, 'delivery_eta' => $stage === 'entregue' ? null : $eta]);

        $orders = app(PortalOrderService::class);
        $client = $quote->client;
        $phone = \App\Services\Portal\CartRecovery::phone($client);
        $store = \App\Services\Portal\StoreIdentity::for((int) $quote->user_id)['name'];
        $this->notify = [
            'quote_id' => $quote->id,
            'status' => 'approved',
            'sale_id' => $quote->sale?->id,
            'client' => $client?->name,
            'has_phone' => (bool) $phone,
            'mailed' => false,
            'url' => $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($orders->deliveryMessage($quote, $client, $store)) : null,
            'message' => $orders->deliveryMessage($quote, $client, $store),
            'title' => 'Entrega do pedido #' . $quote->id . ': ' . PortalOrderService::DELIVERY_STAGES[$stage][0],
        ];
    }

    public function closeNotify(): void
    {
        $this->notify = null;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->sort = 'recentes';
        $this->focus = null;
    }

    public function render()
    {
        $all = $this->baseQuery()->get();
        $orders = app(PortalOrderService::class);

        $counts = [];
        foreach (array_keys(self::TABS) as $key) {
            $counts[$key] = $all->filter(fn ($q) => $this->inTab($q, $key))->count();
        }

        $list = $all->filter(fn ($q) => $this->focus ? $q->id === $this->focus : $this->inTab($q, $this->tab));
        if (($term = trim($this->search)) !== '') {
            $needle = mb_strtolower($term);
            $digits = preg_replace('/\D/', '', $term);
            $list = $list->filter(function ($q) use ($needle, $digits) {
                $hay = mb_strtolower(($q->client?->name ?? '') . ' ' . collect($q->items ?? [])->pluck('name')->implode(' '));

                return str_contains($hay, $needle)
                    || ($digits !== '' && ((string) $q->id === $digits || str_contains(preg_replace('/\D/', '', (string) $q->client?->phone), $digits)));
            });
        }
        $list = match ($this->sort) {
            'antigos' => $list->sortBy('created_at'),
            'valor' => $list->sortByDesc(fn ($q) => $orders->total($q, $q->sale)),
            default => $list->sortByDesc('created_at'),
        };

        // Fotos dos produtos de todos os pedidos da tela numa consulta só.
        $ids = $list->flatMap(fn ($q) => collect($q->items ?? [])->pluck('product_id'))->filter()->unique();
        $products = $ids->isEmpty()
            ? collect()
            : Product::withoutGlobalScope('team_visibility')->where('user_id', Auth::id())->whereIn('id', $ids)->get()->keyBy('id');

        $rows = $list->values()->map(function ($q) use ($orders, $products) {
            $items = $orders->items($q, null, $products);
            foreach ($items as &$item) {
                $p = $item['product_id'] ? $products->get($item['product_id']) : null;
                $item['stock'] = $p ? (int) $p->stock_quantity : null;
                $item['code'] = $p?->product_code;
            }
            unset($item);
            $phone = preg_replace('/\D/', '', (string) $q->client?->phone);
            if ($phone && ! (strlen($phone) >= 12 && str_starts_with($phone, '55'))) {
                $phone = '55' . ltrim($phone, '0');
            }

            return (object) [
                'q' => $q,
                'items' => $items,
                'units' => array_sum(array_column($items, 'quantity')),
                'total' => $orders->total($q, $q->sale),
                'estimated' => $q->quoted_total === null && ! $q->sale,
                'phone' => strlen((string) $phone) >= 12 ? $phone : null,
                'lastPayment' => $q->payment_status ? $q->payments->sortByDesc('id')->first() : null,
                'short' => collect($items)->contains(fn ($i) => $i['stock'] !== null && $i['stock'] < $i['quantity']),
            ];
        });

        $open = $all->filter(fn ($q) => $this->inTab($q, 'abertos'));
        $stats = [
            'new' => $all->where('status', 'pending')->count(),
            'openValue' => $open->sum(fn ($q) => $orders->total($q, $q->sale)),
            'paidWaiting' => $all->filter(fn ($q) => $q->payment_status === 'approved' && $q->status !== 'approved')->count(),
            'monthConfirmed' => $all->where('status', 'approved')->filter(fn ($q) => ($q->responded_at ?? $q->updated_at)?->isCurrentMonth())->count(),
            'monthValue' => $all->where('status', 'approved')->filter(fn ($q) => ($q->responded_at ?? $q->updated_at)?->isCurrentMonth())->sum(fn ($q) => $orders->total($q, $q->sale)),
        ];

        return view('livewire.sales.portal-orders', [
            'rows' => $rows,
            'counts' => $counts,
            'stats' => $stats,
            'orders' => $orders,
            'catalogUrl' => route('portal.catalog', ['userId' => Auth::id()]),
            'filtersOn' => trim($this->search) !== '' || $this->sort !== 'recentes' || $this->focus,
        ]);
    }

    private function baseQuery()
    {
        return ClientQuoteRequest::query()
            ->where('user_id', Auth::id())
            ->with(['client' => fn ($q) => $q->withoutGlobalScope('team_visibility'), 'sale', 'payments']);
    }

    private function inTab(ClientQuoteRequest $q, string $tab): bool
    {
        return match ($tab) {
            'abertos' => in_array($q->status, ['pending', 'reviewing', 'quoted'], true),
            'pagos' => $q->payment_status === 'approved' || ($q->payment_status === 'refunded'),
            'confirmados' => $q->status === 'approved',
            'recusados' => $q->status === 'rejected',
            default => true,
        };
    }

    private function quote(int $id): ?ClientQuoteRequest
    {
        return ClientQuoteRequest::where('user_id', Auth::id())->find($id);
    }
}
