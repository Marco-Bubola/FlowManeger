<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confiar em todos os proxies (ngrok, cloudflare, etc) em desenvolvimento
        $middleware->trustProxies(at: '*');

        // Aliases de middleware personalizados
        $middleware->alias([
            'admin'        => \App\Http\Middleware\EnsureAdmin::class,
            'portal.auth'  => \App\Http\Middleware\ClientPortalMiddleware::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
