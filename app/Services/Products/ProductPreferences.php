<?php

namespace App\Services\Products;

use App\Models\ProductUploadHistory;
use App\Models\User;

/**
 * Preferências de produtos de cada conta (Configurações › Produtos), guardadas
 * em users.preferences['products']. O padrão é o jeito de sempre da Boticário.
 */
class ProductPreferences
{
    /** Mesmo código e nome com outro custo: 'variation' (padrão) ou 'update'. */
    public const COST_CHANGE = 'cost_change';

    /** Menu "Upload de PDF": 'auto' (padrão), 'show' ou 'hide'. */
    public const PDF_UPLOAD = 'pdf_upload';

    public static function get(?User $user): array
    {
        $prefs = $user?->preferences['products'] ?? [];

        return [
            self::COST_CHANGE => in_array($prefs[self::COST_CHANGE] ?? null, ['variation', 'update'], true) ? $prefs[self::COST_CHANGE] : 'variation',
            self::PDF_UPLOAD => in_array($prefs[self::PDF_UPLOAD] ?? null, ['auto', 'show', 'hide'], true) ? $prefs[self::PDF_UPLOAD] : 'auto',
        ];
    }

    public static function save(User $user, array $values): void
    {
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $prefs['products'] = array_merge(self::get($user), array_intersect_key($values, array_flip([self::COST_CHANGE, self::PDF_UPLOAD])));
        $user->forceFill(['preferences' => $prefs])->save();
    }

    /** A conta atualiza o custo (em vez de criar variação "R$ xx") quando ele muda. */
    public static function costChangeUpdates(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        static $cache = [];

        return $cache[$userId] ??= self::get(User::find($userId))[self::COST_CHANGE] === 'update';
    }

    /** Mostra o "Upload de PDF" no menu: sempre, nunca, ou só para quem já importou um PDF. */
    public static function showPdfUpload(?User $user): bool
    {
        if (!$user) {
            return true;
        }
        $mode = self::get($user)[self::PDF_UPLOAD];
        if ($mode !== 'auto') {
            return $mode === 'show';
        }

        // Automático: conta nova ainda vê (pode querer testar); some quando a conta
        // tem produtos e nunca importou um PDF de pedido.
        static $cache = [];

        return $cache[$user->id] ??= (function () use ($user) {
            try {
                return ProductUploadHistory::where('user_id', $user->id)->exists()
                    || !\App\Models\Product::withoutGlobalScope('team_visibility')->where('user_id', $user->id)->exists();
            } catch (\Throwable) {
                return true; // na dúvida, mostra como sempre foi
            }
        })();
    }
}
