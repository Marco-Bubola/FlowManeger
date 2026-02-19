<?php
require 'vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MlPublication;

try {
    $pub = MlPublication::create([
        'ml_item_id' => 'TEST_' . uniqid(),
        'title' => 'Test Publication',
        'price' => 10.00,
        'publication_type' => 'simple',
        'listing_type' => 'gold',
        'status' => 'pending',
        'user_id' => 2,
    ]);
    
    echo "✅ Sucesso! Publicação criada com:\n";
    echo "ID: " . $pub->id . "\n";
    echo "ml_item_id: " . $pub->ml_item_id . "\n";
    echo "Status: " . $pub->status . "\n";
    echo "Created at: " . $pub->created_at . "\n";
    
} catch (\Exception $e) {
    echo "❌ Erro ao criar publicação:\n";
    echo $e->getMessage() . "\n";
    if (method_exists($e, 'getPrevious')) {
        echo "Erro anterior: " . $e->getPrevious() . "\n";
    }
}
?>
