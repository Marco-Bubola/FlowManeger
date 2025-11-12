<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "=== ATUALIZANDO TAGS DAS CATEGORIAS ===\n\n";

// Mapeamento de categorias existentes para tags sugeridas
$categoryTags = [
    'Supermercados e Alimentação' => 'supermercado,mercado,antonelli,atacadao,popular,rofatto,cubatao,alimentacao,feira,hortifruti',
    'Bares e Restaurantes' => 'bar,restaurante,beer,burger,touro,tuttibom,acaiteria,lanchonete,comitiva,aylton,sosbeer',
    'Combustíveis e Postos' => 'posto,combustivel,gasolina,etanol,shell,frogpay,abastecimento,arena,ipiranga',
    'Mecânico' => 'mecanico,pneus,borracharia,oficina,jsrosa,manutencao',
    'Compras e Beleza' => 'compras,beleza,loja,magazine,shopping,tabacaria',
    'Compras Online' => 'shopee,mercadolivre,netshoes,online,ecommerce,internet',
    'Eudora & boticario' => 'eudora,boticario,natura,cosmeticos,perfumaria',
    'Farmácias e Saúde' => 'farmacia,drogaria,pharma,saude,remedio,drogasil,pacheco',
    'streaming' => 'netflix,spotify,prime,disney,hbo,streaming,assinatura',
    'Hospedagem e Viagens' => 'hotel,pousada,airbnb,booking,decolar,viagem,hospedagem,hopihari',
    'Academia' => 'academia,gym,fitness,smartfit,treino,musculacao',
    'beers/maco' => 'beer,cerveja,bebida,bar,choperia,maco',
    'Sorveteria' => 'sorveteria,sorvete,acai,geladeria',
];

$updated = 0;
$notFound = [];

foreach ($categoryTags as $categoryName => $tags) {
    $category = Category::where('type', 'transaction')
        ->where('name', $categoryName)
        ->where('is_active', 1)
        ->first();

    if ($category) {
        $category->tags = $tags;
        $category->save();
        echo "✅ Atualizado: {$categoryName}\n";
        echo "   Tags: {$tags}\n\n";
        $updated++;
    } else {
        $notFound[] = $categoryName;
    }
}

echo "\n=== RESUMO ===\n";
echo "Categorias atualizadas: {$updated}\n";

if (!empty($notFound)) {
    echo "\nCategorias não encontradas:\n";
    foreach ($notFound as $name) {
        echo "  ❌ {$name}\n";
    }
}

echo "\n=== CRIANDO NOVAS CATEGORIAS ESSENCIAIS ===\n\n";

// Verificar qual user_id usar (pegar do primeiro registro)
$existingCategory = Category::where('type', 'transaction')->first();
$userId = $existingCategory ? $existingCategory->user_id : 2;

$newCategories = [
    [
        'name' => 'Transporte',
        'desc_category' => 'Uber, táxi, ônibus, transporte público',
        'tipo' => 'gasto',
        'icone' => 'fas fa-car',
        'hexcolor_category' => '#4ECDC4',
        'tags' => 'uber,99,cabify,taxi,onibus,transporte,metro,trem',
        'user_id' => $userId,
        'type' => 'transaction',
        'is_active' => 1,
    ],
    [
        'name' => 'Educação',
        'desc_category' => 'Cursos, livros, material escolar',
        'tipo' => 'gasto',
        'icone' => 'fas fa-graduation-cap',
        'hexcolor_category' => '#55EFC4',
        'tags' => 'curso,educacao,livro,escola,faculdade,udemy,alura',
        'user_id' => $userId,
        'type' => 'transaction',
        'is_active' => 1,
    ],
    [
        'name' => 'Casa e Moradia',
        'desc_category' => 'Aluguel, condomínio, contas da casa',
        'tipo' => 'gasto',
        'icone' => 'fas fa-home',
        'hexcolor_category' => '#FDCB6E',
        'tags' => 'aluguel,condominio,iptu,luz,agua,gas,casa,moradia',
        'user_id' => $userId,
        'type' => 'transaction',
        'is_active' => 1,
    ],
    [
        'name' => 'Lazer e Entretenimento',
        'desc_category' => 'Cinema, teatro, parques, diversão',
        'tipo' => 'gasto',
        'icone' => 'fas fa-gamepad',
        'hexcolor_category' => '#A29BFE',
        'tags' => 'cinema,teatro,parque,diversao,lazer,entretenimento',
        'user_id' => $userId,
        'type' => 'transaction',
        'is_active' => 1,
    ],
];

foreach ($newCategories as $catData) {
    // Verificar se já existe
    $exists = Category::where('name', $catData['name'])
        ->where('type', 'transaction')
        ->where('user_id', $userId)
        ->exists();

    if (!$exists) {
        Category::create($catData);
        echo "✅ Criada: {$catData['name']}\n";
        echo "   Tags: {$catData['tags']}\n\n";
    } else {
        echo "⚠️  Já existe: {$catData['name']}\n\n";
    }
}

echo "\n=== CONCLUÍDO! ===\n";
echo "As categorias foram atualizadas e novas foram criadas.\n";
echo "A categorização automática agora deve funcionar melhor!\n";
