<?php

namespace App\Services\Portal;

use App\Models\Client;
use App\Models\ClientFavorite;
use App\Models\ClientProductAlert;
use App\Models\ConsortiumNotification;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Services\Collections\CollectionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Lista de desejos do portal ("Meus favoritos") e o "Avise-me".
 *
 * Quando um favorito entra em promoção ou volta ao estoque, grava um aviso
 * pendente por cliente (client_product_alerts, sem repetir cliente + produto +
 * motivo enquanto o anterior não foi visto) e avisa a loja no sino, com o link
 * para "Clientes interessados", de onde ela manda o WhatsApp.
 *
 * Tudo aqui roda também sem usuário da loja logado (portal, rotina), então as
 * consultas a Product/Client tiram o escopo de equipe e filtram por loja.
 */
class WishlistService
{
    public const MAX_FAVORITES = 200;

    public const OWNER_NOTIFICATION_TYPE = 'wishlist_waiting';

    // ─── Favoritos do cliente ──────────────────────────────────────────────

    public static function ready(): bool
    {
        static $ready = null;

        return $ready ??= Schema::hasTable('client_favorites') && Schema::hasTable('client_product_alerts');
    }

    /** @return int[] ids dos produtos favoritados pelo cliente */
    public function favoriteIds(Client $client): array
    {
        if (! self::ready()) {
            return [];
        }

        return ClientFavorite::where('client_id', $client->id)->orderBy('id')->pluck('product_id')->map(fn ($id) => (int) $id)->all();
    }

    /** Produtos da loja do cliente entre os ids pedidos (qualquer estoque/status). */
    protected function storeProductIds(int $storeId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
        if (! $ids) {
            return [];
        }

        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $storeId)
            ->whereIn('id', array_slice($ids, 0, self::MAX_FAVORITES))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** Liga/desliga o favorito. $on null = inverte. Retorna se ficou favoritado. */
    public function toggle(Client $client, int $productId, ?bool $on = null): bool
    {
        if (! $this->storeProductIds((int) $client->user_id, [$productId])) {
            return false;
        }

        $fav = ClientFavorite::where('client_id', $client->id)->where('product_id', $productId)->first();
        $on ??= ! $fav;

        if (! $on) {
            $fav?->delete();
            ClientProductAlert::where('client_id', $client->id)->where('product_id', $productId)->whereNull('contacted_at')->delete();

            return false;
        }

        if (! $fav && ClientFavorite::where('client_id', $client->id)->count() < self::MAX_FAVORITES) {
            ClientFavorite::create(['user_id' => $client->user_id, 'client_id' => $client->id, 'product_id' => $productId]);
        }

        return true;
    }

    /** Junta os favoritos guardados no aparelho (sem login) à conta. Retorna quantos entraram. */
    public function merge(Client $client, array $ids): int
    {
        $valid = $this->storeProductIds((int) $client->user_id, $ids);
        $existing = ClientFavorite::where('client_id', $client->id)->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $room = max(0, self::MAX_FAVORITES - count($existing));
        $new = array_slice(array_values(array_diff($valid, $existing)), 0, $room);

        foreach ($new as $productId) {
            ClientFavorite::firstOrCreate(
                ['client_id' => $client->id, 'product_id' => $productId],
                ['user_id' => $client->user_id]
            );
        }

        return count($new);
    }

    public function setNotify(Client $client, int $productId, array $flags): ?ClientFavorite
    {
        $fav = ClientFavorite::where('client_id', $client->id)->where('product_id', $productId)->first();
        if (! $fav) {
            return null;
        }

        foreach (['notify_promo', 'notify_stock'] as $key) {
            if (array_key_exists($key, $flags)) {
                $fav->{$key} = (bool) $flags[$key];
            }
        }
        $fav->save();

        return $fav;
    }

