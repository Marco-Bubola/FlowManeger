<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar mediações (devoluções / disputas) no Mercado Livre.
 *
 * API referenciada:
 *   GET  /post-purchase/v1/claims/search?player_role=respondent  – lista reclamações
 *   GET  /post-purchase/v1/claims/{claim_id}                      – detalhes
 *   GET  /post-purchase/v1/claims/{claim_id}/messages             – histórico de mensagens
 *   POST /post-purchase/v1/claims/{claim_id}/actions/send-message – enviar mensagem
 *
 * Obs.: makeRequest lança exceção em erro HTTP e devolve o corpo JSON cru em sucesso.
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

            $params = array_filter([
                'player_role' => 'respondent', // vendedor
                'status'      => $filters['status'] ?? null,
                'limit'       => $filters['limit'] ?? 25,
                'offset'      => $filters['offset'] ?? 0,
            ], fn($v) => $v !== null && $v !== '');

            $query    = http_build_query($params);
            $endpoint = "/post-purchase/v1/claims/search?{$query}";

            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            $claims = $response['data'] ?? [];
            return [
                'success' => true,
                'claims'  => $claims,
                'paging'  => $response['paging'] ?? [],
                'total'   => (int)($response['paging']['total'] ?? count($claims)),
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

            $response = $this->makeRequest('GET', "/post-purchase/v1/claims/{$claimId}", [], $token->access_token, Auth::id());

            return ['success' => true, 'data' => $response];
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
                'receiver_role' => 'complainant',
                'message'       => trim($text),
            ];

            $response = $this->makeRequest(
                'POST',
                "/post-purchase/v1/claims/{$claimId}/actions/send-message",
                $payload,
                $token->access_token,
                Auth::id()
            );

            return ['success' => true, 'data' => $response];
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

            $response = $this->makeRequest('GET', "/post-purchase/v1/claims/{$claimId}/messages", [], $token->access_token, Auth::id());

            // A API devolve uma lista de mensagens (ou, eventualmente, {data|messages: [...]})
            $messages = array_is_list($response) ? $response : ($response['messages'] ?? ($response['data'] ?? []));
            usort($messages, fn($a, $b) => strcmp((string)($a['date_created'] ?? ''), (string)($b['date_created'] ?? '')));

            return [
                'success'  => true,
                'messages' => $messages,
            ];
        } catch (\Throwable $e) {
            Log::error('[MediationService] getClaimMessages: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'messages' => []];
        }
    }
}
