<?php

namespace App\Services\Portal;

use App\Http\Controllers\ClientPortalController;
use App\Models\ClientQuoteRequest;
use App\Models\PortalKitExtra;
use App\Models\PortalKitType;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/**
 * Monte seu kit: o cliente escolhe o tipo (cesta, caixa, banheira…), os produtos da loja
 * e os enfeites (balão, chocolate, laço…). O pedido leva os produtos como itens normais
 * e o kit (base + enfeites + mensagem) em client_quote_requests.kit.
 */
class KitService
{
    /** Formatos de kit: [nome, emoji, ícone, cor 1, cor 2]. */
    public const STYLES = [
        'cesta'    => ['Cesta', '🧺', 'fa-basket-shopping', '#f43f5e', '#fb923c'],
        'balao'    => ['Cesta com balão', '🎈', 'fa-heart', '#e11d48', '#ec4899'],
        'banheira' => ['Banheira de vidro', '🛁', 'fa-bath', '#06b6d4', '#8b5cf6'],
        'caixa'    => ['Caixa com visor', '📦', 'fa-box-open', '#b45309', '#f59e0b'],
        'presente' => ['Caixa de presente', '🎁', 'fa-gift', '#10b981', '#3b82f6'],
        'caneca'   => ['Caneca', '☕', 'fa-mug-hot', '#7c3aed', '#ec4899'],
        'bandeja'  => ['Bandeja / café da manhã', '🥐', 'fa-mug-saucer', '#f59e0b', '#ef4444'],
        'maleta'   => ['Maleta / necessaire', '👜', 'fa-briefcase', '#0f172a', '#64748b'],
    ];

    /** Grupos de enfeites: [nome, emoji]. */
    public const GROUPS = [
        'balao'   => ['Balões', '🎈'],
        'doces'   => ['Doces e chocolates', '🍫'],
        'flores'  => ['Flores', '🌹'],
        'laco'    => ['Laços e embalagem', '🎀'],
        'cartao'  => ['Cartões', '💌'],
        'pelucia' => ['Pelúcias', '🧸'],
        'foto'    => ['Fotos', '📸'],
        'outros'  => ['Outros', '✨'],
    ];

    /** Exemplos para começar (a loja muda preço, nome e foto). */
    public const STARTER_TYPES = [
        ['Cesta romântica com balão', 'balao', 'Cesta com balão de coração, papel de seda e laço. Perfeita para namorados e aniversários.', 39.90, 3, 8],
        ['Banheira SPA', 'banheira', 'Banheira de vidro com laço, para hidratantes, sabonetes e chocolates. Um mimo de autocuidado.', 49.90, 3, 6],
        ['Caixa com visor', 'caixa', 'Caixa kraft com janela transparente e fita de cetim. Linda e prática.', 14.90, 2, 4],
        ['Cesta de presente', 'cesta', 'Cesta de vime com palha e laço, cabe de tudo um pouco.', 24.90, 3, 10],
        ['Caixa de presente', 'presente', 'Caixa rígida com tampa e laço. Ótima para presentear.', 19.90, 2, 6],
    ];

    public const STARTER_EXTRAS = [
        ['Balão coração metalizado', 'balao', 12.00, 3],
        ['Balão bubble "Eu te amo"', 'balao', 22.00, 1],
        ['Bombom Ferrero Rocher', 'doces', 4.50, 12],
        ['Caixa de bombons', 'doces', 14.90, 2],
        ['Barra de chocolate', 'doces', 8.90, 4],
        ['Rosa vermelha', 'flores', 9.90, 12],
        ['Laço de cetim', 'laco', 5.00, 2],
        ['Papel de seda e palha', 'laco', 4.00, 1],
        ['Cartão com mensagem', 'cartao', 3.00, 1],
        ['Ursinho de pelúcia', 'pelucia', 29.90, 1],
        ['Fotos polaroid (4 un.)', 'foto', 12.00, 3],
    ];

