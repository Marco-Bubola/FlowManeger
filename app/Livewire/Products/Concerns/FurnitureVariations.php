<?php

namespace App\Livewire\Products\Concerns;

use App\Models\Product;
use App\Services\Choices\ChoiceService;
use App\Services\Products\VariationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Editar produto de loja de móveis (Configurações › Produtos › Tipo de loja):
 * montador de variações tamanho × cor da ferragem × estofado (do catálogo
 * Orçamentos › Estofados e cores) × kit, com preço por tamanho e valor a mais
 * do tecido, e a tabela do grupo para mudar preço, estoque e encomenda de várias
 * de uma vez (igual ao robô da Stylo). A tabela do grupo vale para toda loja.
 */
trait FurnitureVariations
{
    /** Tamanhos: [{v: "120 cm", c: custo, s: venda}] (vazio = o deste produto). */
    public array $fvSizes = [];
    public string $fvSizeInput = '';
    /** Cores da ferragem marcadas (ids do catálogo) e cores escritas à mão. */
    public array $fvFrames = [];
    public string $fvFrameText = '';
    /** Estofados marcados (ids do catálogo). */
    public array $fvFabrics = [];
    /** 'variation' = cada estofado vira variação; 'choice' = o cliente escolhe no portal (sob encomenda). */
    public string $fvFabricMode = 'variation';
    public bool $fvFabricExtra = true;
    public string $fvKits = '';
    public string $fvKitDiscount = '';
    public string $fvStock = '0';
    public bool $fvMadeToOrder = false;

    /** Tabela do grupo: [id => {c, s, q, m}] e as linhas marcadas. */
    public array $fvRows = [];
    public array $fvMarked = [];
    public string $fvBulkCost = '';
    public string $fvBulkSale = '';
    public string $fvBulkAdd = '';
    public string $fvBulkStock = '';

    public const FV_MAX = 40;

    protected function fillFurniture(Product $product): void
    {
        $this->fvMadeToOrder = (bool) $product->made_to_order;
        $this->loadGroupRows();
    }

    /** Itens do catálogo por tipo (todas as listas daquele tipo juntas). */
    public function fvItems(string $kind)
    {
        return $this->choiceCatalogs->where('kind', $kind)->flatMap(fn ($c) => $c->items->where('active', true))->values();
    }

    // ─── Tamanhos ────────────────────────────────────────────────────────────

    public function fvAddSize(): void
    {
        foreach (preg_split('/[,;\n]+/', $this->fvSizeInput) ?: [] as $v) {
            $v = $this->fvClean($v);
            if ($v === '' || collect($this->fvSizes)->contains(fn ($r) => mb_strtolower($r['v']) === mb_strtolower($v))) {
                continue;
            }
            if (preg_match('/^\d+([.,]\d+)?$/', $v)) {
                $v .= ' cm'; // "120" vira "120 cm"
            }
            $this->fvSizes[] = ['v' => $v, 'c' => '', 's' => ''];
        }
        $this->fvSizeInput = '';
    }

    public function fvRemoveSize(int $i): void
    {
        unset($this->fvSizes[$i]);
        $this->fvSizes = array_values($this->fvSizes);
    }

    public function fvToggle(string $list, int $id): void
    {
        if (! in_array($list, ['fvFrames', 'fvFabrics'], true)) {
            return;
        }
        $ids = array_map('intval', $this->{$list});
        $this->{$list} = in_array($id, $ids, true) ? array_values(array_diff($ids, [$id])) : [...$ids, $id];
    }

    /** Marca/desmarca de uma vez todos os estofados de um material (ex.: todos os corinos). */
    public function fvToggleMaterial(string $material): void
    {
        $ids = $this->fvItems('estofado')->filter(fn ($it) => mb_strtolower((string) $it->material) === mb_strtolower($material))->pluck('id')->all();
        $on = array_map('intval', $this->fvFabrics);
        $this->fvFabrics = array_diff($ids, $on) ? array_values(array_unique([...$on, ...$ids])) : array_values(array_diff($on, $ids));
    }

