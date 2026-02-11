<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$product = Product::find(679);

if ($product) {
    echo "Produto ID: {$product->id}\n";
    echo "Nome: {$product->name}\n";
    echo "Preço: {$product->price}\n";
    echo "Estoque: {$product->stock}\n";
    echo "Stock (int): " . (int)$product->stock . "\n";
    echo "Stock === 0: " . ($product->stock === 0 ? 'true' : 'false') . "\n";
    echo "Stock == null: " . ($product->stock == null ? 'true' : 'false') . "\n";
    echo "empty(stock): " . (empty($product->stock) ? 'true' : 'false') . "\n";
} else {
    echo "Produto não encontrado\n";
}
