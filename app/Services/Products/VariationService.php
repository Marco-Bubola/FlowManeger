<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Gestão de variações (produto-pai + variantes) — Estratégia B.
 * 1 nível só (variante não tem sub-variante). Não mexe em estoque/preço:
 * cada variante é independente.
 */
class VariationService
{
    /**
     * Anexa $child como variação de $parent.
     */
    public function attach(Product $parent, Product $child, string $attribute, string $value, ?int $sort = null): void
    {
        if ($parent->id === $child->id) {
            throw new \InvalidArgumentException('Um produto não pode ser variação de si mesmo.');
        }
        if ($parent->parent_id) {
            throw new \InvalidArgumentException('O pai não pode ser, ele mesmo, uma variação.');
        }
        if ($child->is_variation_parent && $child->variants()->exists()) {
            throw new \InvalidArgumentException('Este produto já é pai de outras variações.');
        }
        if (($parent->user_id ?? null) !== ($child->user_id ?? null)) {
            throw new \InvalidArgumentException('Produtos de usuários diferentes.');
        }

        $attribute = trim($attribute) ?: 'Variação';
        $value = trim($value) ?: 'Padrão';

        DB::transaction(function () use ($parent, $child, $attribute, $value, $sort) {
            if (!$parent->is_variation_parent) {
                $parent->update([
                    'is_variation_parent' => true,
                    'variation_attribute' => $attribute,
                    'variation_value'     => $parent->variation_value ?: 'Padrão',
                ]);
            }

            $child->update([
                'parent_id'           => $parent->id,
                'is_variation_parent' => false,
                'variation_attribute' => $attribute,
                'variation_value'     => $value,
                'variation_sort'      => $sort ?? (((int) $parent->variants()->max('variation_sort')) + 1),
            ]);
        });
    }

    /**
     * Liga um produto existente a um grupo de variações (tela Editar e Edição em massa).
     * Se $parent já for uma variação, liga ao produto principal dele. O nome da
     * variação sai do que o nome tem a mais ("Batom Nude" → "Nude"), senão do preço.
     */
    public function link(Product $parent, Product $child): Product
    {
        $root = $parent->parent_id ? ($parent->parent ?? $parent) : $parent;
        $label = $this->labelFor($root, trim((string) $child->name), (string) $child->product_code, (float) $child->price_sale);
        $this->attach($root, $child, $root->variation_attribute ?: 'Variação', $label);

        return $root;
    }

    /**
     * Cria um novo produto e já o anexa como variação de $parent.
     */
    public function createVariant(Product $parent, array $data, string $value): Product
    {
        return DB::transaction(function () use ($parent, $data, $value) {
            $child = Product::create(array_merge([
                'user_id'        => $parent->user_id,
                'category_id'    => $parent->category_id,
                'tipo'           => 'simples',
                'status'         => 'ativo',
                'image'          => $data['image'] ?? ($parent->image ?: 'product-placeholder.png'),
                'stock_quantity' => 0,
                'price'          => 0,
                'price_sale'     => 0,
            ], $data, [
                'product_code'   => $data['product_code'] ?? $this->generateCode(),
            ]));

            $this->attach($parent, $child, $parent->variation_attribute ?: 'Variação', $value);
            return $child->fresh();
        });
    }

    /**
     * Desanexa uma variante (volta a ser standalone). Se o pai ficar sem
     * variantes, deixa de ser pai.
     */
    public function detach(Product $variant): void
    {
        DB::transaction(function () use ($variant) {
            $parent = $variant->parent;

            $variant->update([
                'parent_id'           => null,
                'variation_value'     => null,
                'variation_attribute' => null,
                'variation_sort'      => 0,
            ]);

            if ($parent && !$parent->variants()->exists()) {
                $parent->update(['is_variation_parent' => false]);
            }
        });
    }

    public function generateCode(): string
    {
        do {
            $code = 'VAR-' . strtoupper(substr(uniqid(), -7));
        } while (Product::where('product_code', $code)->exists());
        return $code;
    }

