<?php

namespace App\Livewire\Promotions;

use App\Livewire\Promotions\Concerns\SchedulesPromotion;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionSetting;
use App\Services\Products\PromotionService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Component;

/**
 * Página "Nova promoção": escolhe os produtos em cards (como em Adicionar
 * produtos da venda), ajusta o desconto de cada um e cria as promoções.
 * Lista todos os produtos com estoque, sem paginação.
 * Produto com variações entra com a família inteira: cada variação ganha a
 * própria promoção, porque cada uma tem preço e estoque próprios.
 */
class PromotionsCreate extends Component
{
    use HasNotifications, SchedulesPromotion;

    public string $search = '';
    public string $category = '';
    public string $show = 'todos'; // todos | sem_promo | com_tabela
    public string $sort = 'name';   // name | desconto | estoque | recentes

    /**
     * Produtos escolhidos, na ordem em que foram adicionados.
     * product_id => ['original' => '119,90', 'promo' => '101,90']
     */
    public array $items = [];

    public function mount(): void
    {
        $days = $this->settings->default_days;
        $this->endsAt = $days ? now(Promotion::tz())->addDays($days)->format('Y-m-d') : '';

        if ($productId = (int) request()->query('produto')) {
            $this->toggleProduct($productId);
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'show', 'sort']);
    }

    /** Clique no card: adiciona ou tira (com as variações, se tiver). */
    public function toggleProduct(int $productId): void
    {
        $product = Product::where('user_id', Auth::id())->find($productId);
        if (!$product) {
            return;
        }

        $family = $product->is_variation_parent ? $product->family()->get() : collect([$product]);
        $ids = $family->pluck('id')->all();

        if (array_intersect($ids, array_keys($this->items))) {
            foreach ($ids as $id) {
                unset($this->items[$id]);
            }
            return;
        }

        // Da família, só entram as variações que têm estoque.
        if ($family->count() > 1) {
            $family = $family->filter(fn (Product $m) => $this->hasStock($m))->values();
            if ($family->isEmpty()) {
                $this->notifyWarning('Nenhuma variação deste produto tem estoque.');
                return;
            }
        }

        $service = app(PromotionService::class);
        $skipped = 0;
        foreach ($family as $member) {
            $prices = $service->suggestedPrices($member, $this->settings);
            if ($prices['promo'] === null) {
                $skipped++;
                if ($family->count() > 1) {
                    continue;
                }
            }
            $this->items[$member->id] = [
                'original' => $this->fmt($prices['original']),
                'promo'    => $this->fmt($prices['promo'] ?? $prices['original']),
            ];
        }

        if ($skipped) {
            $this->notifyWarning($skipped === 1
                ? 'Um produto não tem margem para desconto com o lucro mínimo atual. Confira o preço "de".'
                : $skipped . ' produtos não têm margem para desconto com o lucro mínimo atual.', 7000);
        }
    }

    public function removeItem(int $productId): void
    {
        unset($this->items[$productId]);
    }

    public function clearItems(): void
    {
        $this->items = [];
    }

    /** Botões − e +: tira ou dá 1% de desconto, sem passar do mínimo. */
    public function nudge(int $productId, int $direction): void
    {
        [$product, $original, $promo] = $this->row($productId);
        if (!$product || $original <= 0) {
            return;
        }
        $this->items[$productId]['promo'] = $this->fmt(app(PromotionService::class)->stepPrice($product, $original, $promo, $direction, $this->settings));
    }

    public function setPercent(int $productId, $percent): void
    {
        [$product, $original] = $this->row($productId);
        if (!$product || $original <= 0) {
            return;
        }
        $percent = max(1, min(99, (float) $percent));
        $this->items[$productId]['promo'] = $this->fmt(app(PromotionService::class)->priceForPercent($product, $original, $percent, $this->settings));
    }

    /** Usa o maior desconto permitido. */
    public function useMax(int $productId): void
    {
        [$product, $original] = $this->row($productId);
        if ($product && $original > 0) {
            $service = app(PromotionService::class);
            $min = $service->minPromoPrice($product, $this->settings);
            $this->items[$productId]['promo'] = $this->fmt($service->withEnding($min, $min, $this->settings));
        }
    }

    /** Mesmo desconto para todos os escolhidos ('max' = o maior que cada um permite). */
    public function applyToAll($percent): void
    {
        foreach (array_keys($this->items) as $id) {
            $percent === 'max' ? $this->useMax($id) : $this->setPercent($id, $percent);
        }
    }

    public function save(PromotionService $service)
    {
        if (empty($this->items)) {
            $this->notifyError('Escolha pelo menos um produto.');
            return null;
        }

        $products = Product::where('user_id', Auth::id())->whereIn('id', array_keys($this->items))->get()->keyBy('id');
        $this->resetErrorBag();

        // Valida tudo antes de criar, para não ficar metade criada.
        foreach ($this->items as $id => $item) {
            $product = $products->get($id);
            if (!$product) {
                unset($this->items[$id]);
                continue;
            }
            if ($error = $service->validatePrices($product, $this->num($item['original']), $this->num($item['promo']), $this->settings)) {
                $this->addError("items.$id", $error);
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            $this->notifyError('Confira os produtos marcados em vermelho.');
            return null;
        }

        $created = 0;
        $scheduled = 0;
        $startsAt = $this->startsAtValue();
        try {
            // Tudo ou nada: se um produto cruzar outra promoção, nenhuma é criada.
            DB::transaction(function () use ($service, $products, $startsAt, &$created, &$scheduled) {
                foreach ($this->items as $id => $item) {
                    $promo = $service->start($products->get($id), [
                        'original_price' => $this->num($item['original']),
                        'promo_price'    => $this->num($item['promo']),
                        'starts_at'      => $startsAt,
                        'ends_at'        => $this->endsAt ?: null,
                    ]);
                    $promo->status === Promotion::AGENDADA ? $scheduled++ : $created++;
                }
            });
        } catch (InvalidArgumentException $e) {
            $this->addError('dates', $e->getMessage());
            $this->notifyError($e->getMessage(), 8000);
            return null;
        }

        $total = $created + $scheduled;
        session()->flash('success', $scheduled
            ? ($total === 1 ? 'Promoção agendada' : $total . ' promoções agendadas') . ' para ' . Promotion::humanDateTime(\Carbon\Carbon::parse($startsAt, Promotion::tz())) . '.'
            : ($total === 1 ? 'Produto em promoção.' : $total . ' produtos em promoção.'));

        return redirect()->route('promotions.index', $scheduled ? ['tab' => 'agendadas'] : []);
    }

    // ─── Dados para a view ────────────────────────────────────────

    public function getSettingsProperty(): PromotionSetting
    {
        return PromotionSetting::forUser(Auth::id());
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())->where('type', 'product')->orderBy('name')->get();
    }

    /** Produtos raiz (sem as variações) que têm estoque, todos de uma vez. */
    public function getProductsProperty()
    {
        $term = trim($this->search);
        $query = Product::where('user_id', Auth::id())
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->where('stock_quantity', '>', 0)
                ->orWhere('tipo', 'kit')
                ->orWhere(fn ($v) => $v->where('is_variation_parent', true)
                    ->whereHas('variants', fn ($c) => $c->where('stock_quantity', '>', 0))))
            ->with(['category', 'activePromotion'])
            ->withCount('variants')
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")))
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->when($this->show === 'sem_promo', fn ($q) => $q->whereDoesntHave('promotions', fn ($p) => $p->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA])))
            ->when($this->show === 'com_tabela', fn ($q) => $q->where('price_original', '>', 0));

        match ($this->sort) {
            'desconto' => $query->orderByRaw('CASE WHEN price_original > 0 THEN price_sale / price_original ELSE 2 END'),
            'estoque'  => $query->orderByDesc('stock_quantity'),
            'recentes' => $query->latest('id'),
            default    => $query->orderBy('name'),
        };

        return $query->get();
    }

    /** Ids dos cards marcados (o produto principal fica marcado quando alguma variação está escolhida). */
    public function getSelectedRootsProperty(): array
    {
        if (empty($this->items)) {
            return [];
        }

        return Product::where('user_id', Auth::id())->whereIn('id', array_keys($this->items))
            ->get(['id', 'parent_id'])
            ->map(fn (Product $p) => $p->parent_id ?? $p->id)
            ->unique()->values()->all();
    }

    /** Linhas do painel da direita, com os números de cada produto. */
    public function getRowsProperty(): array
    {
        if (empty($this->items)) {
            return [];
        }
        $service = app(PromotionService::class);
        $settings = $this->settings;
        $products = Product::where('user_id', Auth::id())->whereIn('id', array_keys($this->items))->get()->keyBy('id');

        $rows = [];
        foreach ($this->items as $id => $item) {
            $product = $products->get($id);
            if (!$product) {
                continue;
            }
            $original = $this->num($item['original']);
            $promo = $this->num($item['promo']);
            $cost = (float) $product->price;
            $min = $service->minPromoPrice($product, $settings);

            $rows[$id] = [
                'product'  => $product,
                'discount' => $original > 0 && $promo > 0 ? (int) round((1 - $promo / $original) * 100) : 0,
                'max'      => $service->maxDiscountPercent($product, $original, $settings),
                'cost'     => $cost,
                'min'      => $min,
                'profit'   => $promo - $cost,
                'belowMin' => $promo > 0 && $promo < $min,
                'noRoom'   => $original > 0 && $min >= $original,
            ];
        }

        return $rows;
    }

    public function render()
    {
        return view('livewire.promotions.promotions-create', [
            'products' => $this->products,
            'rows'     => $this->rows,
            'selectedRoots' => $this->selectedRoots,
            'settings' => $this->settings,
        ]);
    }

    // ─────────────────────────────────────────────────────────────

    /** @return array{0: ?Product, 1: float, 2: float} */
    private function row(int $productId): array
    {
        if (!isset($this->items[$productId])) {
            return [null, 0.0, 0.0];
        }
        $product = Product::where('user_id', Auth::id())->find($productId);

        return [$product, $this->num($this->items[$productId]['original']), $this->num($this->items[$productId]['promo'])];
    }

    private function hasStock(Product $product): bool
    {
        return ($product->tipo ?? 'simples') === 'kit' || (int) $product->stock_quantity > 0;
    }

    /** "1.449,50" | "1449.50" | 1449.5 → 1449.5 */
    private function num($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $value = str_replace([' ', 'R$'], '', (string) $value);
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return (float) $value;
    }

    private function fmt(?float $value): string
    {
        return $value === null ? '' : number_format($value, 2, ',', '');
    }
}
