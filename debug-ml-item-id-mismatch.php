#!/usr/bin/env php
<?php

/**
 * Script para debugar a diferença de ml_item_id entre ml_publications e mercadolivre_products
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== DEBUG: ml_item_id em ml_publications vs mercadolivre_products ===\n\n";

// Buscar publicações recentes com seus produtos
$publications = DB::table('ml_publications')
    ->select('id', 'ml_item_id', 'title', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Últimas 10 publicações:\n";
echo str_repeat('-', 100) . "\n";
printf("%-5s | %-20s | %-50s | %-20s\n", "ID", "ml_item_id", "Título", "Criado em");
echo str_repeat('-', 100) . "\n";

foreach ($publications as $pub) {
    printf(
        "%-5s | %-20s | %-50s | %-20s\n",
        $pub->id,
        $pub->ml_item_id,
        substr($pub->title ?? 'Sem título', 0, 50),
        optional($pub->created_at)->format('Y-m-d H:i:s') ?? 'N/A'
    );
}

echo "\n\n";

// Buscar produtos vinculados a essas publicações
echo "Produtos vinculados via ml_publication_products:\n";
echo str_repeat('-', 120) . "\n";
printf("%-10s | %-10s | %-20s | %-20s\n", "Pub ID", "Prod ID", "ml_item_id (pub)", "ml_item_id (ml_prod)");
echo str_repeat('-', 120) . "\n";

foreach ($publications as $pub) {
    $linkedProducts = DB::table('ml_publication_products as mpp')
        ->leftJoin('mercadolivre_products as mp', 'mp.product_id', '=', 'mpp.product_id')
        ->where('mpp.ml_publication_id', $pub->id)
        ->select('mpp.product_id', 'mp.ml_item_id as mp_ml_item_id')
        ->get();
    
    foreach ($linkedProducts as $lp) {
        printf(
            "%-10s | %-10s | %-20s | %-20s | %s\n",
            $pub->id,
            $lp->product_id,
            $pub->ml_item_id,
            $lp->mp_ml_item_id ?? 'N/A',
            ($pub->ml_item_id !== $lp->mp_ml_item_id) ? '❌ DIFERENTE' : '✅ IGUAL'
        );
    }
}

echo "\n\n";

// Verificar publicações com ml_item_id temporário (TEMP_)
echo "Publicações com ml_item_id TEMPORÁRIO:\n";
echo str_repeat('-', 100) . "\n";

$tempPubs = DB::table('ml_publications')
    ->where('ml_item_id', 'LIKE', 'TEMP_%')
    ->select('id', 'ml_item_id', 'title', 'status', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($tempPubs->isEmpty()) {
    echo "✅ Nenhuma publicação com ml_item_id temporário encontrada.\n";
} else {
    printf("%-5s | %-25s | %-40s | %-10s | %-20s\n", "ID", "ml_item_id", "Título", "Status", "Criado em");
    echo str_repeat('-', 100) . "\n";
    
    foreach ($tempPubs as $tp) {
        printf(
            "%-5s | %-25s | %-40s | %-10s | %-20s\n",
            $tp->id,
            $tp->ml_item_id,
            substr($tp->title ?? 'Sem título', 0, 40),
            $tp->status,
            optional($tp->created_at)->format('Y-m-d H:i:s') ?? 'N/A'
        );
    }
}

echo "\n\n";

// Verificar se há ml_item_id diferentes entre as tabelas
echo "Comparação direta: ml_publications vs mercadolivre_products:\n";
echo str_repeat('-', 120) . "\n";

$comparison = DB::select("
    SELECT 
        mp.id as pub_id,
        mp.ml_item_id as pub_ml_item_id,
        mp.title as pub_title,
        mpp.product_id,
        mlp.ml_item_id as mlp_ml_item_id,
        mlp.product_id as mlp_product_id,
        CASE 
            WHEN mp.ml_item_id = mlp.ml_item_id THEN 'IGUAL'
            WHEN mp.ml_item_id LIKE 'TEMP_%' THEN 'TEMPORÁRIO'
            ELSE 'DIFERENTE'
        END as status_comparacao
    FROM ml_publications mp
    LEFT JOIN ml_publication_products mpp ON mpp.ml_publication_id = mp.id
    LEFT JOIN mercadolivre_products mlp ON mlp.product_id = mpp.product_id
    WHERE mp.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY mp.created_at DESC
    LIMIT 20
");

printf("%-7s | %-20s | %-20s | %-10s | %-20s\n", "Pub ID", "pub.ml_item_id", "mlp.ml_item_id", "Prod ID", "Status");
echo str_repeat('-', 120) . "\n";

foreach ($comparison as $row) {
    $icon = match($row->status_comparacao) {
        'IGUAL' => '✅',
        'TEMPORÁRIO' => '⏳',
        'DIFERENTE' => '❌',
        default => '❓'
    };
    
    printf(
        "%-7s | %-20s | %-20s | %-10s | %s %-20s\n",
        $row->pub_id,
        $row->pub_ml_item_id ?? 'NULL',
        $row->mlp_ml_item_id ?? 'NULL',
        $row->product_id ?? 'NULL',
        $icon,
        $row->status_comparacao
    );
}

echo "\n" . str_repeat('=', 120) . "\n";
echo "Análise concluída.\n\n";
