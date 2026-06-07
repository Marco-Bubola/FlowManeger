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
        // novos campos do módulo unificado
        'type',
        'target_value',
        'unit',
        'frequency_type',
        'frequency_days',
        'start_date',
        'end_date',
        'is_archived',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
        'goal_frequency' => 'integer',
        'order' => 'integer',
        'reminder_time' => 'datetime:H:i',
        'target_value' => 'decimal:2',
        'frequency_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Hábito está agendado para a data informada (respeita frequency_type/days)?
     */
    public function isScheduledFor(Carbon $date): bool
    {
        if ($this->start_date && $date->lt($this->start_date)) return false;
        if ($this->end_date && $date->gt($this->end_date)) return false;

        $type = $this->frequency_type ?? 'daily';
        if ($type === 'daily' || $type === 'times_per_week') {
            return true;
        }
        if ($type === 'specific_days') {
            $map = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
            $dow = $map[$date->dayOfWeek] ?? null;
            return in_array($dow, (array) ($this->frequency_days ?? []), true);
        }
        if ($type === 'weekly') {
            return true; // simplificado: conta na semana
        }
        return true;
    }

    public function metaGoals()
    {
        return $this->belongsToMany(Goal::class, 'goal_habit', 'daily_habit_id', 'goal_id')
                    ->withPivot('peso')
                    ->withTimestamps();
    }

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
