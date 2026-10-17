<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Um print (ou texto de notificação) que virou ou vai virar lançamento. */
class FinanceCapture extends Model
{
    public const KIND_CARD = 'card';       // compra no cartão de crédito → fatura
    public const KIND_INCOME = 'income';   // entrada → receita no livro caixa
    public const KIND_EXPENSE = 'expense'; // saída no débito/Pix → despesa no livro caixa

    protected $fillable = [
        'user_id', 'source', 'status', 'kind', 'amount', 'description', 'date', 'installments',
        'institution', 'target_type', 'target_id', 'bank_id', 'account_id', 'category_id',
        'linked_existing', 'image_path', 'raw_text', 'parsed', 'wa_from', 'wa_message_id', 'error',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'parsed' => 'array',
        'linked_existing' => 'boolean',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id_bank');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function target(): ?Model
    {
        return match ($this->target_type) {
            'invoice' => Invoice::find($this->target_id),
            'cashbook' => Cashbook::find($this->target_id),
            default => null,
        };
    }

    public function kindLabel(): string
    {
        return match ($this->kind) {
            self::KIND_CARD => 'Compra no cartão',
            self::KIND_INCOME => 'Entrada',
            self::KIND_EXPENSE => 'Saída',
            default => 'Não identificado',
        };
    }

    /** "fatura do Nubank" / "Livro caixa (Inter)" */
    public function destinationLabel(): string
    {
        if ($this->kind === self::KIND_CARD) {
            return $this->bank ? 'Fatura do ' . $this->bank->name : 'Fatura (escolha o cartão)';
        }

        return 'Livro caixa' . ($this->account ? ' (' . $this->account->name . ')' : '');
    }
}
