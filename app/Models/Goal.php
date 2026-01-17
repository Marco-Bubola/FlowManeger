<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'user_id',
        'title',
        'description',
        'periodo',
        'recorrencia_dia',
        'prioridade',
        'data_inicio',
        'data_vencimento',
        'progresso',
        'valor_meta',
        'valor_atual',
        'cofrinho_id',
        'category_id',
        'cor',
        'labels',
        'order',
        'is_archived',
        'completed_at',
    ];

    protected $casts = [
        'labels' => 'array',
        'data_inicio' => 'date',
        'data_vencimento' => 'date',
        'progresso' => 'decimal:2',
        'valor_meta' => 'decimal:2',
        'valor_atual' => 'decimal:2',
        'order' => 'integer',
        'recorrencia_dia' => 'integer',
        'is_archived' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Relacionamentos
    public function list(): BelongsTo
    {
        return $this->belongsTo(GoalList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cofrinho(): BelongsTo
    {
        return $this->belongsTo(Cofrinho::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(GoalChecklist::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GoalComment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(GoalAttachment::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(GoalActivity::class)->latest();
    }

    public function habits(): BelongsToMany
    {
        return $this->belongsToMany(DailyHabit::class, 'goal_habit', 'goal_id', 'daily_habit_id')
                    ->withPivot('peso')
                    ->withTimestamps();
    }

    // Helper: obter todos os checklistItems
    public function checklistItems()
    {
        return GoalChecklistItem::whereIn('checklist_id',
            $this->checklists()->pluck('id')
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeByPrioridade($query, string $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    public function scopeVencendoEm($query, int $dias)
    {
        return $query->whereDate('data_vencimento', '<=', now()->addDays($dias))
                     ->whereDate('data_vencimento', '>=', now());
    }

    public function scopeAtrasadas($query)
    {
        return $query->whereDate('data_vencimento', '<', now())
                     ->whereNull('completed_at');
    }

    // Accessors & Mutators
    public function getProgressoPercentualAttribute(): string
    {
        return number_format($this->progresso, 2) . '%';
    }

    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->completed_at) || $this->progresso >= 100;
    }

    public function getIsAtrasadaAttribute(): bool
    {
        return $this->data_vencimento &&
               $this->data_vencimento->isPast() &&
               !$this->is_completed;
    }

    // Métodos auxiliares
    public function updateProgressoFromCofrinho(): void
    {
        if ($this->cofrinho_id && $this->valor_meta > 0) {
            $valorAtual = $this->cofrinho->cashbooks()
                ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE -value END) as total')
                ->value('total') ?? 0;

            $this->update([
                'valor_atual' => $valorAtual,
                'progresso' => min(($valorAtual / $this->valor_meta) * 100, 100)
            ]);
        }
    }

    public function calculateProgressoFromChecklists(): void
    {
        $totalItems = $this->checklists->sum(function ($checklist) {
            return $checklist->items->count();
        });

        $completedItems = $this->checklists->sum(function ($checklist) {
            return $checklist->items->where('is_completed', true)->count();
        });

        if ($totalItems > 0) {
            $this->update([
                'progresso' => ($completedItems / $totalItems) * 100
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'progresso' => 100
        ]);

        $this->logActivity('completed', 'Meta concluída');
    }

    public function logActivity(string $action, string $description, array $oldValue = null, array $newValue = null): void
    {
        $this->activities()->create([
            'user_id' => auth()->id() ?? $this->user_id,
            'action' => $action,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
