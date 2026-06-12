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
}
