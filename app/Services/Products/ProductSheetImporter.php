<?php

namespace App\Services\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importar produtos por planilha (Produtos › Importar planilha).
 *
 * Uma linha por produto. Linhas com o mesmo "nome" e uma "variação" preenchida
 * viram um grupo: a primeira é o produto principal e as outras, as variações
 * (ex.: Suporte para vaso 30 cm / 40 cm / 50 cm). Código já cadastrado é pulado.
 */
class ProductSheetImporter
{
    /** Coluna da planilha → campo. Os nomes aceitam acento, maiúscula e variações. */
    public const COLUMNS = [
        'nome' => ['nome', 'produto', 'name', 'titulo'],
        'codigo' => ['codigo', 'cod', 'sku', 'referencia', 'ref', 'code'],
        'variacao' => ['variacao', 'tamanho', 'valor da variacao', 'opcao', 'medida'],
        'tipo_variacao' => ['tipo da variacao', 'tipo variacao', 'tipo_variacao', 'atributo', 'variacao por'],
        'custo' => ['custo', 'preco de custo', 'cost'],
        'venda' => ['venda', 'preco', 'preco de venda', 'valor', 'price'],
        'estoque' => ['estoque', 'quantidade', 'qtd', 'qtde', 'stock'],
        'categoria' => ['categoria', 'category'],
        'marca' => ['marca', 'brand'],
        'modelo' => ['modelo', 'model'],
        'descricao' => ['descricao', 'description'],
        'codigo_barras' => ['codigo de barras', 'ean', 'gtin', 'barcode'],
        'peso_kg' => ['peso', 'peso kg', 'peso (kg)', 'peso_kg'],
        'comprimento_cm' => ['comprimento', 'comprimento cm', 'comprimento (cm)', 'comprimento_cm'],
        'largura_cm' => ['largura', 'largura cm', 'largura (cm)', 'largura_cm'],
        'altura_cm' => ['altura', 'altura cm', 'altura (cm)', 'altura_cm'],
        'sob_encomenda' => ['sob encomenda', 'encomenda', 'sob_encomenda'],
        'prazo_dias' => ['prazo', 'prazo dias', 'prazo (dias)', 'prazo_dias', 'dias de producao'],
    ];

    public const TEMPLATE_HEADER = ['nome', 'variação', 'tipo da variação', 'código', 'custo', 'venda', 'estoque', 'categoria', 'marca', 'descrição', 'código de barras', 'peso (kg)', 'comprimento (cm)', 'largura (cm)', 'altura (cm)', 'sob encomenda', 'prazo (dias)'];

    public const TEMPLATE_ROWS = [
        ['Suporte para Vaso Redondo com Rodinhas', '30 cm', 'Tamanho', 'SV-30', '22,00', '48,90', '10', 'Suportes de vaso', 'Móveis Stylo', 'Suporte de madeira com 4 rodízios', '', '1,2', '32', '32', '10', 'não', ''],
        ['Suporte para Vaso Redondo com Rodinhas', '40 cm', 'Tamanho', 'SV-40', '30,00', '64,90', '6', 'Suportes de vaso', 'Móveis Stylo', 'Suporte de madeira com 4 rodízios', '', '1,8', '42', '42', '10', 'não', ''],
        ['Mão Francesa Colonial', '30 cm · Preto', 'Tamanho e cor', 'MF-30P', '18,00', '79,00', '0', 'Mão francesa', 'Móveis Stylo', 'Ferro com pintura eletrostática', '', '0,9', '35', '25', '5', 'sim', '7'],
    ];

