<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'rarity',
        'icon',
        'color',
        'points',
        'criteria',
        'is_secret',
        'order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_secret' => 'boolean',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('unlocked_at', 'progress')
                    ->withTimestamps();
    }

    public function getRarityColorAttribute(): string
    {
        return match($this->rarity) {
            'bronze' => '#CD7F32',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            'platinum' => '#E5E4E2',
        };
    }

    public function getRarityIconAttribute(): string
    {
        return match($this->rarity) {
            'bronze' => 'bi-award',
            'silver' => 'bi-award-fill',
            'gold' => 'bi-trophy',
            'platinum' => 'bi-trophy-fill',
        };
    }
}
