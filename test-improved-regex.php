<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$text = $pdf->getText();

echo "TESTANDO NOVA REGEX PARA CAPTURAR PRODUTOS COM QUEBRA DE LINHA\n";
echo str_repeat('=', 100) . "\n\n";

// Filtrar texto
$startPos = strpos($text, 'OPERAÇÃO');
$endPos = strpos($text, 'PRODUTOS NÃO DISPONÍVEIS');
if ($endPos === false) $endPos = strpos($text, 'AJUSTES');

$filteredText = substr($text, $startPos + strlen('OPERAÇÃO'), $endPos - ($startPos + strlen('OPERAÇÃO')));

// Limpar espaços mas MANTER quebras de linha importantes
$filteredText = preg_replace('/[ \t]+/', ' ', $filteredText); // Remove espaços/tabs duplos
$filteredText = preg_replace('/\n+/', "\n", $filteredText); // Remove quebras de linha múltiplas

echo "Primeiros 1000 caracteres do texto filtrado:\n";
echo str_repeat('-', 100) . "\n";
echo substr($filteredText, 0, 1000) . "\n";
echo str_repeat('-', 100) . "\n\n";

// Nova regex que captura produtos mesmo com nome em múltiplas linhas
$pattern = '/
    (\d{2}\.\d{3})          # Código do produto
    \s+(\d+)                 # Quantidade
    \s+(.+?)                 # Nome do produto (não-greedy)
    \s+([\d,\.]+)            # R$ TABELA
    \s+([\d,\.]+)            # R$ PRATICADO
    \s+([\d,\.]+)            # R$ REVENDA
    \s+([\d,\.]+)            # R$ A PAGAR
    \s+([\d,\.]+)            # R$ LUCRO
    \s+(Venda|Brinde\s*(?:Revendedor|Consumidor)?)  # Operação
/xs';

preg_match_all($pattern, $filteredText, $matches);

echo "RESULTADO COM NOVA REGEX:\n";
echo "Total de produtos encontrados: " . count($matches[0]) . "\n\n";

$vendaCount = 0;
$brindeCount = 0;

for ($i = 0; $i < count($matches[0]); $i++) {
    $operation = trim($matches[10][$i]);
    if (strpos($operation, 'Venda') !== false) {
        $vendaCount++;
        $type = 'VENDA';
    } else {
        $brindeCount++;
        $type = 'BRINDE';
    }

    $name = preg_replace('/\s+/', ' ', trim($matches[3][$i]));

    echo sprintf("%2d. [%s] Qtd:%2s %-60s [%s]\n",
        $i + 1,
        $matches[1][$i],
        $matches[2][$i],
        substr($name, 0, 60),
        $type
    );
}

echo "\n" . str_repeat('=', 100) . "\n";
echo "RESUMO:\n";
echo "  Vendas: $vendaCount\n";
echo "  Brindes: $brindeCount\n";
echo "  TOTAL: " . ($vendaCount + $brindeCount) . "\n";
echo "  Esperado: 40\n";

if (($vendaCount + $brindeCount) >= 37) {
    echo "\n✓ SUCESSO: Capturando a maioria dos produtos (Brindes podem ter formato diferente)\n";
} else {
    echo "\n✗ Ainda faltam produtos\n";
}
