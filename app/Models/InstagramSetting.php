<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Conta do Instagram conectada de cada loja (API do Instagram com login do Instagram). */
class InstagramSetting extends Model
{
    protected $table = 'instagram_settings';

    protected $fillable = [
        'user_id', 'app_id', 'app_secret', 'access_token', 'token_expires_at', 'ig_user_id', 'username',
        'name', 'picture_url', 'account_type', 'connected_at', 'refreshed_at', 'caption_product',
        'caption_promo', 'hashtags', 'last_error',
    ];

    protected $casts = [
        'app_secret' => 'encrypted',
        'access_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'refreshed_at' => 'datetime',
    ];

    protected $hidden = ['app_secret', 'access_token'];

    public const DEFAULT_PRODUCT = "✨ {nome}\n\n💰 {preco}\n{variacoes}\n📦 Pronta entrega. Peça pelo direct ou pelo link na bio.\n\n{hashtags}";
    public const DEFAULT_PROMO = "🔥 OFERTA: {nome}\n\nDe {preco_de} por {preco} ({desconto} off)\n{validade}\n\n📲 Corre que é por pouco tempo! Peça pelo direct ou pelo link na bio.\n\n{hashtags}";

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    public function hasApp(): bool
    {
        return filled($this->app_id) && filled($this->app_secret);
    }

    public function connected(): bool
    {
        return filled($this->access_token) && filled($this->ig_user_id)
            && (! $this->token_expires_at || $this->token_expires_at->isFuture());
    }

    public function captionProduct(): string
    {
        return $this->caption_product ?: self::DEFAULT_PRODUCT;
    }

    public function captionPromo(): string
    {
        return $this->caption_promo ?: self::DEFAULT_PROMO;
    }
}
