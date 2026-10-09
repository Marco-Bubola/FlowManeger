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
 *   GET  /orders/{order_id}                             – descobre o comprador (buyer.id)
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
     * @param string $packId  pode ser o pack_id ou o order_id (pedido sem pack)
     */
    public function getMessages(string $packId): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'messages' => []];
            }

            $sellerId = $token->ml_user_id;
            $endpoint = "/messages/packs/{$packId}/sellers/{$sellerId}?tag=post_sale";

            // makeRequest lança exceção em erro HTTP; aqui a resposta é o corpo JSON cru
            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            $messages = $response['messages'] ?? [];
            // Ordena do mais antigo para o mais recente (visual de chat)
            usort($messages, function ($a, $b) {
                $da = $a['message_date']['created'] ?? $a['date_created'] ?? '';
                $db = $b['message_date']['created'] ?? $b['date_created'] ?? '';
                return strcmp((string)$da, (string)$db);
            });

            return [
                'success'   => true,
                'messages'  => $messages,
                'paging'    => $response['paging'] ?? [],
                'pack_id'   => $packId,
                'seller_id' => (int)$sellerId,
            ];

        } catch (\Exception $e) {
            Log::error('MessageService::getMessages', ['pack_id' => $packId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro ao buscar mensagens: ' . $e->getMessage(), 'messages' => []];
        }
    }

    /**
     * Descobre o ID do comprador de um pack/pedido.
     * Para pedido sem pack, o pack_id é o próprio order_id (GET /orders/{id}).
     * Se for um pack real, busca o primeiro pedido do pack (GET /packs/{id}).
     */
    public function getBuyerId(string $packId): ?int
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            $order = $this->makeRequest('GET', "/orders/{$packId}", [], $token->access_token, Auth::id());
            if (!empty($order['buyer']['id'])) {
                return (int)$order['buyer']['id'];
            }
        } catch (\Exception $e) {
            Log::info('MessageService::getBuyerId - não é order_id, tentando como pack', ['pack_id' => $packId]);
        }

        try {
            $pack    = $this->makeRequest('GET', "/packs/{$packId}", [], $token->access_token, Auth::id());
            $orderId = $pack['orders'][0]['id'] ?? null;
            if (!empty($pack['buyer']['id'])) {
                return (int)$pack['buyer']['id'];
            }
            if ($orderId) {
                $order = $this->makeRequest('GET', "/orders/{$orderId}", [], $token->access_token, Auth::id());
                return !empty($order['buyer']['id']) ? (int)$order['buyer']['id'] : null;
            }
        } catch (\Exception $e) {
            Log::warning('MessageService::getBuyerId', ['pack_id' => $packId, 'error' => $e->getMessage()]);
        }

        return null;
    }

    // ---------------------------------------------------------------
    // Enviar mensagem
    // ---------------------------------------------------------------

    /**
     * Envia uma mensagem para o comprador em um pack.
     *
     * @param string   $packId   pack_id ou order_id
     * @param string   $text     texto da mensagem
     * @param int|null $buyerId  ID do comprador (se nulo, é buscado em /orders/{pack_id})
     */
    public function sendMessage(string $packId, string $text, ?int $buyerId = null): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            if (empty(trim($text))) {
                return ['success' => false, 'message' => 'A mensagem não pode estar vazia.'];
            }

            $buyerId = $buyerId ?: $this->getBuyerId($packId);
            if (!$buyerId) {
                return ['success' => false, 'message' => 'Não foi possível identificar o comprador deste pedido.'];
            }

            $sellerId = (int)$token->ml_user_id;
            $payload  = [
                'from' => ['user_id' => $sellerId],
                'to'   => ['user_id' => (int)$buyerId],
                'text' => trim($text),
            ];

            // makeRequest lança exceção em erro HTTP; chegar aqui significa sucesso
            $response = $this->makeRequest(
                'POST',
                "/messages/packs/{$packId}/sellers/{$sellerId}?tag=post_sale",
                $payload,
                $token->access_token,
                Auth::id()
            );

            return ['success' => true, 'message' => 'Mensagem enviada com sucesso!', 'data' => $response];

        } catch (\Exception $e) {
            Log::error('MessageService::sendMessage', ['pack_id' => $packId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()];
        }
    }
}
