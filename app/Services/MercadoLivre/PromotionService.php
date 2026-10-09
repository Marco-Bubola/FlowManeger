<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar promoções de vendedor no Mercado Livre.
 *
 * API referenciada (app_version=v2):
 *   GET /seller-promotions/users/{user_id}                                   – lista promoções
 *   GET /seller-promotions/promotions/{id}?promotion_type={TYPE}             – detalhes
 *   GET /seller-promotions/promotions/{id}/items?promotion_type={TYPE}       – itens
 *
 * Obs.: makeRequest lança exceção em erro HTTP e devolve o corpo JSON cru em sucesso.
 */
class PromotionService extends MercadoLivreService
{
    /** Tipos de promoção válidos na API (com rótulo em português). */
    public const TYPES = [
        'DEAL'                 => 'Campanha tradicional',
        'LIGHTNING'            => 'Oferta relâmpago',
        'DOD'                  => 'Oferta do dia',
        'SELLER_CAMPAIGN'      => 'Campanha do vendedor',
        'MARKETPLACE_CAMPAIGN' => 'Campanha co-participada',
        'PRICE_DISCOUNT'       => 'Desconto individual',
        'VOLUME'               => 'Desconto por volume',
        'PRE_NEGOTIATED'       => 'Desconto pré-acordado',
        'SMART'                => 'Campanha automatizada',
        'UNHEALTHY_STOCK'      => 'Liquidação de estoque',
    ];

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
     * Filtros (type, status) e paginação (limit, offset) são aplicados localmente.
     *
     * @param array $filters  keys: type (ver self::TYPES), status (started, pending, candidate, finished), limit, offset
     */
    public function getPromotions(array $filters = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'promotions' => [], 'paging' => []];
            }

            $sellerId = $token->ml_user_id;
            $endpoint = "/seller-promotions/users/{$sellerId}?app_version=v2";

            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            $promotions = $response['results'] ?? [];

            if (!empty($filters['type'])) {
                $type = strtoupper($filters['type']);
                $promotions = array_filter($promotions, fn($p) => strtoupper($p['type'] ?? '') === $type);
            }
            if (!empty($filters['status'])) {
                $status = strtolower($filters['status']);
                $promotions = array_filter($promotions, fn($p) => strtolower($p['status'] ?? '') === $status);
            }
            $promotions = array_values($promotions);

            $total  = count($promotions);
            $limit  = (int)($filters['limit'] ?? 0);
            $offset = (int)($filters['offset'] ?? 0);
            if ($limit > 0) {
                $promotions = array_slice($promotions, $offset, $limit);
            }

            return [
                'success'    => true,
                'promotions' => $promotions,
                'paging'     => $response['paging'] ?? [],
                'total'      => $total,
            ];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotions: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'promotions' => [], 'paging' => []];
        }
    }

    /**
     * Retorna detalhes de uma promoção específica.
     */
    public function getPromotion(string $promotionId, string $promotionType): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'data' => null];
            }

            $query    = http_build_query(['promotion_type' => $promotionType, 'app_version' => 'v2']);
            $response = $this->makeRequest('GET', "/seller-promotions/promotions/{$promotionId}?{$query}", [], $token->access_token, Auth::id());

            return ['success' => true, 'data' => $response];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotion: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Retorna os itens participantes de uma promoção.
     */
    public function getPromotionItems(string $promotionId, string $promotionType, int $limit = 50): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'items' => []];
            }

            $query    = http_build_query(['promotion_type' => $promotionType, 'app_version' => 'v2', 'limit' => $limit]);
            $endpoint = "/seller-promotions/promotions/{$promotionId}/items?{$query}";
            $response = $this->makeRequest('GET', $endpoint, [], $token->access_token, Auth::id());

            $items = array_map(function ($item) {
                // Normaliza: preço promocional vem em "price"
                if (!isset($item['new_price']) && isset($item['price'])) {
                    $item['new_price'] = $item['price'];
                }
                $orig = (float)($item['original_price'] ?? 0);
                $new  = (float)($item['new_price'] ?? 0);
                if (!isset($item['discount']) && $orig > 0 && $new > 0 && $new < $orig) {
                    $item['discount'] = round((1 - $new / $orig) * 100);
                }
                return $item;
            }, $response['results'] ?? []);

            return [
                'success' => true,
                'items'   => $items,
                'paging'  => $response['paging'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('[PromotionService] getPromotionItems: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'items' => []];
        }
    }
}
