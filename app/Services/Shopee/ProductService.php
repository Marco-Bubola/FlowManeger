<?php

namespace App\Services\Shopee;

use App\Models\ShopeePublication;
use App\Models\ShopeeSyncLog;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço de sincronização de produtos/publicações com a Shopee Open Platform API v2
 *
 * Endpoints utilizados:
 * - POST /api/v2/product/add_item       → Criar anúncio
 * - POST /api/v2/product/update_item    → Atualizar anúncio
 * - POST /api/v2/product/update_stock   → Atualizar estoque
 * - POST /api/v2/product/update_price   → Atualizar preço
 * - GET  /api/v2/product/get_item_list  → Listar anúncios da loja
 * - GET  /api/v2/product/get_item_base_info → Detalhes de um anúncio
 * - GET  /api/v2/product/get_category   → Categorias disponíveis
 */
class ProductService extends ShopeeService
{
    // =========================================================================
    // Publicação de produto
    // =========================================================================

    /**
     * Cria um novo anúncio na Shopee.
     *
     * @param ShopeePublication $publication Publicação interna com todos os dados
     * @param int               $userId      ID do usuário
     * @return array ['success' => bool, 'shopee_item_id' => string|null, 'message' => string]
     */
    public function createListing(ShopeePublication $publication, int $userId): array
    {
        $startTime = microtime(true);

        try {
            $token = $this->getActiveToken($userId);

            $payload = $this->buildItemPayload($publication);

            $path     = '/api/v2/product/add_item';
            $response = $this->post($path, $payload, $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            $shopeeItemId = (string) ($response['response']['item_id'] ?? '');

            $publication->update([
                'shopee_item_id' => $shopeeItemId,
                'status'         => 'published',
                'sync_status'    => 'synced',
                'last_sync_at'   => now(),
                'error_message'  => null,
            ]);

            $this->logSync($userId, 'publish', 'success',
                "Publicação criada na Shopee: item_id {$shopeeItemId}",
                [
                    'entity_type'        => 'publication',
                    'entity_id'          => $publication->id,
                    'reference_id'       => $shopeeItemId,
                    'execution_time_ms'  => (int) ((microtime(true) - $startTime) * 1000),
                    'response_data'      => $response,
                ]
            );

            return [
                'success'        => true,
                'shopee_item_id' => $shopeeItemId,
                'message'        => 'Anúncio criado com sucesso na Shopee.',
            ];

        } catch (Exception $e) {
            $publication->update([
                'sync_status'   => 'error',
                'error_message' => $e->getMessage(),
            ]);

            $this->logSync($userId, 'publish', 'error',
                'Erro ao criar anúncio Shopee: ' . $e->getMessage(),
                [
                    'entity_type'       => 'publication',
                    'entity_id'         => $publication->id,
                    'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]
            );

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // Sincronização de estoque
    // =========================================================================

    /**
     * Atualiza o estoque de uma publicação na Shopee.
     *
     * @param ShopeePublication $publication
     * @param int               $userId
     * @param int|null          $forceQuantity Quantidade forçada (null = calcula automaticamente)
     */
    public function syncStock(ShopeePublication $publication, int $userId, ?int $forceQuantity = null): array
    {
        $startTime = microtime(true);

        try {
            if (!$publication->shopee_item_id) {
                throw new Exception('Publicação sem item_id na Shopee. Publique o produto primeiro.');
            }

            $token = $this->getActiveToken($userId);

            $quantity = $forceQuantity ?? $publication->calculateAvailableQuantity();

            $path = '/api/v2/product/update_stock';

            if ($publication->has_variations) {
                // Para variações, precisa informar model_id e quantidade por modelo
                $stockList = $this->buildVariationsStockList($publication, $quantity);
                $body = [
                    'item_id'    => (int) $publication->shopee_item_id,
                    'stock_list' => $stockList,
                ];
            } else {
                $body = [
                    'item_id' => (int) $publication->shopee_item_id,
                    'stock_list' => [
                        ['model_id' => 0, 'seller_stock' => [['stock' => $quantity]]],
                    ],
                ];
            }

            $response = $this->post($path, $body, $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            $publication->update([
                'available_quantity' => $quantity,
                'sync_status'        => 'synced',
                'last_sync_at'       => now(),
                'error_message'      => null,
            ]);

            $this->logSync($userId, 'stock_update', 'success',
                "Estoque atualizado na Shopee: {$quantity} unidades",
                [
                    'entity_type'       => 'publication',
                    'entity_id'         => $publication->id,
                    'reference_id'      => $publication->shopee_item_id,
                    'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]
            );

            return [
                'success'  => true,
                'quantity' => $quantity,
                'message'  => "Estoque atualizado para {$quantity} unidades na Shopee.",
            ];

        } catch (Exception $e) {
            $publication->update([
                'sync_status'   => 'error',
                'error_message' => $e->getMessage(),
            ]);

            $this->logSync($userId, 'stock_update', 'error',
                'Erro ao atualizar estoque Shopee: ' . $e->getMessage(),
                [
                    'entity_type'       => 'publication',
                    'entity_id'         => $publication->id,
                    'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]
            );

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // Categorias
    // =========================================================================

    /**
     * Busca as categorias disponíveis na Shopee para o usuário/loja.
     *
     * @param int    $userId
     * @param string $lang Idioma ('pt-BR' para Brasil)
     */
    public function getCategories(int $userId, string $lang = 'pt-BR'): array
    {
        try {
            $token = $this->getActiveToken($userId);
            $path  = '/api/v2/product/get_category';

            $response = $this->get($path, ['language' => $lang], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return $response['response']['category_list'] ?? [];

        } catch (Exception $e) {
            Log::warning('ShopeeProductService: erro ao buscar categorias', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Busca atributos obrigatórios de uma categoria específica.
     */
    public function getCategoryAttributes(int $userId, int $categoryId): array
    {
        try {
            $token    = $this->getActiveToken($userId);
            $path     = '/api/v2/product/get_attributes';
            $response = $this->get($path, ['category_id' => $categoryId], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return $response['response']['attribute_list'] ?? [];

        } catch (Exception $e) {
            Log::warning('ShopeeProductService: erro ao buscar atributos da categoria', [
                'category_id' => $categoryId,
                'error'       => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Busca os canais de logística habilitados para a loja.
     * Endpoint: GET /api/v2/logistics/get_channel_list
     *
     * @param int $userId
     * @return array Lista de canais com logistic_id, logistic_name, enabled
     */
    public function getLogisticsChannels(int $userId): array
    {
        try {
            $token    = $this->getActiveToken($userId);
            $path     = '/api/v2/logistics/get_channel_list';
            $response = $this->get($path, [], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return $response['response']['logistics_channel_list'] ?? [];

        } catch (Exception $e) {
            Log::warning('ShopeeProductService: erro ao buscar canais de logística', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    // =========================================================================
    // Helpers internos
    // =========================================================================

    /**
     * Monta o payload para criação/atualização de um item na Shopee.
     */
    private function buildItemPayload(ShopeePublication $publication): array
    {
        $pictures = collect($publication->pictures ?? [])
            ->map(fn($url) => ['url' => $url])
            ->values()
            ->toArray();

        $payload = [
            'original_price'   => (float) $publication->price,
            'description'      => $publication->description ?? '',
            'item_name'        => $publication->title,
            'item_status'      => 'NORMAL',
            'item_sku'         => 'FLW-' . $publication->user_id . '-' . $publication->id,
            'condition'        => $publication->condition ?? 'NEW',
            'days_to_ship'     => $publication->days_to_ship,
            'category_id'      => (int) $publication->shopee_category_id,
            'image'            => ['image_url_list' => array_column($pictures, 'url')],
            'weight'           => round(max(1, $publication->weight_grams) / 1000, 3), // Shopee usa KG
        ];

        // Buscar canais de logística reais da loja (obrigatório — logistic_id não pode ser 0)
        $logisticChannels = $this->getLogisticsChannels($publication->user_id);
        if (!empty($logisticChannels)) {
            $payload['logistic_info'] = collect($logisticChannels)
                ->filter(fn($ch) => ($ch['enabled'] ?? false))
                ->map(fn($ch) => [
                    'logistic_id' => (int) $ch['logistic_id'],
                    'enabled'     => true,
                    'is_free'     => false,
                ])
                ->values()
                ->toArray();
        } else {
            // Fallback: sem logística configurada — a Shopee retornará erro descritivo
            Log::warning('ShopeeProductService: nenhum canal de logística encontrado para a loja', [
                'user_id'          => $publication->user_id,
                'publication_id'   => $publication->id,
            ]);
            $payload['logistic_info'] = [];
        }

        // Dimensões (opcional mas recomendado)
        if ($publication->length_cm && $publication->width_cm && $publication->height_cm) {
            $payload['dimension'] = [
                'package_length' => (int) $publication->length_cm,
                'package_width'  => (int) $publication->width_cm,
                'package_height' => (int) $publication->height_cm,
            ];
        }

        // Atributos da categoria
        if ($publication->shopee_attributes) {
            $payload['attribute_list'] = $publication->shopee_attributes;
        }

        // Sem variação — estoque direto
        if (!$publication->has_variations) {
            $payload['stock_list'] = [
                ['seller_stock' => [['stock' => $publication->available_quantity]]],
            ];
        }

        return $payload;
    }

    /**
     * Monta a lista de estoque por variação/modelo para update_stock.
     */
    private function buildVariationsStockList(ShopeePublication $publication, int $totalQuantity): array
    {
        $products = $publication->products;
        $list     = [];

        foreach ($products as $product) {
            $modelId = $product->pivot->shopee_model_id;
            if (!$modelId) {
                continue;
            }
            $pivotQty  = max(1, (int) $product->pivot->quantity);
            $available = (int) floor($product->stock_quantity / $pivotQty);

            $list[] = [
                'model_id'      => (int) $modelId,
                'seller_stock'  => [['stock' => max(0, $available)]],
            ];
        }

        return $list;
    }
}
