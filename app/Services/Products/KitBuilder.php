<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Conjunto de móveis montado com as peças que já estão no site.
 * Usado pela página de conjunto (FurnitureKit) e pela ficha com "conjunto"
 * (ProductFichaImporter / robô): cada combinação das variações das peças
 * vira uma opção do conjunto, com a soma dos preços e dos custos, o estoque
 * da peça que acaba primeiro e as fotos das peças.
 */
class KitBuilder
{
    public const MAX_OPTIONS = 40;

    /** Família da peça pelo código ("BQT54", "BQT54-BRVD" ou "MBR"): o principal do grupo. */
    public static function family(int $userId, string $code): ?Product
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }
        $base = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('tipo')->orWhere('tipo', '!=', 'kit'));
        $p = (clone $base)->where('product_code', $code)->first()
            ?? (clone $base)->where('product_code', 'like', str_replace(['%', '_'], ['\%', '\_'], $code) . '-%')->orderBy('id')->first()
            ?? (clone $base)->where('product_code', 'like', str_replace(['%', '_'], ['\%', '\_'], $code) . '%')->orderBy('id')->first();
        if (!$p) {
            return null;
        }

        return $p->parent_id ? (Product::withoutGlobalScope('team_visibility')->find($p->parent_id) ?? $p) : $p;
    }

    /** Variações ativas da família (sem kits). */
    public static function members(Product $root): Collection
    {
        $all = $root->is_variation_parent
            ? Product::withoutGlobalScope('team_visibility')->where('user_id', $root->user_id)
                ->where(fn ($q) => $q->where('id', $root->id)->orWhere('parent_id', $root->id))
                ->orderByRaw('parent_id is not null')->orderBy('variation_sort')->orderBy('id')->get()
            : collect([$root]);

        return $all->filter(fn ($m) => !$m->isKit() && ($m->status ?? 'ativo') !== 'inativo')->values();
    }

    /**
     * Combinações das peças. $lists: uma lista de variações por peça, cada uma com 'color'.
     * Modo 'cor': só junta variações com a mesma cor de ferragem (a da primeira peça).
     */
    public static function combos(array $lists, string $mode = 'cor', int $limit = 400): array
    {
        $combos = [[]];
        foreach ($lists as $list) {
            $next = [];
            foreach ($combos as $c) {
                foreach ($list as $v) {
                    if ($mode === 'cor' && $c && ($v['color'] ?? '') !== '' && ($c[0]['color'] ?? '') !== '' && $v['color'] !== $c[0]['color']) {
                        continue;
                    }
                    // 'igual': mesmo acabamento pelo fim do código (NTU-EP + PTU-EP)
                    if ($mode === 'igual' && $c && self::finish((string) ($v['code'] ?? '')) !== self::finish((string) ($c[0]['code'] ?? ''))) {
                        continue;
                    }
                    $next[] = array_merge($c, [$v]);
                }
            }
            $combos = $next;
            if (count($combos) > $limit) {
                break;
            }
        }

        return $lists ? $combos : [];
    }

    /** Fim do código da variação: "NTU-EP" → "ep". */
    public static function finish(string $code): string
    {
        return Str::lower(str_contains($code, '-') ? Str::after($code, '-') : $code);
    }

    /** Assinatura das peças de uma opção: [[id, qtd], ...] → "12x1_40x2". */
    public static function signature(array $parts): string
    {
        $s = array_map(fn ($p) => $p[0] . 'x' . $p[1], $parts);
        sort($s);

        return implode('_', $s);
    }

    /** CBR + o fim do código de cada peça: CBR-PT-TPT-PTVM. Nunca repete. */
    public static function optionCode(string $base, array $codes, array $taken): string
    {
        $base = $base !== '' ? $base : 'KIT';
        $bits = [];
        foreach ($codes as $c) {
            $c = (string) $c;
            $tail = str_contains($c, '-') ? Str::after($c, '-') : $c;
            $bits[] = Str::upper(preg_replace('/[^A-Za-z0-9-]/', '', $tail));
        }
        $code = mb_substr($base . '-' . implode('-', array_unique(array_filter($bits))), 0, 90);
        $try = $code;
        $n = 2;
        while (isset($taken[mb_strtolower($try)])) {
            $try = $code . '-' . $n++;
        }

        return $try;
    }

    /** Cor da ferragem: a primeira cor do nome da variação, sem o tamanho. "54 cm · Preto com corano Vermelho" → "preto". */
    public static function colorKey(string $value): string
    {
        foreach (preg_split('/\s*[·|\/,+]\s*|\s-\s/u', $value) as $part) {
            $part = Str::lower(Str::ascii(trim($part)));
            $part = trim(preg_replace('/\b\d+([.,]\d+)?\s*(cm|m|mm)\b/', ' ', $part));
            if ($part === '' || preg_match('/^(com|sem)\s+tampo\b/', $part) && !preg_match('/^(com|sem)\s+tampo\s+\S/', $part)
                || str_starts_with($part, 'tampo') || str_starts_with($part, 'kit') || str_starts_with($part, 'assento a')) {
                continue;
            }
            $part = preg_replace('/^(ferragem|estrutura|cor|base)\s+/', '', $part);
            if (preg_match('/^(rose\s*gold|ouro velho|ouro envelhecido|[a-z]+)/', $part, $m)) {
                // "Preta", "brancas" → "preto", "branco" (cadeira preta junta com mesa preta)
                $w = preg_replace('/^(pret|branc|vermelh|amarel|dourad|pratead|rox)as?$/', '$1o', str_replace(' ', '', $m[1]));

                return preg_replace('/^(preto|branco|vermelho|amarelo|dourado|prateado|roxo|verde|azul|bege|cinza)s$/', '$1', $w);
            }
        }

        return '';
    }

    /** "Banqueta de ferro 54 cm com assento" → "Banqueta"; "Mesinha Bistrô Redonda" → "Mesinha". */
    public static function shortLabel(string $name): string
    {
        $first = trim(Str::before(trim($name), ' '));

        return $first !== '' ? Str::ucfirst($first) : 'Peça';
    }

    /**
     * Fotos do conjunto = fotos das peças da opção: a foto principal de cada peça,
     * depois as outras fotos da primeira peça. Os arquivos são copiados (kit-…), assim
     * apagar o conjunto nunca apaga a foto da peça. Troca as fotos que vieram das peças antes.
     */
    public static function copyImages(Product $kit, bool $replaceUploads = false): void
    {
        $disk = Storage::disk('public');
        $parts = $kit->componentes()->orderBy('id')->get()
            ->map(fn ($pc) => Product::withoutGlobalScope('team_visibility')->find($pc->componente_produto_id))
            ->filter();
        $sources = [];
        foreach ($parts as $p) {
            if ($p->image && $p->image !== 'product-placeholder.png') {
                $sources[] = $p->image;
            }
        }
        if ($first = $parts->first()) {
            foreach (ProductImage::where('product_id', $first->id)->orderBy('sort_order')->limit(6)->pluck('filename') as $f) {
                $sources[] = $f;
            }
        }
        $sources = array_values(array_unique(array_filter($sources)));
        if (!$sources) {
            return;
        }

        // tira as fotos que vieram das peças da outra vez (e as da ficha, quando pedido)
        $old = ProductImage::where('product_id', $kit->id)->where('source', 'kit')->get();
        foreach ($old as $img) {
            $disk->delete('products/' . $img->filename);
            $img->delete();
        }
        if ($replaceUploads) {
            ProductImage::where('product_id', $kit->id)->where('source', 'upload')->delete();
        }
        if ($kit->image && str_starts_with((string) $kit->image, 'kit-')) {
            $disk->delete('products/' . $kit->image);
        }

        $copies = [];
        foreach ($sources as $src) {
            if (!$disk->exists('products/' . $src)) {
                continue;
            }
            $ext = pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg';
            $name = 'kit-' . Str::lower(Str::random(24)) . '.' . $ext;
            if ($disk->copy('products/' . $src, 'products/' . $name)) {
                $copies[] = $name;
            }
        }
        if (!$copies) {
            return;
        }
        $kit->image = array_shift($copies);
        $kit->saveQuietly();
        $sort = (int) ProductImage::where('product_id', $kit->id)->max('sort_order');
        foreach ($copies as $name) {
            ProductImage::create([
                'product_id' => $kit->id,
                'filename' => $name,
                'alt_text' => $kit->name,
                'source' => 'kit',
                'sort_order' => ++$sort,
            ]);
        }
    }
}
