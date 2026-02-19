<?php
require 'vendor/autoload.php';

// Testar se o autoload encontra a classe
echo "Testando autoload...\n\n";

// Verificar se HasNotifications existe
echo "HasNotifications: " . (trait_exists('App\Traits\HasNotifications') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "AuthService: " . (class_exists('App\Services\MercadoLivre\AuthService') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "ProductService: " . (class_exists('App\Services\MercadoLivre\ProductService') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "Product Model: " . (class_exists('App\Models\Product') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "Category Model: " . (class_exists('App\Models\Category') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "MercadoLivreProduct: " . (class_exists('App\Models\MercadoLivre\MercadoLivreProduct') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "Settings Livewire: " . (class_exists('App\Livewire\MercadoLivre\Settings') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "ProductIntegration: " . (class_exists('App\Livewire\MercadoLivre\ProductIntegration') ? 'EXISTS' : 'NOT FOUND') . "\n";

echo "\n--- Tentando carregar ProductIntegration manualmente ---\n";
try {
    $file = __DIR__ . '/app/Livewire/MercadoLivre/ProductIntegration.php';
    echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
    echo "File size: " . filesize($file) . " bytes\n";
    
    // Tentar incluir diretamente
    require_once $file;
    echo "File included: OK\n";
    echo "Class exists now: " . (class_exists('App\Livewire\MercadoLivre\ProductIntegration') ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    $prev = $e->getPrevious();
    if ($prev) {
        echo "Caused by: " . $prev->getMessage() . "\n";
    }
}
