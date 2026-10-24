<?php

namespace App\Services\Promotions;

use App\Models\Product;
use App\Models\PromotionSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo da loja: muda os modelos de banner, as datas comemorativas e as
 * mensagens de promoção para combinar com o que a loja vende.
 * Sem escolha salva, a loja com produto sob encomenda é de móveis.
 */
class StoreKind
{
    /** chave => [nome, ícone, exemplos] */
    public const KINDS = [
        'beleza' => ['Beleza e perfumaria', 'bi-flower1', 'Eudora, Boticário, Natura, maquiagem'],
        'moveis' => ['Móveis e decoração', 'bi-lamp', 'Ferro, madeira, sob encomenda'],
        'geral'  => ['Outra loja', 'bi-shop', 'Modelos que servem para tudo'],
    ];

    private static array $memo = [];

    public static function for(?int $userId): string
    {
        if (! $userId) {
            return 'beleza';
        }
        if (isset(self::$memo[$userId])) {
            return self::$memo[$userId];
        }
        $saved = Schema::hasColumn('promotion_settings', 'store_kind')
            ? PromotionSetting::where('user_id', $userId)->value('store_kind') : null;
        if (! isset(self::KINDS[(string) $saved])) {
            $saved = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)->where('made_to_order', true)->exists()
                ? 'moveis' : 'beleza';
        }

        return self::$memo[$userId] = $saved;
    }

    public static function set(int $userId, string $kind): string
    {
        $kind = isset(self::KINDS[$kind]) ? $kind : 'beleza';
        $s = PromotionSetting::forUser($userId);
        $s->user_id = $userId;
        $s->store_kind = $kind;
        $s->save();

        return self::$memo[$userId] = $kind;
    }
}
