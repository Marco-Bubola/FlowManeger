<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar promoções de vendedor no Mercado Livre.
 *
 * API referenciada:
 *   GET  /seller-promotions/search?seller_id={id}&type={type}  – lista promoções
 *   GET  /seller-promotions/{promotion_id}                      – detalhes
 *   PUT  /seller-promotions/{promotion_id}/items               – adicionar itens
 *   POST /seller-promotions                                     – criar promoção
 */
class PromotionService extends MercadoLivreService
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
    // Listagem de promoções
    // ---------------------------------------------------------------

    /**
     * Lista promoções do vendedor.
     *
     * @param array $filters  keys: type (DEAL, LIGHTNING_DEAL, BRAND_PROMO), status (started, stopped, finished), limit, offset
     */
    public function getPromotions(array $filters = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'promotions' => [], 'paging' => []];
            }

            $sellerId = $token->ml_user_id;
            $params   = array_merge([
                'seller_id' => $sellerId,
                'limit'     => 20,
                'offset'    => 0,
            ], array_filter($filters));

            $query    = http_build_query($params);
            $endpoint = "/seller-promotions/search?{$query}";

            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                // A API pode retornar diretamente um array ou {'results':[], 'paging':{}}
                $promotions = $data['results'] ?? (is_array($data) && !isset($data['id']) ? $data : []);
                return [
                    'success'    => true,
                    'promotions' => $promotions,
                    'paging'     => $data['paging'] ?? [],
                    'total'      => $data['paging']['total'] ?? count($promotions),
                ];
            }

            return [
                'success'    => false,
                'message'    => $response['message'] ?? 'Erro ao buscar promoções.',
                'promotions' => [],
                'paging'     => [],
            ];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotions: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'promotions' => [], 'paging' => []];
        }
    }

    /**
     * Retorna detalhes de uma promoção específica.
     */
    public function getPromotion(string $promotionId): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $response = $this->makeRequest('GET', "/seller-promotions/{$promotionId}", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return ['success' => true, 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro.', 'data' => null];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotion: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Retorna os itens participantes de uma promoção.
     */
    public function getPromotionItems(string $promotionId, int $limit = 50, int $offset = 0): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'items' => []];
            }

            $endpoint = "/seller-promotions/{$promotionId}/items?limit={$limit}&offset={$offset}";
            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                return [
                    'success' => true,
                    'items'   => $data['results'] ?? ($data['items'] ?? []),
                    'paging'  => $data['paging'] ?? [],
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro.', 'items' => []];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotionItems: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'items' => []];
        }
    }

    /**
     * Adiciona item(ns) a uma promoção existente.
     *
     * @param string $promotionId
     * @param array  $items  [['item_id' => 'MLB...', 'discount' => 10], ...]
     */
    public function addItemsToPromotion(string $promotionId, array $items): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            $response = $this->makeRequest(
                'PUT',
                "/seller-promotions/{$promotionId}/items",
                $items,
                $token->access_token,
                Auth::id()
            );

            if ($response['success'] ?? false) {
                return ['success' => true, 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao adicionar itens.'];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] addItemsToPromotion: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
