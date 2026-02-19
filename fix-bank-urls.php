<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Atualizar URLs das imagens de bancos
$updated = DB::table('banks')
    ->where('caminho_icone', 'LIKE', '%http://127.0.0.1:8000/%')
    ->update([
        'caminho_icone' => DB::raw('REPLACE(caminho_icone, "http://127.0.0.1:8000/", "")')
    ]);

echo "✓ {$updated} registros de bancos atualizados\n";

// Verificar alguns registros
$banks = DB::table('banks')
    ->select('name', 'caminho_icone')
    ->limit(5)
    ->get();

echo "\nPrimeiros 5 bancos:\n";
foreach ($banks as $bank) {
    echo "  - {$bank->name}: {$bank->caminho_icone}\n";
}
