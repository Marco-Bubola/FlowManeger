<?php

namespace App\Services\Products;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductUploadHistory;
use App\Models\Promotion;
use App\Models\PromotionSend;
use App\Models\PromotionSetting;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Regras da área de Promoções.
 *
 * Preço "de" = R$ TABELA do extrato (products.price_original) ou a soma dos
 * componentes, para kits. Preço "por" = o revenda (price_sale) por padrão,
 * ajustável para mais ou para menos, mas nunca abaixo do lucro mínimo
 * configurado sobre o "a pagar" (price).
 */
class PromotionService
{
    public function settings(?int $userId): PromotionSetting
    {
        return PromotionSetting::forUser($userId);
    }

    /** Menor preço de promoção permitido: a pagar + lucro mínimo, arredondado para cima. */
    public function minPromoPrice(Product $product, ?PromotionSetting $settings = null): float
    {
        $settings ??= $this->settings($product->user_id);
        $min = (float) $product->price * (1 + (float) $settings->min_margin_percent / 100);

        return ceil(round($min * 100, 4)) / 100;
    }

    /** Preço "de" sugerido: tabela do produto ou, para kit, a soma dos componentes. */
    public function originalPriceFor(Product $product): ?float
    {
        if ((float) $product->price_original > 0) {
            return (float) $product->price_original;
        }

        if ($product->tipo === 'kit') {
            $total = 0.0;
            foreach ($product->componentes()->with('componente')->get() as $item) {
                $comp = $item->componente;
                if (!$comp) {
                    continue;
                }
                $unit = (float) $comp->price_original > 0 ? (float) $comp->price_original : (float) $comp->price_sale;
                $total += $unit * max(1, (int) $item->quantidade);
            }

            return $total > 0 ? round($total, 2) : null;
        }

        return null;
    }

    /**
     * Preços iniciais para pôr um produto em promoção.
     * Com tabela: de = tabela, por = revenda. Sem tabela (produtos antigos):
     * de = revenda, por = 10% abaixo. O "por" nunca fica abaixo do mínimo;
     * promo null quando não há margem para desconto.
     *
     * @return array{original: ?float, promo: ?float, min: float}
     */
    public function suggestedPrices(Product $product, ?PromotionSetting $settings = null): array
    {
        $settings ??= $this->settings($product->user_id);
        $min = $this->minPromoPrice($product, $settings);
        $sale = (float) $product->price_sale;
        $tabela = $this->originalPriceFor($product);
        $original = $tabela ?: ($sale > 0 ? $sale : null);

        if (!$original) {
            return ['original' => null, 'promo' => null, 'min' => $min];
        }

        $discount = (float) ($settings->default_discount ?? 10);
        $promo = $tabela && $sale > 0 && $sale < $tabela
            ? $sale
            : $this->withEnding(round($original * (1 - $discount / 100), 2), $min, $settings);
        $promo = max($promo, $min);

        return ['original' => $original, 'promo' => $promo < $original ? $promo : null, 'min' => $min];
    }

    /** Maior desconto (%) que ainda respeita o lucro mínimo. */
    public function maxDiscountPercent(Product $product, float $original, ?PromotionSetting $settings = null): int
    {
        if ($original <= 0) {
            return 0;
        }

        return max(0, (int) floor((1 - $this->minPromoPrice($product, $settings) / $original) * 100));
    }

    /** Preço "por" para um desconto em %, sem passar do mínimo. */
    public function priceForPercent(Product $product, float $original, float $percent, ?PromotionSetting $settings = null): float
    {
        $settings ??= $this->settings($product->user_id);
        $percent = max(0, min(99, $percent));
        $min = $this->minPromoPrice($product, $settings);

        return max($min, $this->withEnding(round($original * (1 - $percent / 100), 2), $min, $settings));
    }

    /**
     * Botões − e +: o próximo preço com 1% a mais ou a menos de desconto.
     * Com o final de preço (,90 etc.) dois percentuais podem dar o mesmo
     * preço, então anda até o preço mudar.
     */
    public function stepPrice(Product $product, float $original, float $promo, int $direction, ?PromotionSetting $settings = null): float
    {
        if ($original <= 0) {
            return $promo;
        }
        $settings ??= $this->settings($product->user_id);
        $current = $promo > 0 ? (int) round((1 - $promo / $original) * 100) : 0;
        $direction = $direction > 0 ? 1 : -1;

        for ($k = 1; $k <= 20; $k++) {
            $percent = $current + $direction * $k;
            if ($percent < 1 || $percent > 99) {
                break;
            }
            $next = $this->priceForPercent($product, $original, $percent, $settings);
            if ($direction > 0 ? $next < $promo : $next > $promo) {
                return $next;
            }
        }

        return $promo;
    }

