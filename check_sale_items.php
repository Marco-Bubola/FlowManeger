<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$items = \App\Models\SaleItem::where('sale_id', 160)->get();
foreach ($items as $i) {
    echo 'id='.$i->id.' price='.$i->price.' price_sale='.$i->price_sale.' qty='.$i->quantity.PHP_EOL;
    echo '  cols: '.implode(', ', array_keys($i->toArray())).PHP_EOL;
}

$items2 = \App\Models\SaleItem::where('sale_id', 151)->get();
echo "--- Venda 151 ---".PHP_EOL;
foreach ($items2 as $i) {
    echo 'id='.$i->id.' price='.$i->price.' price_sale='.$i->price_sale.' qty='.$i->quantity.PHP_EOL;
}
