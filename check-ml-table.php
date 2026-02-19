<?php
require 'vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$db = $app->make('db');

echo "Estrutura da tabela ml_publications:\n";
echo str_repeat('=', 60) . "\n";

$columns = $db->select('SHOW COLUMNS FROM ml_publications');
foreach ($columns as $col) {
    echo $col->Field . " - " . $col->Type . " - Null: " . ($col->Null === 'YES' ? 'YES' : 'NO') . "\n";
}

echo "\n\nMostrando tipos e tamanhos especialmente da coluna 'status':\n";
echo str_repeat('=', 60) . "\n";

foreach ($columns as $col) {
    if ($col->Field === 'status') {
        echo "Campo: " . $col->Field . "\n";
        echo "Tipo: " . $col->Type . "\n";
        echo "Nullable: " . $col->Null . "\n";
        echo "Default: " . $col->Default . "\n";
        echo "Key: " . $col->Key . "\n";
        echo "Extra: " . $col->Extra . "\n";
    }
}
?>
