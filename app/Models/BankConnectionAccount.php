<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Conta ou cartão que veio do banco conectado, e para onde vai no app. */
class BankConnectionAccount extends Model
{
    protected $fillable = [
        'user_id', 'item_id', 'provider_account_id', 'institution', 'name', 'number', 'type', 'subtype',
        'balance', 'target_type', 'target_id', 'import_from', 'last_synced_at', 'imported_count',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'import_from' => 'date',
        'last_synced_at' => 'datetime',
    ];

    public function isCard(): bool
    {
        return $this->type === 'CREDIT';
    }

    public function label(): string
    {
        $kind = $this->isCard() ? 'Cartão' : ($this->subtype === 'SAVINGS_ACCOUNT' ? 'Poupança' : 'Conta');

        return trim(($this->institution ? $this->institution . ' · ' : '') . $kind . ' ' . ($this->number ? '…' . substr($this->number, -4) : ''));
    }
}
