<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalReminder extends Model
{
    protected $fillable = [
        'goal_id', 'remind_at', 'canal', 'enviado_em',
    ];

    protected $casts = [
        'remind_at'  => 'datetime',
        'enviado_em' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function scopePendentes($query)
    {
        return $query->whereNull('enviado_em')->where('remind_at', '<=', now());
    }
}
