<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSplit;
use App\Models\ProductSplitItem;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Desmembrar kit: um produto comprado como kit/combo (ex.: "Mães 2026 combo")
 * vira os produtos que vêm dentro dele, cada um com o seu código. Tira N
 * unidades do kit e soma no estoque de cada item (quantidade por kit × N).
 * O item pode ser um produto que já existe ou um produto novo, cadastrado
 * aqui mesmo com os mesmos dados da tela Novo produto. A receita fica salva
 * (product_splits) e preenche a próxima vez que esse kit for desmembrado.
 */
class BreakdownKit extends Component
{
    use WithFileUploads, HasNotifications;

    public Product $kit;
    public int $units = 1;
    public array $items = [];
    public array $photos = [];
    public bool $archiveKit = false;
    public ?string $lastRecipeAt = null;
    public array $result = [];

    public function mount(Product $product): void
    {
        abort_unless((int) $product->user_id === (int) Auth::id(), 404);
        $this->kit = $product;
        $this->units = 1;
        $this->items = [];
        $this->lastRecipeAt = null;

        $last = ProductSplit::where('user_id', Auth::id())
            ->where('source_product_id', $product->id)
            ->latest('id')->with('items')->first();

        if ($last) {
            foreach ($last->items as $it) {
                $p = Product::where('user_id', Auth::id())->find($it->result_product_id);
                if ($p) {
                    $this->items[] = $this->existingRow($p, max(1, (int) $it->quantity));
                }
            }
            if ($this->items) {
                $this->lastRecipeAt = $last->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y');
            }
        }

        if (!$this->items) {
            $this->items[] = $this->newRow();
        }
    }

    // ─── Itens ────────────────────────────────────────────────────

    public function addItem(string $mode = 'new'): void
    {
        $this->items[] = $mode === 'existing' ? $this->emptyExistingRow() : $this->newRow();
    }

    public function removeItem(string $key): void
    {
        $this->items = array_values(array_filter($this->items, fn ($i) => $i['key'] !== $key));
        unset($this->photos[$key]);
        if (!$this->items) {
            $this->items[] = $this->newRow();
        }
    }

    public function setMode(string $key, string $mode): void
    {
        $i = $this->index($key);
        if ($i === null) {
            return;
        }
        $qty = $this->items[$i]['qty'];
        $this->items[$i] = ($mode === 'existing' ? $this->emptyExistingRow() : $this->newRow()) + [];
        $this->items[$i]['key'] = $key;
        $this->items[$i]['qty'] = $qty;
        unset($this->photos[$key]);
        $this->resetErrorBag();
    }

    public function pickProduct(string $key, int $productId): void
    {
        $i = $this->index($key);
        $p = Product::where('user_id', Auth::id())->find($productId);
        if ($i === null || !$p) {
            return;
        }
        $this->items[$i] = $this->existingRow($p, (int) $this->items[$i]['qty']);
        $this->items[$i]['key'] = $key;
    }

    public function clearPick(string $key): void
    {
        $i = $this->index($key);
        if ($i !== null) {
            $this->items[$i] = array_merge($this->emptyExistingRow(), ['key' => $key, 'qty' => $this->items[$i]['qty']]);
        }
    }

    public function toggleOpen(string $key): void
    {
        $i = $this->index($key);
        if ($i !== null) {
            $this->items[$i]['open'] = !($this->items[$i]['open'] ?? true);
        }
    }

    /** Some o aviso vermelho do campo assim que a pessoa mexe nele. */
    public function updated(string $name): void
    {
        if (str_starts_with($name, 'items.') || str_starts_with($name, 'photos.') || $name === 'units') {
            $this->resetErrorBag($name);
            if (preg_match('/^items\.(\d+)\.search$/', $name, $m)) {
                $this->resetErrorBag("items.{$m[1]}.search");
            }
        }
    }

    public function stepUnits(int $delta): void
    {
        $this->units = max(1, min($this->maxUnits(), $this->units + $delta));
    }

    public function updatedUnits(): void
    {
        $this->units = max(1, min(max(1, $this->maxUnits()), (int) $this->units));
    }

