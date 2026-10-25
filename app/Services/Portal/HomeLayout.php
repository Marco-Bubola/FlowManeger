<?php

namespace App\Services\Portal;

use App\Models\PortalBanner;
use App\Models\PortalSetting;
use App\Services\Promotions\CommercialCalendar;
use Illuminate\Support\Facades\Schema;

/**
 * Montagem da página inicial do catálogo (Vitrine › Página inicial): a ordem dos blocos,
 * quais aparecem, as cores do topo e o estilo dos cantos. Os temas de evento (Black Friday,
 * Natal, Dia das Mães…) preenchem tudo de uma vez; depois a loja muda o que quiser.
 * Fica em portal_settings.home: theme, order, off, colors [header1, header2, primary], style.
 */
class HomeLayout
{
    /** Blocos da página inicial que dá para mudar de lugar ou esconder. */
    public const SECTIONS = [
        'hero'        => ['Banners grandes', 'bi-images', 'O carrossel do topo.'],
        'cats'        => ['Categorias', 'bi-grid-3x3-gap', 'Os atalhos redondos das categorias.'],
        'featured'    => ['Destaques', 'bi-star', 'Os produtos escolhidos na aba Destaques.'],
        'offers'      => ['Ofertas do dia', 'bi-lightning-charge', 'Os produtos com promoção ativa.'],
        'collections' => ['Coleções e faixas', 'bi-collection', 'Uma fileira para cada coleção, com as faixas entre elas.'],
        'recent'      => ['Vistos por último', 'bi-clock-history', 'Os produtos que o cliente abriu por último.'],
    ];

    public const DEFAULT_ORDER = ['hero', 'cats', 'featured', 'offers', 'collections', 'recent'];

    /** Cantos dos cards e banners. */
    public const STYLES = [
        'padrao' => ['Padrão', '8px', 'bi-square'],
        'suave'  => ['Arredondado', '16px', 'bi-app'],
        'reto'   => ['Reto', '2px', 'bi-stop'],
    ];

    /**
     * Cores das áreas de cada tema: [fundo da página, fundo dos cards e das seções].
     * O texto (escuro ou claro) sai sozinho da cor de fundo.
     */
    public const SURFACES = [
        'padrao'     => ['#f0f0f3', '#ffffff'],
        'black'      => ['#0b0b0d', '#1c1c21'],
        'natal'      => ['#f6efe3', '#fffdf8'],
        'maes'       => ['#fcecf3', '#fffafc'],
        'namorados'  => ['#fde8ec', '#fffafb'],
        'pais'       => ['#e8edf3', '#ffffff'],
        'criancas'   => ['#fff6d6', '#ffffff'],
        'consumidor' => ['#e8f0ff', '#ffffff'],
        'cliente'    => ['#efeaff', '#ffffff'],
        'pascoa'     => ['#f5eee2', '#fffcf6'],
        'liquida'    => ['#fff0dc', '#ffffff'],
    ];

    /** Cores da logo do app: o tema "Cores da loja". */
    public const BASE = ['#ec4899', '#3b82f6', '#8b5cf6'];

