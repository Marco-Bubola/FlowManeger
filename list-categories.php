<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== CATEGORIAS DE TRANSAÇÃO EXISTENTES ===\n\n";

$categories = Category::where('type', 'transaction')
    ->where('is_active', 1)
    ->orderBy('user_id')
    ->orderBy('name')
    ->get(['id_category', 'name', 'tipo', 'user_id', 'tags']);

if ($categories->isEmpty()) {
    echo "❌ Nenhuma categoria de transação encontrada!\n\n";
    echo "Isso explica porque a categorização automática não está funcionando.\n";
} else {
    foreach ($categories as $cat) {
        echo sprintf(
            "ID: %d | Nome: %s | Tipo: %s | User ID: %d | Tags: %s\n",
            $cat->id_category,
            $cat->name,
            $cat->tipo ?? 'N/A',
            $cat->user_id,
            $cat->tags ?? 'N/A'
        );
    }
    echo "\nTotal: " . $categories->count() . " categorias\n";
}

echo "\n=== CATEGORIAS SUGERIDAS PARA CRIAR ===\n\n";

$suggestedCategories = [
    ['name' => 'Alimentação', 'tipo' => 'gasto', 'icone' => 'fas fa-utensils', 'color' => '#FF6B6B'],
    ['name' => 'Transporte', 'tipo' => 'gasto', 'icone' => 'fas fa-car', 'color' => '#4ECDC4'],
    ['name' => 'Saúde', 'tipo' => 'gasto', 'icone' => 'fas fa-medkit', 'color' => '#95E1D3'],
    ['name' => 'Compras', 'tipo' => 'gasto', 'icone' => 'fas fa-shopping-cart', 'color' => '#F38181'],
    ['name' => 'Beleza', 'tipo' => 'gasto', 'icone' => 'fas fa-heart', 'color' => '#FFA07A'],
    ['name' => 'Telecomunicações', 'tipo' => 'gasto', 'icone' => 'fas fa-phone', 'color' => '#6C5CE7'],
    ['name' => 'Entretenimento', 'tipo' => 'gasto', 'icone' => 'fas fa-gamepad', 'color' => '#A29BFE'],
    ['name' => 'Viagem', 'tipo' => 'gasto', 'icone' => 'fas fa-plane', 'color' => '#74B9FF'],
    ['name' => 'Educação', 'tipo' => 'gasto', 'icone' => 'fas fa-graduation-cap', 'color' => '#55EFC4'],
    ['name' => 'Casa', 'tipo' => 'gasto', 'icone' => 'fas fa-home', 'color' => '#FDCB6E'],
];

foreach ($suggestedCategories as $cat) {
    echo sprintf("✅ %s (Tipo: %s, Ícone: %s)\n", $cat['name'], $cat['tipo'], $cat['icone']);
}
