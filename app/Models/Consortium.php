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
                if ($participant->contemplation) {
                    $participant->contemplation->returnProductsToStock();
                    $participant->contemplation->delete();
                }
            }

            // Excluir pagamentos
            \App\Models\ConsortiumPayment::whereIn('consortium_participant_id', $consortium->participants->pluck('id'))->delete();

            // Excluir participantes
            $consortium->participants()->delete();

            // Excluir sorteios
            $consortium->draws()->delete();
        });
    }

    /** Só o dono abre ou mexe no consórcio. */
    public function authorizeOwner(): void
    {
        abort_unless((int) $this->user_id === (int) \Illuminate\Support\Facades\Auth::id(), 403, 'Acesso não autorizado.');
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

    /** Quem ocupa vaga: ativos e contemplados (só quem desistiu sai). */
    public function activeParticipants(): HasMany
    {
        return $this->hasMany(ConsortiumParticipant::class)->where('status', '!=', 'quit');
    }

    public function draws(): HasMany
    {
        return $this->hasMany(ConsortiumDraw::class);
    }

    // Accessors
    public function getActiveParticipantsCountAttribute(): int
    {
        // Contemplado continua no grupo (paga até o fim) e ocupa vaga.
        return $this->participants()->where('status', '!=', 'quit')->count();
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
        // total_value já é mensal × meses × vagas.
        return min(100, ($this->total_collected / $this->total_value) * 100);
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

        // Verificar se há participantes elegíveis (não contemplados)
        if ($this->eligibleParticipantsCount() === 0) {
            return false;
        }

        // Verificar se já passou a data de início
        if (now()->lt($this->start_date)) {
            return false;
        }

        return $this->daysUntilNextDraw() === 0;
    }

    /** Dias entre sorteios conforme a frequência. */
    public function frequencyDays(): int
    {
        return match($this->draw_frequency) {
            'weekly' => 7,
            'biweekly' => 14,
            'bimonthly' => 60,
            'quarterly' => 90,
            default => 30,
        };
    }

    /**
     * Quantos dias faltam para liberar o próximo sorteio (0 = já pode).
     * Usa 80% do período como margem. Sempre inteiro e nunca negativo.
     */
    public function daysUntilNextDraw(): int
    {
        $lastDraw = $this->draws()->orderBy('draw_date', 'desc')->first();
        if (!$lastDraw) {
            return $this->start_date && now()->lt($this->start_date)
                ? (int) ceil(now()->startOfDay()->diffInDays($this->start_date->copy()->startOfDay()))
                : 0;
        }

        // diffInDays no Carbon 3 tem sinal: mede do sorteio até hoje.
        $since = (int) floor($lastDraw->draw_date->copy()->startOfDay()->diffInDays(now()->startOfDay()));
        $needed = (int) ceil($this->frequencyDays() * 0.8);

        return max(0, $needed - $since);
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
                'bimonthly' => 'Bimestral',
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

        $monthsSinceStart = (int) floor($this->start_date->diffInMonths(now()));
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

        $frequencyDays = $this->frequencyDays();

        $upcomingDates = [];
        $currentDate = \Carbon\Carbon::parse($startDate);

        for ($i = 1; $i <= $count; $i++) {
            $currentDate = $currentDate->copy()->addDays($frequencyDays);
            if ($currentDate->lte(now()->addYear())) {
                $upcomingDates[] = [
                    'date' => $currentDate,
                    'draw_number' => ($lastDraw ? $lastDraw->draw_number : 0) + $i,
                    'days_until' => (int) ceil(now()->diffInDays($currentDate, false)),
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
