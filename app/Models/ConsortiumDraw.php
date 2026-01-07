<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsortiumDraw extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consortium_id',
        'draw_date',
        'draw_number',
        'winner_participant_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'draw_date' => 'date',
        'draw_number' => 'integer',
    ];

    // Relationships
    public function consortium(): BelongsTo
    {
        return $this->belongsTo(Consortium::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(ConsortiumParticipant::class, 'winner_participant_id');
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'scheduled' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'completed' => 'Realizado',
            'scheduled' => 'Agendado',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function hasWinner(): bool
    {
        return $this->winner_participant_id !== null;
    }
}
