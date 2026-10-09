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

        // Já publicado (clique duplo / reenvio): não cria anúncio repetido
        if ($publication->shopee_item_id) {
            return [
                'success'        => true,
                'shopee_item_id' => (string) $publication->shopee_item_id,
                'message'        => 'Anúncio já existe na Shopee.',
            ];
        }

        try {
            $token = $this->getActiveToken($userId);

            $payload = $this->buildItemPayload($publication, $token);

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
    public function getCategories(int $userId, string $lang = 'pt-br'): array
    {
        try {
            $token = $this->getActiveToken($userId);
            $path  = '/api/v2/product/get_category';

            $response = $this->get($path, ['language' => $lang], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            // Só categoria final (sem filhas) é aceita no add_item
            return collect($response['response']['category_list'] ?? [])
                ->filter(fn ($c) => empty($c['has_children']))
                ->map(fn ($c) => [
                    'category_id'            => $c['category_id'] ?? null,
                    'display_category_name'  => $c['display_category_name'] ?? null,
                    'original_category_name' => $c['original_category_name'] ?? null,
                ])
                ->sortBy(fn ($c) => $c['display_category_name'] ?? $c['original_category_name'] ?? '')
                ->values()
                ->all();

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
     * Monta o payload do add_item (Shopee Open Platform v2).
     */
    private function buildItemPayload(ShopeePublication $publication, \App\Models\ShopeeToken $token): array
    {
        // add_item só aceita image_id_list: sobe as fotos no media_space antes
        $imageIds = [];
        foreach (array_slice((array) ($publication->pictures ?? []), 0, 9) as $url) {
            $id = $this->uploadImage((string) $url);
            if ($id) {
                $imageIds[] = $id;
            }
        }
        if (empty($imageIds)) {
            throw new Exception('Nenhuma foto pôde ser enviada para a Shopee (a Shopee exige ao menos uma imagem).');
        }

        $payload = [
            'original_price'   => (float) $publication->price,
            'description'      => $publication->description ?: $publication->title,
            'item_name'        => $publication->title,
            'item_status'      => 'NORMAL',
            'item_sku'         => 'FLW-' . $publication->user_id . '-' . $publication->id,
            'condition'        => $publication->condition ?? 'NEW',
            'category_id'      => (int) $publication->shopee_category_id,
            'image'            => ['image_id_list' => $imageIds],
            'weight'           => round(max(1, (int) $publication->weight_grams) / 1000, 3), // Shopee usa KG
            'pre_order'        => [
                'is_pre_order' => (int) $publication->days_to_ship > 3,
                'days_to_ship' => max(1, (int) $publication->days_to_ship),
            ],
        ];

        // Buscar canais de logística reais da loja (obrigatório — logistic_id não pode ser 0)
        $logisticChannels = $this->getLogisticsChannels($publication->user_id);
        $payload['logistic_info'] = collect($logisticChannels)
            ->filter(fn($ch) => ($ch['enabled'] ?? false))
            ->map(fn($ch) => [
                'logistic_id' => (int) $ch['logistic_id'],
                'enabled'     => true,
                'is_free'     => false,
            ])
            ->values()
            ->toArray();

        if (empty($payload['logistic_info'])) {
            Log::warning('ShopeeProductService: nenhum canal de logística ativo para a loja', [
                'user_id'          => $publication->user_id,
                'publication_id'   => $publication->id,
            ]);
            throw new Exception('Nenhum canal de envio ativo na sua loja Shopee. Ative um canal de logística no Seller Centre.');
        }

        // Dimensões (opcional mas recomendado)
        if ($publication->length_cm && $publication->width_cm && $publication->height_cm) {
            $payload['dimension'] = [
                'package_length' => max(1, (int) ceil((float) $publication->length_cm)),
                'package_width'  => max(1, (int) ceil((float) $publication->width_cm)),
                'package_height' => max(1, (int) ceil((float) $publication->height_cm)),
            ];
        }

        // Atributos da categoria (formato attribute_list do add_item)
        $attributes = $this->buildAttributeList((array) ($publication->shopee_attributes ?? []));
        if ($attributes) {
            $payload['attribute_list'] = $attributes;
        }

        // Estoque do anúncio (sem variação)
        if (!$publication->has_variations) {
            $payload['seller_stock'] = [['stock' => max(0, (int) $publication->available_quantity)]];
        }

        return $payload;
    }

    /**
     * Converte [attribute_id => texto] ou itens já no formato da Shopee para attribute_list.
     */
    private function buildAttributeList(array $attributes): array
    {
        $list = [];
        foreach ($attributes as $key => $value) {
            if (is_array($value) && isset($value['attribute_id'])) {
                $list[] = $value; // já no formato da API
                continue;
            }
            $text = trim((string) (is_array($value) ? ($value['value'] ?? '') : $value));
            if (!is_numeric($key) || (int) $key <= 0 || $text === '') {
                continue;
            }
            $list[] = [
                'attribute_id'         => (int) $key,
                'attribute_value_list' => [[
                    'value_id'            => 0,
                    'original_value_name' => $text,
                ]],
            ];
        }
        return $list;
    }

    /**
     * Envia uma imagem para o media_space da Shopee e devolve o image_id.
     * Aceita URL pública ou arquivo do storage local (storage/products/...).
     */
    public function uploadImage(string $source): ?string
    {
        try {
            $contents = null;
            $path = parse_url($source, PHP_URL_PATH) ?: $source;

            // Arquivo local: /storage/... → storage/app/public/...
            if (str_contains($path, '/storage/')) {
                $local = storage_path('app/public/' . ltrim(substr($path, strpos($path, '/storage/') + 9), '/'));
                if (is_file($local)) {
                    $contents = file_get_contents($local);
                }
            }
            if ($contents === null && str_starts_with($source, 'http')) {
                $download = \Illuminate\Support\Facades\Http::timeout($this->timeout)->get($source);
                if ($download->successful()) {
                    $contents = $download->body();
                }
            }
            if (!$contents) {
                return null;
            }

            $apiPath   = '/api/v2/media_space/upload_image';
            $timestamp = time();
            $response  = \Illuminate\Support\Facades\Http::timeout($this->timeout)
                ->withQueryParameters([
                    'partner_id' => (int) $this->partnerId,
                    'timestamp'  => $timestamp,
                    'sign'       => $this->signPublic($apiPath, $timestamp),
                ])
                ->attach('image', $contents, basename($path) ?: 'image.jpg')
                ->post($this->getBaseUrl() . $apiPath);

            $data = $response->json() ?? [];
            if (!$response->successful() || !empty($data['error'])) {
                throw new Exception($data['message'] ?? $data['error'] ?? $response->body());
            }

            return $data['response']['image_info']['image_id'] ?? null;

        } catch (\Throwable $e) {
            Log::warning('ShopeeProductService: falha ao enviar imagem', ['source' => $source, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Monta a lista de estoque por variação/modelo para update_stock.
     */
    private function buildVariationsStockList(ShopeePublication $publication, int $totalQuantity): array
    {
        $products = $publication->linkedProducts();
        $list     = [];

        foreach ($products as $product) {
            $modelId = $product->pivot->shopee_model_id;
            if (!$modelId) {
                continue;
            }
            $pivotQty  = max(1, (int) $product->pivot->quantity);
            $available = intdiv(ShopeePublication::productAvailableStock($product), $pivotQty);

            $list[] = [
                'model_id'      => (int) $modelId,
                'seller_stock'  => [['stock' => max(0, $available)]],
            ];
        }

        return $list;
    }
}