    /** Aplica o final de preço escolhido (,90 / ,99 / inteiro), sem ficar abaixo do mínimo. */
    public function withEnding(float $price, float $min, PromotionSetting $settings): float
    {
        $ending = $settings->price_ending ?: 'none';
        if ($ending === 'none' || $price < 1) {
            return $price;
        }

        $cents = ['90' => 0.90, '99' => 0.99, '00' => 0.0][$ending] ?? null;
        if ($cents === null) {
            return $price;
        }

        $candidate = floor($price) + $cents;
        if ($candidate > $price + 0.0001) {
            $candidate -= 1;
        }
        while ($candidate < $min - 0.0001) {
            $candidate += 1;
        }

        return round($candidate, 2);
    }

    /** Devolve a mensagem de erro, ou null quando os valores podem ser salvos. */
    public function validatePrices(Product $product, float $original, float $promo, ?PromotionSetting $settings = null): ?string
    {
        $settings ??= $this->settings($product->user_id);

        if ($original <= 0) {
            return 'Informe o preço original (de).';
        }
        if ($promo <= 0) {
            return 'Informe o preço da promoção (por).';
        }
        if ($promo >= $original) {
            return 'O preço da promoção precisa ser menor que o original.';
        }

        $min = $this->minPromoPrice($product, $settings);
        if ($promo < $min) {
            return sprintf(
                'O preço mínimo para %s é %s (a pagar %s + %s%% de lucro).',
                $product->name,
                $this->money($min),
                $this->money((float) $product->price),
                rtrim(rtrim(number_format((float) $settings->min_margin_percent, 2, ',', ''), '0'), ',')
            );
        }

        return null;
    }

