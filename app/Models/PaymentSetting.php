<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pagamento online do portal (Mercado Pago) de cada loja. */
class PaymentSetting extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'enabled', 'access_token', 'public_key', 'webhook_secret',
        'pix_enabled', 'allow_manual', 'pickup_enabled', 'delivery_enabled', 'delivery_fee',
        'delivery_note', 'pix_minutes',
    ];

    protected $casts = [
        'enabled'          => 'boolean',
        'access_token'     => 'encrypted',
        'webhook_secret'   => 'encrypted',
        'pix_enabled'      => 'boolean',
        'allow_manual'     => 'boolean',
        'pickup_enabled'   => 'boolean',
        'delivery_enabled' => 'boolean',
        'delivery_fee'     => 'decimal:2',
    ];

    protected $hidden = ['access_token', 'webhook_secret'];

    public static function for(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    /** Pix pronto para o cliente pagar: ligado e com o Access Token salvo. */
    public function pixReady(): bool
    {
        return $this->enabled && $this->pix_enabled && filled($this->access_token);
    }

    public function testMode(): bool
    {
        return str_starts_with((string) $this->access_token, 'TEST-');
    }
}
