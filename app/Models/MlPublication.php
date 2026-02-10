<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MlPublication extends Model
{
    use HasFactory;

    protected $table = 'ml_publications';

    protected $fillable = [
        'ml_item_id',
        'ml_category_id',
        'ml_permalink',
        'title',
        'description',
        'price',
        'available_quantity',
        'publication_type',
        'listing_type',
        'condition',
        'warranty',
        'free_shipping',
        'local_pickup',
        'status',
        'sync_status',
        'last_sync_at',
        'error_message',
        'ml_attributes',
        'pictures',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'free_shipping' => 'boolean',
        'local_pickup' => 'boolean',
        'last_sync_at' => 'datetime',
        'ml_attributes' => 'array',
        'pictures' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Produtos vinculados a esta publicação (many-to-many)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'ml_publication_products',
            'ml_publication_id',
            'product_id'
        )
        ->withPivot('quantity', 'unit_cost', 'sort_order')
        ->withTimestamps()
        ->orderByPivot('sort_order');
    }

    /**
     * Usuário criador da publicação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Logs de estoque relacionados
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(MlStockLog::class, 'ml_publication_id');
    }

    /**
     * Pedidos do Mercado Livre vinculados
     */
    public function orders(): HasMany
    {
        return $this->hasMany(MercadoLivreOrder::class, 'ml_item_id', 'ml_item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Apenas publicações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Apenas publicações do tipo kit
     */
    public function scopeKits($query)
    {
        return $query->where('publication_type', 'kit');
    }

    /**
     * Publicações simples (1 produto)
     */
    public function scopeSimple($query)
    {
        return $query->where('publication_type', 'simple');
    }

    /**
     * Publicações com erro de sincronização
     */
    public function scopeWithErrors($query)
    {
        return $query->where('sync_status', 'error');
    }

    /**
     * Publicações pendentes de sincronização
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Publicações de um produto específico (busca na pivot)
     */
    public function scopeWithProduct($query, $productId)
    {
        return $query->whereHas('products', function ($q) use ($productId) {
            $q->where('products.id', $productId);
        });
    }

    /**
     * Publicações que contém produto com mesmo product_code
     */
    public function scopeWithProductCode($query, $productCode)
    {
        return $query->whereHas('products', function ($q) use ($productCode) {
            $q->where('products.product_code', $productCode);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica se está publicada e ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se é um kit (múltiplos produtos)
     */
    public function isKit(): bool
    {
        return $this->publication_type === 'kit';
    }

    /**
     * Verifica se está sincronizado com o ML
     */
    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    /**
     * Calcula quantidade disponível baseada no estoque de todos os produtos
     * Leva em conta a quantidade necessária de cada produto no kit
     */
    public function calculateAvailableQuantity(): int
    {
        $minQuantity = PHP_INT_MAX;

        foreach ($this->products as $product) {
            $quantityNeeded = $product->pivot->quantity; // Quantidade do produto por venda
            $availableUnits = (int) floor($product->stock_quantity / $quantityNeeded);
            $minQuantity = min($minQuantity, $availableUnits);
        }

        return $minQuantity === PHP_INT_MAX ? 0 : $minQuantity;
    }

    /**
     * Atualiza quantidade disponível no ML baseado no estoque
     * Retorna a nova quantidade
     */
    public function syncQuantityToMl(): int
    {
        $newQuantity = $this->calculateAvailableQuantity();
        
        $this->update([
            'available_quantity' => $newQuantity,
            'sync_status' => 'pending', // Marca para sincronização com API do ML
        ]);

        return $newQuantity;
    }

    /**
     * Subtrai estoque de todos os produtos quando há uma venda no ML
     * 
     * @param int $quantity Quantidade de kits vendidos
     * @param string|null $mlOrderId ID do pedido ML para log
     * @return array ['success' => bool, 'message' => string, 'logs' => array]
     */
    public function deductStock(int $quantity, ?string $mlOrderId = null): array
    {
        $transactionId = \Illuminate\Support\Str::uuid()->toString();
        $logs = [];

        try {
            \DB::beginTransaction();

            foreach ($this->products as $product) {
                $quantityToDeduct = $product->pivot->quantity * $quantity; // Ex: kit com 2 shampoos, vendeu 3 kits = 6 unidades
                $oldStock = $product->stock_quantity;
                $newStock = max(0, $oldStock - $quantityToDeduct);

                // Atualiza estoque
                $product->update(['stock_quantity' => $newStock]);

                // Registra log
                $log = MlStockLog::create([
                    'product_id' => $product->id,
                    'ml_publication_id' => $this->id,
                    'operation_type' => 'ml_sale',
                    'quantity_before' => $oldStock,
                    'quantity_after' => $newStock,
                    'quantity_change' => -$quantityToDeduct,
                    'source' => 'MlPublication::deductStock',
                    'ml_order_id' => $mlOrderId,
                    'notes' => "Venda ML: {$quantity} unidade(s) de publicação ID {$this->id}",
                    'transaction_id' => $transactionId,
                ]);

                $logs[] = $log;
            }

            // Atualiza quantidade disponível na publicação
            $this->syncQuantityToMl();

            \DB::commit();

            return [
                'success' => true,
                'message' => 'Estoque deduzido com sucesso',
                'logs' => $logs,
            ];

        } catch (\Exception $e) {
            \DB::rollBack();

            // Marca logs como revertidos
            foreach ($logs as $log) {
                $log->update(['rolled_back' => true]);
            }

            return [
                'success' => false,
                'message' => 'Erro ao deduzir estoque: ' . $e->getMessage(),
                'logs' => $logs,
            ];
        }
    }

    /**
     * Adiciona um produto à publicação
     */
    public function addProduct(int $productId, int $quantity = 1, ?float $unitCost = null, int $sortOrder = 0): void
    {
        $this->products()->attach($productId, [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'sort_order' => $sortOrder,
        ]);

        // Recalcula quantidade disponível
        $this->syncQuantityToMl();
    }

    /**
     * Remove um produto da publicação
     */
    public function removeProduct(int $productId): void
    {
        $this->products()->detach($productId);
        
        // Recalcula quantidade disponível
        $this->syncQuantityToMl();
    }

    /**
     * Atualiza quantidade de um produto na publicação
     */
    public function updateProductQuantity(int $productId, int $quantity): void
    {
        $this->products()->updateExistingPivot($productId, [
            'quantity' => $quantity,
        ]);

        // Recalcula quantidade disponível
        $this->syncQuantityToMl();
    }
}
