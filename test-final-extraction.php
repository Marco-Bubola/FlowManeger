<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
echo "Testando extração com NOVA LÓGICA: $pdfPath\n";
echo str_repeat('=', 80) . "\n\n";

$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$text = $pdf->getText();

// NOVA LÓGICA
$startPos = strpos($text, 'OPERAÇÃO');
$endPos = strpos($text, 'PRODUTOS NÃO DISPONÍVEIS');

if ($endPos === false) {
    $endPos = strpos($text, 'AJUSTES');
}
if ($endPos === false) {
    $endPos = strpos($text, 'PLANO DE PAGAMENTO');
}

echo "Início (OPERAÇÃO): $startPos\n";
echo "Fim (PRODUTOS NÃO DISPONÍVEIS): $endPos\n\n";

if ($startPos !== false && $endPos !== false) {
    $filteredText = substr($text, $startPos + strlen('OPERAÇÃO'), $endPos - ($startPos + strlen('OPERAÇÃO')));
    $filteredText = preg_replace('/\s+/', ' ', $filteredText);
    $filteredText = preg_replace('/\bOPERAÇÃO\b/', '', $filteredText);

    echo "Tamanho do texto filtrado: " . strlen($filteredText) . " caracteres\n\n";

    $pattern = '/(\d{1,5}\.\d{3})\s+(\d+)\s+([A-Za-z0-9À-ÿ\s&\-\/,()+=]+(?:\s*\+\s*\d+\s*[A-Za-z0-9À-ÿ\s&\-\/,()]*)*)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+(Venda|Brinde)?/x';

    preg_match_all($pattern, $filteredText, $matches, PREG_OFFSET_CAPTURE);

    echo "=" . str_repeat('=', 79) . "\n";
    echo "RESULTADO FINAL:\n";
    echo "=" . str_repeat('=', 79) . "\n";
    echo "Total de produtos extraídos: " . count($matches[0]) . "\n\n";

    echo "Lista de produtos:\n";
    echo str_repeat('-', 80) . "\n";

    $productsData = [];
    foreach ($matches[0] as $key => $match) {
        $code = $matches[1][$key][0];
        $qty = $matches[2][$key][0];
        $name = trim($matches[3][$key][0]);
        $operation = $matches[9][$key][0] ?? '';

        if ($operation === 'Venda') {
            $productsData[] = [
                'code' => $code,
                'qty' => $qty,
                'name' => $name
            ];

            echo sprintf("%2d. [%s] Qtd: %2s - %s\n",
                count($productsData),
                $code,
                $qty,
                substr($name, 0, 60)
            );
        }
    }

    echo str_repeat('-', 80) . "\n";
    echo "\nTOTAL DE PRODUTOS COM OPERAÇÃO 'VENDA': " . count($productsData) . "\n";
    echo "(Brindes não são contabilizados)\n";
}
