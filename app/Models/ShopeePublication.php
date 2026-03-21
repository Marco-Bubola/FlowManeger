<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopeePublication extends Model
{
    use HasFactory;

    protected $table = 'shopee_publications';

    protected $fillable = [
        'user_id',
        'shop_id',
        'shopee_item_id',
        'shopee_category_id',
        'shopee_permalink',
        'title',
        'description',
        'price',
        'available_quantity',
        'condition',
        'weight_grams',
        'length_cm',
        'width_cm',
        'height_cm',
        'days_to_ship',
        'has_variations',
        'pictures',
        'shopee_attributes',
        'status',
        'sync_status',
        'error_message',
        'last_sync_at',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'length_cm'          => 'decimal:2',
        'width_cm'           => 'decimal:2',
        'height_cm'          => 'decimal:2',
        'has_variations'     => 'boolean',
        'pictures'           => 'array',
        'shopee_attributes'  => 'array',
        'last_sync_at'       => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produtos internos vinculados a esta publicação Shopee.
     * O pivot inclui shopee_model_id para o mapeamento de variações.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'shopee_publication_products',
            'shopee_publication_id',
            'product_id'
        )
        ->withPivot(
            'shopee_model_id',
            'shopee_model_sku',
            'variation_attributes',
            'quantity',
            'unit_cost',
            'sort_order'
        )
        ->withTimestamps()
        ->orderByPivot('sort_order');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(ShopeeSyncLog::class, 'entity_id')
            ->where('entity_type', 'publication');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Calcula a quantidade disponível com base no estoque dos produtos vinculados.
     * Respeita as quantidades configuradas no pivot.
     */
    public function calculateAvailableQuantity(): int
    {
        $products = $this->products;

        if ($products->isEmpty()) {
            return 0;
        }

        $minQuantity = PHP_INT_MAX;

        foreach ($products as $product) {
            $pivotQty = $product->pivot->quantity ?? 1;
            if ($pivotQty <= 0) {
                $pivotQty = 1;
            }
            $available = (int) floor($product->stock_quantity / $pivotQty);
            $minQuantity = min($minQuantity, $available);
        }

        return max(0, $minQuantity === PHP_INT_MAX ? 0 : $minQuantity);
    }

    /**
     * Verifica se a publicação possui todos os dados logísticos obrigatórios.
     */
    public function hasLogisticsData(): bool
    {
        return $this->weight_grams > 0;
    }
}
