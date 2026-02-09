<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLivreProduct extends Model
{
    use HasFactory;

    protected $table = 'mercadolivre_products';

    protected $fillable = [
        'product_id',
        'ml_item_id',
        'ml_category_id',
        'listing_type',
        'status',
        'ml_permalink',
        'sync_status',
        'last_sync_at',
        'error_message',
        'ml_attributes',
        'ml_price',
        'ml_quantity',
    ];

    protected $casts = [
        'ml_attributes' => 'array',
        'ml_price' => 'decimal:2',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Relacionamento com o produto interno
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Verifica se o produto está publicado no ML
     */
    public function isPublished(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se está sincronizado
     */
    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    /**
     * Verifica se há erro de sincronização
     */
    public function hasError(): bool
    {
        return $this->sync_status === 'error';
    }

    /**
     * Marca como sincronizado
     */
    public function markAsSynced()
    {
        $this->update([
            'sync_status' => 'synced',
            'last_sync_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Marca como erro de sincronização
     */
    public function markAsError(string $message)
    {
        $this->update([
            'sync_status' => 'error',
            'error_message' => $message,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeWithErrors($query)
    {
        return $query->where('sync_status', 'error');
    }
}
