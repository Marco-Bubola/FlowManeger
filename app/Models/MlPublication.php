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
        'ml_family_name',
        'ml_user_product_id',
        'ml_variations',
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
        'ml_variations' => 'array',
    ];

    protected static function booted(): void
    {
        // As variações do item no ML (ml_variations) viram linhas ligáveis a
        // produtos sempre que mudam (busca do item, importação, venda...).
        static::saved(function (MlPublication $publication) {
            if ($publication->wasRecentlyCreated || $publication->wasChanged('ml_variations')) {
                MlPublicationVariation::syncFromPublication($publication);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Variações do anúncio no ML e o produto ligado a cada uma.
     */
    public function variationLinks(): HasMany
    {
        return $this->hasMany(MlPublicationVariation::class, 'ml_publication_id')->orderBy('sort_order');
    }

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
        return $query->usingProducts([$productId]);
    }

    /**
     * Publicações que usam algum destes produtos: no anúncio inteiro (pivot)
     * ou ligado a uma variação.
     */
    public function scopeUsingProducts($query, array $productIds)
    {
        $productIds = array_values(array_filter($productIds));
        return $query->where(function ($w) use ($productIds) {
            $w->whereHas('products', fn ($q) => $q->withoutGlobalScope('team_visibility')->whereIn('products.id', $productIds));
            if (MlPublicationVariation::tableExists()) {
                $w->orWhereHas('variationLinks', fn ($q) => $q->whereIn('product_id', $productIds));
            }
        });
    }

    /**
     * Publicações que contém produto com mesmo product_code
     */
    public function scopeWithProductCode($query, $productCode)
    {
        return $query->where(function ($w) use ($productCode) {
            $w->whereHas('products', function ($q) use ($productCode) {
                $q->where('products.product_code', $productCode);
            });
            if (MlPublicationVariation::tableExists()) {
                $w->orWhereHas('variationLinks', fn ($q) => $q->whereIn(
                    'product_id',
                    Product::withoutGlobalScope('team_visibility')->where('product_code', $productCode)->select('id')
                ));
            }
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
     * Anúncio de família/catálogo (User Products): o título só muda no ML.
     */
    public function hasLockedTitle(): bool
    {
        return filled($this->ml_family_name);
    }

    /**
     * Quantidade de variações do anúncio no ML (0 = sem variações).
     */
    public function mlVariationCount(): int
    {
        return is_array($this->ml_variations) ? count($this->ml_variations) : 0;
    }

    /**
     * Anúncio com 2+ variações: cada variação é ligada ao seu produto
     * (com 1 variação vale o vínculo normal do anúncio).
     */
    public function usesVariationLinks(): bool
    {
        return $this->mlVariationCount() >= 2 && MlPublicationVariation::tableExists();
    }

    /**
     * Variações com produto ligado (produto carregado, sem filtro de equipe).
     */
    public function mappedVariations()
    {
        if (! MlPublicationVariation::tableExists()) {
            return collect();
        }
        return $this->variationLinks()->whereNotNull('product_id')->with('product')->get()
            ->filter(fn ($v) => $v->product !== null)
            ->values();
    }

    /** Variação do pedido com produto ligado (ou null). */
    public function mappedVariation($mlVariationId): ?MlPublicationVariation
    {
        if ($mlVariationId === null || $mlVariationId === '' || ! MlPublicationVariation::tableExists()) {
            return null;
        }
        $row = $this->variationLinks()->where('ml_variation_id', (string) $mlVariationId)
            ->whereNotNull('product_id')->with('product')->first();
        return $row && $row->product ? $row : null;
    }

    /** Quantas vendas da variação o estoque do produto ligado aguenta. */
    public static function variationAvailableQuantity(MlPublicationVariation $row): int
    {
        return $row->product ? intdiv(self::productAvailableStock($row->product), $row->perSale()) : 0;
    }

    /** Tem algum produto ligado (no anúncio ou em variação)? */
    public function hasStockLink(): bool
    {
        if ($this->usesVariationLinks() && $this->mappedVariations()->isNotEmpty()) {
            return true;
        }
        return $this->linkedProducts()->isNotEmpty();
    }

    /**
     * Calcula quantidade disponível baseada no estoque de todos os produtos
     * Leva em conta a quantidade necessária de cada produto no kit.
     * Anúncio com variações ligadas: soma das variações (ligada = estoque do
     * produto; sem produto = o que o ML tem).
     */
    public function calculateAvailableQuantity(): int
    {
        if ($this->usesVariationLinks()) {
            $rows = $this->variationLinks()->with('product')->get();
            if ($rows->contains(fn ($r) => $r->product_id && $r->product)) {
                return (int) $rows->sum(fn ($r) => $r->product_id && $r->product
                    ? self::variationAvailableQuantity($r)
                    : max(0, (int) $r->ml_available_quantity));
            }
        }

        $minQuantity = PHP_INT_MAX;

        // Webhook/fila rodam sem usuário logado: tira o filtro de equipe
        // (a publicação já é do dono).
        foreach ($this->linkedProducts() as $product) {
            $quantityNeeded = max(1, (int) $product->pivot->quantity); // Quantidade do produto por venda
            $availableUnits = intdiv(self::productAvailableStock($product), $quantityNeeded);
            $minQuantity = min($minQuantity, $availableUnits);
        }

        return $minQuantity === PHP_INT_MAX ? 0 : $minQuantity;
    }

    /**
     * Produtos vinculados sem o filtro de equipe (seguro em webhook/fila).
     */
    public function linkedProducts()
    {
        return $this->products()->withoutGlobalScope('team_visibility')->get();
    }

    /**
     * Componentes de um kit (sem filtro de equipe): [[Product $comp, int $porKit], ...]
     */
    protected static function kitComponents(Product $kit): array
    {
        $out = [];
        foreach ($kit->componentes()->get() as $pc) {
            $comp = Product::withoutGlobalScope('team_visibility')->find($pc->componente_produto_id);
            $per = (int) ($pc->quantidade ?? 0);
            if ($comp && $per > 0) {
                $out[] = [$comp, $per];
            }
        }
        return $out;
    }

    /**
     * Estoque vendável do produto. Kit não tem estoque próprio: vale o
     * componente que acaba primeiro (mesma regra de Product::availableStock()).
     */
    public static function productAvailableStock(Product $product): int
    {
        if (! $product->isKit()) {
            return $product->availableStock();
        }
        $min = null;
        foreach (self::kitComponents($product) as [$comp, $per]) {
            $can = intdiv(max(0, (int) $comp->stock_quantity), $per);
            $min = $min === null ? $can : min($min, $can);
        }
        return $min ?? 0;
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
        // Webhook roda sem usuário logado: tira o filtro de equipe só aqui
        // (a publicação já é do dono).
        $pairs = $this->products()->withoutGlobalScope('team_visibility')->get()
            ->map(fn ($p) => [$p, (int) $p->pivot->quantity])
            ->all();

        return $this->deductPairs($pairs, $quantity, $mlOrderId, null);
    }

    /**
     * Venda de uma variação: baixa só o produto ligado a ela
     * (quantidade vendida × unidades por venda).
     */
    public function deductVariationStock(MlPublicationVariation $variation, int $quantity, ?string $mlOrderId = null): array
    {
        return $this->deductPairs([[$variation->product, $variation->perSale()]], $quantity, $mlOrderId, $variation);
    }

    /**
     * @param array $pairs [[Product $product, int $porVenda], ...]
     */
    protected function deductPairs(array $pairs, int $quantity, ?string $mlOrderId, ?MlPublicationVariation $variation): array
    {
        $transactionId = \Illuminate\Support\Str::uuid()->toString();
        $logs = [];
        $hasVarColumn = $variation !== null && \Illuminate\Support\Facades\Schema::hasColumn('ml_stock_logs', 'ml_variation_id');

        try {
            \DB::beginTransaction();

            foreach ($pairs as [$product, $perSale]) {
                if (! $product) {
                    continue;
                }
                $quantityToDeduct = (int) $perSale * $quantity; // Ex: kit com 2 shampoos, vendeu 3 kits = 6 unidades

                // Produto kit não tem estoque próprio: baixa dos componentes
                // (equivale a Product::adjustStock(-qty), sem filtro de equipe).
                $targets = $product->isKit()
                    ? array_map(fn ($c) => [$c[0], $c[1] * $quantityToDeduct], self::kitComponents($product))
                    : [[$product, $quantityToDeduct]];

                foreach ($targets as [$target, $qty]) {
                    $oldStock = (int) $target->stock_quantity;
                    $target->adjustStock(-$qty);
                    $newStock = (int) $target->stock_quantity;

                    // Registra log
                    $data = [
                        'product_id' => $target->id,
                        'ml_publication_id' => $this->id,
                        'operation_type' => 'ml_sale',
                        'quantity_before' => $oldStock,
                        'quantity_after' => $newStock,
                        'quantity_change' => $newStock - $oldStock,
                        'source' => $variation ? 'MlPublication::deductVariationStock' : 'MlPublication::deductStock',
                        'ml_order_id' => $mlOrderId,
                        'notes' => "Venda ML: {$quantity} unidade(s) de publicação ID {$this->id}"
                            . ($variation ? ' (variação ' . $variation->shortLabel() . ')' : '')
                            . ($target->id !== $product->id ? " (componente do kit {$product->id})" : ''),
                        'transaction_id' => $transactionId,
                    ];
                    if ($hasVarColumn) {
                        $data['ml_variation_id'] = $variation->ml_variation_id;
                    }
                    $logs[] = MlStockLog::create($data);
                }
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
    /**
     * Com $syncStock = false só grava o vínculo: a quantidade guardada (a do ML)
     * fica como está e a publicação não é marcada para envio — usado ao ligar
     * um anúncio importado, para não sobrescrever o estoque do ML sem o dono pedir.
     */
    public function addProduct(int $productId, int $quantity = 1, ?float $unitCost = null, int $sortOrder = 0, bool $syncStock = true): void
    {
        $this->products()->attach($productId, [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'sort_order' => $sortOrder,
        ]);

        // Recalcula quantidade disponível
        if ($syncStock) {
            $this->syncQuantityToMl();
        }
    }

    /**
     * Remove um produto da publicação
     */
    public function removeProduct(int $productId, bool $syncStock = true): void
    {
        $this->products()->detach($productId);
        
        // Recalcula quantidade disponível
        if ($syncStock) {
            $this->syncQuantityToMl();
        }
    }

    /**
     * Atualiza quantidade de um produto na publicação
     */
    public function updateProductQuantity(int $productId, int $quantity, bool $syncStock = true): void
    {
        $this->products()->updateExistingPivot($productId, [
            'quantity' => $quantity,
        ]);

        // Recalcula quantidade disponível
        if ($syncStock) {
            $this->syncQuantityToMl();
        }
    }
}
