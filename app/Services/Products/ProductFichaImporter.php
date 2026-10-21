<?php

namespace App\Services\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importar ficha (Produtos › Importar ficha): um produto completo a partir da
 * ficha que uma IA monta com o questionário da Móveis Stylo (bloco JSON) e as
 * fotos. Usado à mão e pelo robô de cadastro (robo-stylo).
 *
 * plan() só confere e monta a prévia; import() grava.
 */
class ProductFichaImporter
{
    public const PHOTO_EXTS = ['jpg', 'jpeg', 'png', 'webp'];

    /** Tira o JSON da ficha: o primeiro bloco ```json``` ou o texto inteiro. */
    public function parse(string $text): array
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $json = null;
        if (preg_match_all('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            // A ficha pode trazer o modelo e a resposta: vale o último bloco
            $json = end($m[1]);
        } elseif (preg_match('/\{.*\}/s', $text, $m)) {
            $json = $m[0];
        }
        if ($json === null) {
            throw new \RuntimeException('Não achei o bloco JSON na ficha.');
        }
        // Vírgula sobrando antes de } ou ] (erro comum de IA)
        $json = preg_replace('/,\s*([}\]])/', '$1', $json);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('O JSON da ficha tem erro: ' . json_last_error_msg() . '.');
        }

        return $data;
    }

    /**
     * Confere a ficha e as fotos enviadas.
     *
     * @param array<int, string> $photoNames nomes dos arquivos de foto enviados
     * @return array{product: array, rows: array<int, array>, errors: array<int, string>, warnings: array<int, string>, missing_photos: array<int, string>, unused_photos: array<int, string>}
     */
    public function plan(int $userId, array $f, array $photoNames): array
    {
        $errors = [];
        $warnings = [];
        $nome = $this->text($f['nome'] ?? '', 255);
        if ($nome === '') {
            $errors[] = 'Falta o "nome" do produto.';
        }

        $vars = array_values(array_filter((array) ($f['variacoes'] ?? []), 'is_array'));
        if (!$vars) {
            // Ficha sem lista de variações: usa os campos soltos como um produto só
            $vars = [[
                'valor' => null, 'codigo' => $f['codigo'] ?? null, 'custo' => $f['custo'] ?? null,
                'venda' => $f['venda'] ?? null, 'preco_tabela' => $f['preco_tabela'] ?? null,
                'estoque' => $f['estoque'] ?? 0, 'embalagem' => $f['embalagem'] ?? null,
                'medidas_peca_cm' => $f['medidas_peca_cm'] ?? null, 'fotos' => $f['fotos'] ?? [],
            ]];
        }
        if (count($vars) > 40) {
            $errors[] = 'A ficha tem ' . count($vars) . ' variações; o máximo é 40.';
            $vars = array_slice($vars, 0, 40);
        }

        $costItems = collect((array) ($f['ficha_de_custo'] ?? []))
            ->filter(fn ($i) => is_array($i))
            ->map(fn ($i) => [
                'n' => $this->text($i['item'] ?? $i['n'] ?? '', 80),
                'q' => ($q = $this->num($i['quantidade'] ?? $i['q'] ?? 1)) > 0 ? $q : 1,
                'v' => round($this->num($i['valor'] ?? $i['v'] ?? 0), 2),
            ])
            ->filter(fn ($i) => $i['n'] !== '' || $i['v'] > 0)
            ->values()->all();
        $costTotal = Product::costItemsTotal($costItems);

        $madeToOrder = $this->bool($f['sob_encomenda'] ?? false);
        $days = (int) $this->num($f['prazo_dias'] ?? 0);
        if ($madeToOrder && $days <= 0) {
            $warnings[] = 'Sob encomenda sem "prazo_dias": o produto entra sem prazo.';
        }

        $attribute = $this->text($f['variacao_por'] ?? '', 60);
        $multi = count($vars) > 1 || trim((string) ($vars[0]['valor'] ?? '')) !== '';
        if ($multi && $attribute === '') {
            $attribute = 'Tamanho';
        }

        $baseCode = $this->text($f['codigo'] ?? '', 90);
        $existing = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)
            ->whereNotNull('product_code')->get(['id', 'product_code', 'name', 'parent_id'])
            ->keyBy(fn ($p) => mb_strtolower(trim($p->product_code)));

        $photoIndex = [];
        foreach ($photoNames as $n) {
            $photoIndex[$this->photoKey($n)] = $n;
            $photoIndex[$this->photoKey(pathinfo($n, PATHINFO_FILENAME))] ??= $n;
        }
        $usedPhotos = [];
        $missingPhotos = [];

        $rows = [];
        $seen = [];
        foreach ($vars as $i => $v) {
            $value = $multi ? $this->text($v['valor'] ?? '', 120) : '';
            if ($multi && $value === '') {
                $value = 'Padrão';
            }
            $code = $this->text($v['codigo'] ?? '', 100);
            if ($code === '') {
                $code = $baseCode !== '' ? ($multi ? $baseCode . '-' . Str::upper(Str::slug($value)) : $baseCode) : '';
            }
            $cost = $v['custo'] ?? null;
            $cost = $cost === null || $cost === '' ? $costTotal : $this->num($cost);
            $box = (array) ($v['embalagem'] ?? $f['embalagem'] ?? []);
            $piece = (array) ($v['medidas_peca_cm'] ?? $f['medidas_peca_cm'] ?? []);

            $photos = [];
            foreach ((array) ($v['fotos'] ?? []) as $p) {
                $p = trim((string) $p);
                if ($p === '') {
                    continue;
                }
                $hit = $photoIndex[$this->photoKey($p)] ?? $photoIndex[$this->photoKey(pathinfo($p, PATHINFO_FILENAME))] ?? null;
                if ($hit) {
                    $photos[] = $hit;
                    $usedPhotos[$hit] = true;
                } else {
                    $missingPhotos[] = $p;
                }
            }

            $row = [
                'value' => $value,
                'name' => $multi && mb_stripos($nome, $value) === false ? $nome . ' ' . $value : $nome,
                'code' => $code,
                'price' => round($cost, 2),
                'price_sale' => round($this->num($v['venda'] ?? 0), 2),
                'price_original' => ($t = round($this->num($v['preco_tabela'] ?? 0), 2)) > 0 ? $t : null,
                'stock' => max(0, (int) round($this->num($v['estoque'] ?? 0))),
                'weight_grams' => ($kg = $this->num($box['peso_kg'] ?? 0)) > 0 ? (int) round($kg * 1000) : null,
                'length_cm' => ($n = $this->num($box['comprimento_cm'] ?? 0)) > 0 ? round($n, 2) : null,
                'width_cm' => ($n = $this->num($box['largura_cm'] ?? 0)) > 0 ? round($n, 2) : null,
                'height_cm' => ($n = $this->num($box['altura_cm'] ?? 0)) > 0 ? round($n, 2) : null,
                'piece' => array_filter([
                    'Altura' => $this->num($piece['altura'] ?? 0),
                    'Largura' => $this->num($piece['largura'] ?? 0),
                    'Profundidade' => $this->num($piece['profundidade'] ?? 0),
                    'Diâmetro' => $this->num($piece['diametro'] ?? 0),
                ], fn ($n) => $n > 0),
                'photos' => $photos,
                'status' => 'ok',
                'note' => '',
                'existing_id' => null,
            ];

            $key = mb_strtolower($code);
            if ($row['price_sale'] <= 0) {
                [$row['status'], $row['note']] = ['error', 'Sem preço de venda'];
            } elseif ($code !== '' && isset($seen[$key])) {
                [$row['status'], $row['note']] = ['error', 'Código repetido na ficha'];
            } elseif ($code !== '' && $existing->has($key)) {
                [$row['status'], $row['note']] = ['skip', 'Já cadastrado'];
                $row['existing_id'] = $existing[$key]->id;
            }
            if ($code !== '') {
                $seen[$key] = true;
            }
            $rows[] = $row;
        }

        // Ficha sem nomes de foto: as fotos enviadas vão para o primeiro item, na ordem do nome
        if ($rows && $photoNames && !collect($rows)->contains(fn ($r) => $r['photos'])) {
            $sorted = $photoNames;
            natcasesort($sorted);
            $rows[0]['photos'] = array_values($sorted);
            $usedPhotos = array_fill_keys($sorted, true);
        }

        if ($rows && collect($rows)->every(fn ($r) => $r['status'] === 'skip')) {
            $warnings[] = 'Todos os códigos desta ficha já estão cadastrados: nada novo para importar.';
        }
        if (!collect($rows)->contains(fn ($r) => $r['photos'])) {
            $warnings[] = 'Nenhuma foto da ficha foi enviada: o produto entra sem foto.';
        }

        $specs = [];
        foreach ([
            'Material' => $f['material'] ?? null,
            'Acabamento' => $f['acabamento'] ?? null,
            'Montagem' => $f['montagem'] ?? null,
            'O que vem na caixa' => $f['o_que_vem_na_caixa'] ?? null,
            'Carga máxima' => ($c = $this->num($f['carga_maxima_kg'] ?? 0)) > 0 ? $this->fmt($c) . ' kg' : null,
        ] as $n => $val) {
            if (($val = $this->text($val ?? '', 255)) !== '') {
                $specs[] = ['n' => $n, 'v' => $val];
            }
        }
        foreach ((array) ($f['ficha_tecnica'] ?? []) as $s) {
            if (!is_array($s)) {
                continue;
            }
            $n = $this->text($s['nome'] ?? $s['n'] ?? '', 80);
            $val = $this->text($s['valor'] ?? $s['v'] ?? '', 255);
            if ($n !== '' && $val !== '' && !collect($specs)->contains(fn ($x) => mb_strtolower($x['n']) === mb_strtolower($n))) {
                $specs[] = ['n' => $n, 'v' => $val];
            }
        }

        $ml = (array) ($f['mercado_livre'] ?? []);
        $condition = Str::lower(Str::ascii((string) ($f['condicao'] ?? 'novo')));

        return [
            'product' => [
                'name' => $nome,
                'attribute' => $multi ? $attribute : '',
                'category' => $this->text($f['categoria'] ?? '', 100),
                'brand' => $this->text($f['marca'] ?? '', 100),
                'model' => $this->text($f['modelo'] ?? '', 100),
                'description' => $this->description($f),
                'barcode' => preg_replace('/\D/', '', (string) ($f['codigo_barras'] ?? '')) ?: null,
                'warranty_months' => ($w = (int) $this->num($f['garantia_meses'] ?? 3)) > 0 ? min($w, 120) : 3,
                'condition' => str_starts_with($condition, 'usad') ? 'used' : 'new',
                'made_to_order' => $madeToOrder,
                'production_days' => $madeToOrder && $days > 0 ? min($days, 365) : null,
                'specs' => $specs,
                'cost_items' => $costItems,
                'ml_title' => $this->text($ml['titulo_anuncio'] ?? '', 120),
                'ml_type' => $this->text($ml['tipo_anuncio'] ?? '', 20),
                'ml_link' => $this->text($ml['link_referencia'] ?? '', 500),
                'ml_quantity' => (int) $this->num($ml['quantidade_anuncio'] ?? 0) ?: null,
            ],
            'rows' => $rows,
            'errors' => $errors,
            'warnings' => $warnings,
            'missing_photos' => array_values(array_unique($missingPhotos)),
            'unused_photos' => array_values(array_diff($photoNames, array_keys($usedPhotos))),
        ];
    }

    /**
     * Grava o plano. $files = nome do arquivo → arquivo enviado.
     *
     * @param array<string, UploadedFile> $files
     * @return array<int, array{id: int, name: string, code: string, action: string}>
     */
    public function import(int $userId, array $plan, array $files, VariationService $variations): array
    {
        $p = $plan['product'];
        $rows = $plan['rows'];
        $result = [];
        $stored = [];

        $store = function (string $name) use ($files, &$stored): ?string {
            if (!isset($files[$name])) {
                return null;
            }

            return $stored[$name] ??= basename($files[$name]->store('products', 'public'));
        };

        DB::transaction(function () use ($userId, $p, $rows, $variations, $store, &$result) {
            $categoryId = $this->category($userId, $p['category'] !== '' ? $p['category'] : 'Geral');
            $multi = $p['attribute'] !== '';

            // Principal do grupo: a primeira linha nova, ou um produto já cadastrado da ficha
            $parent = null;
            if ($multi) {
                foreach ($rows as $r) {
                    if ($r['status'] === 'skip' && $r['existing_id']) {
                        $found = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)->find($r['existing_id']);
                        if ($found) {
                            $parent = $found->parent_id ? ($found->parent ?? $found) : $found;
                            break;
                        }
                    }
                }
            }

            // Fotos da primeira linha servem para as variações que não têm foto própria
            $fallbackPhotos = collect($rows)->first(fn ($r) => $r['photos'])['photos'] ?? [];

            foreach ($rows as $r) {
                if ($r['status'] !== 'ok') {
                    continue;
                }
                $photos = $r['photos'] ?: $fallbackPhotos;
                $main = $photos ? $store($photos[0]) : null;

                $specs = $p['specs'];
                foreach ($r['piece'] as $n => $cm) {
                    $specs[] = ['n' => $n, 'v' => $this->fmt($cm) . ' cm'];
                }

                $isNewParent = $multi && $parent === null;
                $data = array_filter([
                    'user_id' => $userId,
                    'name' => $isNewParent ? $p['name'] : $r['name'],
                    'product_code' => $r['code'] !== '' ? $r['code'] : $variations->generateCode(),
                    'price' => $r['price'],
                    'price_sale' => $r['price_sale'],
                    'price_original' => $r['price_original'],
                    'stock_quantity' => $r['stock'],
                    'category_id' => $categoryId,
                    'image' => $main ?: 'product-placeholder.png',
                    'tipo' => 'simples',
                    'status' => 'ativo',
                    'custos_adicionais' => 0,
                    'description' => $p['description'] ?: null,
                    'brand' => $p['brand'] ?: null,
                    'model' => $p['model'] ?: null,
                    'barcode' => !$multi ? $p['barcode'] : null,
                    'warranty_months' => $p['warranty_months'],
                    'condition' => $p['condition'],
                    'weight_grams' => $r['weight_grams'],
                    'length_cm' => $r['length_cm'],
                    'width_cm' => $r['width_cm'],
                    'height_cm' => $r['height_cm'],
                    'made_to_order' => $p['made_to_order'],
                    'production_days' => $p['production_days'],
                    'specs' => $specs ?: null,
                    'cost_items' => $p['cost_items'] ?: null,
                ], fn ($v) => $v !== null);

                $product = Product::create($data);

                if ($isNewParent) {
                    $product->update([
                        'is_variation_parent' => true,
                        'variation_attribute' => $p['attribute'],
                        'variation_value' => $r['value'],
                    ]);
                    $parent = $product;
                    $action = 'principal';
                } elseif ($multi) {
                    $variations->attach($parent, $product, $parent->variation_attribute ?: $p['attribute'], $r['value']);
                    $action = 'variação';
                } else {
                    $action = 'produto';
                }

                $sort = 0;
                foreach (array_slice($photos, 1) as $name) {
                    if ($file = $store($name)) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'filename' => $file,
                            'alt_text' => $product->name,
                            'source' => 'upload',
                            'sort_order' => ++$sort,
                        ]);
                    }
                }

                $result[] = ['id' => $product->id, 'name' => $product->name, 'code' => $product->product_code, 'action' => $action];
            }
        });

        return $result;
    }

    private function description(array $f): string
    {
        $text = trim((string) ($f['descricao'] ?? ''));
        $uso = trim((string) ($f['uso'] ?? ''));
        if ($uso !== '' && mb_stripos($text, mb_substr($uso, 0, 30)) === false) {
            $text = $uso . ($text !== '' ? "\n\n" . $text : '');
        }

        return mb_substr($text, 0, 5000);
    }

    private function category(int $userId, string $name): int
    {
        $cat = Category::where('user_id', $userId)->where('type', 'product')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        return (int) ($cat ?? Category::create([
            'user_id' => $userId,
            'name' => mb_substr($name, 0, 100),
            'type' => 'product',
            'is_active' => 1,
            'icone' => 'bi-tag',
            'hexcolor_category' => '#6366f1',
        ]))->id_category;
    }

    private function photoKey(string $name): string
    {
        return mb_strtolower(trim(str_replace('\\', '/', $name) === $name ? $name : basename(str_replace('\\', '/', $name))));
    }

    private function text($v, int $max): string
    {
        if (is_array($v)) {
            $v = implode(', ', array_filter(array_map('strval', $v)));
        }

        return mb_substr(trim(preg_replace('/\s+/', ' ', (string) $v)), 0, $max);
    }

    /** 79.9, "79,90", "R$ 1.234,56" → número */
    private function num($v): float
    {
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }
        $v = preg_replace('/[^\d,.\-]/', '', (string) $v);
        if ($v === '' || $v === '-') {
            return 0.0;
        }
        if (str_contains($v, ',')) {
            $v = str_replace(',', '.', str_replace('.', '', $v));
        }

        return (float) $v;
    }

    private function bool($v): bool
    {
        if (is_bool($v)) {
            return $v;
        }

        return in_array(Str::lower(Str::ascii(trim((string) $v))), ['sim', 's', 'x', 'yes', 'y', '1', 'true', 'verdadeiro'], true);
    }

    private function fmt(float $n): string
    {
        return str_replace('.', ',', rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.'));
    }
}
