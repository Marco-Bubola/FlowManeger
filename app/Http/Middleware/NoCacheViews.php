<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheViews
{
    /**
     * Desabilita cache HTTP para rotas que renderizam Blade views
     * para garantir que atualizações sejam vistas imediatamente em produção.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Rotas de renderização de Blade (não são API/JSON)
        if ($this->isViewRoute($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('ETag', md5(microtime()));
        }

        return $response;
    }

    /**
     * Identifica rotas que renderizam Blade views
     */
    private function isViewRoute(Request $request): bool
    {
        $path = $request->path();
        
        // Inclua padrões de rotas que servem Blade
        $viewPatterns = [
            'sales',
            'products',
            'clients',
            'dashboard',
            'cashbook',
            'categories',
            'reports',
        ];

        foreach ($viewPatterns as $pattern) {
            if (str_starts_with($path, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
