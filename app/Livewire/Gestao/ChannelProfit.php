<?php

namespace App\Livewire\Gestao;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Lucro real por canal (Loja, Mercado Livre, Shopee) e por produto:
 * faturamento − custo dos produtos − taxas do marketplace − frete pago (quando conhecido).
 *
 * - Loja: vendas sem origem de marketplace; custo = preço de custo gravado no item.
 * - Mercado Livre: pedidos de anúncios do usuário; pedidos importados como venda
 *   contam só aqui. Taxa = fee_amount, ou sale_fee do pedido bruto, ou estimativa (%).
 * - Shopee: pedidos do usuário; taxa = fee_amount (se existir) ou estimativa (%).
 */
class ChannelProfit extends Component
{
    public const CHANNELS = [
        'loja' => ['label' => 'Loja', 'icon' => 'bi-shop'],
        'ml' => ['label' => 'Mercado Livre', 'icon' => 'bi-bag-check'],
        'shopee' => ['label' => 'Shopee', 'icon' => 'bi-bag-heart'],
    ];

    #[Url]
    public string $month = '';

    /** Quantos meses (terminando no mês escolhido): 1, 3, 6 ou 12. */
    #[Url]
    public int $months = 1;

    #[Url]
    public string $channel = 'all';

    #[Url]
    public string $sort = 'desc';

    public float $mlFeePct = 14;

    public float $shopeeFeePct = 20;

