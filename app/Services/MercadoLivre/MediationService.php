<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar mediações (devoluções / disputas) no Mercado Livre.
 *
 * API referenciada:
 *   GET  /users/{seller_id}/claims                  – lista reclamações/disputas
 *   GET  /claims/{claim_id}                         – detalhes de uma mediação
 *   POST /claims/{claim_id}/messages                – enviar mensagem na mediação
 *   GET  /claims/{claim_id}/messages                – histórico de mensagens
 */
class MediationService extends MercadoLivreService
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
    // Listagem de mediações/reclamações
    // ---------------------------------------------------------------

    /**
     * Lista as reclamações/mediações do vendedor com filtros opcionais.
     *
     * @param array $filters  keys: status (opened|closed), limit, offset, resource_type (order|shipment)
     */
    public function getClaims(array $filters = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'claims' => [], 'paging' => []];
            }

            $sellerId = $token->ml_user_id;
            $params = array_merge([
                'role'   => 'respondent', // seller role
                'limit'  => 25,
                'offset' => 0,
            ], $filters);

            $query    = http_build_query($params);
            $endpoint = "/users/{$sellerId}/claims?{$query}";

            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                return [
                    'success' => true,
                    'claims'  => $data['claims']  ?? ($data['data'] ?? []),
                    'paging'  => $data['paging']  ?? [],
                    'total'   => $data['paging']['total'] ?? count($data['claims'] ?? $data['data'] ?? []),
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Erro ao buscar mediações.',
                'claims'  => [],
                'paging'  => [],
            ];
        } catch (\Throwable $e) {
            Log::error('[MediationService] getClaims: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'claims' => [], 'paging' => []];
        }
    }

    /**
     * Retorna os detalhes de uma mediação específica.
     */
    public function getClaimDetails(int $claimId): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $response = $this->makeRequest('GET', "/claims/{$claimId}", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return ['success' => true, 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro.', 'data' => null];
        } catch (\Throwable $e) {
            Log::error('[MediationService] getClaimDetails: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Envia uma mensagem em uma mediação ativa.
     */
    public function sendMessage(int $claimId, string $text): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            $payload = [
                'message' => $text,
                'author'  => ['type' => 'users', 'id' => $token->ml_user_id],
            ];

            $response = $this->makeRequest('POST', "/claims/{$claimId}/messages", $payload, $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return ['success' => true, 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao enviar mensagem.'];
        } catch (\Throwable $e) {
            Log::error('[MediationService] sendMessage: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Retorna as mensagens de uma mediação.
     */
    public function getClaimMessages(int $claimId): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'messages' => []];
            }

            $response = $this->makeRequest('GET', "/claims/{$claimId}/messages", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                return [
                    'success'  => true,
                    'messages' => $data['messages'] ?? ($data['data'] ?? []),
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro.', 'messages' => []];
        } catch (\Throwable $e) {
            Log::error('[MediationService] getClaimMessages: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'messages' => []];
        }
    }
}
