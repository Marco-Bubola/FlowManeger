<?php

namespace App\Services\Shopee;

use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use App\Models\Product;
use App\Models\MlStockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço de sincronização de estoque multi-canal.
 *
 * Ao receber uma notificação de venda (Shopee ou ML), este serviço:
 * 1. Deduz o estoque dos produtos internos
 * 2. Dispara atualização de estoque em TODOS os canais vinculados
 *
 * Garante consistência de inventário em todos os marketplaces.
 */
class StockSyncService extends ShopeeService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    // =========================================================================
    // Processamento de Venda Shopee
    // =========================================================================

    /**
     * Processa uma venda recebida via Webhook da Shopee.
     *
     * @param string $orderSn     Order SN da Shopee
     * @param string $shopeeItemId Item ID da Shopee
     * @param string|null $shopeeModelId Model ID (para variações)
     * @param int    $quantity    Quantidade vendida
     * @param int    $userId
     * @param array  $rawData Dados brutos do webhook
     */
    public function processShopeeOrder(
        string  $orderSn,
        string  $shopeeItemId,
        ?string $shopeeModelId,
        int     $quantity,
        int     $userId,
        array   $rawData = []
    ): array {
        DB::beginTransaction();
        try {
            // Idempotência: evitar processar o mesmo pedido duas vezes
            $existing = ShopeeOrder::where('shopee_order_sn', $orderSn)->first();
            if ($existing) {
                DB::rollBack();
                return [
                    'success' => true,
                    'message' => 'Pedido já processado anteriormente.',
                    'order'   => $existing,
                ];
            }

            // Criar registro do pedido
            $order = ShopeeOrder::create([
                'user_id'          => $userId,
                'shop_id'          => $rawData['shop_id'] ?? '',
                'shopee_order_sn'  => $orderSn,
                'shopee_item_id'   => $shopeeItemId,
                'shopee_model_id'  => $shopeeModelId,
                'order_status'     => $rawData['status'] ?? 'READY_TO_SHIP',
                'total_amount'     => $rawData['total_amount'] ?? 0,
                'buyer_username'   => $rawData['buyer_username'] ?? null,
                'order_items'      => $rawData['item_list'] ?? null,
                'raw_data'         => $rawData,
                'sync_status'      => 'pending',
                'shopee_created_at'=> now(),
            ]);

            // Encontrar a publicação Shopee pelo item_id
            $publication = ShopeePublication::where('shopee_item_id', $shopeeItemId)
                ->where('user_id', $userId)
                ->with('products')
                ->first();

            if (!$publication) {
                Log::warning('StockSyncService: publicação Shopee não encontrada', [
                    'shopee_item_id' => $shopeeItemId,
                    'order_sn'       => $orderSn,
                ]);
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Pedido salvo, mas publicação interna não encontrada para dedução de estoque.',
                    'order'   => $order,
                ];
            }

            // Deduzir estoque dos produtos internos
            $this->deductStock($publication, $shopeeModelId, $quantity, $userId, $orderSn, 'shopee');

            // Propagar atualização para TODOS os canais vinculados
            $this->propagateStockUpdate($publication->products, $userId);

            $order->update(['sync_status' => 'synced']);

            DB::commit();

            Log::info('StockSyncService: venda Shopee processada', [
                'order_sn'       => $orderSn,
                'shopee_item_id' => $shopeeItemId,
                'quantity'       => $quantity,
            ]);

            return [
                'success' => true,
                'message' => 'Venda Shopee processada e estoque atualizado em todos os canais.',
                'order'   => $order,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('StockSyncService: erro ao processar venda Shopee', [
                'order_sn' => $orderSn,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // Propagação de estoque para todos os canais
    // =========================================================================

    /**
     * Dado um conjunto de produtos internos que tiveram estoque alterado,
     * dispara jobs de sincronização para cada publicação ativa em todos os canais.
     *
     * @param \Illuminate\Support\Collection $products
     * @param int $userId
     */
    public function propagateStockUpdate($products, int $userId): void
    {
        foreach ($products as $product) {
            $product->refresh();

            // Sincronizar todas as publicações ML vinculadas
            $mlPublications = $product->mlPublications()
                ->where('sync_status', '!=', 'error')
                ->whereNotNull('ml_item_id')
                ->get();

            foreach ($mlPublications as $mlPub) {
                \App\Jobs\SyncPublicationToMercadoLivre::dispatch($mlPub)
                    ->onQueue('marketplace');
            }

            // Sincronizar todas as publicações Shopee vinculadas
            $shopeePublications = $product->shopeePublications()
                ->where('sync_status', '!=', 'error')
                ->whereNotNull('shopee_item_id')
                ->where('user_id', $userId)
                ->get();

            foreach ($shopeePublications as $shopeePub) {
                \App\Jobs\SyncPublicationToShopee::dispatch($shopeePub)
                    ->onQueue('marketplace');
            }
        }
    }

    // =========================================================================
    // Helpers internos
    // =========================================================================

    /**
     * Deduz o estoque dos produtos vinculados a uma publicação.
     *
     * @param ShopeePublication $publication
     * @param string|null       $shopeeModelId   Model ID (para variações específicas)
     * @param int               $quantity        Quantidade vendida
     * @param int               $userId
     * @param string            $orderReference  Referência do pedido para log
     * @param string            $source          'shopee' | 'mercadolivre' | 'manual'
     */
    private function deductStock(
        ShopeePublication $publication,
        ?string $shopeeModelId,
        int $quantity,
        int $userId,
        string $orderReference,
        string $source
    ): void {
        $products = $publication->products;

        if ($shopeeModelId) {
            // Modo variação: decrementa apenas o produto mapeado ao model_id
            $products = $products->filter(
                fn($p) => (string) $p->pivot->shopee_model_id === $shopeeModelId
            );
        }

        foreach ($products as $product) {
            $pivotQty    = max(1, (int) ($product->pivot->quantity ?? 1));
            $deductTotal = $quantity * $pivotQty;

            $before = $product->stock_quantity;
            $after  = max(0, $before - $deductTotal);

            $product->decrement('stock_quantity', $deductTotal);

            // Log de movimentação
            MlStockLog::create([
                'product_id'          => $product->id,
                'ml_publication_id'   => null,
                'operation_type'      => 'sale',
                'quantity_before'     => $before,
                'quantity_after'      => $after,
                'quantity_change'     => -$deductTotal,
                'source'              => $source,
                'ml_order_id'         => $orderReference,
                'notes'               => "Venda via {$source} - pedido {$orderReference}",
                'user_id'             => $userId,
                'created_at'          => now(),
            ]);
        }
    }
}
