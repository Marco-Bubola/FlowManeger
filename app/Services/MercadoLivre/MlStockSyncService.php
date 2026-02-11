<?php

namespace App\Services\MercadoLivre;

use App\Models\MlPublication;
use App\Models\Product;
use App\Models\MlStockLog;
use App\Models\MercadoLivreOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MlStockSyncService
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Sincroniza quantidade de uma publicação com o Mercado Livre
     * 
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function syncQuantityToMercadoLivre(MlPublication $publication): array
    {
        try {
            // Recalcula quantidade baseada no estoque
            $availableQuantity = $publication->calculateAvailableQuantity();

            // Obtém token de acesso (por user_id da publicação)
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            // Atualiza via API do ML
            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}", [
                    'available_quantity' => $availableQuantity,
                ]);

            if ($response->successful()) {
                $publication->update([
                    'available_quantity' => $availableQuantity,
                    'sync_status' => 'synced',
                    'last_sync_at' => now(),
                    'error_message' => null,
                ]);

                Log::info("Quantidade sincronizada com ML", [
                    'ml_item_id' => $publication->ml_item_id,
                    'quantity' => $availableQuantity,
                ]);

                return [
                    'success' => true,
                    'message' => 'Quantidade sincronizada com sucesso',
                    'data' => [
                        'quantity' => $availableQuantity,
                        'ml_item_id' => $publication->ml_item_id,
                    ],
                ];
            }

            throw new \Exception("Erro na API do ML: " . $response->body());

        } catch (\Exception $e) {
            $publication->update([
                'sync_status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Erro ao sincronizar com ML", [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Processa uma venda do Mercado Livre
     * Deduz estoque de todos os produtos vinculados
     * 
     * @param string $mlOrderId
     * @param string $mlItemId
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string, 'order' => MercadoLivreOrder]
     */
    public function processMercadoLivreSale(string $mlOrderId, string $mlItemId, int $quantity): array
    {
        try {
            // Busca a publicação
            $publication = MlPublication::where('ml_item_id', $mlItemId)->first();

            if (!$publication) {
                throw new \Exception("Publicação ML {$mlItemId} não encontrada");
            }

            // Deduz estoque de todos os produtos
            $result = $publication->deductStock($quantity, $mlOrderId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Registra a ordem (se ainda não existe)
            $order = MercadoLivreOrder::firstOrCreate(
                ['ml_order_id' => $mlOrderId],
                [
                    'ml_item_id' => $mlItemId,
                    'product_id' => $publication->products->first()->id ?? null,
                    'quantity' => $quantity,
                    'order_status' => 'paid',
                    'payment_status' => 'approved',
                    'sync_status' => 'processed',
                    'date_created' => now(),
                ]
            );

            Log::info("Venda ML processada", [
                'ml_order_id' => $mlOrderId,
                'ml_item_id' => $mlItemId,
                'quantity' => $quantity,
                'products_affected' => $publication->products->count(),
            ]);

            return [
                'success' => true,
                'message' => 'Venda processada e estoque atualizado',
                'order' => $order,
            ];

        } catch (\Exception $e) {
            Log::error("Erro ao processar venda ML", [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'order' => null,
            ];
        }
    }

    /**
     * Sincroniza todas as publicações pendentes
     * Útil para rodar em cron/scheduler
     */
    public function syncAllPending(): array
    {
        $publications = MlPublication::pending()->active()->get();
        $results = [
            'total' => $publications->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($publications as $publication) {
            $result = $this->syncQuantityToMercadoLivre($publication);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'ml_item_id' => $publication->ml_item_id,
                    'error' => $result['message'],
                ];
            }
        }

        return $results;
    }

    /**
     * Verifica e corrige inconsistências entre estoque local e ML
     * 
     * @param MlPublication $publication
     * @return array
     */
    public function auditAndFix(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token não disponível');
            }

            // Consulta quantidade atual no ML
            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$publication->ml_item_id}");

            if (!$response->successful()) {
                throw new \Exception("Erro ao consultar ML: " . $response->body());
            }

            $mlData = $response->json();
            $mlQuantity = $mlData['available_quantity'] ?? 0;
            $localQuantity = $publication->calculateAvailableQuantity();

            $issues = [];

            // Verifica diferença
            if ($mlQuantity !== $localQuantity) {
                $issues[] = "Quantidade divergente: Local={$localQuantity}, ML={$mlQuantity}";

                // Corrige automaticamente
                $syncResult = $this->syncQuantityToMercadoLivre($publication);
                
                if ($syncResult['success']) {
                    $issues[] = "✓ Corrigido automaticamente";
                } else {
                    $issues[] = "✗ Falha na correção: " . $syncResult['message'];
                }
            }

            return [
                'success' => true,
                'consistent' => empty($issues),
                'local_quantity' => $localQuantity,
                'ml_quantity' => $mlQuantity,
                'issues' => $issues,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'consistent' => false,
                'issues' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Busca quantidade disponível de um item no Mercado Livre
     * 
     * @param string $mlItemId
     * @return int|null
     */
    public function getMlItemQuantity(string $mlItemId): ?int
    {
        try {
            $publication = MlPublication::where('ml_item_id', $mlItemId)->first();
            $token = $publication ? $this->getTokenForPublication($publication) : $this->authService->getActiveToken(\Illuminate\Support\Facades\Auth::id());
            if (!$token) {
                Log::error("Token ML não disponível para consulta de quantidade", [
                    'ml_item_id' => $mlItemId
                ]);
                return null;
            }

            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$mlItemId}");

            if (!$response->successful()) {
                Log::error("Erro ao consultar item no ML", [
                    'ml_item_id' => $mlItemId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            return $data['available_quantity'] ?? 0;

        } catch (\Exception $e) {
            Log::error("Exceção ao buscar quantidade no ML", [
                'ml_item_id' => $mlItemId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtém token para a publicação (user_id do dono da publicação).
     */
    protected function getTokenForPublication(MlPublication $publication): ?\App\Models\MercadoLivreToken
    {
        return $this->authService->getActiveToken($publication->user_id);
    }

    /**
     * Atualiza os dados da publicação no Mercado Livre (título, preço, descrição, quantidade).
     * Chamado ao salvar edições na página de edição para refletir no ML.
     *
     * @param MlPublication $publication Publicação já atualizada no banco (title, price, description, etc.)
     * @return array ['success' => bool, 'message' => string]
     */
    public function updatePublicationToMercadoLivre(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $payload = [
                'title' => $publication->title,
                'price' => (float) $publication->price,
                'available_quantity' => $publication->calculateAvailableQuantity(),
            ];

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}", $payload);

            // Descrição no ML é atualizada em endpoint separado (falha aqui não invalida o update principal)
            if ($response->successful() && $publication->description !== null && $publication->description !== '') {
                try {
                    Http::withToken($token->access_token)
                        ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}/description", [
                            'plain_text' => $publication->description,
                        ]);
                } catch (\Throwable $e) {
                    Log::warning('Falha ao atualizar descrição no ML (item atualizado)', ['ml_item_id' => $publication->ml_item_id, 'error' => $e->getMessage()]);
                }
            }

            if ($response->successful()) {
                $publication->update([
                    'available_quantity' => $payload['available_quantity'],
                    'sync_status' => 'synced',
                    'last_sync_at' => now(),
                    'error_message' => null,
                ]);
                Log::info('Publicação atualizada no ML', [
                    'ml_item_id' => $publication->ml_item_id,
                    'fields' => array_keys($payload),
                ]);
                return ['success' => true, 'message' => 'Publicação atualizada no Mercado Livre'];
            }

            throw new \Exception('Erro na API do ML: ' . $response->body());
        } catch (\Exception $e) {
            $publication->update([
                'sync_status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Erro ao atualizar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Busca os dados atuais do anúncio no Mercado Livre e atualiza a publicação local.
     * Assim, o que foi alterado no ML (título, preço, etc.) passa a refletir no sistema.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string, 'publication' => MlPublication|null]
     */
    public function fetchPublicationFromMercadoLivre(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId) {
                return ['success' => false, 'message' => 'Publicação sem ID do ML', 'publication' => null];
            }

            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$itemId}");

            if (!$response->successful()) {
                // Tratamento específico por código de erro HTTP
                $statusCode = $response->status();
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                
                $userFriendlyMessage = match($statusCode) {
                    404 => "Item {$itemId} não encontrado no Mercado Livre (pode ter sido excluído ou expirado)",
                    403 => "Sem permissão para acessar o item {$itemId}",
                    401 => "Token de acesso expirado ou inválido",
                    default => "Erro ao buscar item no ML ({$statusCode}): {$errorMessage}"
                };
                
                throw new \Exception($userFriendlyMessage);
            }

            $data = $response->json();

            $description = $publication->description;
            $descResponse = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$itemId}/description");
            if ($descResponse->successful()) {
                $descData = $descResponse->json();
                $description = $descData['plain_text'] ?? $description;
            }

            $publication->update([
                'title' => $data['title'] ?? $publication->title,
                'price' => $data['price'] ?? $publication->price,
                'available_quantity' => (int) ($data['available_quantity'] ?? $publication->available_quantity),
                'description' => $description,
                'ml_permalink' => $data['permalink'] ?? $publication->ml_permalink,
                'status' => $data['status'] ?? $publication->status,
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'error_message' => null,
                'pictures' => $data['pictures'] ?? $publication->pictures,
            ]);

            Log::info('Publicação atualizada a partir do ML', [
                'ml_item_id' => $itemId,
                'title' => $publication->title,
            ]);

            return [
                'success' => true,
                'message' => 'Dados atualizados do Mercado Livre',
                'publication' => $publication->fresh(),
            ];
        } catch (\Exception $e) {
            $publication->update([
                'sync_status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Erro ao buscar publicação do ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'publication' => null,
            ];
        }
    }

    /**
     * Lista os IDs de itens publicados pelo vendedor no ML.
     *
     * @param int $userId User ID (para obter token)
     * @return array ['success' => bool, 'item_ids' => string[], 'message' => string]
     */
    public function fetchUserItemIdsFromMl(int $userId): array
    {
        try {
            $token = $this->authService->getActiveToken($userId);
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'item_ids' => [], 'message' => 'Conta ML não conectada'];
            }
            $response = Http::withToken($token->access_token)
                ->get('https://api.mercadolibre.com/users/' . $token->ml_user_id . '/items/search', [
                    'limit' => 100,
                    'offset' => 0,
                ]);
            if (!$response->successful()) {
                return ['success' => false, 'item_ids' => [], 'message' => 'Erro ao listar itens no ML'];
            }
            $data = $response->json();
            $itemIds = $data['results'] ?? [];
            return ['success' => true, 'item_ids' => $itemIds, 'message' => ''];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar itens do usuário no ML', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['success' => false, 'item_ids' => [], 'message' => $e->getMessage()];
        }
    }

    /**
     * Busca dados resumidos de vários itens no ML (batch, até 20 por request).
     *
     * @param array $itemIds
     * @param int $userId
     * @return array Lista de arrays com id, title, thumbnail, permalink, price, available_quantity, status
     */
    public function fetchItemsSummaryFromMl(array $itemIds, int $userId): array
    {
        if (empty($itemIds)) {
            return [];
        }
        $token = $this->authService->getActiveToken($userId);
        if (!$token) {
            return [];
        }
        $items = [];
        foreach (array_chunk($itemIds, 20) as $chunk) {
            $ids = implode(',', $chunk);
            $response = Http::withToken($token->access_token)
                ->get('https://api.mercadolibre.com/items', ['ids' => $ids]);
            if (!$response->successful()) {
                continue;
            }
            $data = $response->json();
            foreach ($data as $row) {
                if (isset($row['body']) && isset($row['body']['id'])) {
                    $b = $row['body'];
                    $items[] = [
                        'id' => $b['id'],
                        'title' => $b['title'] ?? '',
                        'thumbnail' => $b['thumbnail'] ?? null,
                        'permalink' => $b['permalink'] ?? null,
                        'price' => $b['price'] ?? 0,
                        'available_quantity' => (int) ($b['available_quantity'] ?? 0),
                        'status' => $b['status'] ?? 'unknown',
                    ];
                }
            }
        }
        return $items;
    }

    /**
     * Pausa uma publicação no Mercado Livre.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string]
     */
    public function pausePublication(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId || str_starts_with($itemId, 'TEMP_')) {
                return ['success' => false, 'message' => 'Publicação ainda não foi publicada no ML'];
            }

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$itemId}", [
                    'status' => 'paused',
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                throw new \Exception("Erro ao pausar item no ML: {$errorMessage}");
            }

            $publication->update([
                'status' => 'paused',
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);

            Log::info('Publicação pausada no ML', ['ml_item_id' => $itemId]);

            return ['success' => true, 'message' => 'Publicação pausada com sucesso'];
        } catch (\Exception $e) {
            Log::error('Erro ao pausar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ativa uma publicação no Mercado Livre.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string]
     */
    public function activatePublication(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId || str_starts_with($itemId, 'TEMP_')) {
                return ['success' => false, 'message' => 'Publicação ainda não foi publicada no ML'];
            }

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$itemId}", [
                    'status' => 'active',
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                throw new \Exception("Erro ao ativar item no ML: {$errorMessage}");
            }

            $publication->update([
                'status' => 'active',
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);

            Log::info('Publicação ativada no ML', ['ml_item_id' => $itemId]);

            return ['success' => true, 'message' => 'Publicação ativada com sucesso'];
        } catch (\Exception $e) {
            Log::error('Erro ao ativar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Importa um anúncio do ML para o sistema (cria MlPublication com dados do ML).
     *
     * @param int $userId
     * @param string $mlItemId
     * @return array ['success' => bool, 'message' => string, 'publication' => MlPublication|null]
     */
    public function createPublicationFromMlItem(int $userId, string $mlItemId): array
    {
        $existing = MlPublication::where('user_id', $userId)->where('ml_item_id', $mlItemId)->first();
        if ($existing) {
            return ['success' => true, 'message' => 'Já existe no sistema', 'publication' => $existing];
        }
        $pub = MlPublication::create([
            'user_id' => $userId,
            'ml_item_id' => $mlItemId,
            'title' => $mlItemId,
            'price' => 0,
            'publication_type' => 'simple',
            'status' => 'active',
            'sync_status' => 'pending',
        ]);
        $result = $this->fetchPublicationFromMercadoLivre($pub);
        if ($result['success'] && $result['publication']) {
            $result['publication']->update(['publication_type' => 'simple']);
            return ['success' => true, 'message' => 'Importado do ML', 'publication' => $result['publication']];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Erro ao importar', 'publication' => $pub];
    }
}
