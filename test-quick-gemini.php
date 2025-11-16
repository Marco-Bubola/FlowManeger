<?php

$apiKey = "AIzaSyBtI06cAwFtuHb7v3AM0tJmhDpGVY99xuE";

echo "Teste rápido Gemini...\n";

$prompt = "Retorne JSON com 3 produtos fictícios no formato: {\"products\":[{\"product_code\":\"40.121\",\"name\":\"Produto\",\"stock_quantity\":1}]}";

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_POSTFIELDS => json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]]
    ])
]);

echo "Chamando API...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";

if ($httpCode === 200) {
    echo "✓ SUCESSO!\n";
    $result = json_decode($response, true);
    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    echo "Resposta: " . substr($text, 0, 200) . "\n";
} else {
    echo "✗ ERRO\n";
    echo substr($response, 0, 500) . "\n";
}