    /**
     * Lê as linhas e monta o que vai acontecer com cada uma, sem gravar nada.
     *
     * @param array<int, array<int, string>> $rows primeira linha = cabeçalho
     * @return array{items: array<int, array>, missing: array<int, string>, ignored: array<int, string>}
     */
    public function plan(int $userId, array $rows): array
    {
        if (count($rows) < 2) {
            return ['items' => [], 'missing' => ['nome', 'venda'], 'ignored' => []];
        }

        [$map, $ignored] = $this->mapHeader(array_shift($rows));
        $missing = array_values(array_diff(['nome', 'venda'], array_keys($map)));
        if ($missing) {
            return ['items' => [], 'missing' => $missing, 'ignored' => $ignored];
        }

        $existingCodes = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)
            ->pluck('product_code')->filter()->map(fn ($c) => mb_strtolower(trim($c)))->flip();

        $items = [];
        $seenCodes = [];
        $familyFirst = [];
        foreach ($rows as $i => $row) {
            $get = fn (string $f) => isset($map[$f]) ? trim((string) ($row[$map[$f]] ?? '')) : '';
            $base = preg_replace('/\s+/', ' ', $get('nome'));
            $value = preg_replace('/\s+/', ' ', $get('variacao'));
            $code = $get('codigo');
            $item = [
                'line' => $i + 2,
                'base' => $base,
                'value' => $value,
                'attribute' => $get('tipo_variacao') ?: ($value !== '' ? 'Tamanho' : ''),
                'name' => $value !== '' && mb_stripos($base, $value) === false ? $base . ' ' . $value : $base,
                'code' => $code,
                'price' => $this->money($get('custo')),
                'price_sale' => $this->money($get('venda')),
                'stock' => max(0, (int) round($this->number($get('estoque')))),
                'category' => $get('categoria'),
                'brand' => $get('marca'),
                'model' => $get('modelo'),
                'description' => $get('descricao'),
                'barcode' => preg_replace('/\D/', '', $get('codigo_barras')),
                'weight_grams' => ($kg = $this->number($get('peso_kg'))) > 0 ? (int) round($kg * 1000) : null,
                'length_cm' => $this->number($get('comprimento_cm')) ?: null,
                'width_cm' => $this->number($get('largura_cm')) ?: null,
                'height_cm' => $this->number($get('altura_cm')) ?: null,
                'made_to_order' => $this->yes($get('sob_encomenda')),
                'production_days' => (int) $this->number($get('prazo_dias')) ?: null,
                'status' => 'ok',
                'note' => '',
                'family' => null,
            ];

            $codeKey = mb_strtolower($code);
            if ($base === '') {
                [$item['status'], $item['note']] = ['error', 'Sem nome'];
            } elseif ($item['price_sale'] <= 0) {
                [$item['status'], $item['note']] = ['error', 'Sem preço de venda'];
            } elseif ($code !== '' && isset($existingCodes[$codeKey])) {
                [$item['status'], $item['note']] = ['skip', 'Código já cadastrado'];
            } elseif ($code !== '' && isset($seenCodes[$codeKey])) {
                [$item['status'], $item['note']] = ['error', 'Código repetido na linha ' . $seenCodes[$codeKey]];
            } elseif (strlen($item['barcode']) > 15) {
                [$item['status'], $item['note']] = ['error', 'Código de barras longo demais'];
            }

            if ($item['status'] === 'ok') {
                if ($code !== '') {
                    $seenCodes[$codeKey] = $item['line'];
                }
                if ($value !== '') {
                    $key = mb_strtolower($base);
                    if (isset($familyFirst[$key])) {
                        $item['family'] = $familyFirst[$key];
                        $item['note'] = 'Variação de "' . $base . '"';
                    } else {
                        $familyFirst[$key] = count($items);
                        $item['note'] = 'Principal do grupo';
                    }
                }
            }
            $items[] = $item;
        }

        // Grupo de uma linha só: produto normal (sem virar variação de nada)
        foreach ($items as $k => $it) {
            if ($it['status'] === 'ok' && $it['note'] === 'Principal do grupo'
                && !collect($items)->contains(fn ($o) => $o['family'] === $k)) {
                $items[$k]['note'] = '';
            }
        }

