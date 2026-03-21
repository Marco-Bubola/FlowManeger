<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select("SHOW COLUMNS FROM products WHERE Field = 'id'");
print_r($cols);
