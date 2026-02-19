<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = \Illuminate\Support\Facades\DB::connection();

echo "=== VERIFICAÇÃO DAS TABELAS DO MERCADO LIVRE ===\n\n";

$tables = [
    'mercadolivre_products',
    'mercadolivre_orders',
    'mercadolivre_tokens',
    'mercadolivre_sync_log',
    'mercadolivre_webhooks'
];

foreach ($tables as $table) {
    echo "📋 Tabela: {$table}\n";
    
    try {
        $columns = $db->select("DESCRIBE {$table}");
        echo "   ✅ Existe com " . count($columns) . " colunas\n";
        
        foreach ($columns as $column) {
            echo "      - {$column->Field} ({$column->Type})\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ ERRO: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// Verifica os novos campos na tabela products
echo "📋 Tabela: products (novos campos ML)\n";
$mlFields = ['barcode', 'brand', 'model', 'warranty_months', 'condition'];
$columns = $db->select("DESCRIBE products");
$existingFields = array_column($columns, 'Field');

foreach ($mlFields as $field) {
    if (in_array($field, $existingFields)) {
        echo "   ✅ Campo {$field} existe\n";
    } else {
        echo "   ❌ Campo {$field} NÃO existe\n";
    }
}

echo "\n=== VERIFICAÇÃO CONCLUÍDA ===\n";
