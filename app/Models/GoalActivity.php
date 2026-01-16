<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'user_id',
        'action',
        'description',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    // Relacionamentos
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'fa-plus-circle',
            'moved' => 'fa-arrows-alt',
            'updated' => 'fa-edit',
            'completed' => 'fa-check-circle',
            'commented' => 'fa-comment',
            'archived' => 'fa-archive',
            'deleted' => 'fa-trash',
            'checklist_added' => 'fa-list',
            'attachment_added' => 'fa-paperclip',
            default => 'fa-circle',
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'text-green-500',
            'moved' => 'text-blue-500',
            'updated' => 'text-yellow-500',
            'completed' => 'text-green-600',
            'commented' => 'text-purple-500',
            'archived' => 'text-gray-500',
            'deleted' => 'text-red-500',
            'checklist_added' => 'text-indigo-500',
            'attachment_added' => 'text-cyan-500',
            default => 'text-slate-500',
        };
    }
}
