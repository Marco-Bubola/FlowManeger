<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsortiumParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consortium_id',
        'client_id',
        'participation_number',
        'entry_date',
        'status',
        'total_paid',
        'is_contemplated',
        'contemplation_date',
        'contemplation_type',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'contemplation_date' => 'date',
        'total_paid' => 'decimal:2',
        'is_contemplated' => 'boolean',
    ];

    /**
     * Boot method to ensure UTF-8 encoding
     */
    protected static function booted()
    {
        static::retrieved(function ($participant) {
            if ($participant->notes) {
                $participant->notes = mb_convert_encoding($participant->notes, 'UTF-8', 'UTF-8');
            }
        });
    }

    // Relationships
    public function consortium(): BelongsTo
    {
        return $this->belongsTo(Consortium::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ConsortiumPayment::class);
    }

    public function contemplation(): HasOne
    {
        return $this->hasOne(ConsortiumContemplation::class);
    }

    // Accessors
    public function getPaymentPercentageAttribute(): float
    {
        $totalExpected = $this->consortium->monthly_value * $this->consortium->duration_months;
        if ($totalExpected <= 0) {
            return 0;
        }
        return ($this->total_paid / $totalExpected) * 100;
    }

    public function getPendingPaymentsCountAttribute(): int
    {
        return $this->payments()->where('status', 'pending')->count();
    }

    public function getLatePaymentsCountAttribute(): int
    {
        return $this->payments()->where('status', 'late')->count();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
            'contemplated' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
            'quit' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
            'defaulter' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
            default => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Ativo',
            'contemplated' => 'Contemplado',
            'quit' => 'Desistente',
            'defaulter' => 'Inadimplente',
            default => 'Desconhecido',
        };
    }

    public function getContemplationTypeLabelAttribute(): string
    {
        return match($this->contemplation_type) {
            'draw' => 'Sorteio',
            'bid' => 'Lance',
            'payoff' => 'Quitação',
            default => 'N/A',
        };
    }

    // ========== MÉTODOS AUXILIARES ==========

    /**
     * Retorna próximas parcelas a vencer (próximos 30 dias)
     */
    public function getUpcomingPayments(int $days = 30)
    {
        return $this->payments()
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Retorna parcelas vencidas
     */
    public function getOverduePayments()
    {
        return $this->payments()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Retorna total em atraso (com juros e multa)
     */
    public function getTotalOverdueWithFees(): float
    {
        $overduePayments = $this->getOverduePayments();

        return $overduePayments->sum(function ($payment) {
            return $payment->getTotalAmountWithFees();
        });
    }

    /**
     * Verifica se participante está em dia
     */
    public function isUpToDate(): bool
    {
        return $this->getOverduePayments()->isEmpty();
    }

    /**
     * Retorna estatísticas do participante
     */
    public function getStatistics(): array
    {
        $allPayments = $this->payments;
        $overduePayments = $this->getOverduePayments();

        return [
            'total_payments' => $allPayments->count(),
            'paid_payments' => $allPayments->where('status', 'paid')->count(),
            'pending_payments' => $allPayments->where('status', 'pending')->count(),
            'overdue_payments' => $overduePayments->count(),
            'total_paid' => $this->total_paid,
            'total_expected' => $this->consortium->monthly_value * $this->consortium->duration_months,
            'payment_percentage' => $this->payment_percentage,
            'total_overdue' => $overduePayments->sum('amount'),
            'total_overdue_with_fees' => $this->getTotalOverdueWithFees(),
            'is_up_to_date' => $this->isUpToDate(),
            'next_payment_date' => $this->payments()
                ->where('status', 'pending')
                ->orderBy('due_date')
                ->first()?->due_date,
        ];
    }

    /**
     * Calcula quantas parcelas faltam pagar
     */
    public function getRemainingPayments(): int
    {
        return $this->payments()
            ->whereIn('status', ['pending', 'overdue'])
            ->count();
    }

    /**
     * Calcula valor total que ainda falta pagar
     */
    public function getRemainingAmount(): float
    {
        return (float) $this->payments()
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('amount');
    }

    /**
     * Verifica se o participante pode ser contemplado por quitação
     */
    public function canBeContemplatedByPayoff(): bool
    {
        if ($this->is_contemplated) {
            return false;
        }

        // Deve estar ativo
        if ($this->status !== 'active') {
            return false;
        }

        // Deve estar com pagamentos em dia
        if (!$this->isUpToDate()) {
            return false;
        }

        // Deve ter pago pelo menos 50% do valor total
        $totalExpected = $this->consortium->monthly_value * $this->consortium->duration_months;
        return $this->payment_percentage >= 50;
    }

    /**
     * Retorna o mês/ano da última parcela paga
     */
    public function getLastPaymentInfo(): ?array
    {
        $lastPayment = $this->payments()
            ->where('status', 'paid')
            ->orderBy('payment_date', 'desc')
            ->first();

        if (!$lastPayment) {
            return null;
        }

        return [
            'date' => $lastPayment->payment_date,
            'amount' => $lastPayment->amount,
            'reference' => $lastPayment->reference_month_name . '/' . $lastPayment->reference_year,
        ];
    }
}
