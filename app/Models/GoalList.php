<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalList extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'color',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relacionamentos
    public function board(): BelongsTo
    {
        return $this->belongsTo(GoalBoard::class, 'board_id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'list_id')->orderBy('order');
    }
}
