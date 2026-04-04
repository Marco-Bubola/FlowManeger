<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Verifica se o usuário possui uma assinatura ativa (ou é admin).
     * Caso contrário, redireciona para a página de planos.
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin sempre passa
        if ($user->isAdmin()) {
            return $next($request);
        }

        $activeSub = $user->activeSubscription;

        // Se não há assinatura ativa
        if (!$activeSub || !$activeSub->isValid()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Assinatura necessária.',
                    'upgrade_url' => route('subscription.plans'),
                ], 402);
            }
            return redirect()->route('subscription.plans')
                ->with('warning', 'Você precisa de um plano ativo para acessar esta área.');
        }

        // Se planos específicos foram informados, verifica o slug
        if (!empty($plans)) {
            $userPlanSlug = $activeSub->plan->slug ?? 'free';
            if (!in_array($userPlanSlug, $plans)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Seu plano atual não inclui este recurso.',
                        'upgrade_url' => route('subscription.plans'),
                    ], 402);
                }
                return redirect()->route('subscription.plans')
                    ->with('warning', 'Seu plano atual não inclui este recurso. Faça upgrade para continuar.');
            }
        }

        return $next($request);
    }
}
