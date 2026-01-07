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
            default => 'N/A',
        };
    }
}
