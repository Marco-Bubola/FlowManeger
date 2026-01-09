<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsortiumContemplation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consortium_participant_id',
        'draw_id',
        'contemplation_type',
        'contemplation_date',
        'redemption_type',
        'redemption_value',
        'redemption_date',
        'products',
        'status',
        'notes',
    ];

    protected $casts = [
        'contemplation_date' => 'datetime',
        'redemption_date' => 'date',
        'redemption_value' => 'decimal:2',
        'products' => 'array',
    ];

    // Relationships
    public function participant(): BelongsTo
    {
        return $this->belongsTo(ConsortiumParticipant::class, 'consortium_participant_id');
    }

    public function draw(): BelongsTo
    {
        return $this->belongsTo(ConsortiumDraw::class, 'draw_id');
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'redeemed' => 'green',
            'pending' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'redeemed' => 'Resgatado',
            'pending' => 'Pendente',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function getRedemptionTypeLabelAttribute(): string
    {
        return match($this->redemption_type) {
            'cash' => 'Dinheiro',
            'products' => 'Produtos',
            'pending' => 'Pendente',
            default => 'Não definido',
        };
    }

    public function getContemplationTypeLabelAttribute(): string
    {
        return match($this->contemplation_type) {
            'draw' => 'Sorteio',
            'bid' => 'Lance',
            'payoff' => 'Quitação',
            default => 'Desconhecido',
        };
    }
}
