<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Carrinho do cliente logado no catálogo, guardado para o lembrete de carrinho abandonado. */
class PortalCart extends Model
{
    protected $fillable = ['user_id', 'client_id', 'items', 'total', 'token', 'last_activity_at', 'reminded_at', 'reminder_via', 'recovered_at', 'quote_id', 'dismissed_at'];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'last_activity_at' => 'datetime',
        'reminded_at' => 'datetime',
        'recovered_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScope('team_visibility');
    }
}
