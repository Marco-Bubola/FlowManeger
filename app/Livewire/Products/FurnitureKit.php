<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProdutoComponente;
use App\Services\Products\KitBuilder;
use App\Services\Products\VariationService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Conjunto de móveis como kit (Configurações › Produtos › Página de kit).
 * Escolhe as peças (ex.: mesinha bistrô ×1 e banqueta 54 cm ×2) e monta uma
 * opção do conjunto para cada combinação das variações. Cada opção é um
 * produto kit ligado às variações de verdade: vender baixa o estoque das
 * peças e o estoque do conjunto é o da peça que acaba primeiro.
 */
class FurnitureKit extends Component
{
    use HasNotifications, WithFileUploads;

    public const MAX_OPTIONS = 40;

    public ?int $rootId = null;

    public string $name = '';
    public string $code = '';
    public string $category_id = '';
    public string $description = '';
    public bool $made_to_order = false;
    public string $production_days = '15';
    /** Desconto do conjunto sobre a soma das peças, em %. */
    public string $discount = '0';
    /** 'cor': só junta peças da mesma cor de ferragem; 'todas': todas as combinações. */
    public string $mode = 'cor';

    /** @var array<int, array{family:int, name:string, label:string, qty:int, variants:array, selected:array}> */
    public array $pieces = [];

    /** @var array<int, array> opções montadas */
    public array $options = [];

    public string $search = '';

    public $photo = null;

    public function mount($kit = null): void
    {
        if ($kit !== null && $kit !== '') {
            $kit = $kit instanceof Product ? $kit : Product::withoutGlobalScope('team_visibility')->findOrFail((int) $kit);
            abort_unless((int) $kit->user_id === (int) Auth::id(), 403);
            $root = $kit->parent_id ? ($kit->parent ?? $kit) : $kit;
            $this->load($root);
        }
    }

    private function load(Product $root): void
    {
        $this->rootId = $root->id;
        $this->name = (string) $root->name;
        $this->code = (string) Str::before((string) $root->product_code, '-');
        $this->category_id = (string) $root->category_id;
        $this->description = (string) $root->description;
        $this->made_to_order = (bool) $root->made_to_order;
        $this->production_days = (string) ($root->production_days ?: 15);

        $members = $root->is_variation_parent ? $root->family()->get() : collect([$root]);
        $members = $members->filter(fn ($m) => $m->isKit());

        // Peças: famílias das variações usadas, na ordem em que aparecem
        $qtyByFamily = [];
        $used = [];
        foreach ($members as $m) {
            foreach ($m->componentes()->with('componente')->get() as $pc) {
                $c = $pc->componente;
                if (!$c) {
                    continue;
                }
                $fam = $c->parent_id ?: $c->id;
                $qtyByFamily[$fam] ??= max(1, (int) $pc->quantidade);
                $used[$fam][$c->id] = true;
            }
        }
        foreach ($qtyByFamily as $fam => $qty) {
            $this->addPiece($fam, $qty, array_keys($used[$fam] ?? []), false);
        }

        $this->generate();

        // O que já existe fica com o preço e o código de hoje; o que saiu do conjunto aparece desligado
        $existing = [];
        foreach ($members as $m) {
            $sig = $this->signature($m->componentes()->get()->map(fn ($pc) => [(int) $pc->componente_produto_id, (int) $pc->quantidade])->all());
            $existing[$sig] = $m;
        }
        $sumParts = 0;
        $sumSale = 0;
        foreach ($this->options as $i => $o) {
            $m = $existing[$o['sig']] ?? null;
            if (!$m) {
                continue;
            }
            $this->options[$i]['existing_id'] = $m->id;
            $this->options[$i]['code'] = (string) $m->product_code;
            $this->options[$i]['sale'] = $this->money((float) $m->price_sale);
            $this->options[$i]['on'] = ($m->status ?? 'ativo') !== 'inativo';
            $sumParts += $o['sum'];
            $sumSale += (float) $m->price_sale;
            unset($existing[$o['sig']]);
        }
        if ($sumParts > 0 && $sumSale > 0 && $sumSale < $sumParts) {
            $this->discount = (string) round((1 - $sumSale / $sumParts) * 100, 1);
        }
        foreach ($existing as $sig => $m) {
            // opção que não bate com as peças de agora: continua listada para poder desligar
            $this->options[] = [
                'sig' => $sig, 'value' => (string) ($m->variation_value ?: $m->name), 'code' => (string) $m->product_code,
                'parts' => $m->componentes()->get()->map(fn ($pc) => ['id' => (int) $pc->componente_produto_id, 'qty' => (int) $pc->quantidade])->all(),
                'cost' => (float) $m->price, 'sum' => (float) $m->price_sale, 'sale' => $this->money((float) $m->price_sale),
                'stock' => $m->availableStock(), 'existing_id' => $m->id, 'on' => ($m->status ?? 'ativo') !== 'inativo', 'image' => $m->image_url, 'orphan' => true,
            ];
        }
    }

