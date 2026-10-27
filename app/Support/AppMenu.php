<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Áreas e páginas do menu que a pessoa pode esconder (Configurações › Menu do app).
 * Esconde nos três lugares de navegação: menu lateral, "Mais" e "Criar" do celular.
 * As páginas continuam funcionando pelo endereço; só somem do menu.
 * Fica salvo em users.preferences.menu = { areas_off: [area], hidden: [rota] }.
 */
class AppMenu
{
    /**
     * area => [nome, ícone, degradê, classe da seção no menu lateral, prefixos (área inteira), itens]
     * item  = [rota (ou caminho), nome, ícone, caminhos extras escondidos junto (ex.: tela de criar)]
     */
    public static function areas(): array
    {
        $areas = [
            'dashboards' => ['Dashboards', 'bi-speedometer2', 'from-blue-500 to-indigo-600', 'sidebar-section-dashboard', [], [
                ['dashboard.cashbook', 'Painel financeiro', 'bi-wallet2'],
                ['dashboard.products', 'Painel de produtos', 'bi-box-seam'],
                ['dashboard.sales', 'Painel de vendas', 'bi-graph-up'],
                ['dashboard.clients', 'Painel de clientes', 'bi-people'],
                ['dashboard.banks', 'Painel de bancos', 'bi-bank'],
            ]],
            'vendas' => ['Vendas', 'bi-bag-check', 'from-violet-500 to-fuchsia-600', 'sidebar-section-vendas', [], [
                ['sales.index', 'Vendas', 'bi-cart3', ['sales/create']],
                ['clients.index', 'Clientes', 'bi-people', ['clients/create']],
                ['promotions.index', 'Promoções', 'bi-percent'],
                ['promotions.storefront', 'Vitrine do catálogo', 'bi-shop-window'],
                ['promotions.reviews', 'Avaliações', 'bi-star-half'],
                ['sales.portal-orders', 'Pedidos do portal', 'bi-bag-heart'],
                ['gestao.receivables', 'A receber', 'bi-cash-coin'],
                ['gestao.collections', 'Cobranças', 'bi-whatsapp'],
                ['gestao.profit', 'Lucro por venda', 'bi-graph-up-arrow'],
                ['gestao.channel-profit', 'Lucro real por canal', 'bi-pie-chart'],
            ]],
            'estoque' => ['Estoque', 'bi-box-seam', 'from-sky-500 to-blue-600', 'sidebar-section-estoque', [], [
                ['products.index', 'Produtos', 'bi-box-seam', ['products/create', 'products/upload']],
                ['categories.index', 'Categorias', 'bi-tags', ['categories/create']],
                ['products.kit.create', 'Criar kit', 'bi-boxes'],
                ['products.bulk-edit', 'Edição em massa', 'bi-pencil-square'],
                ['products.variation-stock', 'Estoque das variações', 'bi-diagram-3'],
                ['products.barcode-scanner', 'Scanner de barras', 'bi-upc-scan'],
                ['gestao.stock-movements', 'Movimentações', 'bi-arrow-left-right'],
                ['gestao.restock', 'Repor estoque', 'bi-arrow-repeat'],
                ['products.interested', 'Interessados', 'bi-heart'],
            ]],
            'financeiro' => ['Financeiro', 'bi-wallet2', 'from-emerald-500 to-teal-600', 'sidebar-section-financeiro', [], [
                ['banks.index', 'Bancos e faturas', 'bi-credit-card', ['banks/create', 'invoices']],
                ['cashbook.index', 'Livro caixa', 'bi-journal-text', ['cashbook/create', 'cashbook/upload']],
                ['finance.print-capture', 'Lançar por print', 'bi-phone'],
                ['cofrinhos.index', 'Cofrinhos', 'bi-piggy-bank', ['cofrinhos/create']],
                ['consortiums.index', 'Consórcios', 'bi-people-fill', ['consortiums/create']],
            ]],
            'mercadolivre' => ['Mercado Livre', 'bi-shop', 'from-yellow-400 to-amber-500', 'sidebar-section-mercadolivre', ['mercadolivre'], []],
            'shopee' => ['Shopee', 'bi-bag-heart', 'from-orange-500 to-red-500', 'sidebar-section-shopee', ['shopee'], []],
            'portal' => ['Portal do cliente', 'bi-box-arrow-up-right', 'from-sky-500 to-cyan-600', 'sidebar-portal-link', [], [
                ['portal.dashboard', 'Portal do cliente', 'bi-box-arrow-up-right'],
            ]],
            'pessoal' => ['Pessoal', 'bi-trophy', 'from-amber-500 to-orange-500', 'sidebar-section-pessoal', [], [
                ['conquistas.hub', 'Metas e hábitos', 'bi-trophy'],
                ['goals.dashboard', 'Metas e objetivos', 'bi-bullseye'],
                ['daily-habits.dashboard', 'Hábitos diários', 'bi-check2-circle'],
                ['achievements.index', 'Conquistas', 'bi-award'],
            ]],
        ];

        foreach ($areas as $key => $area) {
            $areas[$key][5] = array_values(array_filter($area[5], fn ($i) => Route::has($i[0])));
        }

        return $areas;
    }

