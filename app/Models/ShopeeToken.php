<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeToken extends Model
{
    use HasFactory;

    protected $table = 'shopee_tokens';

    protected $fillable = [
        'user_id',
        'shop_id',
        'shop_name',
        'partner_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'refresh_expires_at',
        'region',
        'shop_info',
        'is_active',
        'last_refreshed_at',
    ];

    protected $casts = [
        'expires_at'          => 'datetime',
        'refresh_expires_at'  => 'datetime',
        'last_refreshed_at'   => 'datetime',
        'is_active'           => 'boolean',
        'shop_info'           => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Verifica se o access_token está válido (ainda não expirou). */
    public function isAccessTokenValid(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->lt($this->expires_at->subMinutes(5));
    }

    /** Verifica se o refresh_token ainda é utilizável. */
    public function isRefreshTokenValid(): bool
    {
        if (!$this->refresh_expires_at) {
            return (bool) $this->refresh_token;
        }
        return now()->lt($this->refresh_expires_at->subMinutes(5));
    }

    /**
     * Retorna o token ativo para um usuário (buscando o mais recente e válido).
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }
}
