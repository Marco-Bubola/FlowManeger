<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para consultar reputação e métricas do vendedor no Mercado Livre.
 *
 * API referenciada:
 *   GET /users/{user_id}                    – dados + reputação
 *   (avaliações vêm em seller_reputation.transactions.ratings – frações 0-1)
 */
class ReputationService extends MercadoLivreService
{
    protected function getToken(): ?\App\Models\MercadoLivreToken
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }
        return (new AuthService())->getActiveToken($userId);
    }

    // ---------------------------------------------------------------
    // Reputação geral do vendedor
    // ---------------------------------------------------------------

    /**
     * Retorna dados completos do vendedor (inclui seller_reputation).
     */
    public function getSellerData(): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $sellerId = $token->ml_user_id;
            $response = $this->makeRequest('GET', "/users/{$sellerId}", [], $token->access_token, Auth::id());

            // makeRequest lança exceção em erro HTTP; aqui a resposta é o corpo JSON cru
            return [
                'success' => true,
                'data'    => $response,
            ];
        } catch (\Throwable $e) {
            Log::error('[ReputationService] getSellerData: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Retorna o resumo de avaliações do vendedor.
     * O endpoint /users/{id}/feedback_summary não existe: os dados vêm de
     * /users/{id} → seller_reputation.transactions.ratings (frações 0-1).
     */
    public function getFeedbackSummary(): array
    {
        $res = $this->getSellerData();
        if (!($res['success'] ?? false)) {
            return $res;
        }

        $tx      = $res['data']['seller_reputation']['transactions'] ?? [];
        $ratings = $tx['ratings'] ?? [];

        return [
            'success' => true,
            'data'    => [
                'positive'  => (float)($ratings['positive'] ?? 0),
                'negative'  => (float)($ratings['negative'] ?? 0),
                'neutral'   => (float)($ratings['neutral'] ?? 0),
                'completed' => (int)($tx['completed'] ?? 0),
                'canceled'  => (int)($tx['canceled'] ?? 0),
                'total'     => (int)($tx['total'] ?? 0),
                'period'    => $tx['period'] ?? null,
            ],
        ];
    }

    /**
     * Retorna as métricas de cancelamento / atrasos / reclamações.
     */
    public function getSellerMetrics(): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $sellerId = $token->ml_user_id;
            $response = $this->makeRequest('GET', "/users/{$sellerId}", [], $token->access_token, Auth::id());

            $rep = $response['seller_reputation'] ?? [];
            return [
                'success' => true,
                'data'    => [
                    'metrics'      => $rep['metrics']      ?? [],
                    'transactions' => $rep['transactions'] ?? [],
                    'level_id'     => $rep['level_id']     ?? null,
                    'power_seller' => $rep['power_seller_status'] ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('[ReputationService] getSellerMetrics: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }
}
