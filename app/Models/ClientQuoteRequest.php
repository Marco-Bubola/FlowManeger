<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientQuoteRequest extends Model
{
    protected $table = 'client_quote_requests';

    protected $fillable = [
        'client_id', 'user_id', 'status',
        'items', 'extra_items',
        'client_notes', 'admin_notes',
        'quoted_total', 'valid_until',
    ];

    protected $casts = [
        'items'        => 'array',
        'extra_items'  => 'array',
        'quoted_total' => 'decimal:2',
        'valid_until'  => 'date',
    ];

    public const STATUS_LABELS = [
        'pending'   => 'Aguardando Análise',
        'reviewing' => 'Em Análise',
        'quoted'    => 'Orçamento Enviado',
        'approved'  => 'Aprovado',
        'rejected'  => 'Recusado',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'amber',
        'reviewing' => 'blue',
        'quoted'    => 'purple',
        'approved'  => 'green',
        'rejected'  => 'red',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }
}
