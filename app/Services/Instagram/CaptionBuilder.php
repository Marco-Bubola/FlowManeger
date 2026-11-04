<?php

namespace App\Services\Instagram;

use App\Models\InstagramSetting;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\Portal\StoreIdentity;

/** Legenda pronta a partir do modelo salvo em Configurações › Instagram. */
class CaptionBuilder
{
    public const TAGS = [
        '{nome}' => 'Nome do produto',
        '{preco}' => 'Preço (ou preço da promoção)',
        '{preco_de}' => 'Preço antes da promoção',
        '{desconto}' => 'Desconto em %',
        '{validade}' => 'Até quando vale a promoção',
        '{variacoes}' => 'Cores ou tamanhos à venda',
        '{codigo}' => 'Código do produto',
        '{loja}' => 'Nome da loja',
        '{hashtags}' => 'Suas hashtags',
    ];

    public const MAX = 2200;

    public static function build(InstagramSetting $s, ?Product $product, ?Promotion $promo = null): string
    {
        $tpl = $promo ? $s->captionPromo() : $s->captionProduct();
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $store = StoreIdentity::for($s->user_id)['name'] ?? '';

        $variations = '';
        if ($product) {
            $variations = Product::withoutGlobalScope('team_visibility')
                ->where('user_id', $product->user_id)
                ->where(fn ($q) => $q->where('id', $product->parent_id ?: $product->id)->orWhere('parent_id', $product->parent_id ?: $product->id))
                ->whereNotNull('variation_value')->where('variation_value', '!=', '')
                ->where(fn ($q) => $q->where('stock_quantity', '>', 0)->orWhere('made_to_order', true))
                ->pluck('variation_value')
                ->reject(fn ($v) => preg_match('/^R\$\s*\d/u', (string) $v)) // variação só de custo ("R$ 64,00")
                ->unique()->take(12)->implode(' · ');
            $variations = $variations !== '' ? '🎨 Opções: ' . $variations : '';
        }

        $price = $promo ? (float) $promo->promo_price : (float) ($product?->price_sale ?? 0);
        $values = [
            '{nome}' => $product ? self::title((string) $product->name) : '',
            '{preco}' => $price > 0 ? $money($price) : '',
            '{preco_de}' => $promo ? $money($promo->original_price) : '',
            '{desconto}' => $promo ? $promo->discount_percent . '%' : '',
            '{validade}' => $promo && $promo->ends_at ? '⏰ Válido até ' . $promo->endsLocal()->format('d/m') : '',
            '{variacoes}' => $variations,
            '{codigo}' => (string) ($product?->product_code ?? ''),
            '{loja}' => $store,
            '{hashtags}' => self::hashtags((string) $s->hashtags),
        ];

        return self::clean(strtr($tpl, $values));
    }

    /** "#a #b" a partir do que a pessoa digitou (com ou sem #, vírgulas); no máximo 30. */
    public static function hashtags(string $raw): string
    {
        return collect(preg_split('/[\s,;]+/u', $raw) ?: [])
            ->map(fn ($t) => preg_replace('/[^\p{L}\p{N}_]/u', '', $t))
            ->filter()->unique()->take(30)
            ->map(fn ($t) => '#' . $t)->implode(' ');
    }

    public static function countHashtags(string $caption): int
    {
        return preg_match_all('/#[\p{L}\p{N}_]+/u', $caption);
    }

    /** Tira linhas vazias repetidas e espaços sobrando (quando um campo não tem valor). */
    public static function clean(string $text): string
    {
        $lines = array_map('rtrim', preg_split('/\R/u', $text));
        $text = preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines));

        return mb_substr(trim($text), 0, self::MAX);
    }

    /** "MESA DE JANTAR E APARADOR 400ML" → "Mesa de Jantar e Aparador 400ml". */
    public static function title(string $name): string
    {
        $name = mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
        $name = preg_replace_callback('/(\d)(Ml|Mg|G|Kg|Cm|Un|M)\b/u', fn ($m) => $m[1] . mb_strtolower($m[2]), $name);
        $name = preg_replace_callback('/(?<=\s)(E|De|Da|Do|Das|Dos|Com|Para|Em|A|O|Na|No|Sem)(?=\s)/u', fn ($m) => mb_strtolower($m[1]), $name);

        return preg_replace_callback('/-(\p{Lu})/u', fn ($m) => '-' . mb_strtolower($m[1]), $name);
    }
}
