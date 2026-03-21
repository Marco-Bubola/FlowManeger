<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pubs = \App\Models\MlPublication::select('id', 'title', 'ml_attributes', 'pictures')->get();
foreach ($pubs as $p) {
    echo "=== PUB #{$p->id}: {$p->title} ===" . PHP_EOL;
    $raw = $p->getRawOriginal('ml_attributes');
    echo "ml_attributes raw type: " . gettype($raw) . PHP_EOL;
    echo "ml_attributes raw (first 200): " . substr((string)$raw, 0, 200) . PHP_EOL;
    echo "ml_attributes cast type: " . gettype($p->ml_attributes) . PHP_EOL;
    echo "ml_attributes count: " . (is_array($p->ml_attributes) ? count($p->ml_attributes) : 'NOT ARRAY') . PHP_EOL;
    
    $rawPics = $p->getRawOriginal('pictures');
    echo "pictures raw type: " . gettype($rawPics) . PHP_EOL;
    echo "pictures raw (first 200): " . substr((string)$rawPics, 0, 200) . PHP_EOL;
    echo PHP_EOL;
}
