<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relacionamentos
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoalChecklistItem::class, 'checklist_id')->orderBy('order');
    }

    // Accessors
    public function getProgressoAttribute(): float
    {
        $total = $this->items->count();
        if ($total === 0) return 0;

        $completed = $this->items->where('is_completed', true)->count();
        return ($completed / $total) * 100;
    }
}
