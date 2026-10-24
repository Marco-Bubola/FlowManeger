<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Banner da vitrine do catálogo: no topo (carrossel), nas laterais (telas largas)
 * ou em faixa entre as seções. Sem imagem, vira um banner de texto colorido.
 */
class PortalBanner extends Model
{
    public const PLACEMENTS = [
        'hero'  => 'Topo (carrossel)',
        'strip' => 'Faixa entre as seções',
        'side'  => 'Lateral (telas largas)',
    ];

    protected $fillable = [
        'user_id', 'placement', 'image', 'image_mobile', 'color', 'title', 'subtitle',
        'button_text', 'link_type', 'link_value', 'sort', 'active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'sort'      => 'integer',
        'active'    => 'boolean',
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    /** Ligado e dentro do período. */
    public function scopeLive(Builder $q): Builder
    {
        $today = now()->toDateString();

        return $q->where('active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function mobileUrl(): ?string
    {
        return $this->image_mobile ? asset('storage/' . $this->image_mobile) : null;
    }
}
