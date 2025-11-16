<?php

require 'vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "TESTE DE INTEGRAÇÃO GEMINI AI\n";
echo str_repeat('=', 100) . "\n\n";

// Verificar API Key
$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;

if (empty($apiKey)) {
    die("❌ ERRO: GEMINI_API_KEY não configurada no .env\n");
}

echo "✓ API Key configurada: " . substr($apiKey, 0, 10) . "...\n\n";

// Encontrar PDF mais recente
$pdfFiles = glob('storage/app/private/livewire-tmp/*.pdf');
if (empty($pdfFiles)) {
    die("❌ ERRO: Nenhum PDF encontrado\n");
}

usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$pdfPath = $pdfFiles[0];
echo "✓ PDF selecionado: $pdfPath\n";
echo "  Tamanho: " . number_format(filesize($pdfPath) / 1024, 2) . " KB\n\n";

// Testar conexão com Gemini
echo "Testando conexão com Gemini API...\n";
echo str_repeat('-', 100) . "\n";

$pdfContent = base64_encode(file_get_contents($pdfPath));

$prompt = <<<PROMPT
Você é um extrator de dados de notas fiscais.

Analise o PDF e extraia TODOS os produtos com operação "Venda" (ignore "Brinde").

RETORNE APENAS JSON neste formato:
{
  "products": [
    {
      "product_code": "40.121",
      "stock_quantity": 2,
      "name": "PRODUTO EXEMPLO",
      "price_tabela": 100.00,
      "price_praticado": 80.00,
      "price_revenda": 90.00,
      "price_apagar": 70.00,
      "profit": 20.00,
      "operation": "Venda"
    }
  ]
}

REGRAS:
- Apenas produtos com "Venda"
- Código formato XX.XXX
- Valores como float
- Nome em uma linha
- Extraia de todas as páginas
- APENAS JSON, sem comentários

COMECE:
PROMPT;

$startTime = microtime(true);

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_POSTFIELDS => json_encode([
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => 'application/pdf',
                            'data' => $pdfContent
                        ]
                    ]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'topK' => 1,
            'topP' => 1,
        ]
    ])
]);

$responseBody = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$duration = round(microtime(true) - $startTime, 2);

echo "\nTempo de resposta: {$duration}s\n\n";

if ($httpCode === 200) {
    echo "✓ Gemini respondeu com sucesso!\n\n";

    $result = json_decode($responseBody, true);
    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

    echo "Resposta bruta (primeiros 500 chars):\n";
    echo str_repeat('-', 100) . "\n";
    echo substr($text, 0, 500) . "\n";
    echo str_repeat('-', 100) . "\n\n";

    // Limpar e parsear JSON
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/', '', $text);
    $text = trim($text);

    $data = json_decode($text, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($data['products'])) {
        $count = count($data['products']);
        echo "✓ JSON válido parseado!\n";
        echo "✓ Produtos extraídos: {$count}\n\n";

        echo "Primeiros 5 produtos:\n";
        echo str_repeat('-', 100) . "\n";

        foreach (array_slice($data['products'], 0, 5) as $idx => $p) {
            echo sprintf("%d. [%s] Qtd:%d - %s (R\$ %.2f)\n",
                $idx + 1,
                $p['product_code'] ?? '???',
                $p['stock_quantity'] ?? 0,
                substr($p['name'] ?? 'SEM NOME', 0, 50),
                ($p['price_apagar'] ?? 0)
            );
        }

        if ($count > 5) {
            echo "... e mais " . ($count - 5) . " produtos\n";
        }

        echo "\n" . str_repeat('=', 100) . "\n";
        echo "✓✓✓ TESTE CONCLUÍDO COM SUCESSO! ✓✓✓\n";
        echo "A integração está funcionando perfeitamente!\n";
        echo "Total de produtos extraídos pela IA: {$count}\n";
        echo str_repeat('=', 100) . "\n";

    } else {
        echo "❌ ERRO ao parsear JSON\n";
        echo "Erro: " . json_last_error_msg() . "\n";
        echo "Texto recebido:\n{$text}\n";
    }

} else {
    echo "❌ ERRO na requisição\n";
    echo "Status HTTP: {$httpCode}\n";
    echo "Resposta: {$responseBody}\n";
}
