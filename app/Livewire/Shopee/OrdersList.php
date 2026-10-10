<?php

namespace App\Livewire\Shopee;

use App\Models\MlStockLog;
use App\Models\Product;
use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use App\Models\ShopeeToken;
use App\Services\Shopee\OrderSaleImporter;
use App\Services\Shopee\OrderService;
use App\Traits\HasNotifications;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pedidos Shopee gravados no sistema (webhook + sincronização), com
 * importação para venda.
 */
class OrdersList extends Component
{
    use HasNotifications, WithPagination;

    public const TZ = 'America/Sao_Paulo';

    /** Grupos de status (filtro) => status da Shopee. */
    public const STATUS_GROUPS = [
        'a_pagar' => ['UNPAID'],
        'a_enviar' => ['READY_TO_SHIP', 'PROCESSED', 'RETRY_SHIP', 'INVOICE_PENDING'],
        'enviado' => ['SHIPPED', 'TO_CONFIRM_RECEIVE'],
        'concluido' => ['COMPLETED'],
        'cancelado' => ['CANCELLED', 'IN_CANCEL', 'TO_RETURN'],
    ];

    public const GROUP_LABELS = [
        'a_pagar' => 'A pagar',
        'a_enviar' => 'A enviar',
        'enviado' => 'Enviado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    #[Url(as: 'busca', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    /** 7 | 15 | 30 | 90 | all | custom */
    #[Url(as: 'periodo', except: '30')]
    public string $period = '30';

    public string $dateFrom = '';
    public string $dateTo = '';

    /** Pedido aberto no modal (order sn). */
    #[Url(as: 'pedido', except: '')]
    public string $pedido = '';

    public int $perPage = 12;

    public function updated($name): void
    {
        if (in_array($name, ['search', 'statusFilter', 'period', 'dateFrom', 'dateTo', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function setStatus(string $group): void
    {
        $this->statusFilter = array_key_exists($group, self::STATUS_GROUPS) ? $group : '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->period = '30';
        $this->resetPage();
    }

    public function openOrder(string $sn): void
    {
        $this->pedido = $sn;
    }

    public function closeOrder(): void
    {
        $this->pedido = '';
    }

    // ---------------------------------------------------------------------
    // Ações
    // ---------------------------------------------------------------------

    public function importOrder(int $orderId): void
    {
        $order = ShopeeOrder::where('user_id', Auth::id())->find($orderId);
        if (!$order) {
            $this->notifyError('Pedido não encontrado.');
            return;
        }

        $result = app(OrderSaleImporter::class)->import($order, (int) Auth::id());
        $result['success'] ? $this->notifySuccess($result['message']) : $this->notifyError($result['message']);
    }

    public function syncOrders(): void
    {
        if (!ShopeeToken::getActiveForUser((int) Auth::id())) {
            $this->notifyError('Conecte sua loja Shopee para sincronizar os pedidos.');
            return;
        }

        // Evita sincronização dupla (clique repetido / duas abas); mesma trava da tela de publicações
        $lock = Cache::lock('shopee-import-orders-' . Auth::id(), 300);
        if (!$lock->get()) {
            $this->notifyWarning('Uma sincronização de pedidos já está em andamento.');
            return;
        }

        try {
            $result = app(OrderService::class)->importRecentOrders((int) Auth::id());
            $msg = "Sincronização concluída: {$result['imported']} pedido(s) dos últimos 15 dias"
                . ($result['errors'] > 0 ? ", {$result['errors']} erro(s)." : '.');
            $result['errors'] > 0 ? $this->notifyWarning($msg) : $this->notifySuccess($msg);
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao sincronizar pedidos: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    // ---------------------------------------------------------------------
    // Consulta
    // ---------------------------------------------------------------------

    /** [início, fim] em UTC do período escolhido; null = sem limite. */
    public function periodRange(): array
    {
        $now = Carbon::now(self::TZ);

        if ($this->period === 'custom') {
            $from = $this->parseDate($this->dateFrom)?->startOfDay();
            $to = $this->parseDate($this->dateTo)?->endOfDay();
        } elseif ($this->period === 'all') {
            $from = $to = null;
        } else {
            $days = in_array((int) $this->period, [7, 15, 30, 90], true) ? (int) $this->period : 30;
            $from = $now->copy()->subDays($days - 1)->startOfDay();
            $to = null;
        }

        return [$from?->utc(), $to?->utc()];
    }

    private function parseDate(string $value): ?Carbon
    {
        try {
            return $value !== '' ? Carbon::createFromFormat('Y-m-d', $value, self::TZ) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Pedidos do usuário com período e busca (sem o filtro de status). */
    public function baseQuery(): Builder
    {
        [$from, $to] = $this->periodRange();
        $date = 'COALESCE(shopee_created_at, created_at)';

        return ShopeeOrder::query()
            ->where('user_id', Auth::id())
            ->when($from, fn ($q) => $q->whereRaw("$date >= ?", [$from->format('Y-m-d H:i:s')]))
            ->when($to, fn ($q) => $q->whereRaw("$date <= ?", [$to->format('Y-m-d H:i:s')]))
            ->when(trim($this->search) !== '', fn ($q) => $this->applySearch($q, trim($this->search)));
    }

    /** Busca por nº do pedido, comprador ou produto (nome no pedido, anúncio ou produto vinculado). */
    private function applySearch(Builder $q, string $term): void
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';
        // Os itens ficam em JSON com acentos escapados (ç)
        $escaped = '%' . addcslashes(trim(json_encode($term), '"'), '%_') . '%';

        $itemIds = ShopeePublication::where('user_id', Auth::id())
            ->whereNotNull('shopee_item_id')
            ->where(fn ($w) => $w->where('title', 'like', $like)
                ->orWhereHas('products', fn ($p) => $p->withoutGlobalScope('team_visibility')
                    ->where(fn ($pp) => $pp->where('products.name', 'like', $like)->orWhere('products.product_code', 'like', $like))))
            ->limit(200)
            ->pluck('shopee_item_id');

        $q->where(function ($w) use ($like, $escaped, $itemIds) {
            $w->where('shopee_order_sn', 'like', $like)
                ->orWhere('buyer_username', 'like', $like)
                ->orWhere('order_items', 'like', $like)
                ->orWhere('order_items', 'like', $escaped)
                ->orWhereIn('shopee_item_id', $itemIds);
            foreach ($itemIds as $id) {
                $w->orWhere('order_items', 'like', '%"item_id":' . $id . ',%')
                    ->orWhere('order_items', 'like', '%"item_id":"' . $id . '"%');
            }
        });
    }

    public function stats(): array
    {
        $rows = $this->baseQuery()
            ->selectRaw('order_status, COUNT(*) as n, SUM(total_amount) as total, SUM(COALESCE(fee_amount, 0)) as fees')
            ->groupBy('order_status')
            ->get();

        $out = array_fill_keys(array_keys(self::STATUS_GROUPS), 0);
        $out['all'] = 0;
        $out['revenue'] = 0.0;
        $out['fees'] = 0.0;
        foreach ($rows as $r) {
            $out['all'] += (int) $r->n;
            $group = self::groupOf($r->order_status);
            if ($group) {
                $out[$group] += (int) $r->n;
            }
            if (!in_array($group, ['cancelado', 'a_pagar'], true)) {
                $out['revenue'] += (float) $r->total;
                $out['fees'] += (float) $r->fees;
            }
        }

        return $out;
    }

    public static function groupOf(?string $status): ?string
    {
        foreach (self::STATUS_GROUPS as $group => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $group;
            }
        }

        return null;
    }

    /** Badge do status: [texto, cor]. */
    public static function statusBadge(?string $status): array
    {
        return match ($status) {
            'UNPAID' => ['A pagar', 'yellow'],
            'READY_TO_SHIP', 'RETRY_SHIP' => ['A enviar', 'blue'],
            'PROCESSED' => ['Processando', 'blue'],
            'INVOICE_PENDING' => ['NF pendente', 'blue'],
            'SHIPPED' => ['Enviado', 'indigo'],
            'TO_CONFIRM_RECEIVE' => ['Entregue', 'indigo'],
            'COMPLETED' => ['Concluído', 'green'],
            'IN_CANCEL' => ['Cancelando', 'red'],
            'CANCELLED' => ['Cancelado', 'red'],
            'TO_RETURN' => ['Devolução', 'red'],
            default => [$status ?: '—', 'gray'],
        };
    }

    /**
     * Situação do estoque do pedido: [texto, cor, ícone].
     *
     * @param Collection $logs MlStockLog do pedido
     */
    public static function stockBadge(ShopeeOrder $order, Collection $logs): array
    {
        $sold = $logs->where('operation_type', 'shopee_sale');
        $back = $logs->where('operation_type', 'marketplace_cancel');

        if ($back->isNotEmpty()) {
            return ['Estoque devolvido', 'slate', 'bi-arrow-counterclockwise'];
        }
        if ($sold->isNotEmpty()) {
            return $order->sync_status === 'error'
                ? ['Baixa parcial', 'amber', 'bi-exclamation-triangle-fill']
                : ['Estoque baixado', 'emerald', 'bi-box-seam-fill'];
        }
        if ($order->stock_processed_at) {
            if ($order->sync_status === 'error') {
                return ['Sem baixa: item sem vínculo', 'amber', 'bi-exclamation-triangle-fill'];
            }

            return in_array($order->order_status, ['CANCELLED', 'IN_CANCEL'], true)
                ? ['Sem movimento de estoque', 'slate', 'bi-dash-circle']
                : ['Sem baixa (pedido antigo)', 'slate', 'bi-clock-history'];
        }

        return ['Estoque não baixado', 'slate', 'bi-hourglass-split'];
    }

    /**
     * Anúncios (com produtos) dos itens dos pedidos, por shopee_item_id.
     */
    private function publicationsFor(iterable $orders): Collection
    {
        $ids = collect($orders)->flatMap(fn ($o) => array_column(OrderSaleImporter::items($o), 'item_id'))
            ->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return ShopeePublication::where('user_id', Auth::id())
            ->whereIn('shopee_item_id', $ids)
            ->with(['products' => fn ($q) => $q->withoutGlobalScope('team_visibility')])
            ->get()
            ->keyBy('shopee_item_id');
    }

    /** Itens do pedido com produto vinculado e miniatura. */
    public static function itemsWithProducts(ShopeeOrder $order, Collection $publications): array
    {
        return array_map(function ($item) use ($publications) {
            $pub = $publications[$item['item_id']] ?? null;
            $products = collect();
            if ($pub) {
                $products = $pub->products;
                if ($item['model_id'] !== '' && $item['model_id'] !== '0') {
                    $byModel = $products->filter(fn ($p) => (string) $p->pivot->shopee_model_id === $item['model_id']);
                    if ($byModel->isNotEmpty() || $pub->has_variations) {
                        $products = $byModel;
                    }
                }
            }
            $first = $products->first();
            $thumb = $first && $first->image && $first->image !== 'product-placeholder.png' ? $first->image_url : ($item['image'] ?: null);

            return $item + [
                'products' => $products->values(),
                'thumb' => $thumb,
                'title' => $item['name'] ?: ($pub->title ?? ($first->name ?? 'Item ' . $item['item_id'])),
                'linked' => $products->isNotEmpty(),
            ];
        }, OrderSaleImporter::items($order));
    }

    public function render()
    {
        $query = $this->baseQuery();
        if ($this->statusFilter !== '' && isset(self::STATUS_GROUPS[$this->statusFilter])) {
            $query->whereIn('order_status', self::STATUS_GROUPS[$this->statusFilter]);
        }

        $orders = $query->with('importedSale:id,status')
            ->orderByRaw('COALESCE(shopee_created_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(in_array($this->perPage, [12, 24, 48], true) ? $this->perPage : 12);

        $selected = $this->pedido !== ''
            ? ShopeeOrder::where('user_id', Auth::id())->where('shopee_order_sn', $this->pedido)->with('importedSale')->first()
            : null;

        $pageOrders = collect($orders->items());
        $publications = $this->publicationsFor($selected ? $pageOrders->push($selected) : $pageOrders);

        $sns = collect($orders->items())->pluck('shopee_order_sn')->push($selected?->shopee_order_sn)->filter()->unique();
        $logs = $sns->isEmpty() ? collect() : MlStockLog::whereIn('ml_order_id', $sns)
            ->whereIn('operation_type', ['shopee_sale', 'marketplace_cancel'])
            ->where('source', 'shopee')
            ->orderBy('id')
            ->get()
            ->groupBy('ml_order_id');

        $selectedLogs = collect();
        if ($selected) {
            $selectedLogs = $logs[$selected->shopee_order_sn] ?? collect();
            $names = Product::withoutGlobalScope('team_visibility')->whereIn('id', $selectedLogs->pluck('product_id')->unique())->pluck('name', 'id');
            $selectedLogs->each(fn ($l) => $l->product_name = $names[$l->product_id] ?? "Produto #{$l->product_id}");
        }

        return view('livewire.shopee.orders-list', [
            'orders' => $orders,
            'stats' => $this->stats(),
            'publications' => $publications,
            'logs' => $logs,
            'selected' => $selected,
            'selectedLogs' => $selectedLogs,
            'connected' => (bool) ShopeeToken::getActiveForUser((int) Auth::id()),
        ])->layout('components.layouts.app', ['title' => 'Pedidos Shopee']);
    }
}
