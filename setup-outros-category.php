<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== CONFIGURANDO CATEGORIA 'OUTROS' ===\n\n";

$outrosCategory = Category::where('type', 'transaction')
    ->where('name', 'Outros')
    ->where('is_active', 1)
    ->first();

if ($outrosCategory) {
    // Adicionar tags genéricas para a categoria Outros
    $outrosCategory->tags = 'outros,diversos,geral,variado,miscelanea';
    $outrosCategory->desc_category = 'Despesas diversas não categorizadas';
    $outrosCategory->save();

    echo "✅ Categoria 'Outros' configurada com sucesso!\n";
    echo "   ID: {$outrosCategory->id_category}\n";
    echo "   Nome: {$outrosCategory->name}\n";
    echo "   Descrição: {$outrosCategory->desc_category}\n";
    echo "   Tags: {$outrosCategory->tags}\n";
    echo "   User ID: {$outrosCategory->user_id}\n\n";
    echo "📌 Esta será a categoria padrão para transações não identificadas.\n";
} else {
    echo "❌ Categoria 'Outros' não encontrada!\n\n";
    echo "Criando categoria 'Outros'...\n";

    // Pegar user_id de uma categoria existente
    $existingCategory = Category::where('type', 'transaction')->first();
    $userId = $existingCategory ? $existingCategory->user_id : 2;

    $newOutros = Category::create([
        'name' => 'Outros',
        'desc_category' => 'Despesas diversas não categorizadas',
        'tipo' => 'ambos',
        'icone' => 'fas fa-ellipsis-h',
        'hexcolor_category' => '#95A5A6',
        'tags' => 'outros,diversos,geral,variado,miscelanea',
        'user_id' => $userId,
        'type' => 'transaction',
        'is_active' => 1,
    ]);

    echo "✅ Categoria 'Outros' criada com sucesso!\n";
    echo "   ID: {$newOutros->id_category}\n";
    echo "   Nome: {$newOutros->name}\n";
    echo "   Descrição: {$newOutros->desc_category}\n";
    echo "   Tags: {$newOutros->tags}\n";
    echo "   User ID: {$newOutros->user_id}\n";
}

echo "\n=== TESTANDO CATEGORIA PADRÃO ===\n\n";

// Testar com descrições que não devem ser categorizadas
$testDescriptions = [
    'COMPRA XPTO LTDA',
    'PAGAMENTO RANDOM',
    'EMPRESA DESCONHECIDA',
];

foreach ($testDescriptions as $desc) {
    echo "Testando: {$desc}\n";
    echo "→ Será categorizada como: Outros (ID: {$outrosCategory->id_category})\n\n";
}

echo "=== CONCLUÍDO! ===\n";
echo "Transações não identificadas agora serão categorizadas como 'Outros'.\n";
