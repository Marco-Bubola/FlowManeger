<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
DB::statement('DROP TABLE IF EXISTS shopee_publication_products');
DB::statement('DROP TABLE IF EXISTS shopee_publications');
echo "Tables dropped OK\n";
