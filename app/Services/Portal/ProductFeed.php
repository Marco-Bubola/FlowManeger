<?php

namespace App\Services\Portal;

use App\Http\Controllers\ClientPortalController;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Google e Instagram (Vitrine › Google e Instagram):
 * - mapa do site (sitemap.xml) para o Google achar o catálogo e cada produto;
 * - lista de produtos (produtos.xml, padrão RSS do Google Merchant, que a Meta também lê)
 *   para o Google Shopping e a loja do Instagram/Facebook;
 * - códigos de verificação do Google Search Console e da Meta.
 * Fica em portal_settings.home.seo.
 */
class ProductFeed
{
    public const DEFAULTS = ['google' => true, 'gverify' => '', 'mverify' => '', 'brand' => ''];

    public static function settings(?PortalSetting $s): array
    {
        $c = array_merge(self::DEFAULTS, (array) (($s?->home ?? [])['seo'] ?? []));

        return [
            'google'  => (bool) $c['google'],
            'gverify' => self::token((string) $c['gverify']),
            'mverify' => self::token((string) $c['mverify']),
            'brand'   => mb_substr(trim((string) $c['brand']), 0, 70),
        ];
    }

    public static function settingsFor(int $ownerId): array
    {
        static $memo = [];

        return $memo[$ownerId] ??= self::settings(PortalSetting::where('user_id', $ownerId)->first());
    }

    public static function save(int $ownerId, array $data): array
    {
        $s = PortalSetting::for($ownerId);
        $clean = self::settings(new PortalSetting(['home' => ['seo' => $data]]));
        $s->home = array_merge($s->home ?? [], ['seo' => $clean]);
        $s->save();
        Cache::forget("portal-feed-{$ownerId}");

        return $clean;
    }

