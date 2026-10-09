<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço para busca e importação de pedidos da Shopee.
 *
 * Endpoints:
 * - GET /api/v2/order/get_order_list        → Lista pedidos por data (máx. 15 dias por chamada)
 * - GET /api/v2/order/get_order_detail      → Detalhes de até 50 pedidos
 * - GET /api/v2/payment/get_escrow_detail   → Repasse/tarifas de um pedido
 */
class OrderService extends ShopeeService
{
    /** Campos opcionais do get_order_detail que o sistema usa. */
    public const DETAIL_FIELDS = 'buyer_user_id,buyer_username,estimated_shipping_fee,recipient_address,actual_shipping_fee,goods_to_declare,note,note_update_time,item_list,pay_time,dropshipper,dropshipper_phone,split_up,buyer_cancel_reason,cancel_by,cancel_reason,actual_shipping_fee_confirmed,buyer_cpf_id,fulfillment_flag,pickup_done_time,package_list,shipping_carrier,payment_method,total_amount,invoice_data';

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
     * Busca uma página da lista de pedidos da Shopee.
     *
     * @param int         $userId
     * @param string|null $orderStatus Filtro de status (null = todos)
     * @param int         $pageSize    Quantidade por página (máx. 100)
     * @param string      $cursor      Cursor de paginação
     * @param int         $days        Janela em dias (a Shopee aceita no máximo 15)
     */
    public function getOrderList(
        int     $userId,
        ?string $orderStatus = null,
        int     $pageSize = 100,
        string  $cursor = '',
        int     $days = 15
    ): array {
        try {
            $token = $this->getActiveToken($userId);
            $path  = '/api/v2/order/get_order_list';

            $params = [
                'page_size'        => max(1, min($pageSize, 100)),
                'cursor'           => $cursor,
                'time_range_field' => 'update_time',
                'time_from'        => now()->subDays(max(1, min($days, 15)))->timestamp,
                'time_to'          => now()->timestamp,
            ];
            if ($orderStatus) {
                $params['order_status'] = $orderStatus;
            }

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
            throw $e;
        }
    }

    // =========================================================================
    // Detalhes de um pedido
    // =========================================================================

    /**
     * Busca os detalhes completos de até 50 pedidos.
     *
     * @param int   $userId
     * @param array $orderSnList Lista de Order SNs
     */
    public function getOrderDetail(int $userId, array $orderSnList): array
    {
        $orderSnList = array_values(array_filter(array_map('strval', $orderSnList)));
        if (empty($orderSnList)) {
            return [];
        }

        $token = $this->getActiveToken($userId);
        $out   = [];

        foreach (array_chunk($orderSnList, 50) as $chunk) {
            $response = $this->get('/api/v2/order/get_order_detail', [
                'order_sn_list'            => implode(',', $chunk),
                'response_optional_fields' => self::DETAIL_FIELDS,
            ], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            foreach ($response['response']['order_list'] ?? [] as $order) {
                $out[] = $order;
            }
        }

        return $out;
    }

    /**
     * Tarifas da Shopee no pedido (comissão + taxa de serviço + taxa de transação),
     * via get_escrow_detail. Null quando a chamada falha ou não traz os campos.
     */
    public function getOrderFees(int $userId, string $orderSn): ?float
    {
        try {
            $token    = $this->getActiveToken($userId);
            $response = $this->get('/api/v2/payment/get_escrow_detail', ['order_sn' => $orderSn], $token);

            if (!empty($response['error'])) {
                throw new Exception($response['message'] ?? $response['error']);
            }

            return self::feeFromEscrow($response['response']['order_income'] ?? []);

        } catch (Exception $e) {
            Log::info('ShopeeOrderService: tarifas do pedido indisponíveis', [
                'order_sn' => $orderSn,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Soma as tarifas do order_income do get_escrow_detail.
     */
    public static function feeFromEscrow(array $income): ?float
    {
        $keys = ['commission_fee', 'service_fee'];
        // Taxa de transação: o nome muda conforme a região/versão
        $keys[] = array_key_exists('seller_transaction_fee', $income) ? 'seller_transaction_fee' : 'transaction_fee';

        $found = false;
        $total = 0.0;
        foreach ($keys as $key) {
            if (isset($income[$key]) && is_numeric($income[$key])) {
                $found = true;
                $total += abs((float) $income[$key]);
            }
        }

        return $found ? round($total, 2) : null;
    }

    // =========================================================================
    // Importação e processamento
    // =========================================================================

    /**
     * Busca o pedido na Shopee e grava/atualiza (usado pelo webhook).
     */
    public function syncOrderBySn(int $userId, string $orderSn): array
    {
        $details = $this->getOrderDetail($userId, [$orderSn]);
        if (empty($details)) {
            return ['success' => false, 'message' => "Pedido {$orderSn} não encontrado na Shopee."];
        }

        return $this->saveOrder($details[0], $userId);
    }

    /**
     * Importa pedidos recentes (últimos 15 dias, todos os status), baixando
     * estoque uma vez por pedido pago e devolvendo nos cancelados.
     *
     * @param int $userId
     * @return array ['imported' => int, 'errors' => int]
     */
    public function importRecentOrders(int $userId): array
    {
        $imported = 0;
        $errors   = 0;
        $cursor   = '';

        for ($page = 0; $page < 20; $page++) {
            $list = $this->getOrderList($userId, null, 100, $cursor);
            $sns  = array_column($list['order_list'] ?? [], 'order_sn');

            if (!empty($sns)) {
                foreach ($this->getOrderDetail($userId, $sns) as $order) {
                    $result = $this->saveOrder($order, $userId);
                    if ($result['success'] ?? false) {
                        $imported++;
                    } else {
                        $errors++;
                        Log::error('ShopeeOrderService: erro ao importar pedido', [
                            'order_sn' => $order['order_sn'] ?? '?',
                            'error'    => $result['message'] ?? '',
                        ]);
                    }
                }
            }

            if (empty($list['more']) || empty($list['next_cursor'])) {
                break;
            }
            $cursor = (string) $list['next_cursor'];
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Grava um pedido (get_order_detail) com as tarifas do escrow.
     */
    private function saveOrder(array $order, int $userId): array
    {
        $orderSn = (string) ($order['order_sn'] ?? '');
        $status  = (string) ($order['order_status'] ?? '');

        // Antes do pagamento a Shopee não tem repasse
        $fee = ($orderSn !== '' && $status !== 'UNPAID') ? $this->getOrderFees($userId, $orderSn) : null;

        $shopId = (string) (ShopeeToken::getActiveForUser($userId)?->shop_id ?? '');

        return $this->stockSyncService->syncOrder($order, $userId, $shopId, $fee);
    }
}
