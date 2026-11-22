<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/** Entrada do robô na /robo-api: "Authorization: Bearer <chave>" (a chave sai de /robo/conectar, já logado). */
class RoboToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $chave = (string) $request->bearerToken();
        $linha = strlen($chave) >= 40 ? DB::table('robo_tokens')->where('token_hash', hash('sha256', $chave))->first() : null;
        $user = $linha ? User::find($linha->user_id) : null;
        if (! $user) {
            return response()->json(['erro' => 'Robô não conectado. Conecte de novo nas Configurações do robô.'], 401);
        }
        if (! $linha->last_used_at || now()->diffInMinutes($linha->last_used_at, true) >= 10) {
            DB::table('robo_tokens')->where('id', $linha->id)->update(['last_used_at' => now()]);
        }
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
