<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLivreOrder extends Model
{
    use HasFactory;

    protected $table = 'mercadolivre_orders';

    protected $fillable = [
        'ml_order_id',
        'ml_item_id',
        'product_id',
        'buyer_id',
        'buyer_nickname',
        'buyer_email',
        'buyer_phone',
        'buyer_address',
        'quantity',
        'unit_price',
        'total_amount',
        'currency_id',
        'order_status',
        'payment_status',
        'payment_method',
        'payment_type',
        'shipping_id',
        'tracking_number',
        'shipping_method',
        'shipping_cost',
        'date_created',
        'date_closed',
        'date_last_updated',
        'imported_to_sale_id',
        'sync_status',
        'error_message',
        'raw_data',
    ];

    protected $casts = [
        'buyer_address' => 'array',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'date_created' => 'datetime',
        'date_closed' => 'datetime',
        'date_last_updated' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Relacionamento com produto interno
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relacionamento com venda importada
     */
    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class, 'imported_to_sale_id');
    }

    /**
     * Verifica se o pedido foi pago
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'approved';
    }

    /**
     * Verifica se o pedido foi entregue
     */
    public function isDelivered(): bool
    {
        return $this->order_status === 'delivered';
    }

    /**
     * Verifica se já foi importado
     */
    public function isImported(): bool
    {
        return $this->sync_status === 'imported' && $this->imported_to_sale_id !== null;
    }

    /**
     * Marca como importado
     */
    public function markAsImported(int $saleId)
    {
        $this->update([
            'imported_to_sale_id' => $saleId,
            'sync_status' => 'imported',
            'error_message' => null,
        ]);
    }

    /**
     * Marca como erro
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
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeImported($query)
    {
        return $query->where('sync_status', 'imported');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'approved');
    }

    public function scopeNotImported($query)
    {
        return $query->whereNull('imported_to_sale_id');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('date_created', 'desc');
    }
}
