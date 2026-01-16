<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'text',
        'is_completed',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'order' => 'integer',
    ];

    // Relacionamentos
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(GoalChecklist::class, 'checklist_id');
    }

    // Eventos
    protected static function booted()
    {
        static::saved(function ($item) {
            // Atualizar progresso do goal quando item é marcado/desmarcado
            $item->checklist->goal->calculateProgressoFromChecklists();
        });
    }
}
