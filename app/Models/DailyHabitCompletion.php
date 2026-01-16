<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyHabitCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'user_id',
        'completion_date',
        'times_completed',
        'notes',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'times_completed' => 'integer',
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
