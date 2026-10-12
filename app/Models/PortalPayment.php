<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma tentativa de pagamento de um pedido do portal (Pix, cartão ou boleto). */
class PortalPayment extends Model
{
    protected $fillable = [
        'quote_id', 'user_id', 'provider', 'provider_id', 'method', 'status', 'status_detail',
        'amount', 'qr_code', 'qr_code_base64', 'ticket_url', 'expires_at', 'approved_at',
        'sale_id', 'idempotency_key', 'installments', 'card_brand', 'card_last4', 'barcode',
        'sale_payment_id', 'refunded_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'expires_at'  => 'datetime',
        'approved_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public const METHODS = ['pix' => 'Pix', 'cartao' => 'Cartão', 'boleto' => 'Boleto'];

    public function methodLabel(): string
    {
        $label = self::METHODS[$this->method] ?? ucfirst((string) $this->method);
        if ($this->method === 'cartao' && $this->card_last4) {
            $label .= ' ' . ucfirst((string) $this->card_brand) . ' final ' . $this->card_last4;
        }
        if ($this->method === 'cartao' && $this->installments > 1) {
            $label .= ' em ' . $this->installments . 'x';
        }

        return $label;
    }

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
