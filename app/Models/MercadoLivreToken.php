<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLivreToken extends Model
{
    use HasFactory;

    protected $table = 'mercadolivre_tokens';

    protected $fillable = [
        'user_id',
        'ml_user_id',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'scope',
        'is_active',
        'ml_nickname',
        'user_info',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'user_info' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o token está expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica se o token está próximo de expirar (menos de 1 hora)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at->diffInHours(now()) < 1;
    }

    /**
     * Verifica se o token precisa ser renovado (menos de 24 horas para expirar)
     */
    public function needsRefresh(): bool
    {
        return $this->expires_at->diffInHours(now()) < 24;
    }

    /**
     * Verifica se o token é válido
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Atualiza o token
     */
    public function updateToken(array $tokenData)
    {
        $this->update([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? $this->refresh_token,
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'expires_at' => now()->addSeconds($tokenData['expires_in']),
            'scope' => $tokenData['scope'] ?? $this->scope,
        ]);
    }

    /**
     * Desativa o token
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '<=', now()->addHour());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
