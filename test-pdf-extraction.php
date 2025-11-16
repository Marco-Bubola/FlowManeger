<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Pega o PDF mais recente
$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
if (empty($pdfFiles)) {
    die("Nenhum PDF encontrado\n");
}

// Pega o mais recente
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
echo "Analisando: $pdfPath\n";
echo str_repeat('=', 80) . "\n\n";

$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$text = $pdf->getText();

echo "TEXTO COMPLETO DO PDF:\n";
echo str_repeat('-', 80) . "\n";
echo $text;
echo "\n" . str_repeat('-', 80) . "\n\n";

// Filtrar texto entre OPERAÇÃO e PEDIDO Nº
$startPos = strpos($text, 'OPERAÇÃO');
$endPos = strpos($text, 'PEDIDO Nº');

echo "Posição OPERAÇÃO: $startPos\n";
echo "Posição PEDIDO Nº: $endPos\n\n";

if ($startPos !== false && $endPos !== false) {
    $filteredText = substr($text, $startPos + strlen('OPERAÇÃO'), $endPos - ($startPos + strlen('OPERAÇÃO')));
    $filteredText = preg_replace('/\s+/', ' ', $filteredText);
    $filteredText = preg_replace('/\bOPERAÇÃO\b/', '', $filteredText);

    echo "TEXTO FILTRADO:\n";
    echo str_repeat('-', 80) . "\n";
    echo $filteredText;
    echo "\n" . str_repeat('-', 80) . "\n\n";

    // Aplicar regex
    $pattern = '/(\d{1,5}\.\d{3})\s+           # Código do produto (CÓD.)
            (\d+)\s+                       # Quantidade (QTD.)
            ([A-Za-z0-9À-ÿ\s&\-\/,()+=]+(?:\s*\+\s*\d+\s*[A-Za-z0-9À-ÿ\s&\-\/,()]*)*)\s+   # Nome do produto (incluindo + números)
            ([\d,\.]+)\s+                  # Preço Tabela (R$ TABELA)
            ([\d,\.]+)\s+                  # Preço Praticado (R$ PRATICADO)
            ([\d,\.]+)\s+                  # Preço Revenda (R$ REVENDA)
            ([\d,\.]+)\s+                  # Preço a Pagar (R$ A PAGAR)
            ([\d,\.]+)\s+                  # Lucro (R$ LUCRO)
            (Venda)?                       # Operação (opcional)
        /x';

    preg_match_all($pattern, $filteredText, $matches, PREG_OFFSET_CAPTURE);

    echo "PRODUTOS ENCONTRADOS: " . count($matches[0]) . "\n\n";

    foreach ($matches[0] as $key => $match) {
        echo "Produto " . ($key + 1) . ":\n";
        echo "  Código: " . $matches[1][$key][0] . "\n";
        echo "  Qtd: " . $matches[2][$key][0] . "\n";
        echo "  Nome: " . trim($matches[3][$key][0]) . "\n";
        echo "  Match completo: " . $match[0] . "\n";
        echo str_repeat('-', 40) . "\n";
    }

    // Verificar se há linhas não capturadas
    echo "\n\nVERIFICANDO PRODUTOS NÃO CAPTURADOS:\n";
    echo str_repeat('=', 80) . "\n";

    // Procurar todos os códigos de produto no formato XX.XXX
    preg_match_all('/(\d{2}\.\d{3})\s+(\d+)\s+/', $filteredText, $allCodes);
    echo "Total de códigos encontrados no texto: " . count($allCodes[0]) . "\n";
    echo "Total de produtos extraídos pela regex: " . count($matches[0]) . "\n";
    echo "Diferença (produtos perdidos): " . (count($allCodes[0]) - count($matches[0])) . "\n\n";

    if (count($allCodes[0]) > count($matches[0])) {
        echo "Códigos encontrados no texto mas NÃO extraídos:\n";
        $extractedCodes = array_column($matches[1], 0);
        foreach ($allCodes[1] as $idx => $code) {
            if (!in_array($code[0], $extractedCodes)) {
                // Pegar contexto ao redor do código
                $pos = $code[1];
                $context = substr($filteredText, max(0, $pos - 50), 200);
                echo "\n  Código perdido: " . $code[0] . "\n";
                echo "  Contexto: " . $context . "\n";
                echo str_repeat('-', 40) . "\n";
            }
        }
    }
}
