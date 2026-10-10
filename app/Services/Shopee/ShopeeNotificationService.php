<?php

namespace App\Services\Shopee;

use App\Models\ConsortiumNotification;
use App\Models\ShopeeOrder;
use Illuminate\Support\Facades\Log;

/**
 * Notificações in-app da Shopee (sino + /notificacoes), module = 'shopee'.
 * Mesmo padrão do MlNotificationService: um aviso por evento.
 */
class ShopeeNotificationService
{
    /** Pedidos mais antigos que isso (importação retroativa) não avisam. */
    public const RECENT_HOURS = 72;

    /** Status de pedido pago que contam como "nova venda". */
    public const NOTIFY_STATUSES = [
        'READY_TO_SHIP', 'PROCESSED', 'RETRY_SHIP', 'SHIPPED',
        'TO_CONFIRM_RECEIVE', 'COMPLETED', 'INVOICE_PENDING',
    ];

    /**
     * Avisa o dono da loja sobre um pedido pago. Uma vez por pedido: pushes
     * repetidos, mudanças de status e reimportações não geram outro aviso.
     */
    public function notifyNewOrder(ShopeeOrder $order): ?ConsortiumNotification
    {
        try {
            $userId = (int) $order->user_id;
            if (!$userId || !in_array($order->order_status, self::NOTIFY_STATUSES, true)) {
                return null;
            }

            // Importação de pedidos antigos (primeira conexão, reimportação): sem aviso
            $createdAt = $order->shopee_created_at ?? $order->created_at;
            if ($createdAt && $createdAt->lt(now()->subHours(self::RECENT_HOURS))) {
                return null;
            }

            if ($this->alreadyNotified($userId, (int) $order->id)) {
                return null;
            }

            $items = is_array($order->order_items) ? $order->order_items : [];
            $qty = 0;
            foreach ($items as $item) {
                $qty += (int) ($item['model_quantity_purchased'] ?? $item['quantity'] ?? 1);
            }
            $firstName = trim((string) ($items[0]['item_name'] ?? ''));
            $total = 'R$ ' . number_format((float) $order->total_amount, 2, ',', '.');

            if ($firstName !== '') {
                $what = \Illuminate\Support\Str::limit($firstName, 60);
                if (count($items) > 1) {
                    $what .= ' e mais ' . (count($items) - 1) . ' ' . (count($items) - 1 === 1 ? 'item' : 'itens');
                }
                $message = "Você vendeu {$what} ({$total}). Pedido #{$order->shopee_order_sn}.";
            } else {
                $message = "Pedido #{$order->shopee_order_sn} de {$total} recebido. Hora de separar!";
            }

            return ConsortiumNotification::createGeneric(
                'shopee',
                'order_received',
                $userId,
                'Nova venda na Shopee',
                $message,
                [
                    'priority' => 'high',
                    'entity_type' => 'ShopeeOrder',
                    'entity_id' => $order->id,
                    'action_url' => $this->safeRoute('shopee.orders', ['pedido' => (string) $order->shopee_order_sn]),
                    'data' => [
                        'order_sn' => (string) $order->shopee_order_sn,
                        'total' => (float) $order->total_amount,
                        'items' => $qty,
                        'status' => $order->order_status,
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar notificação Shopee', ['order_sn' => $order->shopee_order_sn, 'error' => $e->getMessage()]);

            return null;
        }
    }

    protected function alreadyNotified(int $userId, int $orderId): bool
    {
        // withoutGlobalScopes inclui as que o usuário excluiu (não recria)
        return ConsortiumNotification::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('module', 'shopee')
            ->where('type', 'order_received')
            ->where('entity_type', 'ShopeeOrder')
            ->where('entity_id', $orderId)
            ->exists();
    }

    protected function safeRoute(string $name, array $params = []): ?string
    {
        try {
            return route($name, $params, false);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
