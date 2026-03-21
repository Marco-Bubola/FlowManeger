<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar mensagens (pós-venda) no Mercado Livre.
 *
 * API referenciada:
 *   GET  /messages/packs/{pack_id}/sellers/{seller_id}  – lista mensagens de um pack
 *   POST /messages/packs/{pack_id}/sellers/{seller_id}  – envia mensagem
 *   GET  /messages/orders/{order_id}                    – mensagens por pedido (simplificado)
 */
class MessageService extends MercadoLivreService
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
    // Mensagens de um pack (set de pedidos) ou pedido
    // ---------------------------------------------------------------

    /**
     * Retorna o histórico de mensagens de um pack/pedido.
     *
     * @param string $packId  pode ser o pack_id ou o order_id
     */
    public function getMessages(string $packId): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'messages' => []];
            }

            $sellerId = $token->ml_user_id;
            $endpoint = "/messages/packs/{$packId}/sellers/{$sellerId}";

            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                return [
                    'success'  => true,
                    'messages' => $data['messages'] ?? [],
                    'paging'   => $data['paging']   ?? [],
                    'pack_id'  => $packId,
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao buscar mensagens.', 'messages' => []];

        } catch (\Exception $e) {
            Log::error('MessageService::getMessages', ['pack_id' => $packId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno ao buscar mensagens.', 'messages' => []];
        }
    }

    // ---------------------------------------------------------------
    // Enviar mensagem
    // ---------------------------------------------------------------

    /**
     * Envia uma mensagem para o comprador em um pack.
     *
     * @param string $packId    pack_id ou order_id
     * @param string $text      texto da mensagem
     * @param array  $attachments  (opcional) lista de fileIds já enviados ao ML
     */
    public function sendMessage(string $packId, string $text, array $attachments = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            if (empty(trim($text))) {
                return ['success' => false, 'message' => 'A mensagem não pode estar vazia.'];
            }

            $sellerId = $token->ml_user_id;
            $payload  = [
                'from'        => ['user_id' => (int)$sellerId, 'email' => null],
                'to'          => [['user_id' => null]],
                'text'        => trim($text),
                'attachments' => $attachments,
            ];

            $response = $this->makeRequest(
                'POST',
                "/messages/packs/{$packId}/sellers/{$sellerId}",
                $payload,
                $token->access_token,
                Auth::id()
            );

            if ($response['success'] ?? false) {
                return ['success' => true, 'message' => 'Mensagem enviada com sucesso!', 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao enviar mensagem.'];

        } catch (\Exception $e) {
            Log::error('MessageService::sendMessage', ['pack_id' => $packId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno ao enviar mensagem.'];
        }
    }

    // ---------------------------------------------------------------
    // Marcar mensagens como lidas
    // ---------------------------------------------------------------

    public function markAsRead(string $packId, array $messageIds): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            $sellerId = $token->ml_user_id;
            $response = $this->makeRequest('PUT', "/messages/packs/{$packId}/sellers/{$sellerId}/unread", [
                'resource_id' => (string)$packId,
                'ids'         => $messageIds,
            ], $token->access_token, Auth::id());

            return ['success' => (bool)($response['success'] ?? false)];

        } catch (\Exception $e) {
            Log::error('MessageService::markAsRead', ['error' => $e->getMessage()]);
            return ['success' => false];
        }
    }
}
