<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consortium extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consortiums';

    protected $fillable = [
        'name',
        'description',
        'monthly_value',
        'duration_months',
        'total_value',
        'max_participants',
        'start_date',
        'status',
        'draw_frequency',
        'user_id',
    ];

    protected $casts = [
        'monthly_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'start_date' => 'date',
        'duration_months' => 'integer',
        'max_participants' => 'integer',
    ];

    // Removido $appends para evitar problemas de serialização no Livewire
    // Use os getters diretamente quando necessário: $consortium->active_participants_count

    /**
     * Boot method to ensure UTF-8 encoding
     */
    protected static function booted()
    {
        static::retrieved(function ($consortium) {
            // Limpa caracteres UTF-8 inválidos
            foreach (['name', 'description'] as $field) {
                if ($consortium->$field) {
                    $consortium->$field = mb_convert_encoding($consortium->$field, 'UTF-8', 'UTF-8');
                }
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConsortiumParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(ConsortiumParticipant::class)->where('status', 'active');
    }

    public function draws(): HasMany
    {
        return $this->hasMany(ConsortiumDraw::class);
    }

    // Accessors
    public function getActiveParticipantsCountAttribute(): int
    {
        return $this->participants()->where('status', 'active')->count();
    }

    public function getContemplatedCountAttribute(): int
    {
        return $this->participants()->where('is_contemplated', true)->count();
    }

    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->participants()->sum('total_paid');
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_value <= 0) {
            return 0;
        }
        return ($this->total_collected / ($this->total_value * $this->max_participants)) * 100;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Ativo',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    // Métodos auxiliares
    public function canAddParticipants(): bool
    {
        return $this->status === 'active' &&
               $this->active_participants_count < $this->max_participants;
    }

    public function canPerformDraw(): bool
    {
        // Consórcio deve estar ativo
        if ($this->status !== 'active') {
            return false;
        }

        // Deve ter participantes ativos
        if ($this->active_participants_count === 0) {
            return false;
        }

        // Verificar se há participantes elegíveis (não contemplados)
        $eligibleCount = $this->participants()
            ->where('status', 'active')
            ->where('is_contemplated', false)
            ->count();

        if ($eligibleCount === 0) {
            return false;
        }

        // Verificar se já passou a data de início
        if (now()->lt($this->start_date)) {
            return false;
        }

        // Verificar frequência de sorteios
        $lastDraw = $this->draws()->orderBy('draw_date', 'desc')->first();

        if ($lastDraw) {
            // Se há sorteio anterior, verificar se já passou o período necessário
            $daysSinceLastDraw = now()->diffInDays($lastDraw->draw_date);

            // Converter frequência para dias
            $frequencyDays = match($this->draw_frequency) {
                'weekly' => 7,
                'biweekly' => 14,
                'monthly' => 30,
                'quarterly' => 90,
                default => 30
            };

            // Deve ter passado pelo menos 80% do período (para ter margem)
            return $daysSinceLastDraw >= ($frequencyDays * 0.8);
        }

        // Se não há sorteio anterior, pode realizar o primeiro
        return true;
    }

    public function getRemainingSlots(): int
    {
        return max(0, $this->max_participants - $this->active_participants_count);
    }
}
