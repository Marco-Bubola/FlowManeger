<?php

namespace App\Services\Portal;

use App\Http\Controllers\ClientPortalController;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Vitrine do catálogo: monta banners, destaques e coleções para a página inicial
 * do portal. Produtos escolhidos são guardados pelo principal da família e
 * aparecem como no catálogo (a variação à venda); o que acabou some sozinho.
 */
class StorefrontService
{
    /** Fundos dos banners e coleções sem imagem. */
    public const COLORS = [
        'blue'   => ['Azul', 'linear-gradient(135deg,#3483fa 0%,#2968c8 100%)'],
        'violet' => ['Lilás', 'linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%)'],
        'pink'   => ['Rosa', 'linear-gradient(135deg,#ec4899 0%,#be185d 100%)'],
        'orange' => ['Laranja', 'linear-gradient(135deg,#ff7733 0%,#f23d4f 100%)'],
        'green'  => ['Verde', 'linear-gradient(135deg,#00a650 0%,#047857 100%)'],
        'gold'   => ['Dourado', 'linear-gradient(135deg,#d6a243 0%,#a16207 100%)'],
        'dark'   => ['Escuro', 'linear-gradient(135deg,#1f2937 0%,#0f172a 100%)'],
    ];

    /**
     * Modelos prontos de banner: tudo editável (textos, selo, cores, fotos).
     * c1/c2 = fundo em degradê, acc = cor do selo e do botão, ink = texto claro ou escuro.
     */
    public const TEMPLATES = [
        'oferta'   => ['name' => 'Oferta relâmpago', 'badge' => 'ATÉ 40% OFF', 'title' => 'Oferta relâmpago', 'subtitle' => 'Só hoje, enquanto durar o estoque', 'button' => 'Aproveitar', 'c1' => '#ff7733', 'c2' => '#e11d48', 'acc' => '#fff7ed', 'ink' => 'light', 'link' => 'offers'],
        'novidade' => ['name' => 'Lançamento', 'badge' => 'NOVIDADE', 'title' => 'Chegou a nova coleção', 'subtitle' => 'Conheça os lançamentos da semana', 'button' => 'Ver novidades', 'c1' => '#f5f3ff', 'c2' => '#ddd6fe', 'acc' => '#7c3aed', 'ink' => 'dark', 'link' => 'none'],
        'maes'     => ['name' => 'Dia das Mães', 'badge' => 'PRESENTES', 'title' => 'Dia das Mães', 'subtitle' => 'Kits lindos a partir de R$ 59,90', 'button' => 'Escolher presente', 'c1' => '#f9a8d4', 'c2' => '#db2777', 'acc' => '#ffffff', 'ink' => 'light', 'link' => 'none'],
        'black'    => ['name' => 'Black Friday', 'badge' => 'BLACK FRIDAY', 'title' => 'Os menores preços do ano', 'subtitle' => 'Descontos de verdade em toda a loja', 'button' => 'Ver ofertas', 'c1' => '#111111', 'c2' => '#27272a', 'acc' => '#facc15', 'ink' => 'light', 'link' => 'offers'],
        'frete'    => ['name' => 'Frete grátis', 'badge' => 'FRETE GRÁTIS', 'title' => 'Entrega grátis no seu bairro', 'subtitle' => 'Em pedidos acima de R$ 150', 'button' => 'Comprar agora', 'c1' => '#10b981', 'c2' => '#047857', 'acc' => '#ecfdf5', 'ink' => 'light', 'link' => 'none'],
        'cupom'    => ['name' => 'Cupom de desconto', 'badge' => 'BEMVINDA10', 'title' => 'Ganhe 10% na primeira compra', 'subtitle' => 'Digite o cupom no carrinho', 'button' => 'Usar cupom', 'c1' => '#6366f1', 'c2' => '#7c3aed', 'acc' => '#ffffff', 'ink' => 'light', 'link' => 'none'],
        'vitrine'  => ['name' => 'Vitrine de produtos', 'badge' => 'MAIS VENDIDOS', 'title' => 'Os queridinhos da semana', 'subtitle' => 'Os produtos que mais saíram', 'button' => 'Ver todos', 'c1' => '#ffffff', 'c2' => '#f1f5f9', 'acc' => '#3483fa', 'ink' => 'dark', 'link' => 'none'],
        'natal'    => ['name' => 'Natal', 'badge' => 'FELIZ NATAL', 'title' => 'Presentes de Natal', 'subtitle' => 'Embrulhamos para presente sem custo', 'button' => 'Ver presentes', 'c1' => '#b91c1c', 'c2' => '#7f1d1d', 'acc' => '#fde68a', 'ink' => 'light', 'link' => 'none'],
    ];

