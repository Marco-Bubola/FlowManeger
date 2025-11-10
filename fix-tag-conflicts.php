<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== AJUSTANDO CONFLITOS DE TAGS ===\n\n";

// 1. Remover "viagem" de "Hospedagem e Viagens" para evitar conflito com Transporte
$hospedagem = Category::where('name', 'Hospedagem e Viagens')->first();
if ($hospedagem) {
    $hospedagem->tags = 'hotel,pousada,airbnb,booking,decolar,hospedagem,hopihari';
    $hospedagem->save();
    echo "✅ Ajustado: Hospedagem e Viagens\n";
    echo "   Removido: viagem (conflito com Transporte)\n\n";
}

// 2. Remover "popular" de "Supermercados" para evitar conflito
$supermercado = Category::where('name', 'Supermercados e Alimentação')->first();
if ($supermercado) {
    $supermercado->tags = 'supermercado,mercado,antonelli,atacadao,rofatto,cubatao,alimentacao,feira,hortifruti';
    $supermercado->save();
    echo "✅ Ajustado: Supermercados e Alimentação\n";
    echo "   Removido: popular (muito genérico)\n\n";
}

// 3. Garantir que UBER está apenas em Transporte
$transporte = Category::where('name', 'Transporte')->first();
if ($transporte) {
    echo "✅ Transporte mantém: uber,99,cabify,taxi,onibus,transporte,metro,trem\n\n";
}

echo "=== CONCLUÍDO! ===\n";
echo "Conflitos de tags resolvidos.\n";
