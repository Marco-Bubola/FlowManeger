<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
echo "ANÁLISE COMPLETA DO PDF\n";
echo str_repeat('=', 100) . "\n\n";

$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$text = $pdf->getText();

// Contar produtos por tipo de operação
preg_match_all('/(\d{2}\.\d{3})\s+(\d+)\s+(.+?)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+(Venda|Brinde)/m', $text, $allMatches);

echo "PRODUTOS ENCONTRADOS NO PDF:\n";
echo str_repeat('-', 100) . "\n";

$vendaProducts = [];
$brindeProducts = [];

for ($i = 0; $i < count($allMatches[0]); $i++) {
    $operation = $allMatches[9][$i];
    if ($operation === 'Venda') {
        $vendaProducts[] = [
            'code' => $allMatches[1][$i],
            'qty' => $allMatches[2][$i],
            'name' => trim($allMatches[3][$i]),
        ];
    } else {
        $brindeProducts[] = [
            'code' => $allMatches[1][$i],
            'qty' => $allMatches[2][$i],
            'name' => trim($allMatches[3][$i]),
        ];
    }
}

echo "\nPRODUTOS COM OPERAÇÃO 'VENDA': " . count($vendaProducts) . "\n";
foreach ($vendaProducts as $idx => $p) {
    echo sprintf("%2d. [%s] Qtd: %2s - %s\n", $idx + 1, $p['code'], $p['qty'], substr($p['name'], 0, 70));
}

echo "\n\nPRODUTOS COM OPERAÇÃO 'BRINDE': " . count($brindeProducts) . "\n";
foreach ($brindeProducts as $idx => $p) {
    echo sprintf("%2d. [%s] Qtd: %2s - %s\n", $idx + 1, $p['code'], $p['qty'], substr($p['name'], 0, 70));
}

echo "\n" . str_repeat('=', 100) . "\n";
echo "TOTAL GERAL: " . (count($vendaProducts) + count($brindeProducts)) . " produtos\n";
echo "ESPERADO (conforme PDF): 40 produtos\n";

$total = count($vendaProducts) + count($brindeProducts);
if ($total == 40) {
    echo "✓ SUCESSO: Todos os 40 produtos foram capturados!\n";
} else {
    echo "✗ PROBLEMA: Faltam " . (40 - $total) . " produtos\n";

    // Procurar códigos que podem ter sido perdidos
    echo "\nProcurando códigos não capturados...\n";
    preg_match_all('/(\d{2}\.\d{3})\s+(\d+)\s+/m', $text, $allCodes);

    $capturedCodes = array_merge(
        array_column($vendaProducts, 'code'),
        array_column($brindeProducts, 'code')
    );

    $lostCodes = [];
    foreach ($allCodes[1] as $code) {
        if (!in_array($code, $capturedCodes) && !in_array($code, $lostCodes)) {
            $lostCodes[] = $code;
        }
    }

    if (!empty($lostCodes)) {
        echo "Códigos encontrados mas não extraídos: " . implode(', ', $lostCodes) . "\n";
    }
}

// Verificar valores específicos de alguns produtos (comparando com a imagem)
echo "\n" . str_repeat('=', 100) . "\n";
echo "VALIDAÇÃO DE VALORES (comparando com PDF):\n";
echo str_repeat('-', 100) . "\n";

$testCases = [
    ['40.121', '2', 'price' => '252.90', 'price_sale' => '459.80'],
    ['48.310', '2', 'price' => '181.80', 'price_sale' => '319.80'],
    ['58.308', '2', 'price' => '44.98', 'price_sale' => '59.98'],
    ['89.446', '2', 'price' => '152.86', 'price_sale' => '203.80'],
];

// Extrair com todos os valores
$pattern = '/(\d{2}\.\d{3})\s+(\d+)\s+(.+?)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+(Venda|Brinde)/m';
preg_match_all($pattern, $text, $fullMatches);

foreach ($testCases as $test) {
    $code = $test[0];
    $qty = $test[1];

    // Encontrar o produto
    for ($i = 0; $i < count($fullMatches[1]); $i++) {
        if ($fullMatches[1][$i] === $code && $fullMatches[2][$i] === $qty) {
            $priceAPagar = str_replace(',', '.', $fullMatches[7][$i]);
            $priceRevenda = str_replace(',', '.', $fullMatches[6][$i]);

            echo "Produto $code (Qtd: $qty):\n";
            echo "  R\$ A PAGAR: $priceAPagar (esperado: {$test['price']})\n";
            echo "  R\$ REVENDA: $priceRevenda (esperado: {$test['price_sale']})\n";

            $priceMatch = (float)$priceAPagar == (float)$test['price'];
            $saleMatch = (float)$priceRevenda == (float)$test['price_sale'];

            if ($priceMatch && $saleMatch) {
                echo "  ✓ Valores corretos\n";
            } else {
                echo "  ✗ VALORES INCORRETOS!\n";
            }
            echo "\n";
            break;
        }
    }
}

echo "\n" . str_repeat('=', 100) . "\n";
echo "SOBRE USAR IA PARA PROCESSAMENTO:\n";
echo str_repeat('=', 100) . "\n";
echo <<<TEXT

SIM, é possível e recomendado usar IA para melhorar a extração de dados! Opções:

1. **GEMINI API (Google)** - Gratuito até certo limite
   - Pode processar PDF diretamente
   - Retorna JSON estruturado
   - Ótimo para PDFs com layouts complexos

2. **GPT-4 Vision (OpenAI)** - Pago
   - Processa imagens/PDFs
   - Alta precisão
   - Custo por requisição

3. **Claude (Anthropic)** - Pago
   - Excelente com documentos
   - Pode processar PDFs longos

4. **OCR + ML Local:**
   - Tesseract OCR (grátis)
   - Treinar modelo próprio com TensorFlow/PyTorch
   - Mais trabalho inicial mas sem custos de API

**RECOMENDAÇÃO PARA SEU CASO:**
- Use Gemini API (grátis) para extrair produtos do PDF
- Envie o PDF e peça JSON estruturado com: código, nome, quantidade, preços
- Sistema aprende categorização automaticamente (já implementado)
- Fallback: se Gemini falhar, usa regex atual

Quer que eu implemente integração com Gemini API?

TEXT;

