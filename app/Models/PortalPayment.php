<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma tentativa de pagamento de um pedido do portal (ex.: um Pix gerado). */
class PortalPayment extends Model
{
    protected $fillable = [
        'quote_id', 'user_id', 'provider', 'provider_id', 'method', 'status', 'status_detail',
        'amount', 'qr_code', 'qr_code_base64', 'ticket_url', 'expires_at', 'approved_at',
        'sale_id', 'idempotency_key',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'expires_at'  => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(ClientQuoteRequest::class, 'quote_id');
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'in_process', 'authorized'], true);
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at && $this->expires_at->isPast();
    }
}
