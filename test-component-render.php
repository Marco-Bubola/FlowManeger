<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Autenticar usuário 2
$user = App\Models\User::find(2);

if (!$user) {
    echo "❌ Usuário 2 não encontrado\n";
    exit(1);
}

Auth::login($user);
echo "✅ Usuário autenticado: {$user->name}\n";

// Criar instância do componente como se fosse uma requisição real
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Testando renderização do componente...\n\n";

try {
    $component = new App\Livewire\MercadoLivre\ProductIntegration();
    
    // Chamar mount
    echo "1. Chamando mount()...\n";
    $component->mount();
    echo "   ✅ Mount executado\n";
    echo "   - isConnected: " . ($component->isConnected ? 'SIM' : 'NÃO') . "\n";
    
    // Chamar render
    echo "\n2. Chamando render()...\n";
    $result = $component->render();
    echo "   ✅ Render executado\n";
    echo "   - Tipo de retorno: " . get_class($result) . "\n";
    
    // Pegar dados passados para a view
    $data = $result->getData();
    echo "   - Produtos: " . $data['products']->count() . " (Total: " . $data['products']->total() . ")\n";
    echo "   - Categorias: " . $data['categories']->count() . "\n";
    
    echo "\n3. Amostra dos produtos:\n";
    foreach ($data['products']->take(3) as $product) {
        $validation = $product->isReadyForMercadoLivre();
        echo "   • #{$product->id}: {$product->name}\n";
        echo "     Estoque: {$product->stock_quantity} | Pronto: " . ($validation['ready'] ? '✅' : '❌') . "\n";
    }
    
    echo "\n✅ COMPONENTE FUNCIONANDO PERFEITAMENTE!\n";
    echo "   O problema é que você precisa estar LOGADO no navegador.\n";
    
} catch (\Throwable $e) {
    echo "❌ ERRO:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n   Stack:\n";
    foreach (array_slice($e->getTrace(), 0, 5) as $trace) {
        if (isset($trace['file'])) {
            echo "   - " . basename($trace['file']) . ":" . ($trace['line'] ?? '?') . "\n";
        }
    }
}

// Verificar logs
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Últimas 10 linhas do log:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "(Log vazio)\n";
}