    /**
     * O código de verificação: aceita só o código ou a linha inteira
     * (<meta name="google-site-verification" content="...">) colada da página do Google ou da Meta.
     */
    public static function token(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $raw, $m)) {
            $raw = $m[1];
        }

        return preg_match('/^[A-Za-z0-9_\-]{6,120}$/', $raw) ? $raw : '';
    }

    public static function feedUrl(int $ownerId): string
    {
        return route('portal.feed', ['userId' => $ownerId]);
    }

    public static function sitemapUrl(int $ownerId): string
    {
        return route('portal.sitemap', ['userId' => $ownerId]);
    }

    /** Produtos à venda no catálogo, com as variações (cada uma é um item no Google e na Meta). */
    private static function products(int $ownerId): Collection
    {
        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->whereIn('status', ['active', 'ativo'])
            ->availableForSale()
            ->where('price_sale', '>', 0)
            ->with(['category', 'activePromotion', 'images'])
            ->orderBy('id')
            ->limit(5000)
            ->get();
    }

    /** Endereço completo (as fotos do upload vêm como /storage/...). */
    private static function abs(?string $u): ?string
    {
        if (! $u) {
            return null;
        }

        return str_starts_with($u, 'http') ? $u : url($u);
    }

    /** Itens da lista de produtos, já no formato do Google/Meta. */
    public static function items(int $ownerId): array
    {
        $brand = self::settingsFor($ownerId)['brand'] ?: (StoreIdentity::for($ownerId)['name'] ?? '');
        $out = [];
        foreach (self::products($ownerId) as $p) {
            $d = ClientPortalController::cardInfo($p);
            $promo = $p->livePromotion();
            $desc = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $p->description)));
            $gtin = preg_replace('/\D/', '', (string) $p->barcode);
            $gtin = in_array(strlen($gtin), [8, 12, 13, 14], true) ? $gtin : '';
            $imgs = array_values(array_filter(array_map([self::class, 'abs'], $d['imgs'])));
            // A foto principal vai em JPG (a Meta não lê .webp); é a mesma da prévia do WhatsApp.
            if ($imgs && $p->image && $p->image !== 'product-placeholder.png' && ! str_starts_with((string) $p->image, 'ml_')) {
                $imgs[0] = route('portal.catalog.preview-image', ['userId' => $ownerId, 'product' => $p->id]);
            }
            $out[] = [
                'id'         => (string) $p->id,
                'group'      => $p->parent_id || $p->is_variation_parent ? (string) ($p->parent_id ?: $p->id) : null,
                'title'      => mb_substr($d['name'], 0, 150),
                'desc'       => mb_substr($desc !== '' ? $desc : $d['name'] . ($d['cat'] ? ' · ' . $d['cat'] : ''), 0, 4900),
                'has_desc'   => $desc !== '',
                'link'       => $d['url'],
                'image'      => $imgs[0] ?? null,
                'more'       => array_slice($imgs, 1, 10),
                'stock'      => $p->made_to_order ? 'preorder' : 'in_stock',
                'price'      => number_format((float) ($promo ? $promo->original_price : $p->price_sale), 2, '.', '') . ' BRL',
                'sale'       => $promo ? number_format((float) $promo->promo_price, 2, '.', '') . ' BRL' : null,
                'sale_dates' => $promo && $promo->starts_at && $promo->ends_at ? $promo->starts_at->toIso8601String() . '/' . $promo->ends_at->toIso8601String() : null,
                'brand'      => trim((string) $p->brand) ?: $brand,
                'gtin'       => $gtin,
                'mpn'        => trim((string) $p->product_code),
                'type'       => $p->category?->name,
                'condition'  => $p->condition === 'used' ? 'used' : 'new',
                'option'     => trim((string) $p->variation_value),
                'weight'     => $p->weight_grams ? (int) $p->weight_grams . ' g' : null,
            ];
        }

        return $out;
    }

    /** O que impede ou atrapalha os produtos no Google e na Meta, para a loja corrigir. */
    public static function check(int $ownerId): array
    {
        $items = self::items($ownerId);
        $noImg = array_values(array_filter($items, fn ($i) => ! $i['image']));
        $noDesc = array_values(array_filter($items, fn ($i) => ! $i['has_desc']));
        $noBrand = array_values(array_filter($items, fn ($i) => $i['brand'] === ''));

        return [
            'total'   => count($items),
            'ok'      => count(array_filter($items, fn ($i) => $i['image'] && $i['has_desc'])),
            'noImg'   => $noImg,
            'noDesc'  => $noDesc,
            'noBrand' => count($noBrand),
            'gtin'    => count(array_filter($items, fn ($i) => $i['gtin'] !== '')),
        ];
    }

    /** XML da lista de produtos (RSS 2.0 com o namespace g: do Google; a Meta aceita o mesmo arquivo). */
    public static function xml(int $ownerId): string
    {
        return Cache::remember("portal-feed-{$ownerId}", 1800, function () use ($ownerId) {
            $id = StoreIdentity::for($ownerId);
            $name = $id['name'] ?: (User::find($ownerId)?->name ?? config('app.name'));
            $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>' . "\n"
                . '<title>' . $e($name) . '</title><link>' . $e(route('portal.catalog', ['userId' => $ownerId])) . '</link>'
                . '<description>' . $e('Produtos de ' . $name) . '</description>' . "\n";
            foreach (self::items($ownerId) as $i) {
                if (! $i['image']) {
                    continue; // o Google e a Meta recusam produto sem foto
                }
                $x .= '<item><g:id>' . $e($i['id']) . '</g:id><title>' . $e($i['title']) . '</title>'
                    . '<description>' . $e($i['desc']) . '</description><link>' . $e($i['link']) . '</link>'
                    . '<g:image_link>' . $e($i['image']) . '</g:image_link>';
                foreach ($i['more'] as $m) {
                    $x .= '<g:additional_image_link>' . $e($m) . '</g:additional_image_link>';
                }
                $x .= '<g:availability>' . $i['stock'] . '</g:availability><g:price>' . $i['price'] . '</g:price>';
                if ($i['sale']) {
                    $x .= '<g:sale_price>' . $i['sale'] . '</g:sale_price>';
                    if ($i['sale_dates']) {
                        $x .= '<g:sale_price_effective_date>' . $e($i['sale_dates']) . '</g:sale_price_effective_date>';
                    }
                }
                $x .= '<g:condition>' . $i['condition'] . '</g:condition>';
                if ($i['brand'] !== '') {
                    $x .= '<g:brand>' . $e($i['brand']) . '</g:brand>';
                }
                if ($i['gtin'] !== '') {
                    $x .= '<g:gtin>' . $i['gtin'] . '</g:gtin>';
                }
                if ($i['mpn'] !== '') {
                    $x .= '<g:mpn>' . $e($i['mpn']) . '</g:mpn>';
                }
                if ($i['gtin'] === '' && ($i['brand'] === '' || $i['mpn'] === '')) {
                    $x .= '<g:identifier_exists>no</g:identifier_exists>';
                }
                if ($i['group']) {
                    $x .= '<g:item_group_id>' . $e($i['group']) . '</g:item_group_id>';
                }
                if ($i['type']) {
                    $x .= '<g:product_type>' . $e($i['type']) . '</g:product_type>';
                }
                if ($i['weight']) {
                    $x .= '<g:shipping_weight>' . $e($i['weight']) . '</g:shipping_weight>';
                }
                $x .= "</item>\n";
            }

            return $x . '</channel></rss>';
        });
    }

    /** Mapa do site da loja: catálogo, páginas da loja, kit e cada produto (com a foto). */
    public static function sitemap(int $ownerId): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        $x .= '<url><loc>' . $e(route('portal.catalog', ['userId' => $ownerId])) . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";
        foreach (array_keys(StoreIdentity::footerPages(StoreIdentity::for($ownerId))) as $page) {
            $x .= '<url><loc>' . $e(route('portal.store-page', ['userId' => $ownerId, 'page' => $page])) . '</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>' . "\n";
        }
        $products = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)->whereIn('status', ['active', 'ativo'])
            ->availableForSale()->topOfFamily()
            ->with('images')->orderByDesc('updated_at')->limit(5000)->get();
        foreach ($products as $p) {
            $x .= '<url><loc>' . $e(ClientPortalController::productUrl($p)) . '</loc>'
                . ($p->updated_at ? '<lastmod>' . $p->updated_at->toDateString() . '</lastmod>' : '')
                . '<changefreq>weekly</changefreq><priority>0.8</priority>';
            foreach (array_slice($p->all_images, 0, 3) as $img) {
                $x .= '<image:image><image:loc>' . $e(self::abs($img)) . '</image:loc></image:image>';
            }
            $x .= "</url>\n";
        }

        return $x . '</urlset>';
    }

    /** Índice geral (flowmaneger.com/sitemap.xml): o mapa de cada loja que quer aparecer no Google. */
    public static function index(): string
    {
        $owners = PortalSetting::query()->pluck('home', 'user_id')
            ->filter(fn ($h) => self::settings(new PortalSetting(['home' => $h]))['google'])
            ->keys()
            ->filter(fn ($id) => Product::withoutGlobalScope('team_visibility')->where('user_id', $id)->whereIn('status', ['active', 'ativo'])->availableForSale()->exists());
        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($owners as $id) {
            $x .= '<sitemap><loc>' . htmlspecialchars(self::sitemapUrl((int) $id), ENT_XML1) . '</loc></sitemap>' . "\n";
        }

        return $x . '</sitemapindex>';
    }

    /** Dados estruturados (schema.org Product) da página do produto: preço, estoque e nota aparecem no Google. */
    public static function jsonLd(Product $p, array $d, ?array $rating, string $storeName): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $d['name'],
            'image' => array_values(array_filter(array_map([self::class, 'abs'], array_slice($d['imgs'], 0, 5)))),
            'description' => Str::limit(trim(strip_tags((string) $p->description)) ?: $d['name'], 4900, ''),
            'sku' => (string) ($p->product_code ?: $p->id),
            'offers' => [
                '@type' => 'Offer',
                'url' => $d['url'],
                'priceCurrency' => 'BRL',
                'price' => number_format((float) $d['price'], 2, '.', ''),
                'availability' => $p->made_to_order ? 'https://schema.org/PreOrder' : ((int) $p->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'),
                'itemCondition' => $p->condition === 'used' ? 'https://schema.org/UsedCondition' : 'https://schema.org/NewCondition',
                'seller' => ['@type' => 'Organization', 'name' => $storeName],
            ],
        ];
        if (($b = trim((string) $p->brand)) !== '') {
            $data['brand'] = ['@type' => 'Brand', 'name' => $b];
        }
        $gtin = preg_replace('/\D/', '', (string) $p->barcode);
        if (in_array(strlen($gtin), [8, 12, 13, 14], true)) {
            $data['gtin'] = $gtin;
        }
        if ($rating && ($rating['n'] ?? 0) > 0) {
            $data['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $rating['avg'], 'reviewCount' => $rating['n']];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }
}
