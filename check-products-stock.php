<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Verificar produtos para user_id = 2
$userId = 2;

$totalProducts = DB::table('products')->where('user_id', $userId)->count();
$withStock = DB::table('products')->where('user_id', $userId)->where('stock_quantity', '>', 0)->count();
$withoutStock = DB::table('products')->where('user_id', $userId)->where('stock_quantity', '<=', 0)->count();

echo "Produtos do usuário {$userId}:\n";
echo "  Total: {$totalProducts}\n";
echo "  Com estoque: {$withStock}\n";
echo "  Sem estoque: {$withoutStock}\n\n";

// Mostrar alguns produtos com estoque
$products = DB::table('products')
    ->where('user_id', $userId)
    ->where('stock_quantity', '>', 0)
    ->select('id', 'name', 'price', 'stock_quantity', 'category_id', 'barcode', 'brand', 'condition')
    ->limit(5)
    ->get();

echo "Primeiros 5 produtos COM estoque:\n";
foreach ($products as $product) {
    echo "  #{$product->id}: {$product->name}\n";
    echo "    Preço: R$ {$product->price} | Estoque: {$product->stock_quantity} | Categoria: {$product->category_id}\n";
    echo "    Barcode: " . ($product->barcode ?: 'SEM EAN') . " | Marca: " . ($product->brand ?: 'SEM MARCA') . " | Condição: " . ($product->condition ?: 'SEM CONDIÇÃO') . "\n";
}

// Verificar se tem categorias
$categories = DB::table('categories')->where('user_id', $userId)->count();
echo "\nCategorias do usuário: {$categories}\n";