    // ───────────────────────────── peças

    public function getResultsProperty(): array
    {
        $term = trim($this->search);
        if (mb_strlen($term) < 2) {
            return [];
        }
        $taken = collect($this->pieces)->pluck('family')->all();

        return Product::where('user_id', Auth::id())
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->whereNull('tipo')->orWhere('tipo', '!=', 'kit'))
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "{$term}%"))
            ->whereNotIn('id', $taken ?: [0])
            ->orderBy('name')->limit(8)->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'code' => $p->product_code, 'image' => $p->image_url,
                'variants' => $p->is_variation_parent ? $p->variants()->count() + 1 : 1,
            ])->all();
    }

    public function addPiece(int $familyId, int $qty = 1, ?array $selected = null, bool $regen = true): void
    {
        $root = Product::where('user_id', Auth::id())->find($familyId);
        if (!$root || $root->isKit() || collect($this->pieces)->contains('family', $root->id)) {
            return;
        }
        $members = $root->is_variation_parent ? $root->family()->get() : collect([$root]);
        $variants = $members->filter(fn ($m) => !$m->isKit() && ($m->status ?? 'ativo') !== 'inativo' || in_array($m->id, $selected ?? [], true))
            ->map(fn ($m) => [
                'id' => $m->id,
                'code' => (string) $m->product_code,
                'value' => (string) ($m->variation_value ?: 'Padrão'),
                'cost' => (float) $m->price,
                'sale' => (float) $m->price_sale,
                'stock' => (int) $m->stock_quantity,
                'made' => (bool) $m->made_to_order,
                'color' => self::colorKey((string) ($m->variation_value ?: '')),
                'image' => $m->image_url,
            ])->values()->all();

        $this->pieces[] = [
            'family' => $root->id,
            'name' => (string) $root->name,
            'label' => self::shortLabel((string) $root->name),
            'qty' => max(1, $qty),
            'variants' => $variants,
            'selected' => $selected ?? array_column($variants, 'id'),
        ];
        $this->search = '';
        if ($this->name === '' && count($this->pieces) === 1) {
            $this->name = 'Conjunto ' . $root->name;
        }
        if ($regen) {
            $this->generate();
        }
    }

    public function removePiece(int $i): void
    {
        unset($this->pieces[$i]);
        $this->pieces = array_values($this->pieces);
        $this->generate();
    }

    public function toggleVariant(int $i, int $id): void
    {
        if (!isset($this->pieces[$i])) {
            return;
        }
        $sel = $this->pieces[$i]['selected'];
        $this->pieces[$i]['selected'] = in_array($id, $sel, true)
            ? array_values(array_diff($sel, [$id]))
            : array_values(array_merge($sel, [$id]));
        $this->generate();
    }

    public function selectAll(int $i, bool $on): void
    {
        if (isset($this->pieces[$i])) {
            $this->pieces[$i]['selected'] = $on ? array_column($this->pieces[$i]['variants'], 'id') : [];
            $this->generate();
        }
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['mode', 'discount'], true) || str_starts_with($prop, 'pieces.')) {
            $this->generate();
        }
        if ($prop === 'code') {
            $this->code = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $this->code));
            $this->generate();
        }
    }

    // ───────────────────────────── opções

    /** Monta as opções do conjunto a partir das peças; preços digitados à mão e códigos que já existem ficam. */
    public function generate(): void
    {
        $keep = collect($this->options)->keyBy('sig');
        $lists = [];
        foreach ($this->pieces as $pi => $piece) {
            $vs = array_values(array_filter($piece['variants'], fn ($v) => in_array($v['id'], $piece['selected'], true)));
            if (!$vs) {
                $this->options = $keep->filter(fn ($o) => $o['existing_id'] ?? null)->values()->all();

                return;
            }
            $lists[] = array_map(fn ($v) => $v + ['piece' => $pi], $vs);
        }
        if (!$lists) {
            $this->options = $keep->filter(fn ($o) => $o['existing_id'] ?? null)->values()->all();

            return;
        }

        $combos = [[]];
        foreach ($lists as $list) {
            $next = [];
            foreach ($combos as $c) {
                foreach ($list as $v) {
                    if ($this->mode === 'cor' && $c && $v['color'] !== '' && $c[0]['color'] !== '' && $v['color'] !== $c[0]['color']) {
                        continue;
                    }
                    $next[] = array_merge($c, [$v]);
                }
            }
            $combos = $next;
            if (count($combos) > 400) {
                break;
            }
        }

        $disc = max(0, min(90, (float) str_replace(',', '.', $this->discount)));
        $out = [];
        $codes = [];
        foreach ($combos as $c) {
            $parts = array_map(fn ($v) => ['id' => $v['id'], 'qty' => (int) $this->pieces[$v['piece']]['qty']], $c);
            $sig = $this->signature(array_map(fn ($p) => [$p['id'], $p['qty']], $parts));
            $sum = array_sum(array_map(fn ($v) => $v['sale'] * $this->pieces[$v['piece']]['qty'], $c));
            $cost = array_sum(array_map(fn ($v) => $v['cost'] * $this->pieces[$v['piece']]['qty'], $c));
            $stock = min(array_map(fn ($v) => intdiv(max(0, $v['stock']), max(1, $this->pieces[$v['piece']]['qty'])), $c));
            $value = implode(' + ', array_map(function ($v) {
                $p = $this->pieces[$v['piece']];
                $label = $p['label'] . ($p['qty'] > 1 ? ' ×' . $p['qty'] : '');

                return trim($label . ' ' . $v['value']);
            }, $c));
            $prev = $keep[$sig] ?? null;
            $code = ($prev['existing_id'] ?? null) ? $prev['code'] : $this->optionCode($c, $codes);
            $codes[mb_strtolower($code)] = true;
            $out[] = [
                'sig' => $sig,
                'value' => mb_substr($value, 0, 120),
                'code' => $code,
                'parts' => $parts,
                'cost' => round($cost, 2),
                'sum' => round($sum, 2),
                'sale' => ($prev['manual'] ?? false) || ($prev['existing_id'] ?? null) ? $prev['sale'] : $this->money($sum * (1 - $disc / 100)),
                'manual' => (bool) ($prev['manual'] ?? false),
                'stock' => $stock,
                'existing_id' => $prev['existing_id'] ?? null,
                'on' => $prev['on'] ?? true,
                'image' => $c[0]['image'] ?? null,
                'made' => collect($c)->contains(fn ($v) => $v['made'])
                    && collect($c)->every(fn ($v) => $v['made'] || $v['stock'] >= $this->pieces[$v['piece']]['qty']),
            ];
        }
        // opções que já existem e não saíram da combinação continuam (para desligar)
        foreach ($keep as $sig => $o) {
            if (($o['existing_id'] ?? null) && !collect($out)->contains('sig', $sig)) {
                $out[] = $o + ['orphan' => true];
            }
        }
        $this->options = $out;
    }

    public function setSale(int $i, $v): void
    {
        if (isset($this->options[$i])) {
            $this->options[$i]['sale'] = $this->money($this->num($v));
            $this->options[$i]['manual'] = true;
        }
    }

    public function applyDiscount(): void
    {
        $disc = max(0, min(90, (float) str_replace(',', '.', $this->discount)));
        foreach ($this->options as $i => $o) {
            $this->options[$i]['sale'] = $this->money($o['sum'] * (1 - $disc / 100));
            $this->options[$i]['manual'] = false;
        }
    }

    public function toggleOption(int $i): void
    {
        if (isset($this->options[$i])) {
            $this->options[$i]['on'] = !$this->options[$i]['on'];
        }
    }

    public function allOptions(bool $on): void
    {
        foreach ($this->options as $i => $o) {
            $this->options[$i]['on'] = $on;
        }
    }

    // ───────────────────────────── gravar

    public function save(VariationService $variations)
    {
        $this->code = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $this->code));
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'category_id' => 'nullable',
        ], [
            'name.required' => 'Dê um nome ao conjunto.',
            'code.required' => 'Dê um código ao conjunto (ex.: CBR).',
        ]);
        $on = collect($this->options)->filter(fn ($o) => $o['on']);
        if ($on->isEmpty() && !$this->rootId) {
            $this->addError('options', 'Escolha as peças e deixe ao menos uma opção ligada.');

            return null;
        }
        if ($on->count() > self::MAX_OPTIONS) {
            $this->addError('options', 'O conjunto ficou com ' . $on->count() . ' opções; o máximo é ' . self::MAX_OPTIONS . '. Desmarque algumas cores das peças.');

            return null;
        }
        if ($on->contains(fn ($o) => $this->num($o['sale']) <= 0)) {
            $this->addError('options', 'Toda opção ligada precisa de preço de venda.');

            return null;
        }
        $uid = (int) Auth::id();
        $codes = collect($this->options)->pluck('code')->map(fn ($c) => mb_strtolower($c));
        if ($codes->duplicates()->isNotEmpty()) {
            $this->addError('options', 'Há códigos repetidos nas opções.');

            return null;
        }
        $clash = Product::withoutGlobalScope('team_visibility')->where('user_id', $uid)
            ->whereIn('product_code', collect($this->options)->whereNull('existing_id')->pluck('code')->all() ?: ['__'])
            ->pluck('product_code')->all();
        if ($clash) {
            $this->addError('options', 'Estes códigos já são de outro produto: ' . implode(', ', array_slice($clash, 0, 4)) . '. Troque o código do conjunto.');

            return null;
        }

        $categoryId = (int) ($this->category_id ?: $this->defaultCategory($uid));
        $image = null;
        if ($this->photo) {
            $image = basename($this->photo->store('products', 'public'));
        }
        $days = max(1, min(365, (int) $this->production_days));

        $root = DB::transaction(function () use ($uid, $categoryId, $image, $days, $variations) {
            $root = $this->rootId ? Product::where('user_id', $uid)->find($this->rootId) : null;
            $multi = count($this->options) > 1;
            $sort = 0;
            foreach ($this->options as $i => $o) {
                $p = $o['existing_id'] ? Product::where('user_id', $uid)->find($o['existing_id']) : null;
                if (!$p && !$o['on']) {
                    continue;
                }
                // Sob encomenda quando o conjunto todo é, ou quando dá para montar: cada peça tem estoque ou é feita sob encomenda
                $made = $this->made_to_order || ($o['made'] ?? false);
                $data = [
                    'name' => $root === null || ($p && $p->id === $root->id) ? $this->name : mb_substr($this->name . ' ' . $o['value'], 0, 255),
                    'price' => $o['cost'],
                    'price_sale' => $this->num($o['sale']),
                    'category_id' => $categoryId,
                    'description' => $this->description ?: null,
                    'tipo' => 'kit',
                    'status' => $o['on'] ? 'ativo' : 'inativo',
                    'made_to_order' => $made,
                    'production_days' => $made ? $days : null,
                ];
                $created = !$p;
                if ($p) {
                    $p->fill($data);
                    if ($image && $root && $p->id === $root->id) {
                        $p->image = $image;
                    }
                    $p->save();
                } else {
                    $p = Product::create($data + [
                        'user_id' => $uid,
                        'product_code' => $o['code'],
                        'stock_quantity' => 0,
                        'image' => ($root === null ? $image : null) ?: 'product-placeholder.png',
                        'custos_adicionais' => 0,
                    ]);
                }
                ProdutoComponente::where('kit_produto_id', $p->id)->delete();
                foreach ($o['parts'] as $part) {
                    $c = Product::where('user_id', $uid)->find($part['id']);
                    if ($c) {
                        ProdutoComponente::create([
                            'kit_produto_id' => $p->id,
                            'componente_produto_id' => $c->id,
                            'quantidade' => $part['qty'],
                            'preco_custo_unitario' => $c->price,
                            'preco_venda_unitario' => $c->price_sale,
                        ]);
                    }
                }
                $p->refreshKitStock();
                // Fotos da opção = fotos das peças (cópia), menos na principal quando veio foto própria
                $ownPhoto = $image && $root && $p->id === $root->id;
                if (!$ownPhoto && ($created || !$p->image || $p->image === 'product-placeholder.png' || str_starts_with((string) $p->image, 'kit-'))) {
                    KitBuilder::copyImages($p);
                }

                if ($root === null) {
                    $root = $p;
                    if ($multi) {
                        $p->update(['is_variation_parent' => true, 'variation_attribute' => 'Conjunto', 'variation_value' => $o['value'], 'variation_sort' => 0]);
                    }
                } elseif ($p->id === $root->id) {
                    if ($root->is_variation_parent) {
                        $p->update(['variation_value' => $o['value']]);
                    }
                } elseif ((int) $p->parent_id === (int) $root->id) {
                    $p->update(['variation_value' => $o['value'], 'variation_sort' => ++$sort]);
                } else {
                    $variations->attach($root, $p, $root->variation_attribute ?: 'Conjunto', $o['value'], ++$sort);
                }
                $this->options[$i]['existing_id'] = $p->id;
            }

            return $root;
        });

        if (!$root) {
            $this->addError('options', 'Nada para salvar.');

            return null;
        }
        $this->rootId = $root->id;
        $this->photo = null;
        $this->notifySuccess('Conjunto "' . $this->name . '" salvo com ' . collect($this->options)->where('on', true)->count() . ' opções.');

        return redirect()->route('products.kit.moveis.edit', ['kit' => $root->id]);
    }

    // ───────────────────────────── ajudantes

    private function signature(array $parts): string
    {
        $s = array_map(fn ($p) => $p[0] . 'x' . $p[1], $parts);
        sort($s);

        return implode('_', $s);
    }

    /** CBR + o fim do código de cada peça: CBR-PT-TPT-PTVM. Nunca repete. */
    private function optionCode(array $combo, array $taken): string
    {
        $base = $this->code !== '' ? $this->code : 'KIT';
        $bits = [];
        foreach ($combo as $v) {
            $c = (string) $v['code'];
            $tail = str_contains($c, '-') ? Str::after($c, '-') : $c;
            $bits[] = Str::upper(preg_replace('/[^A-Za-z0-9-]/', '', $tail));
        }
        $code = mb_substr($base . '-' . implode('-', array_filter($bits)), 0, 90);
        $try = $code;
        $n = 2;
        while (isset($taken[mb_strtolower($try)])) {
            $try = $code . '-' . $n++;
        }

        return $try;
    }

    public static function colorKey(string $value): string
    {
        return KitBuilder::colorKey($value);
    }

    public static function shortLabel(string $name): string
    {
        return KitBuilder::shortLabel($name);
    }

    private function defaultCategory(int $uid): int
    {
        $cat = Category::where('user_id', $uid)->where('type', 'product')->whereRaw('LOWER(name) = ?', ['conjuntos'])->first()
            ?? Category::create(['user_id' => $uid, 'name' => 'Conjuntos', 'type' => 'product', 'is_active' => 1, 'icone' => 'bi-boxes', 'hexcolor_category' => '#a855f7']);

        return (int) $cat->id_category;
    }

    private function money(float $v): string
    {
        return number_format(round($v, 2), 2, ',', '.');
    }

    private function num($v): float
    {
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }
        $v = preg_replace('/[^\d,.\-]/', '', (string) $v);
        if ($v === '' || $v === '-') {
            return 0.0;
        }
        if (str_contains($v, ',')) {
            $v = str_replace(',', '.', str_replace('.', '', $v));
        }

        return (float) $v;
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())->where('type', 'product')->orderBy('name')->get(['id_category', 'name']);
        $on = collect($this->options)->where('on', true);

        return view('livewire.products.furniture-kit', [
            'categories' => $categories,
            'results' => $this->results,
            'onCount' => $on->count(),
        ]);
    }
}