    /**
     * Temas: name, emoji, header1/header2 (degradê do topo), primary (botões e links), style,
     * order (blocos), tpl (modelo do banner do evento), date (CommercialCalendar), notice
     * [beleza e outras, móveis] e featured (título dos destaques).
     */
    public const THEMES = [
        'padrao'     => ['name' => 'Cores da loja', 'emoji' => '🏪', 'colors' => self::BASE, 'style' => 'padrao', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null],
        'black'      => ['name' => 'Black Friday', 'emoji' => '🖤', 'colors' => ['#0a0a0a', '#3f3f46', '#111111'], 'style' => 'reto',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'black', 'date' => 'black',
            'notice' => ['BLACK FRIDAY: descontos em toda a loja, só até domingo', 'BLACK FRIDAY DOS MÓVEIS: preços que não voltam'], 'featured' => 'Os mais desejados da Black'],
        'natal'      => ['name' => 'Natal', 'emoji' => '🎄', 'colors' => ['#b91c1c', '#166534', '#b91c1c'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'natal', 'date' => 'natal',
            'notice' => ['Presentes de Natal com entrega antes do dia 24', 'Natal em casa nova: peças prontas para entregar antes do dia 24'], 'featured' => 'Presentes de Natal'],
        'maes'       => ['name' => 'Dia das Mães', 'emoji' => '💐', 'colors' => ['#ec4899', '#be185d', '#db2777'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'maes', 'date' => 'maes',
            'notice' => ['Presente para a mãe com embalagem caprichada', 'Dia das Mães: uma casa mais bonita para ela'], 'featured' => 'Presentes para as mães'],
        'namorados'  => ['name' => 'Dia dos Namorados', 'emoji' => '💘', 'colors' => ['#e11d48', '#9f1239', '#e11d48'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'offers', 'collections', 'cats', 'recent'], 'tpl' => 'namorados', 'date' => 'namorados',
            'notice' => ['Presente de Dia dos Namorados que encanta', 'O cantinho de vocês mais bonito no Dia dos Namorados'], 'featured' => 'Presentes para o seu amor'],
        'pais'       => ['name' => 'Dia dos Pais', 'emoji' => '👔', 'colors' => ['#1e3a8a', '#0f172a', '#1d4ed8'], 'style' => 'padrao',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'pais', 'date' => 'pais',
            'notice' => ['Presentes para o Dia dos Pais', 'Dia dos Pais: o canto dele em casa'], 'featured' => 'Presentes para os pais'],
        'criancas'   => ['name' => 'Dia das Crianças', 'emoji' => '🎈', 'colors' => ['#f59e0b', '#ec4899', '#f97316'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'criancas', 'date' => 'criancas',
            'notice' => ['Dia das Crianças com presentes e mimos', 'Dia das Crianças'], 'featured' => 'Para os pequenos'],
        'consumidor' => ['name' => 'Semana do Consumidor', 'emoji' => '🛍️', 'colors' => ['#ff7733', '#e11d48', '#ea580c'], 'style' => 'padrao',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'consumidor', 'date' => 'consumidor',
            'notice' => ['Semana do Consumidor: ofertas por tempo limitado', 'Semana do Consumidor: ofertas por tempo limitado'], 'featured' => 'Ofertas da semana'],
        'cliente'    => ['name' => 'Dia do Cliente', 'emoji' => '🤝', 'colors' => ['#0ea5e9', '#6366f1', '#4f46e5'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'cliente', 'date' => 'cliente',
            'notice' => ['Dia do Cliente: um agradecimento especial para você', 'Dia do Cliente: um agradecimento especial para você'], 'featured' => 'Escolhidos para você'],
        'pascoa'     => ['name' => 'Páscoa', 'emoji' => '🐣', 'colors' => ['#a78bfa', '#f472b6', '#8b5cf6'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'pascoa', 'date' => 'pascoa',
            'notice' => ['Páscoa cheia de mimos', 'Páscoa em família'], 'featured' => 'Mimos de Páscoa'],
        'liquida'    => ['name' => 'Liquidação', 'emoji' => '🔥', 'colors' => ['#f97316', '#dc2626', '#dc2626'], 'style' => 'reto',
            'order' => ['hero', 'offers', 'collections', 'featured', 'cats', 'recent'], 'tpl' => 'liquida', 'date' => null,
            'notice' => ['LIQUIDAÇÃO: preços baixos enquanto durar o estoque', 'QUEIMA DE ESTOQUE: peças com preço de fábrica'], 'featured' => 'Últimas peças'],
    ];

    public static function ready(): bool
    {
        return Schema::hasTable('portal_settings') && Schema::hasColumn('portal_settings', 'home');
    }

    /** Temas que combinam com o tipo de loja, com o período da data quando tiver. */
    public static function themes(?string $kind = null): array
    {
        $hidden = CommercialCalendar::HIDDEN[$kind] ?? [];
        $out = [];
        foreach (self::THEMES as $key => $t) {
            if ($t['date'] && in_array($t['date'], $hidden, true)) {
                continue;
            }
            $t['when'] = $t['date'] ? CommercialCalendar::next($t['date']) : null;
            $out[$key] = $t;
        }

        return $out;
    }

    /**
     * Prévia de um tema sem aplicar (Vitrine › Página inicial): o catálogo aberto com ?tema=
     * pelo próprio dono, logado no app, mostra tudo como vai ficar. Para os outros, nada muda.
     * Retorna [tema, com banner, com aviso] ou null.
     */
    public static function previewing(?int $ownerId): ?array
    {
        $key = (string) request()->query('tema', '');
        if (! $ownerId || $key === '' || ! isset(self::THEMES[$key]) || (int) \Illuminate\Support\Facades\Auth::guard('web')->id() !== $ownerId) {
            return null;
        }

        return [$key, request()->query('com_banner', '1') !== '0', request()->query('com_aviso', '1') !== '0'];
    }

    /** Montagem salva, completa (blocos novos entram no fim). Na prévia de tema, a do tema. */
    public static function get(?PortalSetting $s): array
    {
        $h = ($s?->home ?? []) + ['theme' => 'padrao', 'order' => [], 'off' => [], 'colors' => null, 'style' => 'padrao', 'surface' => null];
        if ($pv = self::previewing($s?->user_id ? (int) $s->user_id : null)) {
            $t = self::THEMES[$pv[0]];
            $h = ['theme' => $pv[0], 'order' => $t['order'], 'off' => [], 'colors' => $t['colors'], 'style' => $t['style'], 'surface' => self::SURFACES[$pv[0]] ?? null];
        }
        $order = array_values(array_intersect((array) $h['order'], array_keys(self::SECTIONS)));
        foreach (self::DEFAULT_ORDER as $k) {
            if (! in_array($k, $order, true)) {
                $order[] = $k;
            }
        }
        $colors = is_array($h['colors']) && count($h['colors']) === 3 ? array_values($h['colors']) : (self::THEMES[$h['theme']]['colors'] ?? self::BASE);
        $colors = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, $colors, self::BASE);
        $theme = isset(self::THEMES[$h['theme']]) ? $h['theme'] : 'padrao';
        $surface = is_array($h['surface']) && count($h['surface']) === 2 ? array_values($h['surface']) : (self::SURFACES[$theme] ?? self::SURFACES['padrao']);
        $surface = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, $surface, self::SURFACES['padrao']);

        return [
            'theme'   => $theme,
            'order'   => $order,
            'off'     => array_values(array_intersect((array) $h['off'], array_keys(self::SECTIONS))),
            'colors'  => $colors,
            'style'   => isset(self::STYLES[$h['style']]) ? $h['style'] : 'padrao',
            'surface' => $surface,
        ];
    }

    public static function save(int $ownerId, array $layout): void
    {
        $s = PortalSetting::for($ownerId);
        $s->home = array_merge($s->home ?? [], array_intersect_key($layout, array_flip(['theme', 'order', 'off', 'colors', 'style', 'surface'])));
        $s->save();
    }

    /** Cores e cantos do catálogo (portal-catalog-layout). Sem montagem salva, null = cores da logo. */
    public static function cssTheme(?int $ownerId): ?array
    {
        if (! $ownerId || ! self::ready()) {
            return null;
        }
        $s = PortalSetting::where('user_id', $ownerId)->first();
        $preview = self::previewing($ownerId);
        if (! $preview && (! $s || empty($s->home['colors']) && empty($s->home['surface']) && ($s->home['style'] ?? 'padrao') === 'padrao' && ($s->home['theme'] ?? 'padrao') === 'padrao')) {
            return null;
        }
        $h = self::get($s ?? new PortalSetting(['user_id' => $ownerId]));
        [$a, $b, $p] = $h['colors'];
        // Cards escuros com botões escuros somem: os links e botões ficam dourados.
        if (self::isDark($h['surface'][1]) && self::isDark($p)) {
            $p = '#f59e0b';
        }

        return array_merge([
            'header' => "linear-gradient(90deg, {$a} 0%, {$b} 100%)",
            'hero'   => "linear-gradient(135deg, {$a} 0%, {$b} 100%)",
            'primary'=> $p,
            'dark'   => self::shade($p, -0.18),
            'soft'   => self::shade($p, 0.88),
            'meta'   => $a,
            'radius' => self::STYLES[$h['style']][1],
        ], self::surfaceVars($h['surface'], $p));
    }

    /** Fundo, cards e as cores de texto que combinam (claro em fundo escuro). */
    public static function surfaceVars(array $surface, string $primary): array
    {
        [$bg, $card] = $surface;
        $dark = self::isDark($card);
        $darkBg = self::isDark($bg);

        return [
            'bg'        => $bg,
            'card'      => $card,
            'text'      => $dark ? '#f4f4f5' : '#333333',
            'muted'     => $dark ? '#a1a1aa' : '#737373',
            'line'      => $dark ? '#2e2e35' : self::shade($bg, -0.06),
            'page_text' => $darkBg ? '#e4e4e7' : '#555555',
            'chip'      => $dark ? self::shade($card, 0.08) : '#ffffff',
            'soft'      => $dark ? self::shade($primary, -0.62) : self::shade($primary, 0.88),
            'dark_mode' => $dark,
        ];
    }

    public static function isDark(string $hex): bool
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 128;
    }

    /** Clareia (amount > 0, até 1) ou escurece (amount < 0) uma cor. */
    public static function shade(string $hex, float $amount): string
    {
        $rgb = sscanf(ltrim($hex, '#'), '%02x%02x%02x');
        $rgb = array_map(fn ($v) => (int) round($amount >= 0 ? $v + (255 - $v) * $amount : $v * (1 + $amount)), $rgb);

        return sprintf('#%02x%02x%02x', ...array_map(fn ($v) => max(0, min(255, $v)), $rgb));
    }

    /**
     * Aplica um tema: cores, cantos, ordem dos blocos e o título dos destaques; e, se pedido,
     * cria o banner do evento (no começo do carrossel, no período da data) e troca o aviso do topo.
     */
    public static function apply(int $ownerId, string $key, string $kind, bool $banner, bool $notice): array
    {
        $t = self::THEMES[$key] ?? self::THEMES['padrao'];
        $s = PortalSetting::for($ownerId);
        $s->home = array_merge($s->home ?? [], ['theme' => $key, 'order' => $t['order'], 'off' => [], 'colors' => $t['colors'], 'style' => $t['style'], 'surface' => self::SURFACES[$key] ?? null]);
        if ($t['featured']) {
            $s->featured_title = $t['featured'];
        } elseif ($key === 'padrao') {
            $s->featured_title = null;
        }
        $done = [];
        $moveis = $kind === 'moveis';
        if ($notice && $t['notice']) {
            $s->notice_text = $t['notice'][$moveis ? 1 : 0];
            $s->notice_active = true;
            $done[] = 'aviso';
        }
        $s->save();

        if ($banner && ($data = self::bannerData($ownerId, $key, $kind))) {
            $exists = PortalBanner::where('user_id', $ownerId)->where('placement', 'hero')
                ->where('design->tpl', $t['tpl'])->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))->first();
            if ($exists) {
                $exists->update(['active' => true, 'sort' => $data['sort']]);
            } else {
                PortalBanner::create($data);
            }
            $done[] = 'banner';
        }

        return $done;
    }

