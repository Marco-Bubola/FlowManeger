<?php

namespace App\Services\Portal;

use App\Models\ClientQuoteRequest;
use App\Models\PortalCart;
use App\Models\PortalPageView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Relatório do site: conta as visitas do catálogo sem cookie (o visitante é um
 * resumo do IP + navegador + dia, sem guardar o IP; por isso já é único por dia) e monta os números da tela
 * Vitrine › Relatório do site. Robôs e a própria loja logada não contam.
 */
class SiteStats
{
    public const SOURCES = [
        'whatsapp' => ['WhatsApp', 'bi-whatsapp', '#25d366'],
        'instagram' => ['Instagram', 'bi-instagram', '#e1306c'],
        'facebook' => ['Facebook', 'bi-facebook', '#1877f2'],
        'google' => ['Google', 'bi-google', '#ea4335'],
        'tiktok' => ['TikTok', 'bi-tiktok', '#111827'],
        'outro' => ['Outros sites', 'bi-globe2', '#6366f1'],
        'direto' => ['Link direto', 'bi-link-45deg', '#64748b'],
    ];

    public const KINDS = ['inicio' => 'Página inicial', 'catalogo' => 'Busca e categorias', 'produto' => 'Páginas de produto', 'kit' => 'Monte seu kit', 'carrinho' => 'Carrinho'];

    public const PERIODS = [7 => '7 dias', 30 => '30 dias', 90 => '90 dias'];

