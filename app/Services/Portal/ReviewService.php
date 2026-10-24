<?php

namespace App\Services\Portal;

use App\Models\Client;
use App\Models\ConsortiumNotification;
use App\Models\PortalReview;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Avaliações com estrelas do catálogo. Cada cliente do portal avalia uma vez cada produto
 * (avaliando de novo, troca a dele). Entram no ar na hora; a loja responde ou esconde.
 */
class ReviewService
{
    /** @var array<int, Collection> média e quantidade por produto principal, por loja (uma consulta por página) */
    private static array $memo = [];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_reviews');
    }

    public static function rootId(Product $p): int
    {
        return (int) ($p->parent_id ?: $p->id);
    }

    /** ['avg' => 4.6, 'n' => 12] do produto (da família toda), ou null sem avaliações. */
    public static function summary(int $ownerId, int $rootId): ?array
    {
        if (! self::ready()) {
            return null;
        }
        self::$memo[$ownerId] ??= PortalReview::where('user_id', $ownerId)->where('hidden', false)
            ->selectRaw('product_id, avg(rating) as a, count(*) as n')->groupBy('product_id')->get()
            ->mapWithKeys(fn ($r) => [(int) $r->product_id => ['avg' => round((float) $r->a, 1), 'n' => (int) $r->n]]);

        return self::$memo[$ownerId][$rootId] ?? null;
    }

    /** Quantas de cada nota (5 a 1). */
    public static function histogram(int $ownerId, int $rootId): array
    {
        $rows = PortalReview::where('user_id', $ownerId)->where('product_id', $rootId)->where('hidden', false)
            ->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating');

        return collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($s) => [$s => (int) ($rows[$s] ?? 0)])->all();
    }

    public static function list(int $ownerId, int $rootId, int $limit = 20): Collection
    {
        return PortalReview::where('user_id', $ownerId)->where('product_id', $rootId)->where('hidden', false)
            ->orderByDesc('verified')->orderByDesc('created_at')->limit($limit)->get();
    }

    /** O cliente já comprou algum produto da família? */
    public static function bought(Client $client, int $rootId): bool
    {
        $ids = Product::withoutGlobalScope('team_visibility')->where(fn ($q) => $q->where('id', $rootId)->orWhere('parent_id', $rootId))->pluck('id');

        return DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.client_id', $client->id)->whereIn('sale_items.product_id', $ids)->exists();
    }

    public static function save(Client $client, Product $product, int $rating, ?string $comment): PortalReview
    {
        $root = self::rootId($product);
        $review = PortalReview::firstOrNew(['product_id' => $root, 'client_id' => $client->id]);
        $isNew = ! $review->exists;
        $review->fill([
            'user_id'  => (int) $product->user_id,
            'name'     => PortalReview::shortName($client->name),
            'rating'   => max(1, min(5, $rating)),
            'comment'  => trim((string) $comment) ?: null,
            'verified' => self::bought($client, $root),
        ])->save();
        unset(self::$memo[(int) $product->user_id]);
        if ($isNew) {
            self::notifyOwner($review, $product);
        }

        return $review;
    }

    private static function notifyOwner(PortalReview $r, Product $product): void
    {
        try {
            $name = trim((string) ($product->parent_id ? (Product::withoutGlobalScope('team_visibility')->find($product->parent_id)?->name ?? $product->name) : $product->name));
            ConsortiumNotification::createGeneric('portal', 'portal_review', (int) $r->user_id,
                mb_substr(str_repeat('★', $r->rating) . ' Nova avaliação: ' . $name, 0, 255),
                $r->name . ($r->comment ? ': “' . mb_substr($r->comment, 0, 120) . '”' : ' deu ' . $r->rating . ' estrelas.'),
                ['entity_type' => 'product', 'entity_id' => $r->product_id, 'action_url' => route('promotions.reviews', [], false), 'priority' => $r->rating <= 2 ? 'high' : 'low', 'data' => ['review_id' => $r->id]]);
        } catch (\Throwable $e) {
            Log::warning('Aviso de avaliação não criado: ' . $e->getMessage());
        }
    }

    /**
     * Compre junto: até 2 produtos que mais saíram nas mesmas vendas deste;
     * sem histórico, os da mesma categoria com preço parecido. Só o que está à venda.
     */
    public static function boughtTogether(Product $item, int $limit = 2): Collection
    {
        $owner = (int) $item->user_id;
        $root = self::rootId($item);
        $family = Product::withoutGlobalScope('team_visibility')->where(fn ($q) => $q->where('id', $root)->orWhere('parent_id', $root))->pluck('id');
        $base = fn () => Product::withoutGlobalScope('team_visibility')->where('products.user_id', $owner)
            ->availableForSale()->whereIn('status', ['active', 'ativo'])->topOfFamily()
            ->whereNotIn('products.id', $family)->where('price_sale', '>', 0)
            ->with(['category', 'activePromotion', 'images']);

        $sales = DB::table('sale_items')->whereIn('product_id', $family)->orderByDesc('sale_id')->limit(300)->pluck('sale_id');
        $ids = $sales->isEmpty() ? collect() : DB::table('sale_items')->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereIn('sale_items.sale_id', $sales)->whereNotIn('sale_items.product_id', $family)
            ->selectRaw('COALESCE(products.parent_id, products.id) as root, count(distinct sale_items.sale_id) as n')
            ->groupBy('root')->orderByDesc('n')->limit(10)->pluck('root');
        $found = $ids->isEmpty() ? collect() : $base()->where(fn ($q) => $q->whereIn('products.id', $ids)->orWhereIn('products.parent_id', $ids))->get()
            ->unique(fn ($p) => $p->parent_id ?: $p->id)
            ->sortBy(fn ($p) => $ids->search($p->parent_id ?: $p->id))->values();
        if ($found->count() < $limit && $item->category_id) {
            $price = (float) $item->price_sale;
            $more = $base()->where('category_id', $item->category_id)->whereNotIn('products.id', $found->pluck('id'))
                ->orderByRaw('ABS(price_sale - ?)', [$price * 0.6])->limit($limit)->get();
            $found = $found->concat($more);
        }

        return $found->take($limit)->values();
    }
}
