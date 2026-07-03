<?php

namespace App\Console\Commands\MercadoLivre;

use App\Models\MercadoLivreToken;
use App\Services\MercadoLivre\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Renova preventivamente os tokens OAuth do Mercado Livre de todos os
 * usuários conectados, para manter a integração sempre ativa.
 *
 * getActiveToken() já faz refresh preventivo (quando faltam < 30min) e
 * refresh on-demand (quando expirado), então basta tocá-lo por usuário.
 */
class RefreshTokens extends Command
{
    protected $signature = 'ml:refresh-tokens';

    protected $description = 'Renova preventivamente os tokens OAuth do Mercado Livre dos usuários conectados';

    public function handle(AuthService $auth): int
    {
        $tokens = MercadoLivreToken::where('is_active', true)->get();
        $ok = 0;
        $fail = 0;

        foreach ($tokens as $token) {
            try {
                $active = $auth->getActiveToken($token->user_id, true);
                $active ? $ok++ : $fail++;
            } catch (\Throwable $e) {
                $fail++;
                Log::warning('Falha ao renovar token ML', [
                    'user_id' => $token->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Tokens ML verificados. OK: {$ok} · Falhas: {$fail}");

        return self::SUCCESS;
    }
}