    public bool $showSettings = false;

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }
        if (! in_array($this->months, [1, 3, 6, 12], true)) {
            $this->months = 1;
        }
        if (! in_array($this->channel, ['all', 'loja', 'ml', 'shopee'], true)) {
            $this->channel = 'all';
        }

        $prefs = Auth::user()->preferences['profit'] ?? [];
        $this->mlFeePct = (float) ($prefs['ml_fee_pct'] ?? 14);
        $this->shopeeFeePct = (float) ($prefs['shopee_fee_pct'] ?? 20);
    }

    public function shiftMonth(int $delta): void
    {
        $this->month = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->addMonths($delta)->format('Y-m');
    }

    public function setMonths(int $months): void
    {
        $this->months = in_array($months, [1, 3, 6, 12], true) ? $months : 1;
    }

    public function setChannel(string $channel): void
    {
        $this->channel = array_key_exists($channel, self::CHANNELS) ? $channel : 'all';
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'desc' ? 'asc' : 'desc';
    }

    public function updatedMlFeePct($value): void
    {
        $this->mlFeePct = $this->savePct('ml_fee_pct', $value);
    }

    public function updatedShopeeFeePct($value): void
    {
        $this->shopeeFeePct = $this->savePct('shopee_fee_pct', $value);
    }

    private function savePct(string $key, $value): float
    {
        $pct = round(max(0, min(100, (float) str_replace(',', '.', (string) $value))), 2);

        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['profit'][$key] = $pct;
        $user->preferences = $prefs;
        $user->save();

        return $pct;
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function period(): array
    {
        $end = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->endOfMonth();
        $start = $end->copy()->startOfMonth()->subMonths($this->months - 1);

        return [$start, $end];
    }

    /**
     * Calcula canais e produtos do período.
     *
     * @return array{channels: array, products: array}
     */
    public function report(): array
    {
        [$start, $end] = $this->period();
        $userId = (int) Auth::id();

        $channels = [];
        foreach (array_keys(self::CHANNELS) as $key) {
            $channels[$key] = [
                'revenue' => 0.0, 'cost' => 0.0, 'fees' => 0.0, 'feesEstimated' => 0.0,
                'shipping' => 0.0, 'shippingKnown' => false, 'orders' => 0, 'missingCost' => false,
            ];
        }
        $products = [];

        // Acumula uma linha de produto (por canal) já com a parte rateada.
        $add = function (string $channel, string $key, string $name, float $qty, float $revenue, float $cost, float $fees, bool $missingCost) use (&$products) {
            $id = $channel.'|'.$key;
            $products[$id] ??= ['key' => $key, 'channel' => $channel, 'name' => $name, 'qty' => 0.0, 'revenue' => 0.0, 'cost' => 0.0, 'fees' => 0.0, 'missingCost' => false];
            $products[$id]['qty'] += $qty;
            $products[$id]['revenue'] += $revenue;
            $products[$id]['cost'] += $cost;
            $products[$id]['fees'] += $fees;
            $products[$id]['missingCost'] = $products[$id]['missingCost'] || $missingCost;
        };

        $this->loadLoja($userId, $start, $end, $channels['loja'], $add);
        $this->loadMercadoLivre($userId, $start, $end, $channels['ml'], $add);
        $this->loadShopee($userId, $start, $end, $channels['shopee'], $add);

        foreach ($channels as &$c) {
            $c['profit'] = $c['revenue'] - $c['cost'] - $c['fees'] - $c['shipping'];
            $c['margin'] = $c['revenue'] > 0 ? $c['profit'] / $c['revenue'] * 100 : 0;
        }
        unset($c);

        foreach ($products as &$p) {
            $p['profit'] = $p['revenue'] - $p['cost'] - $p['fees'];
            $p['margin'] = $p['revenue'] > 0 ? $p['profit'] / $p['revenue'] * 100 : 0;
        }
        unset($p);

        return ['channels' => $channels, 'products' => $products];
    }

    private function loadLoja(int $userId, Carbon $start, Carbon $end, array &$ch, callable $add): void
    {
        $sales = DB::table('sales')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['cancelada', 'orcamento'])
            ->where(fn ($q) => $q->whereNull('source')->orWhereNotIn('source', ['mercadolivre', 'shopee']))
            // Vendas geradas a partir de pedidos de marketplace contam só no marketplace.
            ->whereNotIn('id', DB::table('mercadolivre_orders')->whereNotNull('imported_to_sale_id')->select('imported_to_sale_id'))
            ->whereNotIn('id', DB::table('shopee_orders')->whereNotNull('imported_to_sale_id')->select('imported_to_sale_id'))
            ->whereBetween('created_at', [$start, $end])
            ->get(['id', 'total_price']);

        if ($sales->isEmpty()) {
            return;
        }

        $items = DB::table('sale_items')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereIn('sale_items.sale_id', $sales->pluck('id'))
            ->get(['sale_items.sale_id', 'sale_items.product_id', 'sale_items.quantity', 'sale_items.price', 'sale_items.price_sale', 'products.name'])
            ->groupBy('sale_id');

        foreach ($sales as $sale) {
            $revenue = (float) $sale->total_price;
            $lines = $items[$sale->id] ?? collect();
            $gross = $lines->sum(fn ($i) => (float) $i->price_sale * (int) $i->quantity);

            $ch['orders']++;
            $ch['revenue'] += $revenue;

            foreach ($lines as $i) {
                $qty = (int) $i->quantity;
                $cost = (float) $i->price * $qty;
                // Faturamento da venda (com descontos) rateado pelo valor de cada item.
                $share = $gross > 0 ? ((float) $i->price_sale * $qty) / $gross : 1 / max(1, $lines->count());
                $missing = (float) $i->price <= 0;
                $ch['cost'] += $cost;
                $ch['missingCost'] = $ch['missingCost'] || $missing;
                $add('loja', 'p'.$i->product_id, $i->name ?? 'Produto removido', $qty, $revenue * $share, $cost, 0.0, $missing);
            }
        }
    }

    private function loadMercadoLivre(int $userId, Carbon $start, Carbon $end, array &$ch, callable $add): void
    {
        if (! Schema::hasTable('mercadolivre_orders') || ! Schema::hasTable('ml_publications')) {
            return;
        }

        $hasFee = Schema::hasColumn('mercadolivre_orders', 'fee_amount');
        $publications = DB::table('ml_publications')->where('user_id', $userId)->whereNotNull('ml_item_id')->pluck('id', 'ml_item_id');
        if ($publications->isEmpty()) {
            return;
        }

        $orders = DB::table('mercadolivre_orders')
            ->whereIn('ml_item_id', $publications->keys())
            ->where(fn ($q) => $q->whereNull('order_status')->orWhereNotIn('order_status', ['cancelled', 'invalid']))
            ->whereBetween('date_created', [$start, $end])
            ->get();

        $components = $this->components('ml_publication_products', 'ml_publication_id', $publications->values()->all());
        $pct = $this->mlFeePct / 100;

        foreach ($orders as $order) {
            $raw = json_decode((string) $order->raw_data, true) ?: [];
            $items = collect($raw['order_items'] ?? [])->map(fn ($i) => [
                'item_id' => (string) ($i['item']['id'] ?? $order->ml_item_id),
                'title' => $i['item']['title'] ?? null,
                'model_id' => null,
                'qty' => max(1, (int) ($i['quantity'] ?? 1)),
                'gross' => (float) ($i['unit_price'] ?? 0) * max(1, (int) ($i['quantity'] ?? 1)),
                'fee' => isset($i['sale_fee']) && is_numeric($i['sale_fee']) ? (float) $i['sale_fee'] * max(1, (int) ($i['quantity'] ?? 1)) : null,
            ]);
            if ($items->isEmpty()) {
                $items = collect([[
                    'item_id' => (string) $order->ml_item_id, 'title' => null, 'model_id' => null,
                    'qty' => max(1, (int) $order->quantity), 'gross' => (float) $order->unit_price * max(1, (int) $order->quantity), 'fee' => null,
                ]]);
            }

            $revenue = (float) $order->total_amount > 0 ? (float) $order->total_amount : $items->sum('gross');

            // Taxa: gravada > sale_fee do pedido bruto > estimada pelo %.
            if ($hasFee && $order->fee_amount !== null) {
                $fee = (float) $order->fee_amount;
            } elseif ($items->whereNotNull('fee')->isNotEmpty()) {
                $fee = (float) $items->sum('fee');
            } else {
                $fee = round($revenue * $pct, 2);
                $ch['feesEstimated'] += $fee;
            }

            // Frete pago pelo vendedor: custo cheio do envio menos o que o comprador pagou.
            $option = $raw['shipping']['shipping_option'] ?? [];
            $shipping = 0.0;
            if (isset($option['list_cost']) && is_numeric($option['list_cost'])) {
                $shipping = max(0, (float) $option['list_cost'] - (float) ($option['cost'] ?? 0));
                $ch['shippingKnown'] = true;
            }

            $ch['orders']++;
            $ch['revenue'] += $revenue;
            $ch['fees'] += $fee;
            $ch['shipping'] += $shipping;

            $this->distribute('ml', $items, $revenue, $fee + $shipping, $components, fn ($item) => $publications[$item['item_id']] ?? null, $ch, $add);
        }
    }

    private function loadShopee(int $userId, Carbon $start, Carbon $end, array &$ch, callable $add): void
    {
        if (! Schema::hasTable('shopee_orders')) {
            return;
        }

        $hasFee = Schema::hasColumn('shopee_orders', 'fee_amount');
        $orders = DB::table('shopee_orders')
            ->where('user_id', $userId)
            ->whereNotIn('order_status', ['CANCELLED', 'IN_CANCEL', 'UNPAID', 'cancelled'])
            ->where(fn ($q) => $q->whereBetween('shopee_created_at', [$start, $end])
                ->orWhere(fn ($q) => $q->whereNull('shopee_created_at')->whereBetween('created_at', [$start, $end])))
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $publications = Schema::hasTable('shopee_publications')
            ? DB::table('shopee_publications')->where('user_id', $userId)->whereNotNull('shopee_item_id')->pluck('id', 'shopee_item_id')
            : collect();
        $components = Schema::hasTable('shopee_publication_products')
            ? $this->components('shopee_publication_products', 'shopee_publication_id', $publications->values()->all(), true)
            : collect();
        $pct = $this->shopeeFeePct / 100;

        foreach ($orders as $order) {
            $items = collect(json_decode((string) $order->order_items, true) ?: [])->map(function ($i) use ($order) {
                $qty = max(1, (int) ($i['model_quantity_purchased'] ?? $i['quantity'] ?? 1));
                $unit = (float) ($i['model_discounted_price'] ?? $i['model_original_price'] ?? 0);

                return [
                    'item_id' => (string) ($i['item_id'] ?? $order->shopee_item_id),
                    'title' => $i['item_name'] ?? null,
                    'model_id' => isset($i['model_id']) ? (string) $i['model_id'] : null,
                    'qty' => $qty,
                    'gross' => $unit * $qty,
                ];
            });
            if ($items->isEmpty()) {
                $items = collect([['item_id' => (string) $order->shopee_item_id, 'title' => null, 'model_id' => $order->shopee_model_id, 'qty' => 1, 'gross' => 0.0]]);
            }

            $revenue = (float) $order->total_amount > 0 ? (float) $order->total_amount : $items->sum('gross');

            if ($hasFee && $order->fee_amount !== null) {
                $fee = (float) $order->fee_amount;
            } else {
                $fee = round($revenue * $pct, 2);
                $ch['feesEstimated'] += $fee;
            }

            $ch['orders']++;
            $ch['revenue'] += $revenue;
            $ch['fees'] += $fee;

            $this->distribute('shopee', $items, $revenue, $fee, $components, fn ($item) => $publications[$item['item_id']] ?? null, $ch, $add);
        }
    }

    /**
     * Rateia o faturamento e as taxas do pedido entre os itens (pelo valor bruto)
     * e, dentro de cada anúncio, entre os produtos vinculados (pelo custo).
     */
    private function distribute(string $channel, $items, float $revenue, float $charges, $components, callable $publicationOf, array &$ch, callable $add): void
    {
        $gross = $items->sum('gross');

        foreach ($items as $item) {
            $share = $gross > 0 ? $item['gross'] / $gross : 1 / max(1, $items->count());
            $itemRevenue = $revenue * $share;
            $itemCharges = $charges * $share;

            $pubId = $publicationOf($item);
            $parts = collect($pubId ? ($components[$pubId] ?? []) : []);
            if ($item['model_id'] !== null && $parts->contains(fn ($p) => ($p->model_id ?? null) !== null)) {
                $match = $parts->filter(fn ($p) => (string) $p->model_id === $item['model_id']);
                $parts = $match->isNotEmpty() ? $match : $parts->whereNull('model_id');
            }

            if ($parts->isEmpty()) {
                $ch['missingCost'] = true;
                $name = ($item['title'] ?: $item['item_id']).' (anúncio sem produto vinculado)';
                $add($channel, 'item:'.$item['item_id'], $name, $item['qty'], $itemRevenue, 0.0, $itemCharges, true);

                continue;
            }

            $totalCost = $parts->sum(fn ($p) => $p->cost * $p->qty);
            foreach ($parts as $p) {
                $cost = $p->cost * $p->qty * $item['qty'];
                $weight = $totalCost > 0 ? ($p->cost * $p->qty) / $totalCost : 1 / $parts->count();
                $missing = $p->cost <= 0;
                $ch['cost'] += $cost;
                $ch['missingCost'] = $ch['missingCost'] || $missing;
                $add($channel, 'p'.$p->product_id, $p->name ?? 'Produto removido', $p->qty * $item['qty'], $itemRevenue * $weight, $cost, $itemCharges * $weight, $missing);
            }
        }
    }

    /** Produtos de cada publicação: [publication_id => [{product_id, name, qty, cost, model_id}]]. */
    private function components(string $table, string $fk, array $publicationIds, bool $withModel = false)
    {
        if (empty($publicationIds)) {
            return collect();
        }

        $columns = ["pp.$fk as pub_id", 'pp.product_id', 'pp.quantity', 'pp.unit_cost', 'products.name', 'products.price'];
        if ($withModel) {
            $columns[] = 'pp.shopee_model_id as model_id';
        }

        return DB::table("$table as pp")
            ->leftJoin('products', 'products.id', '=', 'pp.product_id')
            ->whereIn("pp.$fk", $publicationIds)
            ->get($columns)
            ->map(function ($r) {
                $r->qty = max(1, (int) $r->quantity);
                // Custo = preço de custo atual do produto (ou o custo salvo no vínculo).
                $r->cost = (float) $r->price > 0 ? (float) $r->price : (float) $r->unit_cost;

                return $r;
            })
            ->groupBy('pub_id');
    }

    public function render()
    {
        [$start, $end] = $this->period();
        $report = $this->report();

        $rows = collect($report['products'])
            ->when($this->channel !== 'all', fn ($c) => $c->where('channel', $this->channel))
            ->groupBy('key')
            ->map(function ($group) {
                $first = $group->first();
                $revenue = $group->sum('revenue');
                $profit = $group->sum('profit');

                return [
                    'name' => $first['name'],
                    'channels' => $group->pluck('channel')->unique()->values()->all(),
                    'qty' => $group->sum('qty'),
                    'revenue' => $revenue,
                    'cost' => $group->sum('cost'),
                    'fees' => $group->sum('fees'),
                    'profit' => $profit,
                    'margin' => $revenue > 0 ? $profit / $revenue * 100 : 0,
                    'missingCost' => $group->contains('missingCost', true),
                ];
            })
            ->sortBy('profit', SORT_REGULAR, $this->sort === 'desc')
            ->values();

        $channels = $report['channels'];
        $totalRevenue = array_sum(array_column($channels, 'revenue'));
        $totalProfit = array_sum(array_column($channels, 'profit'));

        return view('livewire.gestao.channel-profit', [
            'start' => $start,
            'end' => $end,
            'channels' => $channels,
            'rows' => $rows,
            'total' => [
                'revenue' => $totalRevenue,
                'profit' => $totalProfit,
                'margin' => $totalRevenue > 0 ? $totalProfit / $totalRevenue * 100 : 0,
            ],
        ]);
    }
}
