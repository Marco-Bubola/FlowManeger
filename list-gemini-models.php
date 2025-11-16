<?php

$apiKey = "AIzaSyBtI06cAwFtuHb7v3AM0tJmhDpGVY99xuE";

echo "Listando modelos Gemini disponíveis...\n\n";

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        $name = $model['name'] ?? '';
        $displayName = $model['displayName'] ?? '';
        $supportedMethods = implode(', ', $model['supportedGenerationMethods'] ?? []);

        echo "Nome: $name\n";
        echo "Display: $displayName\n";
        echo "Métodos: $supportedMethods\n";
        echo str_repeat('-', 80) . "\n";
    }
} else {
    echo "Erro: " . json_encode($data, JSON_PRETTY_PRINT);
}
