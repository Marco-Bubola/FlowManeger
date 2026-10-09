<?php

namespace App\Services\Stock;

use App\Models\ConsortiumNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Alertas de estoque baixo/esgotado.
 *
 * - Produtos simples (inclui componentes de kit) usam stock_quantity.
 * - Kits usam availableStock() (quantos kits dá para montar).
 * - Mínimo: products.min_stock quando preenchido; senão preferences.stock.minimum do dono (padrão 3).
 * - Uma notificação por mudança de estado (tabela low_stock_alerts): re-alerta só quando
 *   o produto esgota ou 7 dias depois; o registro some quando o estoque volta acima do mínimo.
 */
class LowStockService
{
    public const WINDOW_DAYS = 30;
    public const REALERT_DAYS = 7;
    public const DEFAULT_MINIMUM = 3;

    /** Verifica todos os produtos do usuário. */
    public function check(int $userId): array
    {
        $products = Product::withoutGlobalScopes()->where('user_id', $userId)->get();

        return $this->evaluate($userId, $products);
    }

    /**
     * Verifica só alguns produtos (ex.: logo após uma venda). Inclui os componentes
     * dos kits informados e os kits que usam os produtos informados. Cada produto é
     * avaliado com o mínimo do seu dono e a notificação vai para o dono
     * ($userId é usado quando o produto não tem dono).
     */
    public function checkProducts(int $userId, array $productIds): array
    {
        $ids = collect($productIds)->map(fn ($id) => (int) $id)->filter()->unique();
        if ($ids->isEmpty()) {
            return ['alerted' => 0, 'cleared' => 0];
        }

        $components = DB::table('produto_componentes')->whereIn('kit_produto_id', $ids)->pluck('componente_produto_id');
        $ids = $ids->merge($components)->filter()->unique();
        $kits = DB::table('produto_componentes')->whereIn('componente_produto_id', $ids)->pluck('kit_produto_id');
        $ids = $ids->merge($kits)->filter()->unique()->values();

        $totals = ['alerted' => 0, 'cleared' => 0];
        Product::withoutGlobalScopes()->whereIn('id', $ids)->get()
            ->groupBy(fn ($p) => (int) ($p->user_id ?: $userId))
            ->each(function ($group, $ownerId) use (&$totals) {
                $r = $this->evaluate((int) $ownerId, $group);
                $totals['alerted'] += $r['alerted'];
                $totals['cleared'] += $r['cleared'];
            });

        return $totals;
    }

    /** Mínimo global do usuário (preferences.stock.minimum). */
    public static function userMinimum(?User $user): int
    {
        return max(0, (int) ($user?->preferences['stock']['minimum'] ?? self::DEFAULT_MINIMUM));
    }

    /** Estoque considerado: kit = quantos dá para montar; simples = stock_quantity. */
    public static function effectiveStock(Product $product): int
    {
        return ($product->tipo ?? 'simples') === 'kit'
            ? $product->availableStock()
            : max(0, (int) $product->stock_quantity);
    }

    /**
     * Unidades vendidas nos últimos N dias por produto (vendas não canceladas nem orçamento).
     * Para componentes de kit soma também o que saiu dentro de kits vendidos.
     *
     * @return array<int,int> product_id => quantidade
     */
    public static function soldUnits(array $productIds, int $days = self::WINDOW_DAYS): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (! $ids) {
            return [];
        }
        $since = now()->subDays($days);
        $excluded = ['cancelada', 'orcamento'];

        $sold = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereIn('sale_items.product_id', $chunk)
                ->where('sales.created_at', '>=', $since)
                ->where(fn ($q) => $q->whereNull('sales.status')->orWhereNotIn('sales.status', $excluded))
                ->groupBy('sale_items.product_id')
                ->selectRaw('sale_items.product_id as pid, SUM(sale_items.quantity) as qty')
                ->get()
                ->each(function ($r) use (&$sold) {
                    $sold[(int) $r->pid] = ($sold[(int) $r->pid] ?? 0) + (int) $r->qty;
                });

            DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('produto_componentes as pc', 'pc.kit_produto_id', '=', 'sale_items.product_id')
                ->whereIn('pc.componente_produto_id', $chunk)
                ->where('sales.created_at', '>=', $since)
                ->where(fn ($q) => $q->whereNull('sales.status')->orWhereNotIn('sales.status', $excluded))
                ->groupBy('pc.componente_produto_id')
                ->selectRaw('pc.componente_produto_id as pid, SUM(sale_items.quantity * pc.quantidade) as qty')
                ->get()
                ->each(function ($r) use (&$sold) {
                    $sold[(int) $r->pid] = ($sold[(int) $r->pid] ?? 0) + (int) $r->qty;
                });
        }

        return $sold;
    }

    /**
     * Média diária, dias de estoque e sugestão de reposição.
     * Sugestão = ceil(média × 30) − estoque (mín. 1); sem vendas = mínimo × 2 − estoque (mín. 1).
     */
    public static function suggestion(int $stock, int $sold30, int $minimum): array
    {
        $avg = $sold30 / self::WINDOW_DAYS;
        $daysLeft = $avg > 0 ? (int) floor($stock / $avg) : null;
        $suggested = $avg > 0
            ? max(1, (int) ceil($avg * self::WINDOW_DAYS) - $stock)
            : max(1, $minimum * 2 - $stock);

        return [
            'avg_daily' => round($avg, 2),
            'days_left' => $daysLeft,
            'suggested' => $suggested,
        ];
    }

    /** @param Collection<int,Product> $products produtos de um mesmo dono */
    protected function evaluate(int $userId, Collection $products): array
    {
        $result = ['alerted' => 0, 'cleared' => 0];
        if ($products->isEmpty()) {
            return $result;
        }

        $globalMin = self::userMinimum(User::find($userId));
        $alerts = DB::table('low_stock_alerts')
            ->where('user_id', $userId)
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $low = [];
        foreach ($products as $p) {
            $active = ($p->status === null || $p->status === 'ativo') && ! $p->is_variation_parent;
            $minimum = $p->min_stock !== null ? max(0, (int) $p->min_stock) : $globalMin;
            $stock = $active ? self::effectiveStock($p) : null;

            if (! $active || $stock > $minimum) {
                if ($alerts->has($p->id)) {
                    DB::table('low_stock_alerts')->where('id', $alerts[$p->id]->id)->delete();
                    $result['cleared']++;
                }
                continue;
            }

            $prev = $alerts->get($p->id);
            $due = ! $prev
                || ($stock <= 0 && (int) $prev->stock_at_alert > 0)
                || now()->subDays(self::REALERT_DAYS)->gte($prev->alerted_at);

            if ($due) {
                $low[] = [$p, $stock, $minimum, $prev];
            }
        }

        if (! $low) {
            return $result;
        }

        $sold = self::soldUnits(array_map(fn ($r) => $r[0]->id, $low));

        foreach ($low as [$p, $stock, $minimum, $prev]) {
            try {
                $this->notify($userId, $p, $stock, $minimum, (int) ($sold[$p->id] ?? 0));

                $now = now();
                DB::table('low_stock_alerts')->updateOrInsert(
                    ['user_id' => $userId, 'product_id' => $p->id],
                    ['alerted_at' => $now, 'stock_at_alert' => $stock, 'updated_at' => $now]
                        + ($prev ? [] : ['created_at' => $now])
                );
                $result['alerted']++;
            } catch (\Throwable $e) {
                Log::warning('Falha ao criar alerta de estoque baixo', [
                    'product_id' => $p->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    protected function notify(int $userId, Product $p, int $stock, int $minimum, int $sold30): void
    {
        $s = self::suggestion($stock, $sold30, $minimum);
        $out = $stock <= 0;
        $isKit = ($p->tipo ?? 'simples') === 'kit';
        $name = $p->name . ($p->variation_value ? ' (' . $p->variation_value . ')' : '');

        if ($out) {
            $message = "{$name}: esgotado.";
        } else {
            $pace = $s['days_left'] !== null ? "≈ {$s['days_left']} " . ($s['days_left'] === 1 ? 'dia' : 'dias') : 'sem vendas em 30 dias';
            $message = "{$name}: restam {$stock} " . ($isKit ? ($stock === 1 ? 'kit' : 'kits') : 'un.') . " ({$pace}).";
        }
        $message .= $isKit
            ? ' Sugestão: repor os componentes.'
            : " Sugestão: repor {$s['suggested']} un.";

        try {
            $url = route('gestao.restock');
        } catch (\Throwable $e) {
            $url = null;
        }

        ConsortiumNotification::createGeneric(
            'estoque',
            $out ? 'out_of_stock' : 'low_stock',
            $userId,
            $out ? 'Produto esgotado' : 'Estoque baixo',
            $message,
            [
                'priority' => $out ? 'high' : (($s['days_left'] !== null && $s['days_left'] <= 7) ? 'high' : 'medium'),
                'action_url' => $url,
                'entity_type' => 'Product',
                'entity_id' => $p->id,
                'data' => [
                    'product_id' => $p->id,
                    'product_code' => $p->product_code,
                    'is_kit' => $isKit,
                    'stock' => $stock,
                    'minimum' => $minimum,
                    'sold_30d' => $sold30,
                    'avg_daily' => $s['avg_daily'],
                    'days_left' => $s['days_left'],
                    'suggested_qty' => $s['suggested'],
                ],
            ]
        );
    }
}
