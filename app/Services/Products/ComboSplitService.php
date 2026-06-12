<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductSplit;
use App\Models\ProductSplitItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Divide um combo/kit em produtos separados.
 * Cada item do plano pode ser:
 *  - 'new'       → cria produto novo
 *  - 'linked'    → vincula a um produto existente (não cria)
 *  - 'variation' → cria/vincula como variação de um produto-pai
 */
class ComboSplitService
{
    public function __construct(private VariationService $variations) {}

    /**
     * @param Product $source  combo/kit a dividir
     * @param array   $plan    ['source_action'=>'kept|archived|converted','distribute_stock'=>bool,'items'=>[...]]
     * @return Product[]
     */
    public function split(Product $source, array $plan): array
    {
        $items = $plan['items'] ?? [];
        if (empty($items)) {
            throw new \InvalidArgumentException('Nenhum item para dividir.');
        }

        return DB::transaction(function () use ($source, $plan, $items) {
            $log = ProductSplit::create([
                'user_id'           => $source->user_id ?? Auth::id(),
                'source_product_id' => $source->id,
                'source_action'     => $plan['source_action'] ?? 'kept',
                'distributed_stock' => (bool) ($plan['distribute_stock'] ?? false),
            ]);

            $results = [];
            foreach ($items as $item) {
                $qty  = max(1, (int) ($item['quantity'] ?? 1));
                $mode = $item['mode'] ?? 'new';
                $product = null;
                $parentId = null;

                if ($mode === 'new') {
                    $product = $this->createNewProduct($source, $item);
                } elseif ($mode === 'linked') {
                    $product = Product::where('user_id', $source->user_id)->findOrFail($item['target_product_id']);
                } elseif ($mode === 'variation') {
                    $parent = Product::where('user_id', $source->user_id)->findOrFail($item['parent_id']);
                    $parentId = $parent->id;
                    $child = !empty($item['target_product_id'])
                        ? Product::where('user_id', $source->user_id)->findOrFail($item['target_product_id'])
                        : $this->createNewProduct($source, $item);
                    $this->variations->attach(
                        $parent,
                        $child,
                        $item['attribute'] ?? ($parent->variation_attribute ?: 'Variação'),
                        $item['value'] ?? ($child->name ?? 'Variação')
                    );
                    $product = $child;
                } else {
                    throw new \InvalidArgumentException("Modo inválido: {$mode}");
                }

                // Distribuição de estoque (opcional)
                $stockAssigned = 0;
                if (!empty($plan['distribute_stock'])) {
                    $stockAssigned = (int) ($item['stock'] ?? $qty);
                    if ($stockAssigned > 0) {
                        $product->increment('stock_quantity', $stockAssigned);
                    }
                }

                ProductSplitItem::create([
                    'split_id'            => $log->id,
                    'result_product_id'   => $product->id,
                    'variation_parent_id' => $parentId,
                    'mode'                => $mode,
                    'quantity'            => $qty,
                    'stock_assigned'      => $stockAssigned,
                ]);

                $results[] = $product;
            }

            // Ação na origem
            match ($plan['source_action'] ?? 'kept') {
                'archived'  => $source->update(['status' => 'inativo']),
                'converted' => $source->update(['tipo' => 'simples']),
                default     => null, // 'kept' — combo continua como está
            };

            return $results;
        });
    }

    private function createNewProduct(Product $source, array $item): Product
    {
        return Product::create([
            'user_id'        => $source->user_id,
            'name'           => $item['name'] ?? 'Produto da divisão',
            'product_code'   => !empty($item['product_code']) ? $item['product_code'] : $this->genCode(),
            'price'          => (float) ($item['price'] ?? 0),
            'price_sale'     => (float) ($item['price_sale'] ?? 0),
            'stock_quantity' => (int) ($item['stock_quantity'] ?? 0),
            'category_id'    => $item['category_id'] ?? $source->category_id,
            'image'          => $item['image'] ?? 'product-placeholder.png',
            'tipo'           => 'simples',
            'status'         => 'ativo',
            'description'    => $item['description'] ?? null,
        ]);
    }

    private function genCode(): string
    {
        do {
            $code = 'P-' . strtoupper(substr(uniqid(), -7));
        } while (Product::where('product_code', $code)->exists());
        return $code;
    }
}