    /**
     * Divide o custo do kit entre os itens novos: o que sobra depois dos itens
     * que já existem (pelo custo deles) vai proporcional ao preço de venda × qtd.
     */
    public function splitCost(): void
    {
        $kitCost = (float) $this->kit->price;
        $fixed = 0.0;
        $weights = [];
        foreach ($this->items as $i => $it) {
            $qty = max(1, (int) $it['qty']);
            if ($it['mode'] === 'existing') {
                $fixed += $this->num($it['price']) * $qty;
            } else {
                $weights[$i] = max(0.0, $this->num($it['price_sale'])) * $qty;
            }
        }
        if (!$weights) {
            $this->notifyInfo('Não há itens novos para dividir o custo.');
            return;
        }
        $left = max(0.0, $kitCost - $fixed);
        $sum = array_sum($weights);
        foreach ($weights as $i => $w) {
            $qty = max(1, (int) $this->items[$i]['qty']);
            $share = $sum > 0 ? $left * $w / $sum : $left / count($weights);
            $this->items[$i]['price'] = number_format($share / $qty, 2, ',', '');
        }
        $this->notifySuccess('Custo do kit dividido entre os itens novos.');
    }

    // ─── Confirmar ────────────────────────────────────────────────

    public function confirm()
    {
        $this->validateAll();

        $userId = (int) Auth::id();
        $units = (int) $this->units;
        $created = [];

        DB::transaction(function () use ($userId, $units, &$created) {
            $kit = Product::where('user_id', $userId)->lockForUpdate()->findOrFail($this->kit->id);
            if ((int) $kit->stock_quantity < $units) {
                throw ValidationException::withMessages(['units' => "O kit só tem {$kit->stock_quantity} em estoque."]);
            }

            $split = ProductSplit::create([
                'user_id'           => $userId,
                'source_product_id' => $kit->id,
                'units'             => $units,
                'source_action'     => 'kept',
                'distributed_stock' => true,
            ]);

            foreach ($this->items as $it) {
                $qty = max(1, (int) $it['qty']);
                $add = $qty * $units;

                if ($it['mode'] === 'existing') {
                    $product = Product::where('user_id', $userId)->lockForUpdate()->findOrFail($it['product_id']);
                    $before = (int) $product->stock_quantity;
                    $product->adjustStock($add);
                    $mode = 'linked';
                } else {
                    $before = 0;
                    $product = Product::create([
                        'user_id'         => $userId,
                        'name'            => trim($it['name']),
                        'product_code'    => trim($it['product_code']),
                        'category_id'     => (int) $it['category_id'],
                        'price'           => round($this->num($it['price']), 2),
                        'price_sale'      => round($this->num($it['price_sale']), 2),
                        'price_original'  => $it['price_original'] !== '' ? round($this->num($it['price_original']), 2) : null,
                        'stock_quantity'  => $add,
                        'description'     => trim((string) $it['description']) ?: null,
                        'barcode'         => trim((string) $it['barcode']) ?: null,
                        'brand'           => trim((string) $it['brand']) ?: null,
                        'model'           => trim((string) $it['model']) ?: null,
                        'warranty_months' => (int) ($it['warranty_months'] ?: 0),
                        'condition'       => in_array($it['condition'], ['new', 'used', 'refurbished'], true) ? $it['condition'] : 'new',
                        'image'           => $this->imageFor($it, $kit),
                        'tipo'            => 'simples',
                        'status'          => 'ativo',
                    ]);
                    $mode = 'new';
                }

                ProductSplitItem::create([
                    'split_id'          => $split->id,
                    'result_product_id' => $product->id,
                    'mode'              => $mode,
                    'quantity'          => $qty,
                    'stock_assigned'    => $add,
                ]);

                $created[] = [
                    'id'     => $product->id,
                    'name'   => $product->name,
                    'code'   => $product->product_code,
                    'new'    => $mode === 'new',
                    'added'  => $add,
                    'before' => $before,
                    'after'  => (int) $product->fresh()->stock_quantity,
                ];
            }

            $kit->adjustStock(-$units);
            $kit->refresh();
            if ($this->archiveKit && (int) $kit->stock_quantity === 0) {
                $kit->update(['status' => 'inativo']);
                $split->update(['source_action' => 'archived']);
            }
            $this->kit = $kit;
        });

        $this->result = [
            'units'    => $units,
            'kitStock' => (int) $this->kit->stock_quantity,
            'items'    => $created,
        ];
        $this->photos = [];
        $this->notifySuccess(sprintf('%d %s desmembrado%s em %d produto(s).', $units, $units === 1 ? 'kit' : 'kits', $units === 1 ? '' : 's', count($created)));
    }

