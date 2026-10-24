<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Coleção da vitrine (ex.: "Dia das Mães", "Linha Floratta"): produtos escolhidos
 * pela loja, com banner próprio, uma fileira na página inicial e página filtrada.
 * Guarda o produto principal de cada família; o catálogo mostra a variação à venda.
 */
class PortalCollection extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'image', 'color', 'sort', 'active', 'show_on_home', 'rule', 'rule_data', 'design',
    ];

    protected $casts = [
        'sort'         => 'integer',
        'active'       => 'boolean',
        'show_on_home' => 'boolean',
        'rule_data'    => 'array',
        'design'       => 'array',
    ];

    /** Coleção que se atualiza sozinha (regra), não escolhida à mão. */
    public function isAuto(): bool
    {
        return ($this->rule ?? 'manual') !== 'manual';
    }

    /** Capa montada com um modelo de banner. */
    public function hasDesign(): bool
    {
        return ! $this->image && ! empty($this->design['tpl']);
    }

    /** Produtos da coleção (principal da família): os escolhidos ou os da regra, na ordem. */
    public function rootIds(int $limit = 200): array
    {
        return $this->isAuto()
            ? \App\Services\Portal\StorefrontService::ruleRoots((int) $this->user_id, $this->rule, $this->rule_data ?? [], $limit)
            : array_slice($this->productIds(), 0, $limit);
    }

    /** Ids (principal da família) na ordem escolhida. */
    public function productIds(): array
    {
        return DB::table('portal_collection_product')
            ->where('portal_collection_id', $this->id)
            ->orderBy('sort')->orderBy('id')
            ->pluck('product_id')->map(fn ($v) => (int) $v)->all();
    }

    public function syncProducts(array $ids): void
    {
        DB::table('portal_collection_product')->where('portal_collection_id', $this->id)->delete();
        $rows = [];
        foreach (array_values(array_unique(array_map('intval', $ids))) as $i => $id) {
            $rows[] = ['portal_collection_id' => $this->id, 'product_id' => $id, 'sort' => $i];
        }
        if ($rows) {
            DB::table('portal_collection_product')->insert($rows);
        }
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    protected static function booted(): void
    {
        static::deleting(fn (self $c) => DB::table('portal_collection_product')->where('portal_collection_id', $c->id)->delete());
    }
}