    /** Combinações de cores para os modelos: fundo início, fundo fim, selo/botão, letras. */
    public const PALETTES = [
        ['#ff7733', '#e11d48', '#fff7ed', 'light'],
        ['#3483fa', '#2968c8', '#ffffff', 'light'],
        ['#ec4899', '#be185d', '#fff1f2', 'light'],
        ['#8b5cf6', '#6d28d9', '#fde68a', 'light'],
        ['#10b981', '#047857', '#ecfdf5', 'light'],
        ['#111111', '#27272a', '#facc15', 'light'],
        ['#b91c1c', '#7f1d1d', '#fde68a', 'light'],
        ['#fdf2f8', '#fbcfe8', '#db2777', 'dark'],
        ['#fffbeb', '#fde68a', '#b45309', 'dark'],
        ['#f0f9ff', '#bae6fd', '#0369a1', 'dark'],
        ['#ffffff', '#f1f5f9', '#3483fa', 'dark'],
    ];

    public const LINK_TYPES = [
        'none'       => 'Sem link',
        'offers'     => 'Ofertas',
        'collection' => 'Uma coleção',
        'category'   => 'Uma categoria',
        'product'    => 'Um produto',
        'url'        => 'Outro endereço',
    ];

    public static function gradient(?string $color): string
    {
        return (self::COLORS[$color] ?? self::COLORS['blue'])[1];
    }

    public static function ready(): bool
    {
        static $ok;

        return $ok ??= Schema::hasTable('portal_banners') && Schema::hasTable('portal_settings');
    }

    /** Banners valendo hoje, por lugar. */
    public static function banners(int $ownerId): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        return PortalBanner::where('user_id', $ownerId)->live()->orderBy('sort')->orderBy('id')->get()->groupBy('placement');
    }

    /** Endereço para onde o banner leva (null = sem link). */
    public static function href(PortalBanner $b, int $ownerId): ?string
    {
        $v = trim((string) $b->link_value);

        return match ($b->link_type) {
            'offers'     => route('portal.catalog', ['userId' => $ownerId, 'ofertas' => 1]),
            'collection' => $v !== '' ? route('portal.catalog', ['userId' => $ownerId, 'colecao' => $v]) : null,
            'category'   => $v !== '' ? route('portal.catalog', ['userId' => $ownerId, 'category' => $v]) : null,
            'product'    => (int) $v > 0 ? route('portal.product', ['userId' => $ownerId, 'product' => (int) $v]) : null,
            'url'        => preg_match('~^https?://~i', $v) ? $v : null,
            default      => null,
        };
    }

    /** Link de fora da loja abre em outra aba. */
    public static function external(?string $href): bool
    {
        return $href && ! str_starts_with($href, url('/'));
    }

    public static function settings(int $ownerId): ?PortalSetting
    {
        return self::ready() ? PortalSetting::where('user_id', $ownerId)->first() : null;
    }

    /**
     * Produtos à venda das famílias escolhidas, na ordem escolhida
     * (uma variação por família, a mesma que o catálogo mostra).
     */
    public static function productsFor(int $ownerId, array $roots, int $limit = 24): Collection
    {
        $roots = array_values(array_unique(array_filter(array_map('intval', $roots))));
        if (! $roots) {
            return collect();
        }
        $found = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->availableForSale()
            ->whereIn('status', ['active', 'ativo'])
            ->topOfFamily()
            ->where(fn ($q) => $q->whereIn('parent_id', $roots)->orWhere(fn ($w) => $w->whereNull('parent_id')->whereIn('id', $roots)))
            ->with(['category', 'activePromotion', 'images'])
            ->get()
            ->keyBy(fn ($p) => (int) ($p->parent_id ?: $p->id));

        return collect($roots)->map(fn ($r) => $found[$r] ?? null)->filter()->take($limit)->values();
    }

    /** Coleções ligadas, com os produtos à venda (as vazias ficam de fora). */
    public static function collections(int $ownerId, bool $homeOnly = true): Collection
    {
        if (! Schema::hasTable('portal_collections')) {
            return collect();
        }

        return PortalCollection::where('user_id', $ownerId)->where('active', true)
            ->when($homeOnly, fn ($q) => $q->where('show_on_home', true))
            ->orderBy('sort')->orderBy('id')->get()
            ->map(function (PortalCollection $c) use ($ownerId) {
                $c->setRelation('items', self::productsFor($ownerId, $c->productIds(), 15));

                return $c;
            })
            ->filter(fn ($c) => $c->items->isNotEmpty())
            ->values();
    }

    public static function collectionBySlug(int $ownerId, string $slug): ?PortalCollection
    {
        if ($slug === '' || ! Schema::hasTable('portal_collections')) {
            return null;
        }

        return PortalCollection::where('user_id', $ownerId)->where('active', true)->where('slug', $slug)->first();
    }

    /** Principal da família de cada produto (o que a vitrine guarda). */
    public static function rootsOf(int $ownerId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (! $ids) {
            return [];
        }
        $map = Product::withoutGlobalScope('team_visibility')->where('user_id', $ownerId)->whereIn('id', $ids)
            ->get(['id', 'parent_id'])->mapWithKeys(fn ($p) => [$p->id => (int) ($p->parent_id ?: $p->id)]);

        return array_values(array_unique(array_filter(array_map(fn ($id) => $map[$id] ?? null, $ids))));
    }

    /** Dados do card (o mesmo do catálogo) para cada produto. */
    public static function cards(Collection $products): array
    {
        $out = [];
        foreach ($products as $p) {
            $out[$p->id] = ClientPortalController::cardInfo($p);
        }

        return $out;
    }
}
