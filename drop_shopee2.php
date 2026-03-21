<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
DB::statement('DROP TABLE IF EXISTS shopee_orders');
echo "shopee_orders dropped OK\n";
