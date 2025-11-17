<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
if (empty($pdfFiles)) {
    die("Nenhum PDF encontrado\n");
}

usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
echo "Analisando: $pdfPath\n";
echo str_repeat('=', 80) . "\n\n";

$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$text = $pdf->getText();

echo "TOTAL DE CARACTERES NO PDF: " . strlen($text) . "\n\n";

// Procurar todas as ocorrências de "OPERAÇÃO"
preg_match_all('/OPERAÇÃO/i', $text, $matches, PREG_OFFSET_CAPTURE);
echo "Total de vezes que 'OPERAÇÃO' aparece: " . count($matches[0]) . "\n";
foreach ($matches[0] as $idx => $match) {
    echo "  Posição " . ($idx + 1) . ": " . $match[1] . "\n";
}

// Procurar todas as ocorrências de "PEDIDO Nº"
preg_match_all('/PEDIDO\s+N[ºo°]/i', $text, $matches, PREG_OFFSET_CAPTURE);
echo "\nTotal de vezes que 'PEDIDO Nº' aparece: " . count($matches[0]) . "\n";
foreach ($matches[0] as $idx => $match) {
    echo "  Posição " . ($idx + 1) . ": " . $match[1] . " - Texto: '" . $match[0] . "'\n";
}

// Procurar "Extrato de Pedido" para identificar páginas
preg_match_all('/Extrato de Pedido/i', $text, $matches, PREG_OFFSET_CAPTURE);
echo "\nTotal de vezes que 'Extrato de Pedido' aparece (páginas): " . count($matches[0]) . "\n";
foreach ($matches[0] as $idx => $match) {
    echo "  Página " . ($idx + 1) . " na posição: " . $match[1] . "\n";
}

// Contar todos os códigos de produto
preg_match_all('/^\s*(\d{2}\.\d{3})\s+(\d+)\s+/m', $text, $allCodes);
echo "\n\nTOTAL DE CÓDIGOS DE PRODUTOS NO PDF: " . count($allCodes[0]) . "\n";
echo "Códigos encontrados:\n";
$uniqueCodes = [];
foreach ($allCodes[1] as $code) {
    if (!isset($uniqueCodes[$code])) {
        $uniqueCodes[$code] = 0;
    }
    $uniqueCodes[$code]++;
}
foreach ($uniqueCodes as $code => $count) {
    echo "  $code ($count vezes)\n";
}

echo "\n\n" . str_repeat('=', 80) . "\n";
echo "PROPOSTA DE SOLUÇÃO:\n";
echo str_repeat('=', 80) . "\n";
echo "O PDF tem múltiplas páginas. Precisamos extrair produtos de TODAS as páginas.\n";
echo "Sugestão: Em vez de filtrar entre 'OPERAÇÃO' e 'PEDIDO Nº', devemos:\n";
echo "1. Extrair todas as linhas que começam com padrão: XX.XXX QTD PRODUTO...\n";
echo "2. OU filtrar do primeiro 'OPERAÇÃO' até 'PRODUTOS NÃO DISPONÍVEIS'\n";
echo "3. OU processar cada página 'Extrato de Pedido' separadamente\n";

// Testar abordagem: pegar do primeiro OPERAÇÃO até "PRODUTOS NÃO DISPONÍVEIS"
$startPos = strpos($text, 'OPERAÇÃO');
$endPos = strpos($text, 'PRODUTOS NÃO DISPONÍVEIS');

if ($startPos !== false && $endPos !== false) {
    echo "\n\nTESTANDO NOVA ABORDAGEM (OPERAÇÃO -> PRODUTOS NÃO DISPONÍVEIS):\n";
    echo "Posição inicial: $startPos\n";
    echo "Posição final: $endPos\n";

    $betterText = substr($text, $startPos + strlen('OPERAÇÃO'), $endPos - ($startPos + strlen('OPERAÇÃO')));
    $betterText = preg_replace('/\s+/', ' ', $betterText);

    $pattern = '/(\d{1,5}\.\d{3})\s+(\d+)\s+([A-Za-z0-9À-ÿ\s&\-\/,()+=]+(?:\s*\+\s*\d+\s*[A-Za-z0-9À-ÿ\s&\-\/,()]*)*)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+(Venda|Brinde)?/x';

    preg_match_all($pattern, $betterText, $matches);

    echo "\nProdutos extraídos com nova abordagem: " . count($matches[0]) . "\n";
    echo "\nComparação:\n";
    echo "  Códigos totais no PDF: " . count($allCodes[0]) . "\n";
    echo "  Produtos extraídos (abordagem atual): 14\n";
    echo "  Produtos extraídos (nova abordagem): " . count($matches[0]) . "\n";
}
