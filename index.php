<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

if (PHP_SAPI === 'cli') {
    exit(0);
}

define('LARAVEL_START', microtime(true));

$localBase = __DIR__;

if (file_exists($localMaintenance = $localBase . '/../storage/framework/maintenance.php')) {
    require $localMaintenance;
}

if (is_file($localBase . '/../vendor/autoload.php') && is_file($localBase . '/../bootstrap/app.php')) {
    require $localBase . '/../vendor/autoload.php';

    /** @var Application $app */
    $app = require_once $localBase . '/../bootstrap/app.php';
    $app->handleRequest(Request::capture());
    exit(0);
}

$candidates = [
    // App em pasta irmã da webroot
    __DIR__ . '/../flowmaneger/public/index.php',
    __DIR__ . '/../flowmanager/public/index.php',
    __DIR__ . '/../FlowManeger/public/index.php',
    __DIR__ . '/../FlowManager/public/index.php',
    // App em subpasta da webroot
    __DIR__ . '/flowmaneger/public/index.php',
    __DIR__ . '/flowmanager/public/index.php',
    __DIR__ . '/FlowManeger/public/index.php',
    __DIR__ . '/FlowManager/public/index.php',
];

foreach ($candidates as $target) {
    if (is_file($target)) {
        require $target;
        exit(0);
    }
}

http_response_code(500);
header('Content-Type: text/plain; charset=utf-8');

echo "Erro de configuração: não foi possível localizar o front controller da aplicação.\n\n";
echo "Caminhos verificados:\n";

foreach ($candidates as $path) {
    echo "- {$path}\n";
}

echo "\nDica: se sua webroot já for /home/USUARIO/laravel_xxx/public, a app deve existir em /home/USUARIO/laravel_xxx (com vendor e bootstrap).\n";
exit(1);
