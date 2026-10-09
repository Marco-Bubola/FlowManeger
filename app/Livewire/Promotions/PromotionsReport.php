<?php

namespace App\Livewire\Promotions;

use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vendas em promoção: quanto foi vendido com preço promocional no período.
 *
 * Um item conta como "em promoção" quando foi vendido com promotion_id
 * (gravado em CreateSale/AddProducts quando o produto estava com promoção no ar).
 *
 * - Faturamento = preço cobrado no item (price_sale) × quantidade.
 * - Desconto dado = (preço "de" gravado no item − preço cobrado) × quantidade.
 * - Lucro = faturamento − custo gravado no item (price) × quantidade.
 * - Fatia = faturamento em promoção ÷ faturamento de todos os itens vendidos no período.
 *
 * Só vendas do usuário logado (e visíveis pelo escopo de equipe), sem canceladas
 * e sem orçamentos.
 */
class PromotionsReport extends Component
{
    public const SORTS = ['qty', 'revenue', 'discount', 'profit', 'margin', 'lift'];

    #[Url]
    public string $month = '';

    /** Quantos meses (terminando no mês escolhido): 1, 3, 6 ou 12. */
    #[Url]
    public int $months = 1;

    #[Url]
    public string $sort = 'revenue';

    #[Url]
    public string $dir = 'desc';

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }
        if (! in_array($this->months, [1, 3, 6, 12], true)) {
            $this->months = 1;
        }
        if (! in_array($this->sort, self::SORTS, true)) {
            $this->sort = 'revenue';
        }
        $this->dir = $this->dir === 'asc' ? 'asc' : 'desc';
    }

    public function shiftMonth(int $delta): void
    {
        $this->month = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->addMonths($delta)->format('Y-m');
    }

    public function setMonths(int $months): void
    {
        $this->months = in_array($months, [1, 3, 6, 12], true) ? $months : 1;
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORTS, true)) {
            return;
        }
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = $column;
            $this->dir = 'desc';
        }
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function period(): array
    {
        $end = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->endOfMonth();
        $start = $end->copy()->startOfMonth()->subMonths($this->months - 1);

        return [$start, $end];
    }

    /** Vendas válidas do usuário (escopo de equipe do model Sale + dono = usuário logado). */
    private function salesQuery()
    {
        return Sale::query()
            ->where('sales.user_id', Auth::id())
            ->whereNotIn('sales.status', ['cancelada', 'orcamento']);
    }

    /**
     * @return array{summary: array, rows: \Illuminate\Support\Collection}
     */
    public function report(): array
    {
        [$start, $end] = $this->period();

        $saleIds = $this->salesQuery()->whereBetween('sales.created_at', [$start, $end])->select('sales.id');

        // Total de todos os itens vendidos no período (para a fatia da promoção).
        $all = SaleItem::query()
            ->whereIn('sale_id', $saleIds)
            ->selectRaw('COALESCE(SUM(quantity), 0) as qty, COALESCE(SUM(quantity * price_sale), 0) as revenue')
            ->first();

        // Itens em promoção agrupados por promoção.
        $grouped = SaleItem::query()
            ->whereIn('sale_id', $saleIds)
            ->whereNotNull('promotion_id')
            ->groupBy('promotion_id', 'product_id')
            ->selectRaw('promotion_id, product_id')
            ->selectRaw('SUM(quantity) as qty')
            ->selectRaw('SUM(quantity * price_sale) as revenue')
            ->selectRaw('SUM(quantity * price) as cost')
            ->selectRaw('SUM(quantity * COALESCE(original_price, price_sale)) as gross')
            ->selectRaw('SUM(CASE WHEN price <= 0 THEN 1 ELSE 0 END) as missing_cost')
            ->selectRaw('COUNT(DISTINCT sale_id) as orders')
            ->get();

        $promotions = Promotion::query()
            ->whereIn('id', $grouped->pluck('promotion_id')->unique())
            ->with('product')
            ->get()
            ->keyBy('id');

        $rows = $grouped->map(function ($g) use ($promotions) {
            $promo = $promotions[$g->promotion_id] ?? null;
            $product = $promo?->product;
            $qty = (int) $g->qty;
            $revenue = (float) $g->revenue;
            $cost = (float) $g->cost;
            $discount = max(0, (float) $g->gross - $revenue);
            $profit = $revenue - $cost;

            return [
                'promotion_id' => (int) $g->promotion_id,
                'product_id' => (int) $g->product_id,
                'name' => $product?->name ?? 'Produto removido',
                'code' => $product?->product_code,
                'image' => $product?->image_url ?? asset('storage/products/product-placeholder.png'),
                'status' => $promo?->status,
                'starts_at' => $promo?->starts_at ?? $promo?->created_at,
                'ends_at' => $promo?->ended_at ?? $promo?->ends_at,
                'promo_price' => $promo ? (float) $promo->promo_price : ($qty ? $revenue / $qty : 0),
                'original_price' => $promo ? (float) $promo->original_price : ($qty ? (float) $g->gross / $qty : 0),
                // Preço médio realmente cobrado (pode diferir se o preço foi editado na venda).
                'avg_price' => $qty ? $revenue / $qty : 0,
                'qty' => $qty,
                'orders' => (int) $g->orders,
                'revenue' => $revenue,
                'cost' => $cost,
                'discount' => $discount,
                'profit' => $profit,
                'margin' => $revenue > 0 ? $profit / $revenue * 100 : 0,
                'missingCost' => (int) $g->missing_cost > 0,
                'lift' => null,
            ];
        });

        // Antes x durante (vendas/dia), uma consulta curta por promoção da tabela.
        $rows = $rows->map(fn ($row) => $row + ['pace' => $this->pace($promotions[$row['promotion_id']] ?? null)])
            ->map(function ($row) {
                $p = $row['pace'];
                $row['lift'] = $p && $p['before'] > 0 ? ($p['during'] - $p['before']) / $p['before'] * 100 : null;

                return $row;
            });

        $desc = $this->dir === 'desc';
        $rows = $rows->sortBy(fn ($r) => $r[$this->sort] ?? ($desc ? PHP_FLOAT_MIN : PHP_FLOAT_MAX), SORT_REGULAR, $desc)->values();

        $revenue = (float) $rows->sum('revenue');
        $profit = (float) $rows->sum('profit');
        $allRevenue = (float) ($all->revenue ?? 0);
        $allQty = (int) ($all->qty ?? 0);

        return [
            'summary' => [
                'qty' => (int) $rows->sum('qty'),
                'orders' => (int) $rows->sum('orders'),
                'revenue' => $revenue,
                'discount' => (float) $rows->sum('discount'),
                'cost' => (float) $rows->sum('cost'),
                'profit' => $profit,
                'margin' => $revenue > 0 ? $profit / $revenue * 100 : 0,
                'allRevenue' => $allRevenue,
                'allQty' => $allQty,
                'share' => $allRevenue > 0 ? $revenue / $allRevenue * 100 : 0,
                'shareQty' => $allQty > 0 ? $rows->sum('qty') / $allQty * 100 : 0,
                'promotions' => $rows->count(),
                'missingCost' => $rows->contains('missingCost', true),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Unidades por dia durante a promoção x no mesmo número de dias logo antes
     * dela (até 30 dias), considerando a vida inteira da promoção.
     *
     * @return array{during: float, before: float, days: int, beforeDays: int}|null
     */
    private function pace(?Promotion $promo): ?array
    {
        if (! $promo) {
            return null;
        }

        $start = ($promo->starts_at ?? $promo->created_at)?->copy();
        if (! $start || $start->isFuture()) {
            return null;
        }
        $end = $promo->ended_at ?? $promo->ends_at ?? now();
        if ($end->isFuture()) {
            $end = now();
        }

        $days = max(1, (int) ceil($start->diffInSeconds($end) / 86400));
        $beforeDays = min(30, $days);
        $beforeStart = $start->copy()->subDays($beforeDays);

        $during = (int) SaleItem::query()
            ->where('promotion_id', $promo->id)
            ->whereIn('sale_id', $this->salesQuery()->select('sales.id'))
            ->sum('quantity');

        $before = (int) SaleItem::query()
            ->where('product_id', $promo->product_id)
            ->whereNull('promotion_id')
            ->whereIn('sale_id', $this->salesQuery()->where('sales.created_at', '>=', $beforeStart)->where('sales.created_at', '<', $start)->select('sales.id'))
            ->sum('quantity');

        return [
            'during' => $during / $days,
            'before' => $before / $beforeDays,
            'days' => $days,
            'beforeDays' => $beforeDays,
        ];
    }

    public function render()
    {
        [$start, $end] = $this->period();
        $report = $this->report();

        return view('livewire.promotions.promotions-report', [
            'start' => $start,
            'end' => $end,
            'summary' => $report['summary'],
            'rows' => $report['rows'],
        ]);
    }
}
