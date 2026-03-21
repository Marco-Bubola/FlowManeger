<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeOrder extends Model
{
    use HasFactory;

    protected $table = 'shopee_orders';

    protected $fillable = [
        'user_id',
        'shop_id',
        'shopee_order_sn',
        'shopee_item_id',
        'shopee_model_id',
        'buyer_username',
        'buyer_phone',
        'shipping_address',
        'order_items',
        'total_amount',
        'currency',
        'order_status',
        'payment_method',
        'tracking_number',
        'shipping_carrier',
        'days_to_ship',
        'ship_by_date',
        'imported_to_sale_id',
        'sync_status',
        'error_message',
        'raw_data',
        'shopee_created_at',
        'shopee_updated_at',
    ];

    protected $casts = [
        'shipping_address'   => 'array',
        'order_items'        => 'array',
        'raw_data'           => 'array',
        'total_amount'       => 'decimal:2',
        'ship_by_date'       => 'datetime',
        'shopee_created_at'  => 'datetime',
        'shopee_updated_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function importedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'imported_to_sale_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Retorna um label legível para o status do pedido Shopee.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            'UNPAID'         => 'Aguardando Pagamento',
            'READY_TO_SHIP'  => 'Pronto para Envio',
            'PROCESSED'      => 'Processando',
            'SHIPPED'        => 'Enviado',
            'COMPLETED'      => 'Concluído',
            'IN_CANCEL'      => 'Cancelamento Solicitado',
            'CANCELLED'      => 'Cancelado',
            'INVOICE_PENDING'=> 'Nota Fiscal Pendente',
            default          => $this->order_status,
        };
    }

    /**
     * Cor CSS para badge de status (classes Tailwind).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->order_status) {
            'UNPAID'                     => 'yellow',
            'READY_TO_SHIP', 'PROCESSED' => 'blue',
            'SHIPPED'                    => 'indigo',
            'COMPLETED'                  => 'green',
            'IN_CANCEL', 'CANCELLED'     => 'red',
            default                      => 'gray',
        };
    }
}
