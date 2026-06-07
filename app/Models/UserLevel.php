<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevel extends Model
{
    protected $fillable = [
        'user_id', 'xp', 'level', 'current_streak', 'best_streak', 'last_activity_date',
    ];

    protected $casts = [
        'xp'                 => 'integer',
        'level'              => 'integer',
        'current_streak'     => 'integer',
        'best_streak'        => 'integer',
        'last_activity_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * XP necessário para alcançar um nível (curva progressiva).
     */
    public static function xpForLevel(int $level): int
    {
        // Nível 1 = 0 XP, depois cresce ~ 100 * level^1.5
        if ($level <= 1) return 0;
        return (int) round(100 * pow($level - 1, 1.5));
    }

    /**
     * XP total acumulado até o início do nível atual.
     */
    public function getXpFloorAttribute(): int
    {
        return self::xpForLevel($this->level);
    }

    public function getXpCeilingAttribute(): int
    {
        return self::xpForLevel($this->level + 1);
    }

    public function getProgressToNextLevelAttribute(): float
    {
        $floor = $this->xp_floor;
        $ceil  = $this->xp_ceiling;
        if ($ceil <= $floor) return 100.0;
        return min(100, max(0, (($this->xp - $floor) / ($ceil - $floor)) * 100));
    }
}