    private const BOTS = '~bot|crawl|spider|slurp|facebookexternalhit|whatsapp/|preview|headless|lighthouse|curl|wget|python|monitor~i';

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_page_views');
    }

    public static function hit(Request $request, int $ownerId, string $kind, ?int $productId = null): void
    {
        try {
            if (! self::ready() || $request->method() !== 'GET' || $request->header('X-Livewire') || $request->ajax()) {
                return;
            }
            $ua = (string) $request->userAgent();
            if ($ua === '' || preg_match(self::BOTS, $ua) || Auth::guard('web')->id() === $ownerId) {
                return;
            }
            $day = now()->toDateString();
            PortalPageView::create([
                'user_id' => $ownerId, 'day' => $day, 'kind' => $kind, 'product_id' => $productId,
                'visitor' => substr(hash('sha256', $request->ip() . '|' . $ua . '|' . $day . '|' . config('app.key')), 0, 16),
                'client_id' => Auth::guard('portal')->id(),
                'source' => self::source($request),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // O relatório nunca derruba o catálogo.
        }
    }

    /** De onde a pessoa veio: ?utm_source= ou o site anterior; navegação dentro do site fica sem origem. */
    public static function source(Request $request): ?string
    {
        $utm = strtolower((string) $request->query('utm_source', $request->query('origem', '')));
        $ref = strtolower((string) parse_url((string) $request->headers->get('referer'), PHP_URL_HOST));
        if ($ref !== '' && $ref === strtolower($request->getHost()) && $utm === '') {
            return null;
        }
        $hay = $utm . ' ' . $ref;
        foreach (['whatsapp' => ['whatsapp', 'wa.me'], 'instagram' => ['instagram'], 'facebook' => ['facebook', 'fb.'], 'google' => ['google'], 'tiktok' => ['tiktok']] as $key => $needles) {
            foreach ($needles as $n) {
                if (str_contains($hay, $n)) {
                    return $key;
                }
            }
        }

        return trim($hay) === '' ? 'direto' : 'outro';
    }

    /** Números da tela do relatório para os últimos $days dias. */
    public static function report(int $ownerId, int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $prevFrom = (clone $from)->subDays($days);
        $base = fn () => PortalPageView::where('user_id', $ownerId);

        $views = $base()->where('day', '>=', $from->toDateString())->count();
        $visitors = $base()->where('day', '>=', $from->toDateString())->distinct()->count('visitor');
        $prevVisitors = $base()->whereBetween('day', [$prevFrom->toDateString(), (clone $from)->subDay()->toDateString()])->distinct()->count('visitor');

        // Visitantes por dia (para o gráfico de barras).
        $byDay = $base()->where('day', '>=', $from->toDateString())
            ->selectRaw("day, count(*) as v, count(distinct visitor) as u")->groupBy('day')->get()
            ->keyBy(fn ($r) => Carbon::parse($r->day)->toDateString());
        $series = [];
        for ($d = $from->copy(); $d->lte(now()); $d->addDay()) {
            $k = $d->toDateString();
            $series[] = ['day' => $k, 'label' => $d->format('d/m'), 'week' => $d->locale('pt_BR')->isoFormat('ddd'), 'views' => (int) ($byDay[$k]->v ?? 0), 'visitors' => (int) ($byDay[$k]->u ?? 0)];
        }

        $sources = $base()->where('day', '>=', $from->toDateString())->whereNotNull('source')
            ->selectRaw('source, count(distinct visitor) as n')->groupBy('source')->pluck('n', 'source')->map(fn ($n) => (int) $n)->sortDesc()->all();
        $kinds = $base()->where('day', '>=', $from->toDateString())
            ->selectRaw('kind, count(*) as n')->groupBy('kind')->pluck('n', 'kind')->map(fn ($n) => (int) $n)->all();

        // Produtos mais vistos e quantas vezes entraram em pedidos no período.
        $top = $base()->where('day', '>=', $from->toDateString())->whereNotNull('product_id')
            ->selectRaw('product_id, count(*) as n, count(distinct visitor) as u')->groupBy('product_id')->orderByDesc('n')->limit(10)->get();
        $products = Product::withoutGlobalScope('team_visibility')->where('user_id', $ownerId)->whereIn('id', $top->pluck('product_id'))->with('images')->get()->keyBy('id');

        $quotes = ClientQuoteRequest::where('user_id', $ownerId)->where('created_at', '>=', $from)->get();
        $orders = app(PortalOrderService::class);
        $ordered = [];
        foreach ($quotes as $q) {
            foreach ((array) $q->items as $i) {
                if ($pid = (int) ($i['product_id'] ?? 0)) {
                    $ordered[$pid] = ($ordered[$pid] ?? 0) + (int) ($i['quantity'] ?? 1);
                }
            }
        }
        $topRows = $top->map(fn ($r) => ($p = $products->get($r->product_id)) ? [
            'id' => $p->id, 'name' => $p->name, 'img' => $orders->photo($p), 'views' => (int) $r->n, 'visitors' => (int) $r->u,
            'ordered' => $ordered[$p->id] ?? 0, 'url' => route('portal.product', ['userId' => $ownerId, 'product' => $p->id]),
        ] : null)->filter()->values()->all();

        $confirmed = $quotes->where('status', 'approved');
        $orderCount = $quotes->count();

        $carts = class_exists(PortalCart::class) && Schema::hasTable('portal_carts')
            ? PortalCart::where('user_id', $ownerId)->where('updated_at', '>=', $from)->get() : collect();

        return [
            'views' => $views,
            'visitors' => $visitors,
            'prevVisitors' => $prevVisitors,
            'series' => $series,
            'max' => max(1, collect($series)->max('visitors')),
            'sources' => $sources,
            'kinds' => $kinds,
            'top' => $topRows,
            'orders' => $orderCount,
            'confirmed' => $confirmed->count(),
            'value' => (float) $confirmed->sum(fn ($q) => $orders->total($q, $q->sale)),
            'conversion' => $visitors > 0 ? round($orderCount / $visitors * 100, 1) : 0,
            'cartsOpen' => $carts->whereNull('recovered_at')->where('total', '>', 0)->count(),
            'cartsRecovered' => $carts->whereNotNull('recovered_at')->whereNotNull('reminded_at')->count(),
            'cartsRecoveredValue' => (float) $carts->whereNotNull('recovered_at')->whereNotNull('reminded_at')->sum('total'),
        ];
    }

    /** Apaga visitas com mais de 400 dias (roda junto com os lembretes). */
    public static function prune(): int
    {
        return self::ready() ? PortalPageView::where('day', '<', now()->subDays(400)->toDateString())->delete() : 0;
    }
}
