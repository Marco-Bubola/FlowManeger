<?php

namespace App\Services\Choices;

use App\Models\ChoiceCatalog;
use App\Models\ChoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/**
 * Listas de escolha do produto (cor da ferragem, estofado...) a partir do
 * catálogo da loja. Sob encomenda: não há estoque por combinação; cada item
 * pode somar um valor ao preço (ex.: corino um valor, corvin outro).
 */
class ChoiceService
{
    public const KINDS = [
        'estofado' => ['Estofado', 'bi-palette', 'Tecidos e cores do assento ou encosto'],
        'ferragem' => ['Cor da ferragem', 'bi-paint-bucket', 'Cor da pintura do ferro'],
        'outro' => ['Outra escolha', 'bi-ui-checks-grid', 'Madeira, acabamento, puxador...'],
    ];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('choice_catalogs') && Schema::hasColumn('products', 'choice_lists');
    }

    /** Listas da loja com os itens (para as telas do app e o robô). */
    public static function catalogs(int $userId)
    {
        if (! self::ready()) {
            return collect();
        }

        return ChoiceCatalog::where('user_id', $userId)->with('items')->orderBy('position')->orderBy('id')->get();
    }

    /** Tem escolhas? (para o botão rápido de carrinho mandar à página do produto) */
    public static function has(Product $product): bool
    {
        static $parents = [];
        if (! self::ready()) {
            return false;
        }
        if (! empty($product->choice_lists)) {
            return true;
        }
        if (! $product->parent_id) {
            return false;
        }

        return $parents[$product->parent_id] ??= (bool) Product::withoutGlobalScope('team_visibility')
            ->whereKey($product->parent_id)->whereNotNull('choice_lists')->where('choice_lists', '!=', '[]')->exists();
    }

    /** Listas que o produto liga (as do produto; variação sem listas usa as do produto principal). */
    public static function rawLists(Product $product): array
    {
        $lists = $product->choice_lists ?: [];
        if (! $lists && $product->parent_id) {
            $lists = Product::withoutGlobalScope('team_visibility')->whereKey($product->parent_id)->value('choice_lists') ?: [];
            $lists = is_string($lists) ? (json_decode($lists, true) ?: []) : $lists;
        }

        return array_values(array_filter((array) $lists, fn ($l) => is_array($l) && ! empty($l['c'])));
    }

    /**
     * O que o portal mostra: [{id, label, kind, options: [{id, m, n, label, hex, img, x}]}].
     * Só itens ativos; lista sem item nenhum não aparece.
     */
    public static function forProduct(Product $product): array
    {
        if (! self::ready() || ! ($raw = self::rawLists($product))) {
            return [];
        }
        $cats = ChoiceCatalog::where('user_id', $product->user_id)->whereIn('id', array_column($raw, 'c'))
            ->with(['items' => fn ($q) => $q->where('active', true)])->get()->keyBy('id');

        $out = [];
        foreach ($raw as $l) {
            $cat = $cats[(int) $l['c']] ?? null;
            if (! $cat) {
                continue;
            }
            $only = isset($l['i']) && is_array($l['i']) && $l['i'] ? array_map('intval', $l['i']) : null;
            $items = $cat->items->filter(fn ($it) => $only === null || in_array($it->id, $only, true));
            // Duas cores com o mesmo nome (ex.: dois verdes): o código diferencia.
            $dup = $items->countBy(fn ($it) => mb_strtolower($it->label()))->filter(fn ($n) => $n > 1);
            $opts = $items
                ->map(fn (ChoiceItem $it) => [
                    'id' => $it->id, 'm' => $it->material,
                    'n' => $it->name . (isset($dup[mb_strtolower($it->label())]) && $it->code ? ' ' . $it->code : ''),
                    'label' => $it->label() . (isset($dup[mb_strtolower($it->label())]) && $it->code ? ' ' . $it->code : ''),
                    'hex' => $it->hex, 'img' => $it->imageUrl(), 'x' => round((float) $it->extra_price, 2),
                ])->values()->all();
            if (! $opts) {
                continue;
            }
            $label = trim((string) ($l['l'] ?? '')) ?: $cat->name;
            $out[] = ['id' => $cat->id, 'label' => $label, 'kind' => $cat->kind, 'options' => $opts,
                'grouped' => count(array_unique(array_filter(array_column($opts, 'm')))) > 1];
        }

        return $out;
    }

    /**
     * Confere as escolhas que vieram do portal ({catálogo: item}) com o que o produto aceita.
     * Devolve ['ok', 'missing' => [nomes], 'options' => [{l, v, x}], 'extra', 'text'].
     */
    public static function resolve(Product $product, $picked): array
    {
        $picked = is_array($picked) ? $picked : [];
        $lists = self::forProduct($product);
        $options = [];
        $missing = [];
        foreach ($lists as $l) {
            $id = (int) ($picked[$l['id']] ?? $picked[(string) $l['id']] ?? 0);
            $opt = collect($l['options'])->firstWhere('id', $id);
            if (! $opt) {
                $missing[] = $l['label'];
                continue;
            }
            $options[] = ['l' => $l['label'], 'v' => $opt['label'], 'x' => $opt['x'], 'c' => (int) $l['id'], 'i' => $opt['id']];
        }

        return [
            'ok' => ! $missing,
            'missing' => $missing,
            'options' => $options,
            'extra' => round(array_sum(array_column($options, 'x')), 2),
            'text' => self::text($options),
        ];
    }

    /** "Estofado: Corino · Preto | Cor da ferragem: Preto" (vai na observação da venda). */
    public static function text(array $options): string
    {
        return implode(' | ', array_map(fn ($o) => ($o['l'] ?? '') . ': ' . ($o['v'] ?? ''), $options));
    }
}