    /** O banner que o tema cria (no começo do carrossel, no período da data). Também usado na prévia. */
    public static function bannerData(int $ownerId, string $key, string $kind): ?array
    {
        $t = self::THEMES[$key] ?? null;
        if (! $t || ! $t['tpl'] || ! ($tpl = StorefrontService::template($t['tpl'], $kind))) {
            return null;
        }
        $when = $t['date'] ? CommercialCalendar::next($t['date']) : null;

        return [
            'user_id'     => $ownerId,
            'placement'   => 'hero',
            'title'       => $tpl['title'],
            'subtitle'    => $tpl['subtitle'],
            'button_text' => $tpl['button'],
            'link_type'   => $tpl['link'] ?? 'none',
            'link_value'  => null,
            'sort'        => (int) PortalBanner::where('user_id', $ownerId)->where('placement', 'hero')->min('sort') - 1,
            'active'      => true,
            'starts_at'   => null,
            'ends_at'     => $when ? $when['end']->toDateString() : now()->addDays(7)->toDateString(),
            'design'      => ['tpl' => $t['tpl'], 'badge' => $tpl['badge'], 'c1' => $tpl['c1'], 'c2' => $tpl['c2'], 'acc' => $tpl['acc'], 'ink' => $tpl['ink'],
                'lay' => 'esq', 'src' => in_array($tpl['src'] ?? 'manual', ['novos', 'vendidos', 'ofertas'], true) ? $tpl['src'] : 'vendidos',
                'anim' => 'vivo', 'fmt' => StorefrontService::format($tpl['fmt'] ?? null), 'photos' => []],
        ];
    }
}
