<?php

require __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Caminho do PDF de teste
$pdfPath = __DIR__ . '/storage/app/private/livewire-tmp/xrnGJDOP9DkkVyo9tvqbGe9jrLUlbB-metaMTItMTEtMjUucGRm-.pdf';

if (!file_exists($pdfPath)) {
    die("❌ PDF não encontrado. Por favor, faça upload novamente.\n");
}

echo "🔍 DIAGNÓSTICO DE EXTRAÇÃO DE PRODUTOS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// 1. Extrair texto completo
$parser = new Parser();
$pdf = $parser->parseFile($pdfPath);
$fullText = $pdf->getText();

echo "📄 TEXTO COMPLETO ({strlen($fullText)} caracteres)\n";
echo "-" . str_repeat("-", 70) . "\n";
echo substr($fullText, 0, 1500) . "\n...\n\n";

// 2. Encontrar marcadores
echo "🎯 MARCADORES ENCONTRADOS:\n";
echo "-" . str_repeat("-", 70) . "\n";
$markers = [
    'OPERAÇÃO' => strpos($fullText, 'OPERAÇÃO'),
    'PRODUTOS NÃO DISPONÍVEIS' => strpos($fullText, 'PRODUTOS NÃO DISPONÍVEIS'),
    'PEDIDO Nº' => strpos($fullText, 'PEDIDO Nº'),
    'AJUSTES' => strpos($fullText, 'AJUSTES'),
    'Venda' => substr_count($fullText, 'Venda'),
    'Brinde' => substr_count($fullText, 'Brinde'),
];

foreach ($markers as $marker => $pos) {
    if ($marker === 'Venda' || $marker === 'Brinde') {
        echo "✅ '{$marker}': {$pos} ocorrências\n";
    } else {
        echo ($pos !== false ? "✅" : "❌") . " '{$marker}': " . ($pos !== false ? "posição {$pos}" : "NÃO ENCONTRADO") . "\n";
    }
}
echo "\n";

// 3. Extrair seção filtrada
$startPos = strpos($fullText, 'OPERAÇÃO');
$endPos = strpos($fullText, 'PRODUTOS NÃO DISPONÍVEIS');

if ($startPos !== false && $endPos !== false) {
    $filteredText = substr($fullText, $startPos, $endPos - $startPos);
    echo "✂️ TEXTO FILTRADO ({strlen($filteredText)} caracteres)\n";
    echo "-" . str_repeat("-", 70) . "\n";
    echo substr($filteredText, 0, 1000) . "\n...\n\n";
} else {
    echo "❌ Não foi possível filtrar o texto\n\n";
    $filteredText = $fullText;
}

// 4. Contar produtos potenciais por padrão de código
echo "🔢 CÓDIGOS DE PRODUTO ENCONTRADOS:\n";
echo "-" . str_repeat("-", 70) . "\n";
preg_match_all('/(\d{2}\.\d{3})\s+(\d+)\s+/', $filteredText, $matches, PREG_SET_ORDER);

$totalProducts = 0;
$vendaCount = 0;
$brindeCount = 0;

foreach ($matches as $idx => $match) {
    $code = $match[1];
    $qty = $match[2];

    // Tentar encontrar o contexto (próximas 200 chars)
    $pos = strpos($filteredText, $match[0]);
    $context = substr($filteredText, $pos, 200);

    // Detectar operação
    $operation = 'Desconhecida';
    if (preg_match('/Venda/', $context)) {
        $operation = 'Venda';
        $vendaCount++;
    } elseif (preg_match('/Brinde/', $context)) {
        $operation = 'Brinde';
        $brindeCount++;
    }

    // Extrair nome do produto
    preg_match('/' . preg_quote($code) . '\s+\d+\s+(.+?)\s+\d+,\d+/', $context, $nameMatch);
    $name = isset($nameMatch[1]) ? trim(preg_replace('/\s+/', ' ', $nameMatch[1])) : '???';

    $totalProducts++;

    echo sprintf(
        "%2d. Código: %s | Qtd: %2d | Op: %-10s | Nome: %s\n",
        $idx + 1,
        $code,
        $qty,
        $operation,
        substr($name, 0, 40)
    );
}

echo "\n";
echo "📊 RESUMO:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "Total de produtos encontrados: {$totalProducts}\n";
echo "Produtos 'Venda': {$vendaCount}\n";
echo "Produtos 'Brinde': {$brindeCount}\n";
echo "\n";

// 5. Testar chamada Gemini
echo "🤖 TESTANDO GEMINI API:\n";
echo "-" . str_repeat("-", 70) . "\n";

$apiKey = 'AIzaSyBtI06cAwFtuHb7v3AM0tJmhDpGVY99xuE';
$model = 'gemini-2.0-flash-exp';

// Preparar texto (limpar e limitar)
$cleanText = preg_replace('/\s+/', ' ', $filteredText);
$cleanText = trim($cleanText);
if (strlen($cleanText) > 8000) {
    $cleanText = substr($cleanText, 0, 8000);
}

$prompt = <<<PROMPT
Você é um extrator de dados de notas fiscais.

Extraia TODOS os produtos com operação "Venda" do texto abaixo.

RETORNE APENAS JSON PURO neste formato:
{
  "products": [
    {
      "product_code": "40.121",
      "stock_quantity": 2,
      "name": "ESTJ ZAAD REG/22",
      "operation": "Venda"
    }
  ]
}

TEXTO:
{$cleanText}

JSON:
PROMPT;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'maxOutputTokens' => 2048,
        ]
    ])
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

    echo "Resposta Gemini ({strlen($text)} caracteres):\n";
    echo substr($text, 0, 500) . "\n";

    // Tentar parsear JSON
    $cleanJson = preg_replace('/```json\s*/i', '', $text);
    $cleanJson = preg_replace('/```\s*/', '', $cleanJson);
    $cleanJson = trim($cleanJson);

    $parsed = json_decode($cleanJson, true);
    if ($parsed && isset($parsed['products'])) {
        echo "\n✅ JSON parseado com sucesso!\n";
        echo "Produtos extraídos pela IA: " . count($parsed['products']) . "\n";
    } else {
        echo "\n❌ Erro ao parsear JSON: " . json_last_error_msg() . "\n";
        echo "Texto limpo:\n{$cleanJson}\n";
    }
} else {
    echo "❌ Erro na API\n";
    echo substr($response, 0, 500) . "\n";
}

echo "\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "✅ DIAGNÓSTICO COMPLETO\n";
