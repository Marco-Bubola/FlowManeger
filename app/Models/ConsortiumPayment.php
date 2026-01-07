<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsortiumPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consortium_participant_id',
        'reference_month',
        'reference_year',
        'amount',
        'payment_date',
        'due_date',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'reference_month' => 'integer',
        'reference_year' => 'integer',
    ];

    // Relationships
    public function participant(): BelongsTo
    {
        return $this->belongsTo(ConsortiumParticipant::class, 'consortium_participant_id');
    }

    // Accessors
    public function getIsLateAttribute(): bool
    {
        return $this->status === 'pending' && now()->greaterThan($this->due_date);
    }

    public function getDaysLateAttribute(): int
    {
        if (!$this->is_late) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
            'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
            'late' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
            'cancelled' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
            default => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'late' => 'Atrasado',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function getReferenceMonthNameAttribute(): string
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        return $months[$this->reference_month] ?? '';
    }
}
