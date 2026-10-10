<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Uma variação de um anúncio do ML (ex.: "Cor: Nude") e o produto do estoque
 * que sai quando ela é vendida.
 */
class MlPublicationVariation extends Model
{
    protected $table = 'ml_publication_variations';

    protected $fillable = [
        'ml_publication_id',
        'ml_variation_id',
        'label',
        'attribute_values',
        'seller_sku',
        'gtin',
        'picture_url',
        'ml_available_quantity',
        'product_id',
        'quantity',
        'stock_confirmed',
        'link_source',
        'sort_order',
    ];

    protected $casts = [
        'attribute_values' => 'array',
        'ml_available_quantity' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'stock_confirmed' => 'boolean',
        'sort_order' => 'integer',
    ];

    private static ?bool $tableExists = null;

    public static function tableExists(): bool
    {
        if (self::$tableExists === null) {
            try {
                self::$tableExists = Schema::hasTable('ml_publication_variations');
            } catch (\Throwable $e) {
                return false;
            }
        }
        return self::$tableExists;
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(MlPublication::class, 'ml_publication_id');
    }

    /** Produto ligado (sem filtro de equipe: webhook/fila rodam sem usuário). */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withoutGlobalScope('team_visibility');
    }

    public function perSale(): int
    {
        return max(1, (int) $this->quantity);
    }

    /** Valores dos atributos da variação ("Nude", "P"...). */
    public function values(): array
    {
        return collect($this->attribute_values ?? [])->pluck('value')->filter()->values()->all();
    }

    /** "Nude / P" (só os valores), para textos curtos. */
    public function shortLabel(): string
    {
        $v = implode(' / ', $this->values());
        return $v !== '' ? $v : ($this->label ?: ('#' . $this->ml_variation_id));
    }

    /**
     * Grava/atualiza as linhas a partir de ml_variations da publicação (que vem
     * do item do ML). Mantém o produto escolhido; apaga variações que o ML não
     * tem mais.
     */
    public static function syncFromPublication(MlPublication $publication): void
    {
        if (! self::tableExists() || ! $publication->exists) {
            return;
        }

        $variations = is_array($publication->ml_variations) ? $publication->ml_variations : [];
        $keep = [];
        foreach (array_values($variations) as $i => $v) {
            if (! isset($v['id'])) {
                continue;
            }
            $vid = (string) $v['id'];
            $keep[] = $vid;
            $attrs = is_array($v['attributes'] ?? null) ? $v['attributes'] : [];
            $label = $v['name_label'] ?? null;
            if (! filled($label)) {
                $label = collect($attrs)->map(fn ($a) => trim(($a['name'] ?? '') !== '' ? $a['name'] . ': ' . ($a['value'] ?? '') : ($a['value'] ?? '')))->filter()->implode(' · ');
            }
            if (! filled($label)) {
                $label = $v['label'] ?? null;
            }

            $row = self::firstOrNew(['ml_publication_id' => $publication->id, 'ml_variation_id' => $vid]);
            $row->fill([
                'label' => filled($label) ? mb_substr((string) $label, 0, 255) : null,
                'attribute_values' => $attrs ?: (filled($v['label'] ?? null) ? [['name' => null, 'value' => $v['label']]] : null),
                'seller_sku' => filled($v['seller_sku'] ?? null) ? mb_substr((string) $v['seller_sku'], 0, 120) : null,
                'gtin' => filled($v['gtin'] ?? null) ? mb_substr((string) $v['gtin'], 0, 40) : null,
                'picture_url' => filled($v['picture'] ?? null) ? mb_substr((string) $v['picture'], 0, 500) : null,
                'ml_available_quantity' => (int) ($v['available_quantity'] ?? 0),
                'sort_order' => $i,
            ]);
            if ($row->isDirty()) {
                $row->save();
            }
        }

        $stale = self::where('ml_publication_id', $publication->id);
        if ($keep) {
            $stale->whereNotIn('ml_variation_id', $keep);
        }
        $stale->delete();
    }

    /** "Nude", "nude ", "NUDÉ" ficam iguais. */
    public static function norm(?string $v): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower(Str::ascii((string) $v))));
    }

    /** Chaves com que um produto pode casar: todos os valores juntos e cada um. */
    public function matchKeys(): array
    {
        $vals = array_map([self::class, 'norm'], $this->values());
        $vals = array_values(array_filter($vals));
        $keys = $vals;
        if (count($vals) > 1) {
            $keys[] = implode(' ', $vals);
        }
        return array_values(array_unique($keys));
    }
}
