<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para validação e despacho de Webhooks da Shopee.
 *
 * A Shopee envia webhooks com:
 * - Header `Authorization`: assinatura HMAC-SHA256 do body
 * - Body JSON com: code, shop_id, timestamp, data
 *
 * Documentação: https://open.shopee.com/documents/v2/OpenAPI_Guide#Webhook
 */
class WebhookService extends ShopeeService
{
    protected StockSyncService $stockSyncService;

    // Códigos de notificação da Shopee
    const NOTIFICATION_ORDER_STATUS = 3;   // Mudança de status de pedido
    const NOTIFICATION_ITEM_UPDATE  = 9;   // Atualização de item
    const NOTIFICATION_BANNED       = 11;  // Loja/item banido

    public function __construct(StockSyncService $stockSyncService)
    {
        parent::__construct();
        $this->stockSyncService = $stockSyncService;
    }

    // =========================================================================
    // Validação do Webhook
    // =========================================================================

    /**
     * Valida a autenticidade de um webhook recebido da Shopee.
     *
     * A Shopee inclui o header "Authorization" com:
     *   HMAC-SHA256( partner_id + partner_key + timestamp + shop_id + body )
     * ou
     *   SHA256( url_path + "|" + body )  (dependendo da versão)
     *
     * Ref: https://open.shopee.com/documents/v2/OpenAPI_Guide#Webhook
     */
    public function validateWebhook(Request $request): bool
    {
        $authorization = $request->header('Authorization');

        if (!$authorization) {
            Log::warning('ShopeeWebhook: header Authorization ausente');
            return false;
        }

        $rawBody = $request->getContent();

        // A assinatura da Shopee é: HMAC_SHA256(partner_key, URL + "|" + body)
        $urlPath  = $request->getPathInfo();
        $expected = hash_hmac('sha256', $urlPath . '|' . $rawBody, $this->partnerKey);

        if (!hash_equals($expected, $authorization)) {
            Log::warning('ShopeeWebhook: assinatura inválida', [
                'expected'  => substr($expected, 0, 10) . '...',
                'received'  => substr($authorization, 0, 10) . '...',
            ]);
            return false;
        }

        return true;
    }

    // =========================================================================
    // Processamento do Webhook
    // =========================================================================

    /**
     * Processa o payload de um webhook recebido.
     *
     * @param array $payload Dados do webhook
     * @param int   $userId  Usuário dono da loja (resolvido via shop_id)
     */
    public function processWebhook(array $payload, int $userId): array
    {
        $code   = (int) ($payload['code'] ?? 0);
        $shopId = (string) ($payload['shop_id'] ?? '');

        Log::info('ShopeeWebhook: notificação recebida', [
            'code'    => $code,
            'shop_id' => $shopId,
        ]);

        return match ($code) {
            self::NOTIFICATION_ORDER_STATUS => $this->handleOrderStatus($payload, $userId),
            self::NOTIFICATION_ITEM_UPDATE  => $this->handleItemUpdate($payload, $userId),
            default                         => ['success' => true, 'message' => "Notificação {$code} recebida (não processada)."],
        };
    }

    // =========================================================================
    // Handlers específicos
    // =========================================================================

    /**
     * Processa notificação de mudança de status de pedido.
     * Código 3: order status change
     */
    private function handleOrderStatus(array $payload, int $userId): array
    {
        $data    = $payload['data'] ?? [];
        $orderSn = (string) ($data['ordersn'] ?? '');
        $status  = (string) ($data['status'] ?? '');

        if (!$orderSn) {
            return ['success' => false, 'message' => 'order_sn ausente no payload'];
        }

        Log::info('ShopeeWebhook: mudança de status de pedido', [
            'order_sn' => $orderSn,
            'status'   => $status,
        ]);

        // Deduzir estoque apenas quando o pedido está pronto para envio
        if (in_array($status, ['READY_TO_SHIP', 'PROCESSED'])) {
            return $this->stockSyncService->processShopeeOrder(
                orderSn:       $orderSn,
                shopeeItemId:  (string) ($data['shopee_item_id'] ?? ''),
                shopeeModelId: (string) ($data['model_id'] ?? '') ?: null,
                quantity:      (int) ($data['quantity'] ?? 1),
                userId:        $userId,
                rawData:       array_merge($payload['data'] ?? [], [
                    'shop_id' => $payload['shop_id'] ?? '',
                    'status'  => $status,
                ])
            );
        }

        return ['success' => true, 'message' => "Status {$status} registrado (sem ação de estoque)."];
    }

    /**
     * Processa notificação de atualização de item.
     * Código 9: item update
     */
    private function handleItemUpdate(array $payload, int $userId): array
    {
        $data       = $payload['data'] ?? [];
        $itemId     = (string) ($data['item_id'] ?? '');
        $updateType = (string) ($data['update_type'] ?? '');

        Log::info('ShopeeWebhook: atualização de item', [
            'item_id'     => $itemId,
            'update_type' => $updateType,
        ]);

        return ['success' => true, 'message' => "Item {$itemId} atualização recebida: {$updateType}"];
    }

    // =========================================================================
    // Resolver usuário pelo shop_id
    // =========================================================================

    /**
     * Descobre o user_id interno a partir do shop_id do webhook.
     */
    public function resolveUserByShopId(string $shopId): ?int
    {
        $token = ShopeeToken::where('shop_id', $shopId)
            ->where('is_active', true)
            ->first();

        return $token?->user_id;
    }
}
