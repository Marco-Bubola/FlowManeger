<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simular autenticação do usuário 2
$user = App\Models\User::find(2);

if (!$user) {
    echo "Usuário 2 não encontrado!\n";
    exit(1);
}

Auth::login($user);

echo "Usuário autenticado: {$user->name} (ID: {$user->id})\n\n";

// Testar query do ProductIntegration
$query = App\Models\Product::with(['category', 'mercadoLivreProduct'])
    ->where('user_id', Auth::id())
    ->where('stock_quantity', '>', 0);

$count = $query->count();
$products = $query->orderBy('created_at', 'desc')->limit(3)->get();

echo "Produtos com estoque: {$count}\n\n";

echo "Primeiros 3 produtos:\n";
foreach ($products as $product) {
    $validation = $product->isReadyForMercadoLivre();
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: {$product->id}\n";
    echo "Nome: {$product->name}\n";
    echo "Preço: R$ " . number_format($product->price_sale ?? $product->price, 2, ',', '.') . "\n";
    echo "Estoque: {$product->stock_quantity}\n";
    echo "Status ML: " . ($product->mercadoLivreProduct ? "Publicado ({$product->mercadoLivreProduct->status})" : "Não publicado") . "\n";
    echo "\nValidação ML:\n";
    if ($validation['ready']) {
        echo "  ✅ Pronto para publicar!\n";
    } else {
        echo "  ❌ Pendências:\n";
        foreach ($validation['errors'] as $error) {
            echo "     • {$error}\n";
        }
    }
    echo "\n";
}

// Testar AuthService
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Testando conexão ML...\n";
$authService = new App\Services\MercadoLivre\AuthService();
$isConnected = $authService->isConnected(Auth::id());
echo "Conectado ao ML: " . ($isConnected ? "✅ SIM" : "❌ NÃO") . "\n";
