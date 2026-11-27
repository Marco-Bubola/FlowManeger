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

    /** Tipo de loja: 'auto' (padrão), 'revenda' ou 'moveis' (Editar produto com variações de móvel). */
    public const STORE_KIND = 'store_kind';

    /** Página de criar/editar kit: 'auto' (segue o tipo de loja), 'revenda' (Eudora) ou 'moveis' (conjuntos de móveis). */
    public const KIT_PAGE = 'kit_page';

    public static function get(?User $user): array
    {
        $prefs = $user?->preferences['products'] ?? [];

        return [
            self::COST_CHANGE => in_array($prefs[self::COST_CHANGE] ?? null, ['variation', 'update'], true) ? $prefs[self::COST_CHANGE] : 'variation',
            self::PDF_UPLOAD => in_array($prefs[self::PDF_UPLOAD] ?? null, ['auto', 'show', 'hide'], true) ? $prefs[self::PDF_UPLOAD] : 'auto',
            self::STORE_KIND => in_array($prefs[self::STORE_KIND] ?? null, ['auto', 'revenda', 'moveis'], true) ? $prefs[self::STORE_KIND] : 'auto',
            self::KIT_PAGE => in_array($prefs[self::KIT_PAGE] ?? null, ['auto', 'revenda', 'moveis'], true) ? $prefs[self::KIT_PAGE] : 'auto',
        ];
    }

    public static function save(User $user, array $values): void
    {
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $prefs['products'] = array_merge(self::get($user), array_intersect_key($values, array_flip([self::COST_CHANGE, self::PDF_UPLOAD, self::STORE_KIND, self::KIT_PAGE])));
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

    /**
     * Loja de móveis? Ligado em Configurações › Produtos. No automático vale
     * para quem já tem peça sob encomenda ou produto com escolhas no portal.
     */
    public static function isFurniture(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        $mode = self::get($user)[self::STORE_KIND];
        if ($mode !== 'auto') {
            return $mode === 'moveis';
        }
        static $cache = [];

        return $cache[$user->id] ??= (function () use ($user) {
            try {
                $base = \App\Models\Product::withoutGlobalScope('team_visibility')->where('user_id', $user->id);

                return (clone $base)->where('made_to_order', true)->exists()
                    || (\App\Services\Choices\ChoiceService::ready() && (clone $base)->whereNotNull('choice_lists')->where('choice_lists', '!=', '[]')->exists());
            } catch (\Throwable) {
                return false;
            }
        })();
    }

    /** Criar/editar kit abre a página de conjuntos de móveis? No automático segue o tipo de loja. */
    public static function furnitureKitPage(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        $mode = self::get($user)[self::KIT_PAGE];

        return $mode === 'auto' ? self::isFurniture($user) : $mode === 'moveis';
    }
}
