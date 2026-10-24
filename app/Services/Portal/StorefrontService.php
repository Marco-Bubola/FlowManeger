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
     * Modelos prontos de banner: tudo editável (textos, selo, cores, fotos, layout).
     * look = estilo do desenho, deco = ícone do fundo, pics = quantas fotos, src = de onde vêm as fotos
     * (manual, novos, vendidos, ofertas: as automáticas se atualizam sozinhas), date = data comemorativa
     * (CommercialCalendar) que já preenche o período. c1/c2 = degradê, acc = selo e botão, ink = letras.
     */
    public const TEMPLATES = [
        'oferta' => ['name' => 'Oferta relâmpago', 'group' => 'ofertas', 'look' => 'base', 'deco' => 'bolt', 'pics' => 1, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'ATÉ 40% OFF', 'title' => 'Oferta relâmpago', 'subtitle' => 'Só hoje, enquanto durar o estoque', 'button' => 'Aproveitar', 'c1' => '#ff7733', 'c2' => '#e11d48', 'acc' => '#fff7ed', 'ink' => 'light'],
        'ofertas3' => ['name' => 'Ofertas da semana', 'group' => 'ofertas', 'look' => 'clean', 'deco' => 'tag', 'pics' => 3, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'OFERTAS DA SEMANA', 'title' => 'Preços que você não vai ver de novo', 'subtitle' => 'Os produtos em promoção agora', 'button' => 'Ver ofertas', 'c1' => '#fff1f2', 'c2' => '#ffe4e6', 'acc' => '#e11d48', 'ink' => 'dark'],
        'liquida' => ['name' => 'Queima de estoque', 'group' => 'ofertas', 'look' => 'stripe', 'deco' => 'percent', 'pics' => 1, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'QUEIMA DE ESTOQUE', 'title' => 'Liquidação total', 'subtitle' => 'Últimas unidades com preço baixo', 'button' => 'Ver ofertas', 'c1' => '#dc2626', 'c2' => '#7f1d1d', 'acc' => '#fde047', 'ink' => 'light'],
        'leve3' => ['name' => 'Leve 3 pague 2', 'group' => 'ofertas', 'look' => 'ticket', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'LEVE 3 PAGUE 2', 'title' => 'Monte o seu kit e economize', 'subtitle' => 'Válido nos produtos participantes', 'button' => 'Montar kit', 'c1' => '#059669', 'c2' => '#065f46', 'acc' => '#fef9c3', 'ink' => 'light'],
        'frete' => ['name' => 'Frete grátis', 'group' => 'ofertas', 'look' => 'bigicon', 'deco' => 'truck', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'FRETE GRÁTIS', 'title' => 'Entrega grátis no seu bairro', 'subtitle' => 'Em pedidos acima de R$ 150', 'button' => 'Comprar agora', 'c1' => '#10b981', 'c2' => '#047857', 'acc' => '#ecfdf5', 'ink' => 'light'],
        'cupom' => ['name' => 'Cupom de desconto', 'group' => 'ofertas', 'look' => 'ticket', 'deco' => 'tag', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'BEMVINDA10', 'title' => 'Ganhe 10% na primeira compra', 'subtitle' => 'Digite o cupom no carrinho', 'button' => 'Usar cupom', 'c1' => '#6366f1', 'c2' => '#7c3aed', 'acc' => '#ffffff', 'ink' => 'light'],
        'novidade' => ['name' => 'Lançamento', 'group' => 'produtos', 'look' => 'round', 'deco' => 'star', 'pics' => 1, 'src' => 'novos', 'date' => null, 'link' => 'news',
            'badge' => 'NOVIDADE', 'title' => 'Chegou a nova coleção', 'subtitle' => 'Conheça os lançamentos da semana', 'button' => 'Ver novidades', 'c1' => '#f5f3ff', 'c2' => '#ddd6fe', 'acc' => '#7c3aed', 'ink' => 'dark'],
        'chegaram' => ['name' => 'Acabaram de chegar', 'group' => 'produtos', 'look' => 'clean', 'deco' => 'spark', 'pics' => 3, 'src' => 'novos', 'date' => null, 'link' => 'news',
            'badge' => 'ACABOU DE CHEGAR', 'title' => 'Novidades fresquinhas', 'subtitle' => 'Os últimos produtos que entraram na loja', 'button' => 'Ver novidades', 'c1' => '#ffffff', 'c2' => '#f5f3ff', 'acc' => '#7c3aed', 'ink' => 'dark'],
        'vitrine' => ['name' => 'Mais vendidos', 'group' => 'produtos', 'look' => 'clean', 'deco' => null, 'pics' => 3, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'MAIS VENDIDOS', 'title' => 'Os queridinhos da semana', 'subtitle' => 'Os produtos que mais saíram', 'button' => 'Ver todos', 'c1' => '#ffffff', 'c2' => '#f1f5f9', 'acc' => '#3483fa', 'ink' => 'dark'],
        'top' => ['name' => 'Campeão de vendas', 'group' => 'produtos', 'look' => 'round', 'deco' => 'trophy', 'pics' => 1, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'O MAIS VENDIDO', 'title' => 'Todo mundo está levando', 'subtitle' => 'O campeão de vendas da loja', 'button' => 'Quero o meu', 'c1' => '#fff7ed', 'c2' => '#fed7aa', 'acc' => '#c2410c', 'ink' => 'dark'],
        'destaque' => ['name' => 'Produto em destaque', 'group' => 'produtos', 'look' => 'tilt', 'deco' => 'star', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'product',
            'badge' => 'DESTAQUE', 'title' => 'Escolhido especialmente para você', 'subtitle' => 'Um produto que a loja recomenda', 'button' => 'Ver produto', 'c1' => '#0f766e', 'c2' => '#134e4a', 'acc' => '#ccfbf1', 'ink' => 'light'],
        'kit' => ['name' => 'Kits para presente', 'group' => 'produtos', 'look' => 'tilt', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'KITS PRESENTE', 'title' => 'Presentes prontos e lindos', 'subtitle' => 'Embalados com carinho', 'button' => 'Ver kits', 'c1' => '#fdf4ff', 'c2' => '#f5d0fe', 'acc' => '#a21caf', 'ink' => 'dark'],
        'ano_novo' => ['name' => 'Ano Novo', 'group' => 'datas', 'look' => 'serif', 'deco' => 'spark', 'pics' => 1, 'src' => 'manual', 'date' => 'ano_novo', 'link' => 'offers',
            'badge' => 'FELIZ ANO NOVO', 'title' => 'Comece o ano renovada', 'subtitle' => 'Ofertas especiais de Réveillon', 'button' => 'Ver ofertas', 'c1' => '#0f172a', 'c2' => '#1e3a8a', 'acc' => '#fde68a', 'ink' => 'light'],
        'volta_aulas' => ['name' => 'Volta às aulas', 'group' => 'datas', 'look' => 'base', 'deco' => 'book', 'pics' => 1, 'src' => 'manual', 'date' => 'volta_aulas', 'link' => 'none',
            'badge' => 'VOLTA ÀS AULAS', 'title' => 'Tudo para começar o ano', 'subtitle' => 'Kits e presentes com desconto', 'button' => 'Ver produtos', 'c1' => '#fde047', 'c2' => '#f59e0b', 'acc' => '#1e293b', 'ink' => 'dark'],
        'carnaval' => ['name' => 'Carnaval', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'spark', 'pics' => 1, 'src' => 'manual', 'date' => 'carnaval', 'link' => 'offers',
            'badge' => 'CARNAVAL', 'title' => 'Folia com desconto', 'subtitle' => 'Ofertas até a Quarta de Cinzas', 'button' => 'Aproveitar', 'c1' => '#a855f7', 'c2' => '#ec4899', 'acc' => '#fde047', 'ink' => 'light'],
        'mulher' => ['name' => 'Dia da Mulher', 'group' => 'datas', 'look' => 'round', 'deco' => 'flower', 'pics' => 1, 'src' => 'manual', 'date' => 'mulher', 'link' => 'none',
            'badge' => '8 DE MARÇO', 'title' => 'Dia da Mulher', 'subtitle' => 'Presentes para todas elas', 'button' => 'Ver presentes', 'c1' => '#fce7f3', 'c2' => '#f9a8d4', 'acc' => '#be185d', 'ink' => 'dark'],
        'consumidor' => ['name' => 'Dia do Consumidor', 'group' => 'datas', 'look' => 'base', 'deco' => 'percent', 'pics' => 1, 'src' => 'ofertas', 'date' => 'consumidor', 'link' => 'offers',
            'badge' => 'DIA DO CONSUMIDOR', 'title' => 'Semana do Consumidor', 'subtitle' => 'Descontos especiais por tempo limitado', 'button' => 'Ver ofertas', 'c1' => '#0ea5e9', 'c2' => '#2563eb', 'acc' => '#fef08a', 'ink' => 'light'],
        'pascoa' => ['name' => 'Páscoa', 'group' => 'datas', 'look' => 'round', 'deco' => 'egg', 'pics' => 1, 'src' => 'manual', 'date' => 'pascoa', 'link' => 'none',
            'badge' => 'PÁSCOA', 'title' => 'Páscoa cheia de mimos', 'subtitle' => 'Presentes doces para toda a família', 'button' => 'Ver presentes', 'c1' => '#fef3c7', 'c2' => '#fcd34d', 'acc' => '#7c2d12', 'ink' => 'dark'],
        'maes' => ['name' => 'Dia das Mães', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'heart', 'pics' => 1, 'src' => 'manual', 'date' => 'maes', 'link' => 'none',
            'badge' => 'PRESENTES', 'title' => 'Dia das Mães', 'subtitle' => 'Kits lindos a partir de R$ 59,90', 'button' => 'Escolher presente', 'c1' => '#f9a8d4', 'c2' => '#db2777', 'acc' => '#ffffff', 'ink' => 'light'],
        'namorados' => ['name' => 'Dia dos Namorados', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'heart', 'pics' => 1, 'src' => 'manual', 'date' => 'namorados', 'link' => 'none',
            'badge' => 'DIA DOS NAMORADOS', 'title' => 'Presente para quem você ama', 'subtitle' => 'Kits a dois com desconto', 'button' => 'Ver presentes', 'c1' => '#f43f5e', 'c2' => '#9f1239', 'acc' => '#fff1f2', 'ink' => 'light'],
        'junina' => ['name' => 'Festa Junina', 'group' => 'datas', 'look' => 'base', 'deco' => 'flag', 'pics' => 1, 'src' => 'manual', 'date' => 'junina', 'link' => 'offers',
            'badge' => 'ARRAIÁ', 'title' => 'Arraiá de ofertas', 'subtitle' => 'Promoções quentinhas o mês todo', 'button' => 'Aproveitar', 'c1' => '#f97316', 'c2' => '#b45309', 'acc' => '#fef3c7', 'ink' => 'light'],
        'pais' => ['name' => 'Dia dos Pais', 'group' => 'datas', 'look' => 'base', 'deco' => 'tie', 'pics' => 1, 'src' => 'manual', 'date' => 'pais', 'link' => 'none',
            'badge' => 'DIA DOS PAIS', 'title' => 'Presentes para o paizão', 'subtitle' => 'Perfumes e kits a partir de R$ 49,90', 'button' => 'Escolher presente', 'c1' => '#1e3a8a', 'c2' => '#0f172a', 'acc' => '#93c5fd', 'ink' => 'light'],
        'cliente' => ['name' => 'Dia do Cliente', 'group' => 'datas', 'look' => 'round', 'deco' => 'star', 'pics' => 1, 'src' => 'ofertas', 'date' => 'cliente', 'link' => 'offers',
            'badge' => 'DIA DO CLIENTE', 'title' => 'Obrigada por estar com a gente', 'subtitle' => 'Ofertas de agradecimento', 'button' => 'Ver ofertas', 'c1' => '#ecfeff', 'c2' => '#a5f3fc', 'acc' => '#0e7490', 'ink' => 'dark'],
        'criancas' => ['name' => 'Dia das Crianças', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'balloon', 'pics' => 1, 'src' => 'manual', 'date' => 'criancas', 'link' => 'none',
            'badge' => 'DIA DAS CRIANÇAS', 'title' => 'Diversão com desconto', 'subtitle' => 'Presentes para a criançada', 'button' => 'Ver presentes', 'c1' => '#22d3ee', 'c2' => '#6366f1', 'acc' => '#fde047', 'ink' => 'light'],
        'black' => ['name' => 'Black Friday', 'group' => 'datas', 'look' => 'stripe', 'deco' => 'star', 'pics' => 1, 'src' => 'ofertas', 'date' => 'black', 'link' => 'offers',
            'badge' => 'BLACK FRIDAY', 'title' => 'Os menores preços do ano', 'subtitle' => 'Descontos de verdade em toda a loja', 'button' => 'Ver ofertas', 'c1' => '#111111', 'c2' => '#27272a', 'acc' => '#facc15', 'ink' => 'light'],
        'natal' => ['name' => 'Natal', 'group' => 'datas', 'look' => 'serif', 'deco' => 'snow', 'pics' => 1, 'src' => 'manual', 'date' => 'natal', 'link' => 'none',
            'badge' => 'FELIZ NATAL', 'title' => 'Presentes de Natal', 'subtitle' => 'Embrulhamos para presente sem custo', 'button' => 'Ver presentes', 'c1' => '#b91c1c', 'c2' => '#7f1d1d', 'acc' => '#fde68a', 'ink' => 'light'],
    ];

    /** Grupos da galeria de modelos. */
    public const TEMPLATE_GROUPS = [
        'datas'    => ['Datas comemorativas', 'bi-calendar-heart'],
        'produtos' => ['Produtos', 'bi-box-seam'],
        'ofertas'  => ['Ofertas e avisos', 'bi-percent'],
    ];

    /** Modelos de um grupo; os de data na ordem em que as datas chegam. */
    public static function templatesIn(string $group): Collection
    {
        $list = collect(self::TEMPLATES)->where('group', $group);
        if ($group === 'datas') {
            $list = $list->sortBy(fn ($t) => \App\Services\Promotions\CommercialCalendar::next($t['date'])['start']->timestamp);
        }

        return $list;
    }

    /** De onde vêm as fotos do modelo. */
    public const PHOTO_SOURCES = [
        'manual'   => ['Eu escolho', 'bi-hand-index'],
        'novos'    => ['Produtos novos', 'bi-stars'],
        'vendidos' => ['Mais vendidos', 'bi-trophy'],
        'ofertas'  => ['Em oferta', 'bi-percent'],
    ];

    /** Layouts do modelo. */
    public const LAYOUTS = [
        'esq'    => ['Texto à esquerda', 'bi-layout-text-sidebar-reverse'],
        'dir'    => ['Foto à esquerda', 'bi-layout-text-sidebar'],
        'centro' => ['Só texto, no centro', 'bi-text-center'],
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
        'news'       => 'Novidades',
        'bestsellers'=> 'Mais vendidos',
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
            'news'       => route('portal.catalog', ['userId' => $ownerId, 'ordem' => 'novos']),
            'bestsellers'=> route('portal.catalog', ['userId' => $ownerId, 'ordem' => 'vendidos']),
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

    /**
     * Produtos para as fotos automáticas dos modelos: os mais novos, os mais vendidos
     * (90 dias) ou os em promoção. Só os que estão à venda e têm foto; sem nenhum
     * vendido ou em oferta, ficam os mais novos.
     */
    public static function autoProducts(int $ownerId, string $src, int $n = 3): Collection
    {
        static $memo = [];
        $key = "$ownerId:$src:$n";
        if (isset($memo[$key])) {
            return $memo[$key];
        }
        $base = fn () => Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->availableForSale()
            ->whereIn('status', ['active', 'ativo'])
            ->topOfFamily()
            ->whereNotNull('image')->where('image', '!=', '')->where('image', '!=', 'product-placeholder.png');
        $since = now()->subDays(90);
        $list = match ($src) {
            'vendidos' => $base()->withSum(['saleItems as sold_qty' => fn ($q) => $q->where('created_at', '>=', $since)], 'quantity')
                ->whereHas('saleItems', fn ($q) => $q->where('created_at', '>=', $since))
                ->orderByDesc('sold_qty')->limit($n)->get(),
            'ofertas'  => $base()->whereHas('promotions', fn ($q) => $q->where('status', \App\Models\Promotion::ATIVA))
                ->orderByDesc('updated_at')->limit($n)->get(),
            default    => collect(),
        };
        if ($list->isEmpty()) {
            $list = $base()->orderByDesc('id')->limit($n)->get();
        }

        return $memo[$key] = $list->values();
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