        return ['items' => $items, 'missing' => [], 'ignored' => $ignored];
    }

    /** Grava as linhas ok do plano. Devolve quantos produtos foram criados. */
    public function import(int $userId, array $items, VariationService $variations): int
    {
        $created = 0;
        $categories = [];

        DB::transaction(function () use ($userId, $items, $variations, &$created, &$categories) {
            $parents = [];
            foreach ($items as $k => $it) {
                if ($it['status'] !== 'ok') {
                    continue;
                }
                $catName = $it['category'] !== '' ? $it['category'] : 'Geral';
                $categories[$catName] ??= $this->category($userId, $catName);

                $data = [
                    'user_id' => $userId,
                    'name' => $it['family'] === null && $it['note'] === 'Principal do grupo' ? $it['base'] : $it['name'],
                    'product_code' => $it['code'] !== '' ? $it['code'] : $variations->generateCode(),
                    'price' => $it['price'],
                    'price_sale' => $it['price_sale'],
                    'stock_quantity' => $it['stock'],
                    'category_id' => $categories[$catName],
                    'image' => 'product-placeholder.png',
                    'tipo' => 'simples',
                    'status' => 'ativo',
                    'custos_adicionais' => 0,
                    'description' => $it['description'] ?: null,
                    'brand' => $it['brand'] ?: null,
                    'model' => $it['model'] ?: null,
                    'barcode' => $it['barcode'] ?: null,
                    'weight_grams' => $it['weight_grams'],
                    'length_cm' => $it['length_cm'],
                    'width_cm' => $it['width_cm'],
                    'height_cm' => $it['height_cm'],
                    'made_to_order' => $it['made_to_order'],
                    'production_days' => $it['made_to_order'] ? $it['production_days'] : null,
                ];

                $product = Product::create(array_filter($data, fn ($v) => $v !== null));
                $created++;

                if ($it['note'] === 'Principal do grupo') {
                    // O principal também é um tamanho: guarda o valor dele já de saída
                    $product->update([
                        'is_variation_parent' => true,
                        'variation_attribute' => $it['attribute'] ?: 'Variação',
                        'variation_value' => $it['value'],
                    ]);
                    $parents[$k] = $product;
                } elseif ($it['family'] !== null && isset($parents[$it['family']])) {
                    $variations->attach($parents[$it['family']], $product, $parents[$it['family']]->variation_attribute, $it['value']);
                }
            }
        });

        return $created;
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

    /** @return array{0: array<string,int>, 1: array<int,string>} */
    private function mapHeader(array $header): array
    {
        $map = [];
        $ignored = [];
        foreach ($header as $idx => $title) {
            $norm = trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', mb_strtolower(Str::ascii((string) $title)))));
            $field = null;
            foreach (self::COLUMNS as $f => $aliases) {
                if (in_array($norm, array_map(fn ($a) => str_replace('_', ' ', $a), $aliases), true)) {
                    $field = $f;
                    break;
                }
            }
            if ($field && !isset($map[$field])) {
                $map[$field] = $idx;
            } elseif (trim((string) $title) !== '') {
                $ignored[] = (string) $title;
            }
        }

        return [$map, $ignored];
    }

    /** "R$ 1.234,56", "1234,56" ou "1234.56" → 1234.56 */
    private function money(string $v): float
    {
        return round($this->number(str_ireplace('r$', '', $v)), 2);
    }

    private function number(string $v): float
    {
        $v = preg_replace('/[^\d,.\-]/', '', $v);
        if ($v === '' || $v === '-') {
            return 0.0;
        }
        if (str_contains($v, ',')) {
            $v = str_replace(',', '.', str_replace('.', '', $v));
        }

        return (float) $v;
    }

    private function yes(string $v): bool
    {
        return in_array(mb_strtolower(Str::ascii(trim($v))), ['sim', 's', 'x', 'yes', 'y', '1', 'verdadeiro', 'true'], true);
    }
}