    /** Depois de desmembrar: começa outra vez com a mesma receita. */
    public function again(): void
    {
        $this->result = [];
        $this->mount($this->kit->fresh());
    }

    // ─── Tela ────────────────────────────────────────────────────

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
            ->where('type', 'product')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id_category', 'name', 'icone']);
    }

    public function render()
    {
        $search = [];
        foreach ($this->items as $it) {
            $term = trim((string) ($it['search'] ?? ''));
            if ($it['mode'] === 'existing' && !$it['product_id'] && mb_strlen($term) >= 2) {
                $search[$it['key']] = Product::where('user_id', Auth::id())
                    ->where('id', '!=', $this->kit->id)
                    ->where('tipo', '!=', 'kit')
                    ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                        ->orWhere('product_code', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%"))
                    ->orderByRaw('CASE WHEN product_code = ? THEN 0 ELSE 1 END', [$term])
                    ->orderBy('name')
                    ->limit(8)
                    ->get(['id', 'name', 'product_code', 'stock_quantity', 'price', 'price_sale', 'image', 'variation_value', 'parent_id']);
            }
        }

        return view('livewire.products.breakdown-kit', [
            'search'     => $search,
            'categories' => $this->categories,
            'maxUnits'   => $this->maxUnits(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────

    private function validateAll(): void
    {
        $this->resetErrorBag();
        $errors = [];
        $userId = (int) Auth::id();
        $max = $this->maxUnits();

        if ($this->kit->isKit()) {
            $errors['units'] = 'Kit montado no app não tem estoque próprio para desmembrar.';
        } elseif ($max < 1) {
            $errors['units'] = 'O kit está sem estoque.';
        } elseif ($this->units < 1 || $this->units > $max) {
            $errors['units'] = "Escolha de 1 a {$max} unidade(s).";
        }

        $codes = [];
        $picked = [];
        $catIds = $this->categories->pluck('id_category')->map(fn ($v) => (int) $v)->all();

        foreach ($this->items as $i => $it) {
            $k = "items.{$i}";
            if ((int) $it['qty'] < 1 || (int) $it['qty'] > 999) {
                $errors["{$k}.qty"] = 'Quantidade por kit de 1 a 999.';
            }

            if ($it['mode'] === 'existing') {
                if (!$it['product_id']) {
                    $errors["{$k}.search"] = 'Escolha o produto na busca.';
                } elseif (in_array($it['product_id'], $picked, true)) {
                    $errors["{$k}.search"] = 'Este produto já está na lista. Aumente a quantidade dele.';
                } elseif (!Product::where('user_id', $userId)->whereKey($it['product_id'])->exists()) {
                    $errors["{$k}.search"] = 'Produto não encontrado.';
                }
                $picked[] = $it['product_id'];
                continue;
            }

            $name = trim((string) $it['name']);
            $code = trim((string) $it['product_code']);
            if ($name === '') {
                $errors["{$k}.name"] = 'Informe o nome.';
            } elseif (mb_strlen($name) > 255) {
                $errors["{$k}.name"] = 'Nome muito longo.';
            }
            if ($code === '') {
                $errors["{$k}.product_code"] = 'Informe o código do item.';
            } elseif (in_array(mb_strtolower($code), $codes, true)) {
                $errors["{$k}.product_code"] = 'Código repetido na lista.';
            } elseif ($code === $this->kit->product_code) {
                $errors["{$k}.product_code"] = 'Use um código diferente do kit.';
            } elseif ($other = Product::where('user_id', $userId)->where('product_code', $code)->first(['id', 'name'])) {
                $errors["{$k}.product_code"] = 'Já existe "' . Str::limit($other->name, 40) . '" com este código. Use "Produto que já tenho".';
            }
            $codes[] = mb_strtolower($code);
            if (!in_array((int) $it['category_id'], $catIds, true)) {
                $errors["{$k}.category_id"] = 'Escolha a categoria.';
            }
            if (trim((string) $it['price']) === '' || $this->num($it['price']) < 0) {
                $errors["{$k}.price"] = 'Informe o custo (pode usar "Dividir o custo").';
            }
            if ($this->num($it['price_sale']) <= 0) {
                $errors["{$k}.price_sale"] = 'Informe o preço de venda.';
            }
            if (mb_strlen((string) $it['barcode']) > 15) {
                $errors["{$k}.barcode"] = 'Código de barras com até 15 dígitos.';
            }
            if (mb_strlen((string) $it['description']) > 5000) {
                $errors["{$k}.description"] = 'Descrição muito longa.';
            }
            $photo = $this->photos[$it['key']] ?? null;
            if ($photo) {
                $mime = strtolower((string) $photo->getMimeType());
                if (!str_starts_with($mime, 'image/') || $photo->getSize() > 2048 * 1024) {
                    $errors["photos.{$it['key']}"] = 'Foto até 2 MB (jpg, png, webp).';
                }
            }
        }

        if ($errors) {
            // Abre os itens com erro para a pessoa ver o que falta
            foreach (array_keys($errors) as $key) {
                if (preg_match('/^items\.(\d+)\./', $key, $m)) {
                    $this->items[(int) $m[1]]['open'] = true;
                }
            }
            throw ValidationException::withMessages($errors);
        }
    }

    private function imageFor(array $it, Product $kit): string
    {
        $photo = $this->photos[$it['key']] ?? null;
        if ($photo) {
            return basename($photo->store('products', 'public'));
        }
        if (!empty($it['use_kit_photo']) && $kit->image && $kit->image !== 'product-placeholder.png'
            && Storage::disk('public')->exists('products/' . $kit->image)) {
            // Cópia própria: apagar um produto apaga a foto dele
            $ext = pathinfo($kit->image, PATHINFO_EXTENSION) ?: 'jpg';
            $name = Str::random(40) . '.' . $ext;
            Storage::disk('public')->copy('products/' . $kit->image, 'products/' . $name);

            return $name;
        }

        return 'product-placeholder.png';
    }

    private function maxUnits(): int
    {
        return $this->kit->isKit() ? 0 : max(0, (int) $this->kit->stock_quantity);
    }

    private function index(string $key): ?int
    {
        foreach ($this->items as $i => $it) {
            if ($it['key'] === $key) {
                return $i;
            }
        }

        return null;
    }

    private function newRow(): array
    {
        $n = count(array_filter($this->items, fn ($i) => $i['mode'] === 'new')) + 1;

        return [
            'key'             => Str::random(8),
            'mode'            => 'new',
            'open'            => true,
            'qty'             => 1,
            'product_id'      => null,
            'search'          => '',
            'label'           => '',
            'code_label'      => '',
            'stock'           => 0,
            'name'            => '',
            'product_code'    => $this->kit->product_code ? $this->kit->product_code . '-' . $n : '',
            'category_id'     => (string) ($this->kit->category_id ?? ''),
            'price'           => '',
            'price_sale'      => '',
            'price_original'  => '',
            'description'     => '',
            'barcode'         => '',
            'brand'           => (string) ($this->kit->brand ?? ''),
            'model'           => '',
            'warranty_months' => '3',
            'condition'       => 'new',
            'use_kit_photo'   => false,
        ];
    }

    private function emptyExistingRow(): array
    {
        return array_merge($this->newRow(), ['mode' => 'existing', 'product_code' => '', 'brand' => '']);
    }

    private function existingRow(Product $p, int $qty): array
    {
        return array_merge($this->emptyExistingRow(), [
            'qty'        => max(1, $qty),
            'product_id' => $p->id,
            'label'      => $p->name . ($p->parent_id && $p->variation_value ? ' · ' . $p->variation_value : ''),
            'code_label' => (string) $p->product_code,
            'stock'      => (int) $p->stock_quantity,
            'price'      => number_format((float) $p->price, 2, ',', ''),
            'price_sale' => number_format((float) $p->price_sale, 2, ',', ''),
            'image_url'  => $p->image_url,
        ]);
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

        return is_numeric($value) ? (float) $value : 0.0;
    }
}