    /**
     * Resolve a entrada de um produto (criação manual ou importação):
     *  - Procura um produto existente do mesmo usuário por CÓDIGO ou NOME.
     *  - Se achar e já houver um membro da família com o MESMO price e price_sale
     *    → soma o estoque nesse membro.            (action: 'stock')
     *  - Se achar mas o preço/preço de venda DIFERE
     *    → cria uma variação real (parent_id).      (action: 'variation')
     *  - Se não achar nada
     *    → cria um produto standalone.              (action: 'created')
     *
     * @return array{action:string, product:\App\Models\Product}
     */
    public function intake(int $userId, array $data): array
    {
        $code      = trim((string) ($data['product_code'] ?? ''));
        $name      = trim((string) ($data['name'] ?? ''));
        $price     = round((float) ($data['price'] ?? 0), 2);
        $priceSale = round((float) ($data['price_sale'] ?? 0), 2);
        $stock     = max(0, (int) ($data['stock_quantity'] ?? 0));

        // 1) Âncora: produto existente por código OU nome (prioriza código exato)
        $anchor = Product::where('user_id', $userId)
            ->where(function ($q) use ($code, $name) {
                $applied = false;
                if ($code !== '') { $q->where('product_code', $code); $applied = true; }
                if ($name !== '') { $applied ? $q->orWhere('name', $name) : $q->where('name', $name); }
            })
            ->when($code !== '', fn ($q) => $q->orderByRaw('CASE WHEN product_code = ? AND name = ? THEN 0 WHEN product_code = ? THEN 1 ELSE 2 END', [$code, $name, $code]))
            ->first();

        if (!$anchor) {
            return ['action' => 'created', 'product' => $this->createStandalone($userId, $data, $code, $price, $priceSale, $stock)];
        }

        // Raiz da família (se a âncora já for uma variante, sobe pro pai)
        $root = $anchor->parent_id ? ($anchor->parent ?? $anchor) : $anchor;

        // 2) O estoque entra no MESMO item: o produto achado pelo código (se o preço
        //    bate) ou um membro da família com o mesmo código/nome e o mesmo preço.
        //    Antes ia para o primeiro membro com o mesmo preço, então a cor "Nude"
        //    importada caía na "Vermelho" quando as duas custavam igual.
        $samePrice = fn (Product $p) => abs((float) $p->price - $price) < 0.005
            && abs((float) $p->price_sale - $priceSale) < 0.005;
        $sameName = fn (Product $p) => $name !== '' && mb_strtolower(trim($p->name)) === mb_strtolower($name);
        $sameCode = fn (Product $p) => $code !== '' && $p->product_code === $code;

        $members = $root->family()->get();
        $family = $members->filter($samePrice);
        // Código que só um item da família usa identifica o item; código repetido
        // (todas as cores com o mesmo código) precisa do nome para saber qual é.
        $codeIsUnique = $code !== '' && $members->filter($sameCode)->count() === 1;

        $match = $family->first(fn ($p) => $sameCode($p) && $sameName($p))
            ?? ($codeIsUnique ? $family->first($sameCode) : null)
            ?? $family->first(fn ($p) => $sameName($p) && ($code === '' || !$members->contains($sameCode) || $sameCode($p)));

        if ($match) {
            if ($stock > 0) {
                $match->increment('stock_quantity', $stock);
            }
            return ['action' => 'stock', 'product' => $match->fresh()];
        }

        // 3) Item diferente (outro preço, ou outro código/nome) → cria variação na raiz
        $value = $this->labelFor($root, $name, $code, $priceSale);
        $variant = $this->createVariant($root, [
            'name'           => $name !== '' ? $name : $root->name,
            'product_code'   => $code !== '' ? $code : $this->generateCode(),
            'price'          => $price,
            'price_sale'     => $priceSale,
            'stock_quantity' => $stock,
            'category_id'    => $data['category_id'] ?? $root->category_id,
            'image'          => $data['image'] ?? ($root->image ?: 'product-placeholder.png'),
            'description'    => $data['description'] ?? null,
            'status'         => $data['status'] ?? 'ativo',
        ], $value);

        return ['action' => 'variation', 'product' => $variant];
    }

    /**
     * Nome curto da variação: o que o nome tem a mais que o produto principal
     * ("batom matte Nude" → "Nude"); sem isso, o preço; se o preço já é usado
     * por outra variação, o código.
     */
    public function labelFor(Product $root, string $name, string $code, float $priceSale): string
    {
        $base = trim((string) $root->name);
        if ($name !== '' && $base !== '' && mb_stripos($name, $base) === 0) {
            $rest = trim(mb_substr($name, mb_strlen($base)), " -·|/");
            if ($rest !== '') {
                return mb_substr($rest, 0, 120);
            }
        }

        $byPrice = 'R$ ' . number_format($priceSale, 2, ',', '.');
        $taken = $root->family()->pluck('variation_value')->filter()->all();
        if (!in_array($byPrice, $taken, true)) {
            return $byPrice;
        }

        return $code !== '' ? $code : ($name !== '' ? mb_substr($name, 0, 120) : $byPrice);
    }

    private function createStandalone(int $userId, array $data, string $code, float $price, float $priceSale, int $stock): Product
    {
        $attrs = [
            'user_id'           => $userId,
            'name'              => trim((string) ($data['name'] ?? 'Produto')),
            'product_code'      => $code !== '' ? $code : $this->generateCode(),
            'price'             => $price,
            'price_sale'        => $priceSale,
            'stock_quantity'    => $stock,
            'category_id'       => $data['category_id'] ?? null,
            'image'             => $data['image'] ?? 'product-placeholder.png',
            'tipo'              => 'simples',
            'status'            => $data['status'] ?? 'ativo',
            'custos_adicionais' => 0,
            'description'       => $data['description'] ?? null,
        ];

        // Colunas opcionais (algumas são NOT NULL com default no banco):
        // só incluímos quando vierem preenchidas, deixando o default agir.
        foreach (['barcode', 'brand', 'model', 'warranty_months', 'condition'] as $opt) {
            if (isset($data[$opt]) && $data[$opt] !== null && $data[$opt] !== '') {
                $attrs[$opt] = $data[$opt];
            }
        }

        return Product::create($attrs);
    }
}
