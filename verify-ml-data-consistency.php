#!/usr/bin/env php
<?php

/**
 * Script para verificar se há produtos sem vínculo ou inconsistências
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== VERIFICAÇÃO FINAL: Consistência dos dados ===\n\n";

// 1. Verificar publicações sem ml_item_id correto
echo "1. Publicações com ml_item_id inválido:\n";
echo str_repeat('-', 80) . "\n";

$invalidPubs = DB::table('ml_publications')
    ->where(function($q) {
        $q->whereNull('ml_item_id')
          ->orWhere('ml_item_id', '')
          ->orWhere('ml_item_id', 'LIKE', 'TEMP_%');
    })
    ->select('id', 'ml_item_id', 'title', 'status', 'created_at')
    ->get();

if ($invalidPubs->isEmpty()) {
    echo "✅ Todas as publicações têm ml_item_id válido!\n";
} else {
    foreach ($invalidPubs as $pub) {
        echo "❌ Publicação #{$pub->id}: {$pub->ml_item_id} - {$pub->title}\n";
    }
}

echo "\n2. Produtos em publicações SEM vínculo em mercadolivre_products:\n";
echo str_repeat('-', 80) . "\n";

$unlinkedProducts = DB::select("
    SELECT DISTINCT
        mpp.ml_publication_id,
        mpp.product_id,
        mp.ml_item_id,
        mp.title
    FROM ml_publication_products mpp
    INNER JOIN ml_publications mp ON mp.id = mpp.ml_publication_id
    LEFT JOIN mercadolivre_products mlp ON mlp.product_id = mpp.product_id 
        AND mlp.ml_item_id = mp.ml_item_id
    WHERE mp.ml_item_id NOT LIKE 'TEMP_%'
        AND mp.ml_item_id IS NOT NULL
        AND mlp.id IS NULL
    ORDER BY mpp.ml_publication_id, mpp.product_id
");

if (empty($unlinkedProducts)) {
    echo "✅ Todos os produtos das publicações estão vinculados em mercadolivre_products!\n";
} else {
    foreach ($unlinkedProducts as $row) {
        echo "❌ Publicação #{$row->ml_publication_id} ({$row->ml_item_id})\n";
        echo "   Produto #{$row->product_id} não está vinculado\n";
    }
}

echo "\n3. Resumo geral:\n";
echo str_repeat('-', 80) . "\n";

$stats = [
    'total_publications' => DB::table('ml_publications')->count(),
    'valid_publications' => DB::table('ml_publications')
        ->where('ml_item_id', 'NOT LIKE', 'TEMP_%')
        ->whereNotNull('ml_item_id')
        ->count(),
    'total_ml_products' => DB::table('mercadolivre_products')->count(),
    'unique_products_published' => DB::table('mercadolivre_products')
        ->distinct('product_id')
        ->count('product_id'),
];

echo "📊 Estatísticas:\n";
echo "   Total de publicações: {$stats['total_publications']}\n";
echo "   Publicações válidas: {$stats['valid_publications']}\n";
echo "   Total de vínculos em mercadolivre_products: {$stats['total_ml_products']}\n";
echo "   Produtos únicos publicados: {$stats['unique_products_published']}\n";

echo "\n4. Detalhamento por publicação:\n";
echo str_repeat('-', 80) . "\n";

$pubDetails = DB::select("
    SELECT 
        mp.id,
        mp.ml_item_id,
        mp.title,
        mp.publication_type,
        COUNT(DISTINCT mpp.product_id) as num_products_linked,
        COUNT(DISTINCT mlp.id) as num_ml_product_records
    FROM ml_publications mp
    LEFT JOIN ml_publication_products mpp ON mpp.ml_publication_id = mp.id
    LEFT JOIN mercadolivre_products mlp ON mlp.ml_item_id = mp.ml_item_id
    WHERE mp.ml_item_id NOT LIKE 'TEMP_%'
    GROUP BY mp.id, mp.ml_item_id, mp.title, mp.publication_type
    ORDER BY mp.id DESC
");

printf("%-5s | %-20s | %-10s | %-15s | %s\n", "ID", "ml_item_id", "Tipo", "Prods Linked", "Status");
echo str_repeat('-', 80) . "\n";

foreach ($pubDetails as $detail) {
    $status = ($detail->num_products_linked == $detail->num_ml_product_records) ? '✅ OK' : '⚠️ VERIFICAR';
    printf(
        "%-5s | %-20s | %-10s | %-15s | %s\n",
        $detail->id,
        $detail->ml_item_id,
        $detail->publication_type,
        "{$detail->num_products_linked} → {$detail->num_ml_product_records}",
        $status
    );
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Verificação concluída!\n\n";
