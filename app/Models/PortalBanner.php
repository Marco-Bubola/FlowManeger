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
        'button_text', 'link_type', 'link_value', 'sort', 'active', 'starts_at', 'ends_at', 'design',
    ];

    protected $casts = [
        'sort'      => 'integer',
        'active'    => 'boolean',
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'design'    => 'array',
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

    /** Banner montado a partir de um modelo pronto (sem imagem pronta). */
    public function isDesign(): bool
    {
        return ! $this->image && ! empty($this->design['tpl']);
    }

    /** Fotos do modelo: de produtos (sempre a foto atual) ou enviadas. */
    public function designPhotos(): array
    {
        return self::photoUrls((int) $this->user_id, $this->design['photos'] ?? []);
    }

    /** Endereço de cada foto do modelo ({p: produto} ou {f: arquivo enviado}). */
    public static function photoUrls(int $userId, array $photos, bool $keepEmpty = false): array
    {
        $list = collect($photos)->take(3);
        $ids = $list->pluck('p')->filter()->all();
        $products = $ids ? Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)->whereIn('id', $ids)->get()->keyBy('id') : collect();

        return $list->map(function ($ph) use ($products) {
            if (! empty($ph['f'])) {
                return asset('storage/' . $ph['f']);
            }
            $p = $products[$ph['p'] ?? 0] ?? null;

            return $p && $p->image && $p->image !== 'product-placeholder.png' ? $p->image_url : ($p?->all_images[0] ?? null);
        })->when(! $keepEmpty, fn ($c) => $c->filter())->values()->all();
    }

    /** Arquivos enviados para as fotos do modelo (para apagar quando saem). */
    public function designFiles(): array
    {
        return collect($this->design['photos'] ?? [])->pluck('f')->filter()->values()->all();
    }

    public function mobileUrl(): ?string
    {
        return $this->image_mobile ? asset('storage/' . $this->image_mobile) : null;
    }
}
