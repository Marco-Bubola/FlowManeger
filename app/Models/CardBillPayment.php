<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de que a fatura de um ciclo do cartão foi paga.
 */
class CardBillPayment extends Model
{
    protected $fillable = [
        'user_id',
        'id_bank',
        'cycle_start',
        'cycle_end',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'cycle_start' => 'date',
        'cycle_end' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'id_bank', 'id_bank');
    }
}