    /**
     * Produtos para a página "Meus favoritos", na ordem em que foram salvos
     * (mais recente primeiro). Inclui esgotados e inativos.
     */
    public function products(int $storeId, array $ids): Collection
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! $ids) {
            return collect();
        }

        $order = array_flip(array_reverse($ids));

        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $storeId)
            ->whereIn('id', $ids)
            ->with(['activePromotion', 'images', 'category'])
            ->get()
            ->sortBy(fn ($p) => $order[$p->id] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Disponibilidade sem revelar quantidades: in | low | out | off.
     *
     * @return array{key:string, label:string}
     */
    public function availability(Product $product): array
    {
        if (! in_array($product->status, ['active', 'ativo'], true)) {
            return ['key' => 'off', 'label' => 'Indisponível no momento'];
        }

        $stock = (int) $product->stock_quantity;

        return match (true) {
            $stock <= 0 => ['key' => 'out', 'label' => 'Esgotado'],
            $stock <= 3 => ['key' => 'low', 'label' => 'Últimas unidades!'],
            default     => ['key' => 'in', 'label' => 'Em estoque'],
        };
    }

    // ─── Avisos para o cliente no portal ───────────────────────────────────

    /** Avisos que o cliente ainda não viu (mais recentes primeiro), com o produto. */
    public function unseenAlerts(Client $client): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        // Promoções agendadas que começaram sem passar pelo model (rotina horária).
        $this->sweepPromotions(clientId: $client->id);

        return ClientProductAlert::where('client_id', $client->id)
            ->unseen()
            ->with('product:id,name,variation_value,user_id')
            ->latest('id')
            ->get()
            ->filter(fn ($a) => $a->product)
            ->values();
    }

    /** Mesmo resultado para o menu, a faixa e a página na mesma requisição. */
    public function unseenAlertsForRequest(Client $client): Collection
    {
        $key = 'wishlist.unseen.' . $client->id;
        $attrs = request()->attributes;
        if (! $attrs->has($key)) {
            $attrs->set($key, $this->unseenAlerts($client));
        }

        return $attrs->get($key);
    }

    public function markSeen(Client $client): int
    {
        request()->attributes->remove('wishlist.unseen.' . $client->id);

        return ClientProductAlert::where('client_id', $client->id)->unseen()->update(['seen_at' => now()]);
    }

    // ─── Gatilhos: promoção e estoque ──────────────────────────────────────

    /** Estoque saiu de 0 para mais de 0: avisa quem pediu "volta ao estoque". */
    public function productRestocked(Product $product): int
    {
        if (! self::ready() || ! in_array($product->status, ['active', 'ativo'], true) || (int) $product->stock_quantity <= 0) {
            return 0;
        }

        $clientIds = ClientFavorite::where('product_id', $product->id)
            ->where('user_id', $product->user_id)
            ->where('notify_stock', true)
            ->pluck('client_id');

        $created = $this->createAlerts($product, $clientIds, ClientProductAlert::STOCK);
        if ($created > 0) {
            $this->notifyOwner($product, ClientProductAlert::STOCK);
        }

        return $created;
    }

    /** Promoção começou a valer: avisa quem pediu "entrar em promoção". */
    public function promotionStarted(Promotion $promotion, bool $fromSweep = false): int
    {
        if (! self::ready() || ! in_array($promotion->status, [Promotion::ATIVA, Promotion::AGENDADA], true)) {
            return 0;
        }

        $product = Product::withoutGlobalScope('team_visibility')->find($promotion->product_id);
        if (! $product || ! in_array($product->status, ['active', 'ativo'], true)) {
            return 0;
        }
        $promotion->setRelation('product', $product);
        if (! $promotion->isLive()) {
            return 0;
        }

        $clientIds = ClientFavorite::where('product_id', $product->id)
            ->where('user_id', $product->user_id)
            ->where('notify_promo', true)
            // Na varredura, quem favoritou depois que a promoção já estava no ar não é avisado.
            ->when($fromSweep && $promotion->updated_at, fn ($q) => $q->where('created_at', '<=', $promotion->updated_at))
            ->pluck('client_id');

        $created = $this->createAlerts($product, $clientIds, ClientProductAlert::PROMO, $promotion->id);
        if ($created > 0) {
            $this->notifyOwner($product, ClientProductAlert::PROMO, $promotion);
        }

        return $created;
    }

    /**
     * Promoções valendo em produtos favoritados que ainda não geraram aviso
     * (as agendadas são ligadas em massa, sem eventos do model).
     */
    public function sweepPromotions(?int $userId = null, ?int $clientId = null): int
    {
        if (! self::ready()) {
            return 0;
        }

        $favorites = ClientFavorite::query()
            ->where('notify_promo', true)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->select('product_id');

        $promotions = Promotion::query()
            ->current()
            ->whereIn('product_id', $favorites)
            ->whereNotExists(fn ($q) => $q->from('client_product_alerts')
                ->whereColumn('client_product_alerts.promotion_id', 'promotions.id')
                ->when($clientId, fn ($w) => $w->where('client_product_alerts.client_id', $clientId)))
            ->limit(50)
            ->get();

        return $promotions->sum(fn (Promotion $p) => $this->promotionStarted($p, true));
    }

    /**
     * Grava um aviso por cliente, sem repetir: um por promoção e, para o mesmo
     * cliente + produto + motivo, nenhum novo enquanto houver um não visto.
     */
    protected function createAlerts(Product $product, Collection $clientIds, string $reason, ?int $promotionId = null): int
    {
        if ($clientIds->isEmpty()) {
            return 0;
        }

        $skip = ClientProductAlert::where('product_id', $product->id)
            ->where('reason', $reason)
            ->whereIn('client_id', $clientIds)
            ->where(fn ($q) => $q->whereNull('seen_at')
                ->when($promotionId, fn ($w) => $w->orWhere('promotion_id', $promotionId)))
            ->pluck('client_id')
            ->all();

        $created = 0;
        foreach ($clientIds->unique()->diff($skip) as $clientId) {
            ClientProductAlert::create([
                'user_id'      => $product->user_id,
                'client_id'    => $clientId,
                'product_id'   => $product->id,
                'reason'       => $reason,
                'promotion_id' => $promotionId,
            ]);
            $created++;
        }

        return $created;
    }

    // ─── Loja: sino e WhatsApp ─────────────────────────────────────────────

    /** Clientes que a loja ainda não avisou, por produto. */
    public function waitingCount(int $productId): int
    {
        return ClientProductAlert::where('product_id', $productId)->waiting()->distinct()->count('client_id');
    }

    /**
     * "3 clientes estão esperando o Batom Nude — avisar no WhatsApp".
     * Um aviso não lido por produto: chegando mais clientes, o mesmo é atualizado.
     */
    public function notifyOwner(Product $product, string $reason, ?Promotion $promotion = null): ?ConsortiumNotification
    {
        try {
            $count = max(1, $this->waitingCount($product->id));
            $name = $this->productName($product);
            $title = $count === 1
                ? "1 cliente está esperando o {$name}"
                : "{$count} clientes estão esperando o {$name}";
            $what = $reason === ClientProductAlert::PROMO
                ? 'Entrou em promoção' . ($promotion ? ' (-' . $promotion->discount_percent . '%)' : '')
                : 'Voltou ao estoque';
            $message = "{$what}. Favoritaram no catálogo e pediram aviso — avisar no WhatsApp.";
            $url = route('products.interested', ['produto' => $product->id], false);

            $existing = ConsortiumNotification::where('user_id', $product->user_id)
                ->where('type', self::OWNER_NOTIFICATION_TYPE)
                ->where('entity_type', 'product')
                ->where('entity_id', $product->id)
                ->where('is_read', false)
                ->first();

            $data = ['product_id' => $product->id, 'reason' => $reason, 'waiting' => $count];

            if ($existing) {
                $existing->update(['title' => mb_substr($title, 0, 255), 'message' => $message, 'data' => $data]);

                return $existing;
            }

            return ConsortiumNotification::createGeneric('portal', self::OWNER_NOTIFICATION_TYPE, (int) $product->user_id, $title, $message, [
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'action_url'  => $url,
                'priority'    => 'medium',
                'data'        => $data,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Aviso de lista de desejos não criado: ' . $e->getMessage(), ['product_id' => $product->id]);

            return null;
        }
    }

    /** Mensagem do WhatsApp da loja para o cliente. */
    public function whatsappMessage(ClientProductAlert|ClientFavorite $row, Product $product, Client $client, ?string $storeName): string
    {
        $first = trim(explode(' ', trim((string) $client->name))[0] ?? '');
        $name = $this->productName($product);
        $promo = $product->livePromotion();
        $reason = $row instanceof ClientProductAlert ? $row->reason : ($promo ? ClientProductAlert::PROMO : ClientProductAlert::STOCK);

        $lines = [($first ? "Oi, {$first}!" : 'Oi!') . ($storeName ? " Aqui é da {$storeName}." : '')];
        if ($reason === ClientProductAlert::PROMO && $promo) {
            $lines[] = "O {$name} que você favoritou no nosso catálogo entrou em promoção: de " . $this->money((float) $promo->original_price) . ' por ' . $this->money((float) $promo->promo_price) . '.';
        } elseif ((int) $product->stock_quantity > 0) {
            $lines[] = "O {$name} que você favoritou no nosso catálogo voltou ao estoque!";
        } else {
            $lines[] = "Vi que você favoritou o {$name} no nosso catálogo. Assim que chegar eu te aviso!";
        }
        $lines[] = '';
        $lines[] = 'Veja aqui: ' . route('portal.catalog', ['userId' => $product->user_id, 'produto' => $product->id]);

        return implode("\n", $lines);
    }

    public function whatsappUrl(?string $phone, string $text): ?string
    {
        $collections = app(CollectionService::class);
        $digits = $collections->whatsappPhone($phone);

        return $digits ? $collections->whatsappUrl($digits, $text) : null;
    }

    public function productName(Product $product): string
    {
        return $product->variation_value && ! str_contains(mb_strtolower($product->name), mb_strtolower($product->variation_value))
            ? $product->name . ' ' . $product->variation_value
            : $product->name;
    }

    public function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public function storeName(int $userId): ?string
    {
        return User::whereKey($userId)->value('name');
    }
}
