<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Aviso "Avise-me": um favorito do cliente entrou em promoção (promo) ou
 * voltou ao estoque (stock). seen_at = o cliente viu no portal;
 * contacted_at = a loja avisou pelo WhatsApp.
 */
class ClientProductAlert extends Model
{
    public const PROMO = 'promo';
    public const STOCK = 'stock';

    protected $fillable = ['user_id', 'client_id', 'product_id', 'reason', 'promotion_id', 'seen_at', 'contacted_at'];

    protected $casts = [
        'seen_at'      => 'datetime',
        'contacted_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('team_visibility');
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScope('team_visibility');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /** Ainda não visto pelo cliente no portal. */
    public function scopeUnseen(Builder $q): Builder
    {
        return $q->whereNull('seen_at');
    }

    /** A loja ainda não avisou pelo WhatsApp. */
    public function scopeWaiting(Builder $q): Builder
    {
        return $q->whereNull('contacted_at');
    }
}
