<?php

namespace App\Services\Products;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

/**
 * Lê o "Extrato de Pedido" (Boticário/Eudora) e devolve as linhas de produto.
 *
 * Usado pelo upload de produtos e pela recuperação do preço de tabela a partir
 * dos PDFs já enviados. A linha de valores do extrato tem, nesta ordem:
 * R$ TABELA | R$ PRATICADO | R$ REVENDA | R$ A PAGAR | R$ LUCRO | Operação.
 * Todos os valores são o total da linha (quantidade × unitário).
 */
class OrderPdfParser
{
    /**
     * Lê o arquivo e devolve os produtos com valores já por unidade.
     *
     * @return array<int, array{product_code:string, name:string, stock_quantity:int, price:float, price_sale:float, price_original:float, operation:string}>
     */
    public function parseFile(string $path): array
    {
        $text = (new Parser())->parseFile($path)->getText();
        $products = $this->separateProducts($this->filterText($text))['products'];

        return array_map(fn ($p) => $this->toUnitPrices($p), $products);
    }

    /**
     * Converte os valores totais da linha em valores por unidade.
     */
    public function toUnitPrices(array $product): array
    {
        $qty = (int) ($product['stock_quantity'] ?? 0);
        $product['price_original'] = (float) ($product['price_resell'] ?? 0);

        if ($qty > 0) {
            foreach (['price', 'price_sale', 'price_original'] as $key) {
                $product[$key] = round(((float) ($product[$key] ?? 0)) / $qty, 2);
            }
        }

        return $product;
    }

    /**
     * Acha onde a tabela de produtos começa, tolerando variações de acento e
     * caixa no cabeçalho. Se o cabeçalho não existir (layout novo), cai para a
     * primeira linha que tem cara de produto: "12.345  3  NOME DO PRODUTO".
     *
     * @return int|null deslocamento em bytes logo após o cabeçalho, ou null
     */
    public function encontrarInicioDaTabela(string $text): ?int
    {
        // OPERACAO / OPERAÇÃO / OPERAÇAO / OPERACÃO, em qualquer caixa
        $padraoCabecalho = '/OPERA[CÇ][ÃA]O/iu';

        if (preg_match($padraoCabecalho, $text, $m, PREG_OFFSET_CAPTURE)) {
            Log::info('Cabeçalho da tabela encontrado: "' . $m[0][0] . '"');
            return $m[0][1] + strlen($m[0][0]);
        }

        // Sem cabeçalho: procura a primeira linha de produto (código 12.345).
        if (preg_match('/^\s*\d{2,5}\.\d{3}\s+\d+\s+\S/mu', $text, $m, PREG_OFFSET_CAPTURE)) {
            Log::warning('Cabeçalho ausente; começando na primeira linha de produto encontrada.');
            return $m[0][1];
        }

        return null;
    }

    public function filterText($text)
    {
        // A tabela de produtos começa depois do cabeçalho "OPERAÇÃO".
        // A busca precisa ser tolerante: dependendo de como o PDF foi gerado, a
        // palavra sai sem acento, com caixa diferente ou com o acento decomposto
        // (C+cedilha em vez de Ç). Um `strpos` exato falhava nesses PDFs e o
        // texto filtrado voltava vazio — o arquivo era rejeitado sem explicação.
        $startPos = $this->encontrarInicioDaTabela($text);

        if ($startPos === null) {
            Log::warning('Cabeçalho da tabela de produtos não encontrado no PDF.');
            return '';
        }

        // O marcador final é a linha que começa com "TOTAL"
        // Usamos regex com modo multiline (m) para encontrar a linha que começa com TOTAL
        $endPos = false;
        if (preg_match('/^\s*TOTAL/m', $text, $matches, PREG_OFFSET_CAPTURE, $startPos)) {
            $endPos = $matches[0][1];
            Log::info('Marcador final "TOTAL" encontrado na posição: ' . $endPos);
        }

        // Fallbacks, caso "TOTAL" não seja encontrado
        if ($endPos === false) {
            $endPos = strpos($text, 'PRODUTOS NÃO DISPONÍVEIS', $startPos);
            if($endPos) Log::info('Usando fallback "PRODUTOS NÃO DISPONÍVEIS"');
        }
        if ($endPos === false) {
            $endPos = strpos($text, 'AJUSTES', $startPos);
            if($endPos) Log::info('Usando fallback "AJUSTES"');
        }
        if ($endPos === false) {
            $endPos = strpos($text, 'PLANO DE PAGAMENTO', $startPos);
            if($endPos) Log::info('Usando fallback "PLANO DE PAGAMENTO"');
        }

        // $startPos já aponta para logo depois do cabeçalho
        $textStart = $startPos;

        $filteredText = '';
        if ($endPos !== false) {
            $filteredText = substr($text, $textStart, $endPos - $textStart);
        } else {
            // Se NENHUM marcador final for encontrado, pega tudo do início até o fim do texto.
            Log::warning('Nenhum marcador final foi encontrado. Usando o resto do texto a partir de "OPERAÇÃO".');
            $filteredText = substr($text, $textStart);
        }

        Log::info('Texto filtrado (com quebras de linha) - caracteres: ' . strlen($filteredText));
        return trim($filteredText);
    }