    /** Preferências salvas: ['areas_off' => [...], 'hidden' => [...]] só com chaves que existem. */
    public static function prefs($user = null): array
    {
        $user = $user ?? auth()->user();
        $menu = $user && is_array($user->preferences) ? ($user->preferences['menu'] ?? []) : [];
        $areas = static::areas();
        $items = collect($areas)->flatMap(fn ($a) => array_column($a[5], 0))->all();

        return [
            'areas_off' => array_values(array_intersect((array) ($menu['areas_off'] ?? []), array_keys($areas))),
            'hidden' => array_values(array_intersect((array) ($menu['hidden'] ?? []), $items)),
        ];
    }

    /** Caminhos (sem a barra inicial) que somem do menu: [exatos, prefixos]. */
    public static function hiddenPaths($user = null): array
    {
        $prefs = static::prefs($user);
        $exact = [];
        $prefix = [];
        foreach (static::areas() as $key => [$name, $icon, $grad, $class, $areaPrefixes, $items]) {
            $areaOff = in_array($key, $prefs['areas_off'], true);
            if ($areaOff) {
                array_push($prefix, ...$areaPrefixes);
            }
            foreach ($items as $item) {
                if (! $areaOff && ! in_array($item[0], $prefs['hidden'], true)) {
                    continue;
                }
                $exact[] = static::path($item[0]);
                foreach ($item[3] ?? [] as $extra) {
                    $prefix[] = $extra;
                }
            }
        }

        return [array_values(array_unique(array_filter($exact))), array_values(array_unique($prefix))];
    }

    /** CSS que esconde os links do menu lateral, do "Mais", do "Criar" e da barra de baixo. */
    public static function css($user = null): string
    {
        [$exact, $prefix] = static::hiddenPaths($user);
        if (! $exact && ! $prefix) {
            return '';
        }
        $base = rtrim(url('/'), '/');
        $scopes = ['#sidebarScroll', '#mobileMoreSheet', '#mobileFabSheet', '.mobile-bottom-tabbar'];
        $sel = [];
        foreach ($scopes as $scope) {
            foreach ($exact as $p) {
                $u = addslashes($base . '/' . $p);
                $sel[] = "{$scope} a[href=\"{$u}\"]";
                $sel[] = "{$scope} a[href^=\"{$u}?\"]";
            }
            foreach ($prefix as $p) {
                $u = addslashes($base . '/' . $p);
                $sel[] = "{$scope} a[href=\"{$u}\"]";
                $sel[] = "{$scope} a[href^=\"{$u}/\"]";
                $sel[] = "{$scope} a[href^=\"{$u}?\"]";
            }
        }

        // Seções inteiras desligadas somem já no carregamento
        $prefs = static::prefs($user);
        foreach (static::areas() as $key => $area) {
            if (in_array($key, $prefs['areas_off'], true)) {
                $sel[] = '#sidebarScroll .' . $area[3];
            }
        }

        return implode(",\n", $sel) . " { display: none !important; }";
    }

    public static function hiddenCount($user = null): int
    {
        $prefs = static::prefs($user);
        $n = count($prefs['hidden']);
        foreach (static::areas() as $key => $area) {
            if (in_array($key, $prefs['areas_off'], true)) {
                $n += max(1, count($area[5]));
            }
        }

        return $n;
    }

    private static function path(string $routeOrPath): ?string
    {
        if (! Route::has($routeOrPath)) {
            return null;
        }
        try {
            $u = strtok(route($routeOrPath), '?');

            return ltrim(substr($u, strlen(rtrim(url('/'), '/'))), '/');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
