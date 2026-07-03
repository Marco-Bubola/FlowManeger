<?php

namespace App\Services\MercadoLivre;

use App\Models\ConsortiumNotification;
use App\Models\MlPublication;
use App\Models\MercadoLivreProduct;
use App\Models\MercadoLivreToken;
use Illuminate\Support\Facades\Log;

/**
 * Cria notificações in-app para eventos do Mercado Livre (pedido, pergunta,
 * mensagem, reclamação, erro de sync). Reutiliza a tabela/Model genérico
 * ConsortiumNotification (module = 'mercadolivre'), exibida no sino unificado.
 */
class MlNotificationService
{
    public function notifyNewOrder(int $userId, string $orderId, ?string $detail = null): void
    {
        $this->create(
            $userId,
            'order_received',
            'Novo pedido no Mercado Livre',
            $detail ?: "Pedido #{$orderId} recebido no Mercado Livre.",
            [
                'priority' => 'high',
                'action_url' => $this->safeRoute('mercadolivre.orders.show', $orderId) ?? $this->safeRoute('mercadolivre.orders'),
                'entity_type' => 'MercadoLivreOrder',
                'data' => ['order_id' => $orderId],
            ]
        );
    }

    public function notifyNewQuestion(int $userId, string $questionId, ?string $text = null): void
    {
        $this->create(
            $userId,
            'question_received',
            'Nova pergunta no Mercado Livre',
            $text ? \Illuminate\Support\Str::limit($text, 120) : "Você recebeu uma nova pergunta (#{$questionId}).",
            [
                'priority' => 'high',
                'action_url' => $this->safeRoute('mercadolivre.questions'),
                'data' => ['question_id' => $questionId],
            ]
        );
    }

    public function notifyNewMessage(int $userId, string $messageId): void
    {
        $this->create(
            $userId,
            'message_received',
            'Nova mensagem no Mercado Livre',
            'Você recebeu uma nova mensagem pós-venda.',
            [
                'priority' => 'medium',
                'action_url' => $this->safeRoute('mercadolivre.messages'),
                'data' => ['message_id' => $messageId],
            ]
        );
    }

    public function notifyClaim(int $userId, string $claimId, ?string $reason = null): void
    {
        $this->create(
            $userId,
            'claim_opened',
            'Reclamação no Mercado Livre',
            $reason ? "Reclamação aberta: {$reason}" : "Uma reclamação foi aberta (#{$claimId}).",
            [
                'priority' => 'high',
                'action_url' => $this->safeRoute('mercadolivre.mediations'),
                'data' => ['claim_id' => $claimId],
            ]
        );
    }

    public function notifySyncError(int $userId, string $itemId, string $error): void
    {
        $this->create(
            $userId,
            'sync_error',
            'Erro de sincronização no Mercado Livre',
            "Falha ao sincronizar o anúncio {$itemId}: " . \Illuminate\Support\Str::limit($error, 140),
            [
                'priority' => 'high',
                'action_url' => $this->safeRoute('mercadolivre.publications'),
                'data' => ['ml_item_id' => $itemId],
            ]
        );
    }

    /**
     * Descobre o user_id do app a partir do ID do anúncio (item) do ML.
     */
    public function resolveUserByItem(?string $mlItemId): ?int
    {
        if (!$mlItemId) {
            return null;
        }

        $userId = MlPublication::where('ml_item_id', $mlItemId)->value('user_id');
        if ($userId) {
            return (int) $userId;
        }

        $product = MercadoLivreProduct::where('ml_item_id', $mlItemId)->with('product')->first();
        return $product?->product?->user_id;
    }

    /**
     * Descobre o user_id do app a partir do ID do vendedor no ML.
     */
    public function resolveUserBySeller(?int $mlUserId): ?int
    {
        if (!$mlUserId) {
            return null;
        }
        return MercadoLivreToken::where('ml_user_id', $mlUserId)->value('user_id');
    }

    protected function create(int $userId, string $type, string $title, string $message, array $options = []): void
    {
        try {
            ConsortiumNotification::createGeneric('mercadolivre', $type, $userId, $title, $message, $options);
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar notificação ML', ['type' => $type, 'error' => $e->getMessage()]);
        }
    }

    protected function safeRoute(string $name, $param = null): ?string
    {
        try {
            return $param !== null ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
