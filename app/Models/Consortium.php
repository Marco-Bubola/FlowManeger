<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'mode',
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

    // Accessors para valores totais
    protected function totalValuePossible(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->monthly_value * $this->duration_months * $this->max_participants
        );
    }

    protected function totalValueReal(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->monthly_value * $this->duration_months * $this->active_participants_count
        );
    }

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

        // Cascata de exclusão
        static::deleting(function ($consortium) {
            // Excluir contemplações dos participantes
            foreach ($consortium->participants as $participant) {
                $participant->contemplation()?->delete();
            }

            // Excluir pagamentos
            \App\Models\ConsortiumPayment::whereIn('consortium_participant_id', $consortium->participants->pluck('id'))->delete();

            // Excluir participantes
            $consortium->participants()->delete();

            // Excluir sorteios
            $consortium->draws()->delete();
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
        if ($this->mode === 'payoff') {
            return false;
        }

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

    /**
     * Obter contagem de participantes elegíveis para sorteio
     */
    public function eligibleParticipantsCount(): int
    {
        return $this->participants()
            ->where('status', 'active')
            ->where('is_contemplated', false)
            ->count();
    }

    // Accessor para label de frequência
    protected function drawFrequencyLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->draw_frequency) {
                'weekly' => 'Semanal',
                'biweekly' => 'Quinzenal',
                'monthly' => 'Mensal',
                'quarterly' => 'Trimestral',
                default => 'Mensal'
            }
        );
    }

    protected function modeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->mode) {
                'payoff' => 'Resgate por quitação',
                default => 'Sorteio'
            }
        );
    }

    // ========== MÉTODOS AUXILIARES FINANCEIROS ==========

    /**
     * Retorna o valor esperado de arrecadação até o momento
     */
    public function getExpectedCollectionUntilNow(): float
    {
        if (!$this->start_date || now()->lt($this->start_date)) {
            return 0;
        }

        $monthsSinceStart = now()->diffInMonths($this->start_date);
        $monthsSinceStart = min($monthsSinceStart + 1, $this->duration_months);

        return $this->monthly_value * $this->active_participants_count * $monthsSinceStart;
    }

    /**
     * Retorna o total de pagamentos vencidos
     */
    public function getOverdueAmount(): float
    {
        return (float) \App\Models\ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $this->participants->pluck('id'))
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');
    }

    /**
     * Retorna número de pagamentos em atraso
     */
    public function getOverduePaymentsCount(): int
    {
        return \App\Models\ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $this->participants->pluck('id'))
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();
    }

    /**
     * Verifica se o consórcio está saudável financeiramente
     */
    public function isFinanciallyHealthy(): bool
    {
        $expected = $this->getExpectedCollectionUntilNow();
        if ($expected <= 0) {
            return true;
        }

        $collectionRate = ($this->total_collected / $expected) * 100;
        return $collectionRate >= 80; // 80% ou mais é considerado saudável
    }

    /**
     * Retorna a taxa de pagamentos realizados
     */
    public function getPaymentRate(): float
    {
        $totalPayments = \App\Models\ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $this->participants->pluck('id'))
            ->count();

        if ($totalPayments === 0) {
            return 0;
        }

        $paidPayments = \App\Models\ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $this->participants->pluck('id'))
            ->where('status', 'paid')
            ->count();

        return ($paidPayments / $totalPayments) * 100;
    }

    /**
     * Retorna estatísticas completas do consórcio
     */
    public function getStatistics(): array
    {
        $allPayments = \App\Models\ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $this->participants->pluck('id'))
            ->get();

        return [
            'total_participants' => $this->participants()->count(),
            'active_participants' => $this->active_participants_count,
            'contemplated_participants' => $this->contemplated_count,
            'total_draws' => $this->draws()->count(),
            'total_collected' => $this->total_collected,
            'expected_collection' => $this->getExpectedCollectionUntilNow(),
            'overdue_amount' => $this->getOverdueAmount(),
            'overdue_payments_count' => $this->getOverduePaymentsCount(),
            'payment_rate' => $this->getPaymentRate(),
            'is_healthy' => $this->isFinanciallyHealthy(),
            'total_payments' => $allPayments->count(),
            'paid_payments' => $allPayments->where('status', 'paid')->count(),
            'pending_payments' => $allPayments->where('status', 'pending')->count(),
            'completion_percentage' => $this->completion_percentage,
        ];
    }

    /**
     * Retorna os próximos sorteios previstos
     */
    public function getUpcomingDrawDates(int $count = 5): array
    {
        if ($this->mode === 'payoff') {
            return [];
        }

        $lastDraw = $this->draws()->orderBy('draw_date', 'desc')->first();
        $startDate = $lastDraw ? $lastDraw->draw_date : $this->start_date;

        if (!$startDate) {
            return [];
        }

        $frequencyDays = match($this->draw_frequency) {
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30,
            'quarterly' => 90,
            default => 30
        };

        $upcomingDates = [];
        $currentDate = \Carbon\Carbon::parse($startDate);

        for ($i = 1; $i <= $count; $i++) {
            $currentDate = $currentDate->copy()->addDays($frequencyDays);
            if ($currentDate->lte(now()->addYear())) {
                $upcomingDates[] = [
                    'date' => $currentDate,
                    'draw_number' => ($lastDraw ? $lastDraw->draw_number : 0) + $i,
                    'days_until' => now()->diffInDays($currentDate, false),
                ];
            }
        }

        return $upcomingDates;
    }

    /**
     * Verifica se pode encerrar o consórcio
     */
    public function canComplete(): bool
    {
        // Todos devem estar contemplados ou sem participantes ativos
        $activeNonContemplated = $this->participants()
            ->where('status', 'active')
            ->where('is_contemplated', false)
            ->count();

        return $activeNonContemplated === 0;
    }
}
