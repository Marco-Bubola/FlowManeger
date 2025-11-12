<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== TESTE DE CATEGORIZAÇÃO AUTOMÁTICA ===\n\n";

// Simular o funcionamento do getCategoryMapping
$categories = Category::where('is_active', 1)
    ->where('type', 'transaction')
    ->get();

$mapping = [];

foreach ($categories as $category) {
    $categoryId = $category->id_category;
    $categoryNameLower = strtolower($category->name);

    // Adicionar tags
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

echo "Total de palavras-chave mapeadas: " . count($mapping) . "\n\n";

// Exemplos de descrições de transações para testar
$testTransactions = [
    'SUPERMERCADO ANTONELLI',
    'POSTO SHELL',
    'UBER - CORRIDA',
    'NETFLIX ASSINATURA',
    'SHOPEE - COMPRA ONLINE',
    'FARMACIA DROGASIL',
    'BURGER KING',
    'ACADEMIA SMARTFIT',
    'CINEMA CINEMARK',
    'BOTICARIO - PERFUME',
];

echo "=== TESTANDO CATEGORIZAÇÕES ===\n\n";

foreach ($testTransactions as $description) {
    echo "Descrição: {$description}\n";

    $found = false;
    foreach ($mapping as $keyword => $categoryData) {
        if (stripos($description, $keyword) !== false) {
            echo "✅ Categoria: {$categoryData['name']} (ID: {$categoryData['id']})\n";
            echo "   Palavra-chave encontrada: {$keyword}\n";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "❌ Nenhuma categoria encontrada\n";
    }

    echo "\n";
}

echo "=== CATEGORIAS DISPONÍVEIS ===\n\n";
foreach ($categories as $cat) {
    echo "ID {$cat->id_category}: {$cat->name}\n";
    if ($cat->tags) {
        echo "  Tags: {$cat->tags}\n";
    }
}
