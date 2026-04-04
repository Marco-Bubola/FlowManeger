<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPortalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('portal')->check()) {
            return redirect()->route('portal.login')
                ->with('error', 'Faça login para acessar o portal.');
        }

        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        if (! $client->portal_active) {
            Auth::guard('portal')->logout();
            return redirect()->route('portal.login')
                ->with('error', 'Seu acesso ao portal foi desativado. Entre em contato com o vendedor.');
        }

        if (
            $client->needsPortalOnboarding()
            && ! $request->routeIs('portal.profile')
            && ! $request->routeIs('portal.profile.update')
            && ! $request->routeIs('portal.logout')
        ) {
            return redirect()->route('portal.profile')
                ->with('error', 'Antes de continuar, defina sua nova senha e complete seu cadastro.');
        }

        return $next($request);
    }
}
