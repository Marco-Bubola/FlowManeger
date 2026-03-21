<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para consultar reputação e métricas do vendedor no Mercado Livre.
 *
 * API referenciada:
 *   GET /users/{user_id}                    – dados + reputação
 *   GET /users/{user_id}/selling_reputation – reputação de vendedor
 *   GET /users/{user_id}/feedback_summary   – resumo de feedbacks
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

            if ($response['success'] ?? false) {
                return [
                    'success' => true,
                    'data'    => $response['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Erro ao buscar dados do vendedor.',
                'data'    => null,
            ];
        } catch (\Throwable $e) {
            Log::error('[ReputationService] getSellerData: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Retorna o resumo de feedbacks do vendedor.
     */
    public function getFeedbackSummary(): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $sellerId = $token->ml_user_id;
            $response = $this->makeRequest('GET', "/users/{$sellerId}/feedback_summary", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return [
                    'success' => true,
                    'data'    => $response['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Erro ao buscar feedbacks.',
                'data'    => null,
            ];
        } catch (\Throwable $e) {
            Log::error('[ReputationService] getFeedbackSummary: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
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

            if ($response['success'] ?? false) {
                $rep = $response['data']['seller_reputation'] ?? [];
                return [
                    'success' => true,
                    'data'    => [
                        'metrics'         => $rep['metrics']         ?? [],
                        'transactions'    => $rep['transactions']    ?? [],
                        'level_id'        => $rep['level_id']        ?? null,
                        'power_seller'    => $rep['power_seller_status'] ?? null,
                    ],
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro.', 'data' => null];
        } catch (\Throwable $e) {
            Log::error('[ReputationService] getSellerMetrics: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }
}
