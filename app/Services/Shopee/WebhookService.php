<?php

namespace App\Services\Shopee;

use App\Models\ShopeeOrder;
use App\Models\ShopeeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para validação e despacho de Webhooks da Shopee.
 *
 * A Shopee envia webhooks com:
 * - Header `Authorization`: HMAC-SHA256(url + '|' + body, partner_key)
 * - Body JSON com: code, shop_id, timestamp, data
 *
 * Documentação: https://open.shopee.com/documents/v2/OpenAPI_Guide#Webhook
 */
class WebhookService extends ShopeeService
{
    protected StockSyncService $stockSyncService;

    // Códigos de push da Shopee (Open Platform v2)
    const NOTIFICATION_ORDER_STATUS   = 3;   // order_status_push
    const NOTIFICATION_TRACKING_NO    = 4;   // order_trackingno_push
    const NOTIFICATION_BANNED_ITEM    = 6;   // banned_item_push (só registra)

    public function __construct(StockSyncService $stockSyncService)
    {
        parent::__construct();
        $this->stockSyncService = $stockSyncService;
    }

    // =========================================================================
    // Validação do Webhook
    // =========================================================================

    /**
     * Valida a autenticidade de um push recebido da Shopee.
     *
     * Header "Authorization" = HMAC-SHA256( URL de callback + "|" + body, partner_key ),
     * onde a URL é a URL completa cadastrada no console da Shopee.
     * Atrás de proxy o esquema pode chegar como http: confere as variações.
     */
    public function validateWebhook(Request $request): bool
    {
        $authorization = trim((string) $request->header('Authorization'));

        if ($authorization === '' || $this->partnerKey === '') {
            Log::warning('ShopeeWebhook: header Authorization ausente ou partner_key não configurada');
            return false;
        }

        $rawBody = $request->getContent();

        $urls = array_unique(array_filter([
            $request->url(),
            $request->fullUrl(),
            preg_replace('#^http://#', 'https://', $request->url()),
            preg_replace('#^http://#', 'https://', $request->fullUrl()),
            route('shopee.webhook.handle'),
            preg_replace('#^http://#', 'https://', route('shopee.webhook.handle')),
        ]));

        foreach ($urls as $url) {
            $expected = hash_hmac('sha256', $url . '|' . $rawBody, $this->partnerKey);
            if (hash_equals($expected, strtolower($authorization))) {
                return true;
            }
        }

        Log::warning('ShopeeWebhook: assinatura inválida', [
            'url'      => $request->url(),
            'received' => substr($authorization, 0, 10) . '...',
        ]);
        return false;
    }

    // =========================================================================
    // Processamento do Webhook
    // =========================================================================

    /**
     * Processa o payload de um push recebido.
     * Roda como o dono da loja (sem usuário logado os produtos não aparecem).
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

        return self::runAsUser($userId, fn () => match ($code) {
            self::NOTIFICATION_ORDER_STATUS => $this->handleOrderStatus($payload, $userId),
            self::NOTIFICATION_TRACKING_NO  => $this->handleTrackingNumber($payload, $userId),
            default                         => ['success' => true, 'message' => "Notificação {$code} recebida (não processada)."],
        });
    }

    // =========================================================================
    // Handlers específicos
    // =========================================================================

    /**
     * Código 3: mudança de status de pedido.
     * O push só traz ordersn/status: busca o pedido completo (itens, valores)
     * e grava; o estoque é baixado uma vez por pedido e devolvido no cancelamento.
     */
    private function handleOrderStatus(array $payload, int $userId): array
    {
        $data    = $payload['data'] ?? [];
        $orderSn = (string) ($data['ordersn'] ?? $data['order_sn'] ?? '');
        $status  = (string) ($data['status'] ?? '');

        if (!$orderSn) {
            return ['success' => false, 'message' => 'order_sn ausente no payload'];
        }

        Log::info('ShopeeWebhook: mudança de status de pedido', [
            'order_sn' => $orderSn,
            'status'   => $status,
        ]);

        try {
            $result = app(OrderService::class)->syncOrderBySn($userId, $orderSn);
            if ($result['success'] ?? false) {
                return $result;
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        // Sem detalhes da API: ainda assim cancelamento devolve o estoque
        if ($status === 'CANCELLED') {
            $back = $this->stockSyncService->restoreShopeeOrder($orderSn, $userId);
            return ['success' => true, 'message' => "Pedido cancelado: {$back} item(ns) devolvido(s) ao estoque."];
        }

        if ($status !== '') {
            ShopeeOrder::where('shopee_order_sn', $orderSn)
                ->where('user_id', $userId)
                ->where('order_status', '!=', 'CANCELLED')
                ->update(['order_status' => $status]);
        }

        // Falha na API: devolve erro para a fila/Shopee tentar de novo
        throw new \RuntimeException('Não foi possível buscar o pedido na Shopee: ' . ($result['message'] ?? ''));
    }

    /**
     * Código 4: código de rastreio do pedido.
     */
    private function handleTrackingNumber(array $payload, int $userId): array
    {
        $data     = $payload['data'] ?? [];
        $orderSn  = (string) ($data['ordersn'] ?? $data['order_sn'] ?? '');
        $tracking = (string) ($data['tracking_no'] ?? $data['tracking_number'] ?? '');

        if (!$orderSn || !$tracking) {
            return ['success' => true, 'message' => 'Push de rastreio sem pedido/código.'];
        }

        $n = ShopeeOrder::where('shopee_order_sn', $orderSn)
            ->where('user_id', $userId)
            ->update(['tracking_number' => mb_substr($tracking, 0, 100)]);

        return ['success' => true, 'message' => $n ? "Rastreio {$tracking} salvo." : "Pedido {$orderSn} ainda não importado."];
    }

    // =========================================================================
    // Resolver usuário pelo shop_id
    // =========================================================================

    /**
     * Descobre o user_id interno a partir do shop_id do webhook.
     */
    public function resolveUserByShopId(string $shopId): ?int
    {
        if ($shopId === '') {
            return null;
        }

        $token = ShopeeToken::where('shop_id', $shopId)
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return $token?->user_id;
    }
}
