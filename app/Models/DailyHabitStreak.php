<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyHabitStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'user_id',
        'current_streak',
        'longest_streak',
        'last_completion_date',
        'total_completions',
    ];

    protected $casts = [
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'total_completions' => 'integer',
        'last_completion_date' => 'date',
    ];

    // Relationships
    public function habit()
    {
        return $this->belongsTo(DailyHabit::class, 'habit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
