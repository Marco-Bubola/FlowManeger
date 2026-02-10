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

            // Obtém token de acesso
            $token = $this->authService->getValidToken();
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
            // Obtém token
            $token = $this->authService->getValidToken();
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
            $token = $this->authService->getValidToken();
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
}
