<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== TESTE FINAL DE CATEGORIZAÇÃO AUTOMÁTICA ===\n\n";

// Simular o mapeamento completo
$categories = Category::where('is_active', 1)
    ->where('type', 'transaction')
    ->get();

$mapping = [];

foreach ($categories as $category) {
    $categoryId = $category->id_category;

    if (!empty($category->tags)) {
        $tags = explode(',', $category->tags);
        foreach ($tags as $tag) {
            $tag = trim(strtoupper($tag));
            if (!empty($tag)) {
                $mapping[$tag] = ['id' => $categoryId, 'name' => $category->name];
            }
        }
    }
}

echo "📊 Total de palavras-chave mapeadas: " . count($mapping) . "\n\n";

// Buscar categoria "Outros"
$outrosCategory = Category::where('type', 'transaction')
    ->where('name', 'Outros')
    ->first();

echo "📌 Categoria padrão (Outros): ID {$outrosCategory->id_category}\n\n";

// Testar várias transações
$testTransactions = [
    // DEVE SER IDENTIFICADO
    ['desc' => 'SUPERMERCADO ANTONELLI', 'expected' => 'Supermercados e Alimentação'],
    ['desc' => 'POSTO SHELL GASOLINA', 'expected' => 'Combustíveis e Postos'],
    ['desc' => 'UBER VIAGEM', 'expected' => 'Transporte'],
    ['desc' => 'NETFLIX MENSALIDADE', 'expected' => 'streaming'],
    ['desc' => 'FARMACIA POPULAR', 'expected' => 'Farmácias e Saúde'],

    // NÃO DEVE SER IDENTIFICADO - VAI PARA "OUTROS"
    ['desc' => 'EMPRESA DESCONHECIDA LTDA', 'expected' => 'Outros'],
    ['desc' => 'PAGAMENTO XPTO', 'expected' => 'Outros'],
    ['desc' => 'COMPRA RANDOM 123', 'expected' => 'Outros'],
    ['desc' => 'SERVIÇO ABC', 'expected' => 'Outros'],
];

echo "=== TESTANDO CATEGORIZAÇÃO ===\n\n";

$success = 0;
$total = count($testTransactions);

foreach ($testTransactions as $test) {
    $description = $test['desc'];
    $expected = $test['expected'];

    echo "🔍 Testando: {$description}\n";

    // Simular a lógica de determineCategoryId
    $found = false;
    $categoryName = null;

    foreach ($mapping as $keyword => $categoryData) {
        if (stripos($description, $keyword) !== false) {
            $categoryName = $categoryData['name'];
            $found = true;
            break;
        }
    }

    if (!$found) {
        $categoryName = 'Outros';
    }

    if ($categoryName === $expected) {
        echo "   ✅ CORRETO: {$categoryName}\n";
        $success++;
    } else {
        echo "   ❌ ERRO: Esperado '{$expected}', obteve '{$categoryName}'\n";
    }

    echo "\n";
}

echo "=== RESULTADO FINAL ===\n";
echo "Sucesso: {$success}/{$total} (" . round(($success/$total)*100, 1) . "%)\n";

if ($success === $total) {
    echo "\n🎉 PERFEITO! Todas as categorizações estão funcionando corretamente!\n";
    echo "\n✅ Transações identificadas → Categoria específica\n";
    echo "✅ Transações NÃO identificadas → Categoria 'Outros' (ID: {$outrosCategory->id_category})\n";
} else {
    echo "\n⚠️  Algumas categorizações falharam. Verifique o mapeamento.\n";
}