    /**
     * Põe o produto em promoção. Uma promoção ativa ou agendada que já
     * existir para o produto é encerrada como "substituída".
     *
     * @param array{original_price:float, promo_price:float, starts_at?:mixed, ends_at?:mixed, message?:?string, source?:string} $data
     */
    public function start(Product $product, array $data): Promotion
    {
        $settings = $this->settings($product->user_id);
        $original = round((float) $data['original_price'], 2);
        $promo = round((float) $data['promo_price'], 2);

        if ($error = $this->validatePrices($product, $original, $promo, $settings)) {
            throw new InvalidArgumentException($error);
        }

        $startsAt = $this->date($data['starts_at'] ?? null);
        $endsAt = $this->date($data['ends_at'] ?? null, true);
        $this->assertDates($startsAt, $endsAt);

        return DB::transaction(function () use ($product, $data, $original, $promo, $startsAt, $endsAt) {
            Promotion::where('product_id', $product->id)
                ->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA])
                ->get()
                ->each(fn (Promotion $p) => $this->end($p, 'substituida'));

            // O "de" vira o preço de tabela, menos quando é só o revenda (produto
            // sem tabela), para não confundir as sugestões depois.
            if (round((float) $product->price_original, 2) !== $original && $original !== round((float) $product->price_sale, 2)) {
                $product->forceFill(['price_original' => $original])->save();
            }

            return Promotion::create([
                'user_id'        => $product->user_id,
                'product_id'     => $product->id,
                'original_price' => $original,
                'promo_price'    => $promo,
                'starts_at'      => $startsAt,
                'ends_at'        => $endsAt,
                'status'         => $startsAt && $startsAt->isFuture() ? Promotion::AGENDADA : Promotion::ATIVA,
                'message'        => $this->cleanMessage($data['message'] ?? null),
                'source'         => $data['source'] ?? 'manual',
            ]);
        });
    }

    /** Edita valores, datas ou mensagem de uma promoção ativa ou agendada. */
    public function update(Promotion $promotion, array $data): Promotion
    {
        $product = $promotion->product;
        $original = round((float) ($data['original_price'] ?? $promotion->original_price), 2);
        $promo = round((float) ($data['promo_price'] ?? $promotion->promo_price), 2);

        if ($error = $this->validatePrices($product, $original, $promo)) {
            throw new InvalidArgumentException($error);
        }

        $startsAt = array_key_exists('starts_at', $data) ? $this->date($data['starts_at']) : $promotion->starts_at;
        $endsAt = array_key_exists('ends_at', $data) ? $this->date($data['ends_at'], true) : $promotion->ends_at;
        $this->assertDates($startsAt, $endsAt);

        DB::transaction(function () use ($promotion, $product, $data, $original, $promo, $startsAt, $endsAt) {
            $promotion->fill([
                'original_price' => $original,
                'promo_price'    => $promo,
                'starts_at'      => $startsAt,
                'ends_at'        => $endsAt,
                'status'         => $startsAt && $startsAt->isFuture() ? Promotion::AGENDADA : Promotion::ATIVA,
            ]);
            if (array_key_exists('message', $data)) {
                $promotion->message = $this->cleanMessage($data['message']);
            }
            $promotion->save();

            // O "de" vira o preço de tabela, menos quando é só o revenda (produto
            // sem tabela), para não confundir as sugestões depois.
            if (round((float) $product->price_original, 2) !== $original && $original !== round((float) $product->price_sale, 2)) {
                $product->forceFill(['price_original' => $original])->save();
            }
        });

        return $promotion->refresh();
    }

    /** Retira a promoção. O produto volta a ser vendido pelo price_sale. */
    public function end(Promotion $promotion, string $reason = 'manual'): void
    {
        if ($promotion->status === Promotion::ENCERRADA) {
            return;
        }

        $promotion->forceFill([
            'status'       => Promotion::ENCERRADA,
            'ended_reason' => $reason,
            'ended_at'     => now(),
        ])->save();
    }

    /**
     * Liga as agendadas que chegaram na data e encerra as que venceram ou
     * ficaram sem estoque. Roda de hora em hora e ao abrir a página.
     *
     * @return array{started:int, expired:int, out_of_stock:int}
     */
    public function refreshStatuses(?int $userId = null): array
    {
        $scope = fn (Builder $q) => $userId ? $q->where('user_id', $userId) : $q;

        $started = $scope(Promotion::query())
            ->where('status', Promotion::AGENDADA)
            ->where('starts_at', '<=', now())
            ->update(['status' => Promotion::ATIVA, 'updated_at' => now()]);

        $expired = $scope(Promotion::query())
            ->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update(['status' => Promotion::ENCERRADA, 'ended_reason' => 'vencida', 'ended_at' => now(), 'updated_at' => now()]);

        // Quem desligou "encerrar ao zerar o estoque" fica de fora.
        $keepOpen = Schema::hasColumn('promotion_settings', 'auto_end_out_of_stock')
            ? PromotionSetting::where('auto_end_out_of_stock', false)->pluck('user_id')
            : collect();
        $outOfStock = $scope(Promotion::query())
            ->where('status', Promotion::ATIVA)
            ->when($keepOpen->isNotEmpty(), fn ($q) => $q->whereNotIn('user_id', $keepOpen))
            ->whereHas('product', fn ($p) => $p->withoutGlobalScopes()->where('stock_quantity', '<=', 0))
            ->update(['status' => Promotion::ENCERRADA, 'ended_reason' => 'sem_estoque', 'ended_at' => now(), 'updated_at' => now()]);

        return ['started' => $started, 'expired' => $expired, 'out_of_stock' => $outOfStock];
    }

    /**
     * Produtos com estoque cujo preço de tabela dá desconto acima do mínimo
     * configurado e que ainda não estão em promoção, do maior desconto ao menor.
     */
    public function suggestionsQuery(int $userId, ?PromotionSetting $settings = null): Builder
    {
        $settings ??= $this->settings($userId);
        $minFactor = 1 - ((float) $settings->suggest_min_discount / 100);

        return Product::query()
            ->where('products.user_id', $userId)
            ->where('stock_quantity', '>', 0)
            ->where('status', 'ativo')
            ->where('price_original', '>', 0)
            ->where('price_sale', '>', 0)
            ->whereRaw('price_sale <= price_original * ?', [$minFactor])
            ->whereDoesntHave('promotions', fn ($q) => $q->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA]))
            ->orderByRaw('(price_sale / price_original) asc');
    }

    /** Tipos de sugestão: chave => [nome, ícone, explicação]. */
    public const SUGGESTION_TYPES = [
        'tabela'   => ['Desconto de tabela', 'bi-receipt', 'Revenda bem abaixo do preço de tabela'],
        'antigos'  => ['Mais antigos', 'bi-hourglass-bottom', 'Há mais tempo no estoque'],
        'parados'  => ['Parados', 'bi-pause-circle', 'Sem venda há 60 dias ou mais'],
        'estoque'  => ['Mais estoque', 'bi-boxes', 'Maior quantidade em estoque'],
        'margem'   => ['Mais margem', 'bi-graph-up-arrow', 'Mais espaço para dar desconto'],
        'vendidos' => ['Mais vendidos', 'bi-trophy', 'Os que mais saíram nos últimos 90 dias'],
    ];

    /**
     * Top N produtos com estoque e sem promoção para um tipo de sugestão.
     * Cada produto vem com suggestion_note (o motivo) e suggestion_prices.
     */
    public function suggestionsByType(int $userId, string $type, int $limit = 10, ?PromotionSetting $settings = null, string $term = ''): Collection
    {
        $settings ??= $this->settings($userId);
        if ($type === 'tabela') {
            $query = $this->suggestionsQuery($userId, $settings);
        } else {
            $query = Product::query()
                ->where('products.user_id', $userId)
                ->where('stock_quantity', '>', 0)
                ->where('status', 'ativo')
                ->where('price_sale', '>', 0)
                ->whereDoesntHave('promotions', fn ($q) => $q->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA]));
        }
        $query->with('category')->withCount('variants')
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")));

        $since60 = now()->subDays(60);
        $since90 = now()->subDays(90);
        $margin = 1 + (float) $settings->min_margin_percent / 100;

        match ($type) {
            'antigos'  => $query->orderBy('created_at')->orderBy('id'),
            'parados'  => $query->whereDoesntHave('saleItems', fn ($q) => $q->where('created_at', '>=', $since60))
                                ->withMax('saleItems as last_sold_at', 'created_at')
                                ->orderByRaw('last_sold_at IS NOT NULL')->orderBy('last_sold_at')->orderBy('created_at'),
            'estoque'  => $query->orderByDesc('stock_quantity'),
            'margem'   => $query->orderByRaw('(price_sale - price * ?) desc', [$margin]),
            'vendidos' => $query->withSum(['saleItems as sold_qty' => fn ($q) => $q->where('created_at', '>=', $since90)], 'quantity')
                                ->whereHas('saleItems', fn ($q) => $q->where('created_at', '>=', $since90))
                                ->orderByDesc('sold_qty'),
            default    => null,
        };

        return $query->limit($limit)->get()->each(function (Product $p) use ($type, $settings) {
            $prices = $this->suggestedPrices($p, $settings);
            $p->setAttribute('suggestion_prices', $prices);
            $p->setAttribute('suggestion_note', match ($type) {
                'antigos'  => 'no estoque desde ' . $p->created_at?->format('d/m/Y'),
                'parados'  => $p->last_sold_at ? 'última venda ' . Carbon::parse($p->last_sold_at)->format('d/m/Y') : 'nunca vendido',
                'estoque'  => $p->stock_quantity . ' em estoque',
                'margem'   => 'pode baixar até ' . $this->money(max(0, (float) $p->price_sale - $this->minPromoPrice($p, $settings))),
                'vendidos' => (int) $p->sold_qty . ' vendidos em 90 dias',
                default    => 'tabela ' . $this->money((float) $p->price_original),
            });
        });
    }

    /** Mensagem de WhatsApp de uma promoção, com o modelo e o rodapé do usuário. */
    public function message(Promotion $promotion, ?Client $client = null, ?PromotionSetting $settings = null): string
    {
        $settings ??= $this->settings($promotion->user_id);
        $template = trim((string) $promotion->message) !== '' ? $promotion->message : $settings->template();

        $body = $this->fill($template, $promotion, $client);
        if ($client && ($settings->greet_client ?? true) && !str_contains($template, '{cliente}')) {
            $body = 'Oi, ' . $this->firstName($client) . "! 💜\n" . $body;
        }

        return $this->withFooter($body, $promotion->user_id, $settings, $client);
    }

    /** "Ofertas da semana": várias promoções numa mensagem só. */
    public function offersMessage(Collection $promotions, ?Client $client = null, ?PromotionSetting $settings = null): string
    {
        $first = $promotions->first();
        if (!$first) {
            return '';
        }
        $settings ??= $this->settings($first->user_id);

        $lines = [];
        $lines[] = $client ? 'Oi, ' . $this->firstName($client) . '! 💜' : '';
        $lines[] = '🔥 *OFERTAS DA SEMANA* 🔥';
        $lines[] = '';
        foreach ($promotions as $promo) {
            $lines[] = '• *' . $this->productName($promo) . '*';
            $lines[] = '  ~' . $this->money((float) $promo->original_price) . '~ por *'
                . $this->money((float) $promo->promo_price) . '* (' . $promo->discount_percent . '% OFF)';
        }
        $lines[] = '';
        $lines[] = 'Enquanto durar o estoque. Me chama para garantir o seu!';

        $body = trim(implode("\n", $lines));

        return $this->withFooter($body, $first->user_id, $settings, $client);
    }

    /** Link wa.me com o texto pronto (e o número do cliente, quando houver). */
    public function whatsappUrl(string $text, ?Client $client = null): string
    {
        $phone = $client ? $this->whatsappPhone($client->phone) : '';

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }

    public function whatsappPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        return strlen($digits) <= 11 ? '55' . $digits : $digits;
    }

    /**
     * Clientes que já compraram o produto (ou outro da mesma família/código)
     * ou um produto da mesma categoria, com telefone, para sugerir o envio.
     *
     * @return Collection<int, array{client:Client, reason:string, last_send:?PromotionSend}>
     */
    public function interestedClients(Promotion $promotion, int $limit = 12): Collection
    {
        $product = $promotion->product;
        $userId = $promotion->user_id;

        $byProduct = Client::query()
            ->where('clients.user_id', $userId)
            ->whereNotNull('phone')->where('phone', '!=', '')
            ->whereHas('sales.saleItems.product', fn ($q) => $q->where('product_code', $product->product_code))
            ->limit($limit)->get();

        $byCategory = collect();
        if ($byProduct->count() < $limit && $product->category_id) {
            $byCategory = Client::query()
                ->where('clients.user_id', $userId)
                ->whereNotNull('phone')->where('phone', '!=', '')
                ->whereNotIn('id', $byProduct->pluck('id'))
                ->whereHas('sales.saleItems.product', fn ($q) => $q->where('category_id', $product->category_id))
                ->limit($limit - $byProduct->count())->get();
        }

        $sends = PromotionSend::where('promotion_id', $promotion->id)
            ->whereNotNull('client_id')
            ->latest()->get()->groupBy('client_id');

        return $byProduct->map(fn ($c) => ['client' => $c, 'reason' => 'Já comprou este produto'])
            ->concat($byCategory->map(fn ($c) => ['client' => $c, 'reason' => 'Compra a mesma categoria']))
            ->map(fn ($row) => $row + ['last_send' => $sends->get($row['client']->id)?->first()])
            ->values();
    }

    public function recordSend(int $userId, array $promotionIds, ?int $clientId, string $channel, string $kind = 'produto'): PromotionSend
    {
        return PromotionSend::create([
            'user_id'       => $userId,
            'promotion_id'  => count($promotionIds) === 1 ? $promotionIds[0] : null,
            'client_id'     => $clientId,
            'kind'          => $kind,
            'channel'       => $channel,
            'promotion_ids' => array_values($promotionIds),
        ]);
    }

    /** Resumo das vendas feitas em promoção (itens com promotion_id). */
    public function salesSummary(int $userId): array
    {
        $row = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.user_id', $userId)
            ->whereNotNull('sale_items.promotion_id')
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as qty')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.price_sale), 0) as revenue')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * (sale_items.original_price - sale_items.price_sale)), 0) as savings')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * (sale_items.price_sale - sale_items.price)), 0) as profit')
            ->first();

        return [
            'qty'     => (int) ($row->qty ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
            'savings' => (float) ($row->savings ?? 0),
            'profit'  => (float) ($row->profit ?? 0),
        ];
    }

    /**
     * Relê os PDFs de uploads antigos e preenche o preço de tabela dos
     * produtos que ainda não têm. Não mexe em estoque nem em outros preços.
     *
     * @return array{files:int, read:int, updated:int}
     */
    public function backfillOriginalPrices(int $userId, OrderPdfParser $parser): array
    {
        $uploads = ProductUploadHistory::where('user_id', $userId)
            ->whereNotNull('file_path')
            ->where('file_type', 'pdf')
            ->orderBy('created_at')
            ->get();

        $byCode = [];
        $files = 0;
        foreach ($uploads as $upload) {
            if (!Storage::disk('public')->exists($upload->file_path)) {
                continue;
            }
            try {
                $rows = $parser->parseFile(Storage::disk('public')->path($upload->file_path));
            } catch (\Throwable $e) {
                Log::warning('Não foi possível reler PDF para preço de tabela', ['upload' => $upload->id, 'error' => $e->getMessage()]);
                continue;
            }
            $files++;
            foreach ($rows as $row) {
                if ((float) ($row['price_original'] ?? 0) > 0) {
                    // Uploads mais novos sobrescrevem os antigos.
                    $byCode[$row['product_code']] = (float) $row['price_original'];
                }
            }
        }

        $updated = 0;
        foreach (array_chunk(array_keys($byCode), 200, true) as $codes) {
            Product::where('user_id', $userId)
                ->whereIn('product_code', $codes)
                ->where(fn ($q) => $q->whereNull('price_original')->orWhere('price_original', '<=', 0))
                ->get()
                ->each(function (Product $p) use ($byCode, &$updated) {
                    $p->forceFill(['price_original' => $byCode[$p->product_code]])->save();
                    $updated++;
                });
        }

        return ['files' => $files, 'read' => count($byCode), 'updated' => $updated];
    }

    public function catalogLink(int $userId): string
    {
        return route('portal.catalog', ['userId' => $userId]);
    }

    public function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    // ─────────────────────────────────────────────────────────────

    private function fill(string $template, Promotion $promotion, ?Client $client): string
    {
        $stock = (int) ($promotion->product?->stock_quantity ?? 0);
        $validity = $promotion->ends_at
            ? 'Válido até ' . $promotion->ends_at->format('d/m') . ' ou enquanto durar o estoque (' . $stock . ' un.)'
            : 'Enquanto durar o estoque (' . $stock . ' un.)';

        $text = strtr($template, [
            '{nome}'     => $this->productName($promotion),
            '{de}'       => $this->money((float) $promotion->original_price),
            '{por}'      => $this->money((float) $promotion->promo_price),
            '{desconto}' => $promotion->discount_percent . '%',
            '{economia}' => $this->money($promotion->savings),
            '{validade}' => $validity,
            '{estoque}'  => (string) $stock,
            '{cliente}'  => $client ? $this->firstName($client) : '',
            '{link}'     => $this->catalogLink($promotion->user_id),
        ]);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    private function withFooter(string $body, int $userId, PromotionSetting $settings, ?Client $client): string
    {
        $footer = [];
        if (trim((string) $settings->footer) !== '') {
            $footer[] = trim($settings->footer);
        }
        if ($settings->footer_catalog_link && !str_contains($body, $this->catalogLink($userId))) {
            $footer[] = 'Veja mais no catálogo: ' . $this->catalogLink($userId);
        }

        return $footer ? $body . "\n\n" . implode("\n", $footer) : $body;
    }

    private function productName(Promotion $promotion): string
    {
        return mb_convert_case(mb_strtolower((string) $promotion->product?->name), MB_CASE_TITLE, 'UTF-8');
    }

    private function firstName(Client $client): string
    {
        return mb_convert_case(mb_strtolower(strtok(trim((string) $client->name), ' ') ?: ''), MB_CASE_TITLE, 'UTF-8');
    }

    private function cleanMessage(?string $message): ?string
    {
        $message = trim((string) $message);

        return $message === '' ? null : $message;
    }

    private function date($value, bool $endOfDay = false): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        $date = $value instanceof Carbon ? $value->copy() : Carbon::parse($value);

        // Campo de data sem hora: a promoção vale até o fim do dia escolhido.
        if ($endOfDay && $date->format('H:i:s') === '00:00:00') {
            $date->endOfDay();
        }

        return $date;
    }

    private function assertDates(?Carbon $startsAt, ?Carbon $endsAt): void
    {
        if ($endsAt && $endsAt->isPast()) {
            throw new InvalidArgumentException('A data de fim já passou.');
        }
        if ($startsAt && $endsAt && $endsAt->lte($startsAt)) {
            throw new InvalidArgumentException('A data de fim precisa ser depois do início.');
        }
    }
}
