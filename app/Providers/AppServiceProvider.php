<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observadores de modelos
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        
        // Forçar HTTPS apenas quando a REQUISIÇÃO ATUAL veio por HTTPS (ngrok, proxy).
        // Não forçar por causa do APP_URL: no localhost (HTTP) isso geraria URLs https://
        // e o servidor PHP não suporta SSL → "Unsupported SSL request" e recursos quebrados.
        if ($this->app->environment('local', 'development')) {
            $request = request();
            if ($request->header('X-Forwarded-Proto') === 'https' ||
                $request->header('X-Forwarded-Ssl') === 'on' ||
                $request->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
                $request->server('HTTPS') === 'on' ||
                $request->isSecure()) {
                URL::forceScheme('https');
            }
        }
        
        // Em produção, sempre forçar HTTPS
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
