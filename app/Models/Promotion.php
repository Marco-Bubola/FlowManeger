<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Promoção de um produto: preço "de" (riscado) e preço "por".
 *
 * O price_sale do produto não é alterado: enquanto a promoção está no ar o
 * preço cobrado é o promo_price; ao retirar, volta a valer o price_sale.
 * Isso também mantém o upload de PDF somando estoque no mesmo produto, já que
 * o VariationService compara price/price_sale para decidir.
 */
class Promotion extends Model
{
    public const ATIVA = 'ativa';
    public const AGENDADA = 'agendada';
    public const ENCERRADA = 'encerrada';

    public const ENDED_REASONS = [
        'manual'      => 'Retirada',
        'vencida'     => 'Venceu',
        'sem_estoque' => 'Sem estoque',
        'substituida' => 'Substituída',
    ];

    protected $fillable = [
        'user_id', 'product_id', 'original_price', 'promo_price',
        'starts_at', 'ends_at', 'status', 'ended_reason', 'ended_at',
        'message', 'source',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'promo_price'    => 'decimal:2',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'ended_at'       => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sends()
    {
        return $this->hasMany(PromotionSend::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeOfUser(Builder $q, ?int $userId): Builder
    {
        return $q->where('promotions.user_id', $userId);
    }

    /** Ativas e dentro do prazo (sem olhar o estoque). */
    public function scopeCurrent(Builder $q): Builder
    {
        return $q->where('promotions.status', self::ATIVA)
            ->where(fn ($w) => $w->whereNull('promotions.starts_at')->orWhere('promotions.starts_at', '<=', now()))
            ->where(fn ($w) => $w->whereNull('promotions.ends_at')->orWhere('promotions.ends_at', '>', now()));
    }

    /** Promoções que estão valendo agora (status, datas e estoque). */
    public function scopeLive(Builder $q): Builder
    {
        return $q->current()
            ->whereHas('product', fn ($p) => $p->where('stock_quantity', '>', 0));
    }

    /** Está valendo agora? Confere datas e estoque mesmo antes da rotina horária rodar. */
    public function isLive(): bool
    {
        if ($this->status !== self::ATIVA) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return (int) ($this->product?->stock_quantity ?? 0) > 0;
    }

    public function getDiscountPercentAttribute(): int
    {
        $original = (float) $this->original_price;

        return $original > 0 ? (int) round((1 - (float) $this->promo_price / $original) * 100) : 0;
    }

    public function getSavingsAttribute(): float
    {
        return max(0, round((float) $this->original_price - (float) $this->promo_price, 2));
    }

    public function getLastSendAttribute(): ?PromotionSend
    {
        return $this->relationLoaded('sends')
            ? $this->sends->sortByDesc('created_at')->first()
            : $this->sends()->latest()->first();
    }
}
