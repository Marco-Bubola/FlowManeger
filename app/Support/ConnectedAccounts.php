<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Contas que a loja já conectou no app (Instagram, WhatsApp, Mercado Livre, Shopee).
 * Usado no Perfil e em Informações da loja para não pedir de novo o que já veio da conexão.
 * Cada item: name, icon (Bootstrap Icons), color, on, handle (@usuario / apelido / loja), url (perfil público), manage (rota da tela).
 */
class ConnectedAccounts
{
    private static array $memo = [];

    public static function for(?int $userId): array
    {
        if (! $userId) {
            return [];
        }
        if (isset(self::$memo[$userId])) {
            return self::$memo[$userId];
        }

        $has = fn ($t) => rescue(fn () => Schema::hasTable($t), false, false);
        $out = [];

        $ig = $has('instagram_settings') ? rescue(fn () => \App\Models\InstagramSetting::where('user_id', $userId)->first(), null, false) : null;
        $igOn = $ig && $ig->connected() && filled($ig->username);
        $out['instagram'] = ['Instagram', 'bi-instagram', 'from-pink-500 to-orange-400', $igOn, $igOn ? '@' . ltrim($ig->username, '@') : null, $igOn ? 'https://instagram.com/' . ltrim($ig->username, '@') : null, 'settings.instagram'];

        $wa = $has('whatsapp_settings') ? rescue(fn () => \App\Models\WhatsAppSetting::where('user_id', $userId)->first(), null, false) : null;
        $waOn = $wa && $wa->enabled && filled($wa->phone_number_id) && filled($wa->access_token);
        $out['whatsapp'] = ['WhatsApp', 'bi-whatsapp', 'from-green-500 to-emerald-600', $waOn, $waOn ? 'Lançar por print ligado' : null, null, 'settings.whatsapp'];

        $ml = $has('mercadolivre_tokens') ? rescue(fn () => \App\Models\MercadoLivreToken::where('user_id', $userId)->where('is_active', true)->latest('updated_at')->first(), null, false) : null;
        $mlOn = (bool) $ml;
        $nick = $ml?->ml_nickname;
        $out['mercadolivre'] = ['Mercado Livre', 'bi-bag-check', 'from-yellow-400 to-amber-500', $mlOn, $mlOn ? ($nick ?: 'Conta conectada') : null, $nick ? 'https://www.mercadolivre.com.br/perfil/' . rawurlencode($nick) : null, 'settings.connections'];

        $sp = $has('shopee_tokens') ? rescue(fn () => \App\Models\ShopeeToken::where('user_id', $userId)->where('is_active', true)->latest('updated_at')->first(), null, false) : null;
        $spOn = (bool) $sp;
        $out['shopee'] = ['Shopee', 'bi-bag', 'from-orange-500 to-red-500', $spOn, $spOn ? ($sp->shop_name ?: 'Loja conectada') : null, $spOn && $sp->shop_id ? 'https://shopee.com.br/shop/' . $sp->shop_id : null, 'settings.connections'];

        return self::$memo[$userId] = array_map(fn ($a) => array_combine(['name', 'icon', 'color', 'on', 'handle', 'url', 'manage'], $a), $out);
    }
}
