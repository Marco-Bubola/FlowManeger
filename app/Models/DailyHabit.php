<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyHabit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'icon',
        'color',
        'goal_frequency',
        'reminder_time',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'goal_frequency' => 'integer',
        'order' => 'integer',
        'reminder_time' => 'datetime:H:i',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completions()
    {
        return $this->hasMany(DailyHabitCompletion::class, 'habit_id');
    }

    public function streak()
    {
        return $this->hasOne(DailyHabitStreak::class, 'habit_id')->where('user_id', auth()->id());
    }

    // Check if completed today
    public function isCompletedToday()
    {
        return $this->completions()
            ->where('user_id', auth()->id())
            ->whereDate('completion_date', Carbon::today())
            ->exists();
    }

    // Get today's completion
    public function getTodayCompletion()
    {
        return $this->completions()
            ->where('user_id', auth()->id())
            ->whereDate('completion_date', Carbon::today())
            ->first();
    }

    // Get completion rate (%)
    public function getCompletionRate($days = 30)
    {
        $total = $this->completions()
            ->where('user_id', auth()->id())
            ->where('completion_date', '>=', Carbon::now()->subDays($days))
            ->count();

        return $days > 0 ? round(($total / $days) * 100, 1) : 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}
