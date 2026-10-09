<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Services\Portal\WishlistService;
use Illuminate\Support\Facades\Log;

/**
 * "Avise-me": quando uma promoção passa a valer (criada no ar, ligada ou com o
 * início mudado), grava os avisos de quem favoritou o produto no portal.
 * As agendadas ligadas em massa pela rotina são pegas pela varredura
 * (WishlistService::sweepPromotions).
 */
class PromotionWishlistObserver
{
    public function saved(Promotion $promotion): void
    {
        if (! $promotion->wasRecentlyCreated && ! $promotion->wasChanged('status') && ! $promotion->wasChanged('starts_at')) {
            return;
        }

        try {
            app(WishlistService::class)->promotionStarted($promotion);
        } catch (\Throwable $e) {
            Log::warning('Avise-me (promoção) falhou: ' . $e->getMessage(), ['promotion_id' => $promotion->id]);
        }
    }
}
