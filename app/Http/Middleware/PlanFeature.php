<?php

namespace App\Http\Middleware;

use App\Support\PlanGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Mostra "faz parte do plano X" no lugar das telas que o plano da pessoa não inclui (Admin › Planos liga). */
class PlanFeature
{
    public function handle(Request $request, Closure $next): Response
    {
        $feature = PlanGate::missing($request->user(), $request->route()?->getName());
        if (! $feature) {
            return $next($request);
        }
        if ($request->expectsJson() || ! $request->isMethod('GET')) {
            return response()->json([
                'error' => 'Seu plano não inclui ' . PlanGate::FEATURES[$feature][0] . '.',
                'upgrade_url' => route('subscription.plans'),
            ], 402);
        }

        return response()->view('plans.locked', [
            'feature' => $feature,
            'info' => PlanGate::FEATURES[$feature],
            'plan' => PlanGate::planWith($feature),
            'current' => $request->user()->plan(),
        ]); // 200: a navegação do Livewire (wire:navigate) mostra a página normalmente
    }
}