    private static array $memo = [];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_kit_types') && Schema::hasTable('portal_kit_extras');
    }

    /** A loja tem algum tipo de kit ligado (mostra o "Monte seu kit" no catálogo). */
    public static function enabled(?int $ownerId): bool
    {
        if (! $ownerId || ! self::ready()) {
            return false;
        }

        return self::$memo[$ownerId] ??= PortalKitType::where('user_id', $ownerId)->where('active', true)->exists();
    }

    public static function style(?string $key): array
    {
        return self::STYLES[$key] ?? self::STYLES['cesta'];
    }

    public static function imageUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }

    public static function types(int $ownerId, bool $onlyActive = true)
    {
        return PortalKitType::where('user_id', $ownerId)
            ->when($onlyActive, fn ($q) => $q->where('active', true))
            ->orderBy('position')->orderBy('id')->get();
    }

    public static function extras(int $ownerId, bool $onlyActive = true)
    {
        return PortalKitExtra::where('user_id', $ownerId)
            ->when($onlyActive, fn ($q) => $q->where('active', true))
            ->orderBy('position')->orderBy('id')->get();
    }

    /** Cria os exemplos que ainda não existem (pelo nome). Devolve quantos criou. */
    public static function seed(int $ownerId): int
    {
        $n = 0;
        $pos = (int) PortalKitType::where('user_id', $ownerId)->max('position');
        foreach (self::STARTER_TYPES as [$name, $style, $desc, $price, $min, $max]) {
            if (! PortalKitType::where('user_id', $ownerId)->where('name', $name)->exists()) {
                PortalKitType::create(['user_id' => $ownerId, 'name' => $name, 'style' => $style, 'description' => $desc,
                    'base_price' => $price, 'min_items' => $min, 'max_items' => $max, 'position' => ++$pos]);
                $n++;
            }
        }
        $pos = (int) PortalKitExtra::where('user_id', $ownerId)->max('position');
        foreach (self::STARTER_EXTRAS as [$name, $group, $price, $max]) {
            if (! PortalKitExtra::where('user_id', $ownerId)->where('name', $name)->exists()) {
                PortalKitExtra::create(['user_id' => $ownerId, 'name' => $name, 'group' => $group, 'price' => $price, 'max_qty' => $max, 'position' => ++$pos]);
                $n++;
            }
        }
        unset(self::$memo[$ownerId]);

        return $n;
    }

    /** Produtos que podem entrar no kit: à venda, com estoque ou sob encomenda, sem kits prontos. */
    public static function productQuery(int $ownerId)
    {
        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->whereIn('status', ['active', 'ativo'])
            ->availableForSale()
            ->where(fn ($q) => $q->whereNull('tipo')->orWhere('tipo', '!=', 'kit'))
            // Variações que só mudam o custo ("R$ 64,00", da regra do upload) não aparecem.
            ->where(fn ($q) => $q->whereNull('variation_value')->orWhere('variation_value', 'not like', 'R$%'));
    }

    /** Dados dos produtos para a tela de montar (com promoção, foto e categoria). */
    public static function products(int $ownerId): array
    {
        return self::productQuery($ownerId)
            ->with(['category', 'activePromotion', 'images'])
            ->orderBy('name')->limit(800)->get()
            ->map(function (Product $p) {
                $d = ClientPortalController::cardInfo($p);

                return ['id' => $d['id'], 'name' => $d['name'], 'price' => $d['price'], 'old' => $d['old'], 'img' => $d['img'],
                    'cat' => $p->category_id ? (int) $p->category_id : null, 'catName' => $d['cat'], 'stock' => $d['stock'], 'url' => $d['url']];
            })->values()->all();
    }

    /**
     * Confere o kit montado com os preços e limites do servidor.
     * $in: type, products[id => qtd], extras[id => qtd], message, to, from.
     * Devolve ['ok' => true, 'items' => [...], 'kit' => [...]] ou ['ok' => false, 'message' => '...'].
     */
    public static function build(int $ownerId, array $in): array
    {
        $type = PortalKitType::where('user_id', $ownerId)->where('active', true)->find((int) ($in['type'] ?? 0));
        if (! $type) {
            return ['ok' => false, 'message' => 'Escolha um tipo de kit.'];
        }

        $wanted = collect((array) ($in['products'] ?? []))->mapWithKeys(fn ($q, $id) => [(int) $id => max(0, min(20, (int) $q))])->filter();
        $cats = array_map('intval', (array) ($type->category_ids ?? []));
        $products = self::productQuery($ownerId)->with('activePromotion')->whereIn('id', $wanted->keys())->get()->keyBy('id');
        $items = [];
        foreach ($wanted as $id => $qty) {
            $p = $products[$id] ?? null;
            if (! $p || ($cats && ! in_array((int) $p->category_id, $cats, true))) {
                return ['ok' => false, 'message' => 'Um dos produtos do kit não está mais disponível. Confira a lista.'];
            }
            if (! $p->made_to_order && (int) $p->stock_quantity < $qty) {
                return ['ok' => false, 'message' => "Só temos {$p->stock_quantity} de \"{$p->name}\" agora."];
            }
            $items[] = ['product_id' => $p->id, 'name' => $p->name, 'quantity' => $qty, 'notes' => 'Kit: ' . $type->name, 'price_ref' => $p->currentSalePrice()];
        }
        $count = array_sum(array_column($items, 'quantity'));
        if ($count < $type->min_items) {
            return ['ok' => false, 'message' => "Este kit leva pelo menos {$type->min_items} " . ($type->min_items == 1 ? 'produto' : 'produtos') . '.'];
        }
        if ($count > $type->max_items) {
            return ['ok' => false, 'message' => "Este kit cabe até {$type->max_items} " . ($type->max_items == 1 ? 'produto' : 'produtos') . '.'];
        }

        $wantedExtras = collect((array) ($in['extras'] ?? []))->mapWithKeys(fn ($q, $id) => [(int) $id => max(0, (int) $q)])->filter();
        $extraRows = PortalKitExtra::where('user_id', $ownerId)->where('active', true)->whereIn('id', $wantedExtras->keys())->get()->keyBy('id');
        $extras = [];
        foreach ($wantedExtras as $id => $qty) {
            $e = $extraRows[$id] ?? null;
            if (! $e) {
                return ['ok' => false, 'message' => 'Um dos enfeites não está mais disponível. Confira a lista.'];
            }
            $extras[] = ['id' => $e->id, 'name' => $e->name, 'group' => $e->group, 'price' => (float) $e->price, 'qty' => min($qty, max(1, $e->max_qty))];
        }

        $base = (float) $type->base_price;
        $extrasTotal = round(collect($extras)->sum(fn ($e) => $e['price'] * $e['qty']), 2);

        return ['ok' => true, 'items' => $items, 'kit' => [
            'type_id' => $type->id,
            'name' => $type->name,
            'style' => $type->style,
            'base_price' => $base,
            'extras' => $extras,
            'message' => trim(mb_substr((string) ($in['message'] ?? ''), 0, 300)),
            'to' => trim(mb_substr((string) ($in['to'] ?? ''), 0, 60)),
            'from' => trim(mb_substr((string) ($in['from'] ?? ''), 0, 60)),
            'total' => round($base + $extrasTotal, 2),
        ]];
    }

    /** O que o kit soma ao pedido além dos produtos (base + enfeites). */
    public static function total(ClientQuoteRequest $quote): float
    {
        return round((float) ($quote->kit['total'] ?? 0), 2);
    }

    /** Linhas do kit para mostrar nos pedidos: [nome, qtd, valor]. */
    public static function lines(?array $kit): array
    {
        if (! $kit) {
            return [];
        }
        $lines = [[self::style($kit['style'] ?? null)[1] . ' ' . ($kit['name'] ?? 'Kit'), 1, (float) ($kit['base_price'] ?? 0)]];
        foreach ($kit['extras'] ?? [] as $e) {
            $lines[] = [(self::GROUPS[$e['group'] ?? 'outros'][1] ?? '✨') . ' ' . $e['name'], (int) $e['qty'], (float) $e['price'] * (int) $e['qty']];
        }

        return $lines;
    }
}