    public function fvClear(): void
    {
        $this->reset(['fvSizes', 'fvSizeInput', 'fvFrames', 'fvFrameText', 'fvFabrics', 'fvKits', 'fvKitDiscount']);
        $this->fvStock = '0';
    }

    private function fvClean(?string $v): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace(['·', ','], ' ', (string) $v)));
    }

    private function fvMoney($v): ?float
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        // "1.234,56" e "1234.56"
        $v = str_contains($v, ',') ? str_replace(['.', ','], ['', '.'], $v) : $v;

        return is_numeric($v) ? round((float) $v, 2) : null;
    }

    /**
     * As combinações que o "Criar" vai fazer, já com nome, preço e amostras:
     * [{v, c, s, kit, dots: [css]}]. Nome como o do robô: "120 cm · Preto com corino Verde · Kit com 2".
     */
    public function fvCombos(): array
    {
        $root = $this->product->parent_id ? $this->product->parent : $this->product;
        $baseCost = (float) $root->price;
        $baseSale = (float) $root->price_sale;

        $sizes = $this->fvSizes ?: [null];
        $frames = $this->fvItems('ferragem')->whereIn('id', array_map('intval', $this->fvFrames))->values()
            ->map(fn ($it) => ['n' => $this->fvClean($it->name), 'dot' => $this->fvDot($it), 'x' => 0.0])->all();
        foreach (preg_split('/[,;\n]+/', $this->fvFrameText) ?: [] as $t) {
            if (($t = $this->fvClean($t)) !== '') {
                $frames[] = ['n' => $t, 'dot' => null, 'x' => 0.0];
            }
        }
        $frames = $frames ?: [null];
        $fabrics = $this->fvFabricMode === 'variation'
            ? $this->fvItems('estofado')->whereIn('id', array_map('intval', $this->fvFabrics))->values()
                ->map(fn ($it) => ['n' => $this->fvClean(trim(mb_strtolower((string) $it->material) . ' ' . $it->name . ($this->fvDupName($it) && $it->code ? ' ' . $it->code : ''))), 'dot' => $this->fvDot($it), 'x' => (float) $it->extra_price])->all()
            : [];
        $fabrics = $fabrics ?: [null];
        $kits = collect(preg_split('/[^0-9]+/', $this->fvKits) ?: [])->map(fn ($k) => (int) $k)->filter(fn ($k) => $k > 1 && $k <= 50)->unique()->sort()->values()->all();
        $kits = [1, ...$kits];
        $disc = min(max((float) str_replace(',', '.', $this->fvKitDiscount ?: '0'), 0), 60) / 100;

        if ($sizes === [null] && $frames === [null] && $fabrics === [null]) {
            return [];
        }

        $out = [];
        foreach ($sizes as $sz) {
            foreach ($frames as $fr) {
                foreach ($fabrics as $fb) {
                    $color = $fr && $fb ? $fr['n'] . ' com ' . $fb['n'] : ($fr['n'] ?? (isset($fb['n']) ? Str::ucfirst($fb['n']) : null));
                    $name = implode(' · ', array_filter([$sz['v'] ?? null, $color]));
                    $cost = $this->fvMoney($sz['c'] ?? '') ?? $baseCost;
                    $sale = ($this->fvMoney($sz['s'] ?? '') ?? $baseSale) + ($this->fvFabricExtra ? ($fb['x'] ?? 0) : 0);
                    foreach ($kits as $k) {
                        $out[] = [
                            'v' => $name . ($k > 1 ? ' · Kit com ' . $k : ''),
                            'c' => round($cost * $k, 2),
                            's' => round($sale * $k * ($k > 1 ? 1 - $disc : 1), 2),
                            'kit' => $k,
                            'dots' => array_values(array_filter([$fr['dot'] ?? null, $fb['dot'] ?? null])),
                        ];
                    }
                }
            }
        }

        return $out;
    }

    /** Duas cores com o mesmo nome no catálogo (dois verdes): o código diferencia. */
    private function fvDupName($item): bool
    {
        static $names = null;
        $names ??= $this->fvItems('estofado')->countBy(fn ($it) => mb_strtolower($it->label()));

        return ($names[mb_strtolower($item->label())] ?? 0) > 1;
    }

    private function fvDot($item): string
    {
        return $item->image ? "background-image:url('" . $item->imageUrl() . "');background-size:cover" : 'background:' . ($item->hex ?: '#e2e8f0');
    }

    /** Cria as combinações como variações (cada uma com seu estoque, ligada a este produto). */
    public function fvCreate(VariationService $variations): void
    {
        if ($this->product->parent_id) {
            return;
        }
        $combos = $this->fvCombos();
        $choiceOnly = ! $combos && $this->fvFabricMode === 'choice' && $this->fvFabrics;
        if (! $combos && ! $choiceOnly) {
            $this->addError('fvSizes', 'Escolha pelo menos um tamanho, uma cor ou um estofado.');

            return;
        }
        $root = $this->product;
        $taken = $root->family()->pluck('variation_value')->filter()->map(fn ($v) => mb_strtolower($v))->all();
        $new = array_values(array_filter($combos, fn ($c) => ! in_array(mb_strtolower($c['v']), $taken, true)));
        if (count($new) + count($taken) > self::FV_MAX) {
            $this->addError('fvSizes', 'Ficariam ' . (count($new) + count($taken)) . ' variações no grupo. O máximo é ' . self::FV_MAX . ' (o Mercado Livre não aceita mais). Deixe o estofado como escolha no portal ou marque menos.');

            return;
        }
        $hasSize = (bool) $this->fvSizes;
        $hasColor = $this->fvFrames || trim($this->fvFrameText) !== '' || ($this->fvFabricMode === 'variation' && $this->fvFabrics);
        $attribute = $hasSize && $hasColor ? 'Tamanho e cor' : ($hasSize ? 'Tamanho' : 'Cor');
        $stock = $this->fvMadeToOrder ? 0 : max(0, (int) $this->fvStock);
        $codeBase = trim((string) $root->product_code);
        $usedCodes = Product::where('user_id', Auth::id())->pluck('product_code')->map(fn ($c) => mb_strtolower((string) $c))->flip();
        $base = trim(preg_replace('/\s+/', ' ', $root->name));

        $created = 0;
        try {
            DB::transaction(function () use ($root, $new, $attribute, $stock, $codeBase, $usedCodes, $base, $variations, $choiceOnly, &$created) {
                if ($new) {
                    $root->update(['is_variation_parent' => true, 'variation_attribute' => $attribute]);
                    $root->family()->where('id', '!=', $root->id)->update(['variation_attribute' => $attribute]);
                }
                $n = 0;
                foreach ($new as $c) {
                    $code = $codeBase !== '' ? mb_substr($codeBase . '-' . Str::slug($c['v']), 0, 60) : $variations->generateCode();
                    while (isset($usedCodes[mb_strtolower($code)])) {
                        $code = mb_substr($code, 0, 55) . '-' . (++$n);
                    }
                    $usedCodes[mb_strtolower($code)] = true;
                    $k = $c['kit'];
                    $variations->createVariant($root->fresh(), [
                        'name' => $base . ' ' . $c['v'],
                        'product_code' => $code,
                        'price' => $c['c'],
                        'price_sale' => $c['s'],
                        'stock_quantity' => $k > 1 ? 0 : $stock,
                        'category_id' => $root->category_id,
                        'description' => $root->description,
                        'specs' => $root->specs,
                        'brand' => $root->brand,
                        'model' => $root->model,
                        'warranty_months' => $root->warranty_months,
                        'condition' => $root->condition ?: 'new',
                        'weight_grams' => $root->weight_grams ? (int) $root->weight_grams * $k : null,
                        'length_cm' => $root->length_cm,
                        'width_cm' => $root->width_cm,
                        'height_cm' => $root->height_cm ? round((float) $root->height_cm * $k, 1) : null,
                        'made_to_order' => $this->fvMadeToOrder,
                        'production_days' => $this->fvMadeToOrder ? $root->production_days : null,
                        'cost_items' => $root->cost_items,
                    ], $c['v']);
                    $created++;
                }
                // Estofado como escolha do cliente: liga a lista no produto principal (as variações usam a dele)
                if ($this->fvFabricMode === 'choice' && $this->fvFabrics && ChoiceService::ready()) {
                    $this->fvLinkFabricChoices($root);
                }
            });
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao criar as variações: ' . $e->getMessage());

            return;
        }

        $this->product->refresh();
        $this->fillChoiceLists($this->product);
        $this->variationAttribute = $this->product->variation_attribute ?: $this->variationAttribute;
        $fabricNote = $this->fvFabricMode === 'choice' && $this->fvFabrics ? ' O estofado ficou como escolha no portal.' : '';
        $this->fvClear();
        $this->loadGroupRows();
        $this->dispatch('product-updated');
        if ($created) {
            $this->notifySuccess(($created === 1 ? '1 variação criada.' : "{$created} variações criadas.") . $fabricNote . ' Ponha a foto de cada uma.');
        } elseif ($fabricNote) {
            $this->notifySuccess(trim($fabricNote));
        } else {
            $this->notifyWarning('Essas variações já existem no grupo.');
        }
    }

    /** Liga as listas de estofado no produto com só os itens marcados. */
    private function fvLinkFabricChoices(Product $root): void
    {
        $picked = array_map('intval', $this->fvFabrics);
        $lists = collect($root->choice_lists ?: [])->keyBy(fn ($l) => (int) ($l['c'] ?? 0));
        foreach ($this->choiceCatalogs->where('kind', 'estofado') as $cat) {
            $ids = $cat->items->pluck('id')->intersect($picked)->values()->all();
            if (! $ids) {
                continue;
            }
            $all = count($ids) === $cat->items->where('active', true)->count();
            $lists[$cat->id] = ['c' => $cat->id, 'l' => $lists[$cat->id]['l'] ?? 'Estofado', 'i' => $all ? null : $ids];
        }
        $root->update(['choice_lists' => $lists->values()->all() ?: null]);
    }

    // ─── Tabela do grupo: mudar várias de uma vez ────────────────────────────

    public function loadGroupRows(): void
    {
        $root = $this->product->parent_id ? $this->product->parent : $this->product;
        $this->fvRows = [];
        if (! $root || (! $root->is_variation_parent && ! $this->product->parent_id)) {
            return;
        }
        foreach ($root->family()->get() as $p) {
            $this->fvRows[$p->id] = [
                'c' => number_format((float) $p->price, 2, ',', '.'),
                's' => number_format((float) $p->price_sale, 2, ',', '.'),
                'q' => (string) (int) $p->stock_quantity,
                'm' => (bool) $p->made_to_order,
            ];
        }
        $this->fvMarked = array_values(array_intersect(array_map('intval', $this->fvMarked), array_keys($this->fvRows)));
    }

    /** Pedaços dos nomes das variações (120 cm, Preto, corino Verde...) para marcar com um toque. */
    public function groupTokens($family): array
    {
        $count = [];
        foreach ($family as $p) {
            foreach (preg_split('/\s+·\s+|\s+com\s+|\s+e\s+/u', (string) $p->variation_value) ?: [] as $t) {
                $t = trim($t);
                if ($t !== '') {
                    $count[$t] = ($count[$t] ?? 0) + 1;
                }
            }
        }

        return array_keys(array_filter($count, fn ($n) => $n > 1 && $n < count($family)));
    }

    public function fvMarkToken(string $token): void
    {
        $ids = Product::whereIn('id', array_keys($this->fvRows))->where('user_id', Auth::id())->get(['id', 'variation_value'])
            ->filter(fn ($p) => in_array($token, array_map('trim', preg_split('/\s+·\s+|\s+com\s+|\s+e\s+/u', (string) $p->variation_value) ?: []), true))
            ->pluck('id')->all();
        $on = array_map('intval', $this->fvMarked);
        $this->fvMarked = array_diff($ids, $on) ? array_values(array_unique([...$on, ...$ids])) : array_values(array_diff($on, $ids));
    }

    public function fvMarkAll(bool $on = true): void
    {
        $this->fvMarked = $on ? array_map('intval', array_keys($this->fvRows)) : [];
    }

    public function fvToggleRow(int $id): void
    {
        $on = array_map('intval', $this->fvMarked);
        $this->fvMarked = in_array($id, $on, true) ? array_values(array_diff($on, [$id])) : [...$on, $id];
    }

    /** Aplica custo, venda, +R$, estoque nas marcadas (só na tabela; salva no botão). */
    public function fvApplyBulk(?string $mto = null): void
    {
        if (! $this->fvMarked) {
            $this->notifyWarning('Marque as variações primeiro.');

            return;
        }
        $cost = $this->fvMoney($this->fvBulkCost);
        $sale = $this->fvMoney($this->fvBulkSale);
        $add = $this->fvMoney($this->fvBulkAdd);
        $stock = trim($this->fvBulkStock) === '' ? null : max(0, (int) $this->fvBulkStock);
        if ($cost === null && $sale === null && $add === null && $stock === null && $mto === null) {
            $this->notifyWarning('Ponha o custo, a venda, o valor a mais ou o estoque.');

            return;
        }
        $fmt = fn ($v) => number_format($v, 2, ',', '.');
        foreach ($this->fvMarked as $id) {
            if (! isset($this->fvRows[$id])) {
                continue;
            }
            $r = &$this->fvRows[$id];
            if ($cost !== null) {
                $r['c'] = $fmt($cost);
            }
            if ($sale !== null) {
                $r['s'] = $fmt($sale);
            }
            if ($add !== null) {
                $r['s'] = $fmt(max(0, ($this->fvMoney($r['s']) ?? 0) + $add));
            }
            if ($stock !== null) {
                $r['q'] = (string) $stock;
            }
            if ($mto !== null) {
                $r['m'] = $mto === 'on';
            }
            unset($r);
        }
        $this->reset(['fvBulkCost', 'fvBulkSale', 'fvBulkAdd', 'fvBulkStock']);
        $this->saveGroup();
    }

    /** Grava preço, estoque e encomenda de todas as linhas da tabela. */
    public function saveGroup(): void
    {
        $ids = array_map('intval', array_keys($this->fvRows));
        $products = Product::where('user_id', Auth::id())->whereIn('id', $ids)->get()->keyBy('id');
        $changed = 0;
        foreach ($this->fvRows as $id => $r) {
            $p = $products[(int) $id] ?? null;
            if (! $p) {
                continue;
            }
            $data = [
                'price' => $this->fvMoney($r['c']) ?? (float) $p->price,
                'price_sale' => $this->fvMoney($r['s']) ?? (float) $p->price_sale,
                'stock_quantity' => max(0, (int) $r['q']),
                'made_to_order' => (bool) $r['m'],
            ];
            $p->fill($data);
            if ($p->isDirty()) {
                $p->save();
                $changed++;
            }
        }
        // O que está aberto no formulário acompanha
        if (isset($this->fvRows[$this->product->id])) {
            $self = $this->product->fresh();
            $this->product = $self;
            $this->price = number_format((float) $self->price, 2, '.', '');
            $this->price_sale = number_format((float) $self->price_sale, 2, '.', '');
            $this->stock_quantity = (string) $self->stock_quantity;
            $this->made_to_order = (bool) $self->made_to_order;
            $this->dispatch('currency-input-set', name: 'price', value: $this->price);
            $this->dispatch('currency-input-set', name: 'price_sale', value: $this->price_sale);
        }
        $this->loadGroupRows();
        $this->dispatch('product-updated');
        $changed
            ? $this->notifySuccess($changed === 1 ? '1 variação atualizada.' : "{$changed} variações atualizadas.")
            : $this->notifyInfo('Nada mudou.');
    }
}
