<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    // Definindo a tabela associada ao model
    protected $table = 'banks';

    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'registration_date',
        'user_id',
        'caminho_icone',
        'credit_limit',
        'due_day',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'due_day' => 'integer',
    ];

    // Definindo a chave primária
    protected $primaryKey = 'id_bank';

    // Definindo o relacionamento com o modelo Invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id_bank');
    }
    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function billPayments()
    {
        return $this->hasMany(CardBillPayment::class, 'id_bank', 'id_bank');
    }

    /**
     * Vencimento da fatura cujo ciclo termina em $cycleEnd.
     * Se o dia de vencimento é depois do fechamento, vence no mesmo mês;
     * senão, no mês seguinte (ex.: fecha dia 28, vence dia 5).
     */
    public function dueDateFor(\Carbon\Carbon $cycleEnd): ?\Carbon\Carbon
    {
        if (! $this->due_day) {
            return null;
        }

        $month = $cycleEnd->copy()->startOfMonth();
        if ($this->due_day <= $cycleEnd->day) {
            $month->addMonth();
        }

        return $month->day(min($this->due_day, $month->daysInMonth))->startOfDay();
    }
}
