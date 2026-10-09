<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Lembrete de pagamento enviado a um cliente (Gestão › Cobranças).
 * remindable: VendaParcela, Sale (saldo em aberto) ou ConsortiumPayment.
 */
class PaymentReminder extends Model
{
    protected $fillable = [
        'user_id', 'remindable_type', 'remindable_id', 'client_id', 'channel', 'template', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