    public function separateProducts($text)
    {
        $rawLines = explode("\n", $text);
        $lines = [];
        foreach ($rawLines as $l) {
            $l = trim($l);
            if ($l !== '') $lines[] = $l;
        }

        $allProducts = [];
        $currentProduct = null;
        // O código do produto aparece em dois formatos conforme a versão do
        // extrato: com ponto de milhar ("53.506") nos PDFs antigos e sem ponto
        // ("53506") nos novos. A regex antiga exigia o ponto, então nenhuma
        // linha do extrato novo era reconhecida como produto.
        $productRegex = '/^(\d{2,6}(?:\.\d{3})?)\s+(\d+)\s+(.*)/';
        // A operação pode ser composta: além de "Venda" e "Brinde", o extrato
        // traz "Doação Brinde" (brinde garantido). A alternação simples parava
        // na primeira palavra e a linha inteira deixava de casar, então esses
        // itens sumiam do resultado sem aviso.
        $palavraOperacao = '(?:Venda|Brinde|Doa[çc][ãa]o|Bonifica[çc][ãa]o|Troca|Garantido)';
        $valuesRegex = '/([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+('
            . $palavraOperacao . '(?:\s+' . $palavraOperacao . ')*)$/u';

        $finalizeValues = function (&$currentProduct, array $valueMatches, array $lines, int $i): int {
            $currentProduct['values'] = array_slice($valueMatches, 1, 5);
            $operation = end($valueMatches);

            if ($operation === 'Doação') {
                $next = $lines[$i + 1] ?? '';
                $nextNext = $lines[$i + 2] ?? '';
                if (stripos($next, 'FIDELIDADEVD') !== false) {
                    $operation = 'Doação FIDELIDADEVD';
                    $i++;
                } elseif (stripos($nextNext, 'FIDELIDADEVD') !== false) {
                    $operation = 'Doação FIDELIDADEVD';
                    $i += 2;
                }
            }

            $currentProduct['operation'] = $operation;
            return $i;
        };

        $total = count($lines);
        for ($i = 0; $i < $total; $i++) {
            $line = $lines[$i];

            $isNewProductLine = preg_match($productRegex, $line, $matches);

            if ($isNewProductLine) {
                if ($currentProduct) {
                    $allProducts[] = $currentProduct;
                }

                $potentialName = trim($matches[3]);
                $currentProduct = [
                    'product_code' => $matches[1],
                    'stock_quantity' => (int)$matches[2],
                    'name' => $potentialName,
                    'values' => [],
                    'operation' => ''
                ];

                if (preg_match($valuesRegex, $potentialName, $valueMatches)) {
                    $currentProduct['name'] = trim(preg_replace($valuesRegex, '', $potentialName));
                    $i = $finalizeValues($currentProduct, $valueMatches, $lines, $i);

                    $allProducts[] = $currentProduct;
                    $currentProduct = null;
                }

            } elseif ($currentProduct) {
                if (preg_match($valuesRegex, $line, $valueMatches)) {
                    $i = $finalizeValues($currentProduct, $valueMatches, $lines, $i);

                    $allProducts[] = $currentProduct;
                    $currentProduct = null;
                } else {
                    if (stripos($line, 'FIDELIDADEVD') !== false) {
                        continue;
                    }
                    $currentProduct['name'] .= ' ' . $line;
                }
            }
        }

        // Adiciona o último produto se ele existir (caso o arquivo termine)
        if ($currentProduct) {
            $allProducts[] = $currentProduct;
        }

        Log::info('Regex finalizou ' . count($allProducts) . ' produtos completos.');

        $formattedProducts = [];
        foreach ($allProducts as $p) {
            if (!empty($p['operation']) && (count($p['values']) === 5 || $p['operation'] === 'Brinde')) {
                 // Para brindes, os valores podem não estar presentes, mas ainda assim são produtos válidos.
                $isBrindeSemValor = ($p['operation'] === 'Brinde' && empty($p['values']));

                $formattedProducts[] = [
                    'product_code' => $p['product_code'],
                    'name' => preg_replace('/\s+/', ' ', trim($p['name'])),
                    'stock_quantity' => $p['stock_quantity'],
                    'price_resell' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][0]),
                    'price_to_pay' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][1]),
                    'price_sale' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][2]),
                    'price' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][3]),
                    'profit' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][4]),
                    'operation' => $p['operation'],
                    'category_id' => 1,
                    'user_id' => Auth::id(),
                    'image' => 'product-placeholder.png',
                    'status' => 'ativo',
                ];
            }
        }

        return ['products' => $formattedProducts];
    }

    /**
     * Converte "1.449,50" → 1449.50 e "239,80" → 239.80.
     * Antes só trocava a vírgula por ponto, e "1.449,50" virava 1.449.
     */
    public function formatPrice($price): float
    {
        $price = str_replace(' ', '', trim((string) $price));

        if (str_contains($price, ',')) {
            $price = str_replace(['.', ','], ['', '.'], $price);
        }

        return (float) $price;
    }
}
