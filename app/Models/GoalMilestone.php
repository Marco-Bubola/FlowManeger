<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalMilestone extends Model
{
    protected $fillable = [
        'goal_id', 'titulo', 'valor_alvo', 'data_alvo', 'atingido_em', 'order',
    ];

    protected $casts = [
        'valor_alvo'  => 'decimal:2',
        'data_alvo'   => 'date',
        'atingido_em' => 'datetime',
        'order'       => 'integer',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function getIsReachedAttribute(): bool
    {
        return !is_null($this->atingido_em);
    }
}
