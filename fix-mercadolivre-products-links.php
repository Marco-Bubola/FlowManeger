#!/usr/bin/env php
<?php

/**
 * Script para corrigir produtos não vinculados em mercadolivre_products
 * quando a publicação existe em ml_publications com ml_item_id correto
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MlPublication;
use App\Models\MercadoLivreProduct;

echo "\n=== CORREÇÃO: Vincular produtos em mercadolivre_products ===\n\n";

// Buscar todas as publicações com ml_item_id válido (não temporário)
$publications = MlPublication::where('ml_item_id', 'NOT LIKE', 'TEMP_%')
    ->whereNotNull('ml_item_id')
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de publicações encontradas: " . $publications->count() . "\n\n";

$totalFixed = 0;
$totalSkipped = 0;
$errors = [];

foreach ($publications as $publication) {
    echo "Processando publicação #{$publication->id} - {$publication->ml_item_id}\n";
    echo "  Título: " . substr($publication->title, 0, 60) . "\n";
    
    // Buscar produtos vinculados a esta publicação
    $linkedProducts = DB::table('ml_publication_products')
        ->where('ml_publication_id', $publication->id)
        ->get();
    
    echo "  Produtos vinculados: " . $linkedProducts->count() . "\n";
    
    foreach ($linkedProducts as $link) {
        // Verificar se já existe em mercadolivre_products
        $exists = MercadoLivreProduct::where('product_id', $link->product_id)
            ->where('ml_item_id', $publication->ml_item_id)
            ->exists();
        
        if ($exists) {
            echo "    ✓ Produto #{$link->product_id} já vinculado\n";
            $totalSkipped++;
            continue;
        }
        
        // Criar registro em mercadolivre_products
        try {
            MercadoLivreProduct::create([
                'product_id' => $link->product_id,
                'ml_item_id' => $publication->ml_item_id,
                'ml_permalink' => $publication->ml_permalink,
                'ml_category_id' => $publication->ml_category_id,
                'listing_type' => $publication->listing_type,
                'status' => $publication->status,
                'ml_price' => $publication->price,
                'ml_quantity' => $publication->available_quantity,
                'ml_attributes' => $publication->ml_attributes ?? [],
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);
            
            echo "    ✅ Produto #{$link->product_id} VINCULADO ao ml_item_id {$publication->ml_item_id}\n";
            $totalFixed++;
            
        } catch (\Exception $e) {
            echo "    ❌ ERRO ao vincular produto #{$link->product_id}: {$e->getMessage()}\n";
            $errors[] = [
                'publication_id' => $publication->id,
                'product_id' => $link->product_id,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    echo "\n";
}

echo str_repeat('=', 80) . "\n";
echo "RESUMO:\n";
echo "  ✅ Produtos vinculados: $totalFixed\n";
echo "  ⏭️  Produtos já vinculados (pulados): $totalSkipped\n";
echo "  ❌ Erros: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nERROS ENCONTRADOS:\n";
    foreach ($errors as $error) {
        echo "  - Publicação #{$error['publication_id']}, Produto #{$error['product_id']}: {$error['error']}\n";
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Correção concluída!\n\n";
