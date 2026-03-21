<?php

namespace App\Services\Shopee;

use App\Models\ShopeeOrder;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço para busca e importação de pedidos da Shopee.
 *
 * Endpoints:
 * - GET /api/v2/order/get_order_list       → Lista pedidos por status/data
 * - GET /api/v2/order/get_order_detail     → Detalhes de um pedido
 */
class OrderService extends ShopeeService
{
    protected StockSyncService $stockSyncService;

    public function __construct(StockSyncService $stockSyncService)
    {
        parent::__construct();
        $this->stockSyncService = $stockSyncService;
    }

    // =========================================================================
    // Listagem de pedidos
    // =========================================================================

    /**
     * Busca a lista de pedidos da Shopee para uma loja.
     *
     * @param int    $userId
     * @param string $orderStatus Filtro de status (READY_TO_SHIP, COMPLETED, etc.)
     * @param int    $pageSize    Quantidade por página (máx. 100)
     * @param int    $cursor      Cursor de paginação
     */
    public function getOrderList(
        int    $userId,
        string $orderStatus = 'READY_TO_SHIP',
        int    $pageSize = 50,
        int    $cursor = 0
    ): array {
        try {
            $token = $this->getActiveToken($userId);
            $path  = '/api/v2/order/get_order_list';

            $params = [
                'order_status'   => $orderStatus,
                'page_size'      => min($pageSize, 100),
                'cursor'         => (string) $cursor,
                'time_range_field' => 'create_time',
                'time_from'      => now()->subDays(30)->timestamp,
                'time_to'        => now()->timestamp,
            ];

            $response = $this->get($path, $params, $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return $response['response'] ?? [];

        } catch (Exception $e) {
            Log::error('ShopeeOrderService: erro ao listar pedidos', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    // =========================================================================
    // Detalhes de um pedido
    // =========================================================================

    /**
     * Busca os detalhes completos de um ou mais pedidos.
     *
     * @param int   $userId
     * @param array $orderSnList Lista de Order SNs
     */
    public function getOrderDetail(int $userId, array $orderSnList): array
    {
        try {
            $token = $this->getActiveToken($userId);
            $path  = '/api/v2/order/get_order_detail';

            $params = [
                'order_sn_list'   => implode(',', $orderSnList),
                'request_order_status_pending' => false,
                'response_optional_fields'     => 'buyer_user_id,buyer_username,estimated_shipping_fee,recipient_address,actual_shipping_fee,goods_to_declare,note,note_update_time,item_list,pay_time,dropshipper,dropshipper_phone,split_up,buyer_cancel_reason,cancel_by,cancel_reason,actual_shipping_fee_confirmed,buyer_cpf_id,fulfillment_flag,pickup_done_time,package_list,shipping_carrier,payment_method,total_amount,invoice_data',
            ];

            $response = $this->get($path, $params, $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return $response['response']['order_list'] ?? [];

        } catch (Exception $e) {
            Log::error('ShopeeOrderService: erro ao buscar detalhes de pedido', [
                'user_id'       => $userId,
                'order_sn_list' => $orderSnList,
                'error'         => $e->getMessage(),
            ]);
            return [];
        }
    }

    // =========================================================================
    // Importação e processamento
    // =========================================================================

    /**
     * Importa pedidos recentes da Shopee, processando estoque para novos.
     *
     * @param int $userId
     * @return array ['imported' => int, 'errors' => int]
     */
    public function importRecentOrders(int $userId): array
    {
        $statuses  = ['READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED'];
        $imported  = 0;
        $errors    = 0;

        foreach ($statuses as $status) {
            $list = $this->getOrderList($userId, $status, 50);
            $sns  = array_column($list['order_list'] ?? [], 'order_sn');

            if (empty($sns)) {
                continue;
            }

            $details = $this->getOrderDetail($userId, $sns);

            foreach ($details as $order) {
                try {
                    $this->processOrderDetail($order, $userId);
                    $imported++;
                } catch (Exception $e) {
                    $errors++;
                    Log::error('ShopeeOrderService: erro ao importar pedido', [
                        'order_sn' => $order['order_sn'] ?? '?',
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Processa os dados de um pedido e registra no banco.
     */
    private function processOrderDetail(array $order, int $userId): void
    {
        $orderSn = $order['order_sn'];

        // Idempotência
        if (ShopeeOrder::where('shopee_order_sn', $orderSn)->exists()) {
            return;
        }

        $items       = $order['item_list'] ?? [];
        $shopeeItemId = (string) ($items[0]['item_id'] ?? '');
        $modelId     = (string) ($items[0]['model_id'] ?? '');
        $quantity    = array_sum(array_column($items, 'model_quantity_purchased'));

        $this->stockSyncService->processShopeeOrder(
            orderSn:       $orderSn,
            shopeeItemId:  $shopeeItemId,
            shopeeModelId: $modelId ?: null,
            quantity:      max(1, $quantity),
            userId:        $userId,
            rawData:       array_merge($order, [
                'buyer_username' => $order['buyer_username'] ?? null,
                'item_list'      => $items,
                'total_amount'   => $order['total_amount'] ?? 0,
                'shop_id'        => $order['shop_id'] ?? '',
                'status'         => $order['order_status'] ?? 'READY_TO_SHIP',
            ])
        );
    }
}
