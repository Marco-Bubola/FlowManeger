<?php

namespace App\Services\MercadoLivre;

use App\Models\MercadoLivreWebhook;
use App\Models\MercadoLivreOrder;
use App\Models\MlPublication;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use App\Services\MercadoLivre\MlNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Service para processar webhooks do Mercado Livre
 * 
 * Webhooks recebem notificações em tempo real sobre:
 * - Novos pedidos
 * - Alterações em pedidos
 * - Perguntas de clientes
 * - Atualizações de produtos
 * - Mensagens
 */
class WebhookService extends MercadoLivreService
{
    protected OrderService $orderService;
    protected ProductService $productService;
    protected MlStockSyncService $stockSyncService;
    protected MlNotificationService $notifications;

    /** Token do dono da conta ML do webhook em processamento. */
    protected ?string $accessToken = null;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
        $this->productService = new ProductService();
        // MlStockSyncService depende de AuthService → resolver via container
        $this->stockSyncService = app(MlStockSyncService::class);
        $this->notifications = new MlNotificationService();
    }
    
    /**
     * Validar autenticidade do webhook
     * 
     * @param Request $request
     * @return bool
     */
    public function validateWebhook(Request $request): bool
    {
        // O ML não assina as notificações: confere se vieram para o nosso app.
        $appId = (string) config('services.mercadolivre.app_id');
        $received = (string) $request->input('application_id', '');

        if ($appId !== '' && $received !== '' && $received !== $appId) {
            Log::warning('Webhook ML de outro aplicativo ignorado', ['application_id' => $received]);
            return false;
        }

        return true;
    }

    /**
     * Processar notificação de webhook
     * 
     * @param string $topic Tópico da notificação (orders, items, questions, etc)
     * @param string $resource ID do recurso
     * @param array $rawData Dados brutos recebidos
     * @return array
     */
    public function processWebhook(string $topic, string $resource, array $rawData = []): array
    {
        // Compatibilidade: registra e processa de uma vez (usado pela rota de teste/cron).
        $webhook = $this->record($topic, $resource, $rawData);
        return $this->processLogged($webhook);
    }

    /**
     * Apenas registra o webhook recebido (para depois processar via fila).
     */
    public function record(string $topic, string $resource, array $rawData = []): MercadoLivreWebhook
    {
        return $this->logWebhook($topic, $resource, $rawData);
    }

    /**
     * Processa um webhook já registrado (chamado pelo job ProcessMercadoLivreWebhook).
     */
    public function processLogged(MercadoLivreWebhook $webhook): array
    {
        // O webhook chega sem ninguém logado: age como o dono da conta ML,
        // para usar o token dele e enxergar os produtos dele.
        $ownerId = $this->notifications->resolveUserBySeller((int) $webhook->ml_user_id);
        $previousUser = Auth::user();

        try {
            if (!$ownerId) {
                $result = ['success' => false, 'message' => 'Conta do Mercado Livre não conectada a nenhum usuário'];
                $webhook->markAsError($result['message']);
                return $result;
            }

            Auth::onceUsingId($ownerId);
            $this->accessToken = app(AuthService::class)->getActiveToken($ownerId)?->access_token;

            $resourceId = $webhook->getResourceId() ?: $webhook->resource;

            $result = match($webhook->topic) {
                'orders', 'orders_v2' => $this->handleOrderWebhook($resourceId),
                'items'     => $this->handleItemWebhook($resourceId),
                'questions' => $this->handleQuestionWebhook($resourceId),
                'claims'    => $this->handleClaimWebhook($resourceId),
                'messages'  => $this->handleMessageWebhook($resourceId),
                default     => [
                    'success' => true,
                    'message' => "Tópico ignorado: {$webhook->topic}",
                ],
            };

            if ($result['success'] ?? false) {
                $webhook->markAsProcessed($result);
            } else {
                $webhook->markAsError($result['message'] ?? 'Falha no processamento do webhook');
            }

            return $result;

        } catch (\Exception $e) {
            $webhook->markAsError($e->getMessage());

            Log::error('Erro ao processar webhook', [
                'webhook_id' => $webhook->id,
                'topic' => $webhook->topic,
                'resource' => $webhook->resource,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ];
        } finally {
            if ($previousUser) {
                Auth::setUser($previousUser);
            } elseif (method_exists(Auth::guard(), 'forgetUser')) {
                Auth::guard()->forgetUser();
            }
        }
    }

    /**
     * Registrar webhook no banco de dados (colunas reais da tabela).
     */
    protected function logWebhook(string $topic, string $resource, array $rawData): MercadoLivreWebhook
    {
        return MercadoLivreWebhook::create([
            'topic' => $topic,
            'resource' => $resource,
            'ml_user_id' => (int) ($rawData['user_id'] ?? 0),
            'application_id' => (int) ($rawData['application_id'] ?? 0),
            'attempts' => 0,
            'sent' => isset($rawData['sent']) ? Carbon::parse($rawData['sent']) : Carbon::now(),
            'received_at' => Carbon::now(),
            'processed' => false,
            'raw_data' => $rawData,
        ]);
    }
    
    /**
     * Processar webhook de pedido
     * 
     * @param string $orderId ID do pedido no ML
     * @return array
     */
    public function handleOrderWebhook(string $orderId): array
    {
        try {
            Log::info('Processando webhook de pedido', ['order_id' => $orderId]);

            $orderData = $this->orderService->getOrderDetails($orderId);

            if (!$orderData) {
                return [
                    'success' => false,
                    'message' => 'Pedido não encontrado no ML',
                ];
            }

            $status = $orderData['status'] ?? '';
            $existing = MercadoLivreOrder::where('ml_order_id', $orderId)->first();
            $wasPaid = in_array($existing?->order_status, ['paid', 'confirmed'], true);

            // Baixa o estoque (uma vez por pedido; repetições são ignoradas).
            $stockResults = [];
            if (in_array($status, ['paid', 'confirmed'], true)) {
                foreach ($orderData['order_items'] ?? [] as $item) {
                    $mlItemId = $item['item']['id'] ?? null;
                    if (!$mlItemId) {
                        continue;
                    }
                    $stockResult = $this->stockSyncService->processMercadoLivreSale($orderId, $mlItemId, (int) ($item['quantity'] ?? 1));
                    $stockResults[] = $stockResult;

                    if (!($stockResult['success'] ?? false)) {
                        Log::warning('Falha ao processar estoque de item', [
                            'order_id' => $orderId,
                            'ml_item_id' => $mlItemId,
                            'error' => $stockResult['message'] ?? 'Unknown error',
                        ]);
                    }
                }
            }

            // Guarda valor, comprador e status (o painel soma daqui).
            $order = $this->orderService->recordOrder($orderData);
            $order->update(['raw_data' => $orderData]);

            // Cancelado no ML: devolve o estoque e cancela a venda importada.
            if ($status === 'cancelled') {
                $this->stockSyncService->restoreMercadoLivreSale((string) $orderId);

                $sale = $order->imported_to_sale_id ? Sale::find($order->imported_to_sale_id) : null;
                if ($sale && $sale->status !== 'cancelada') {
                    // O estoque já voltou pelo histórico do ML acima.
                    $sale->forceFill(['status' => 'cancelada', 'stock_applied' => false])->save();
                }
            }

            // Avisa só na primeira vez que o pedido aparece pago.
            if (!$wasPaid && in_array($status, ['paid', 'confirmed'], true)) {
                $this->notifications->notifyNewOrder(Auth::id(), (string) $orderId);
            }

            return [
                'success' => true,
                'message' => $existing ? 'Pedido atualizado' : 'Pedido registrado',
                'order_id' => $orderId,
                'stock_results' => $stockResults,
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de pedido', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar pedido: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processar webhook de item (produto)
     * 
     * @param string $itemId ID do item no ML
     * @return array
     */
    public function handleItemWebhook(string $itemId): array
    {
        try {
            $publication = MlPublication::where('ml_item_id', $itemId)->where('user_id', Auth::id())->first();

            if (!$publication) {
                return [
                    'success' => true,
                    'message' => 'Anúncio não está no sistema (ignorado)',
                ];
            }

            $itemData = $this->makeRequest('GET', "/items/{$itemId}", [], $this->accessToken, Auth::id());

            // Status do ML fora da lista da tabela vira "pausado".
            $mlStatus = $itemData['status'] ?? null;
            $status = in_array($mlStatus, ['active', 'paused', 'closed', 'under_review'], true) ? $mlStatus : 'paused';

            $publication->update(array_filter([
                'status' => $mlStatus ? $status : null,
                'price' => $itemData['price'] ?? null,
                'ml_permalink' => $itemData['permalink'] ?? null,
            ], fn ($v) => $v !== null));

            return [
                'success' => true,
                'message' => 'Anúncio atualizado',
                'item_id' => $itemId,
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de item', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar item: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processar webhook de pergunta
     * 
     * @param string $questionId ID da pergunta
     * @return array
     */
    public function handleQuestionWebhook(string $questionId): array
    {
        try {
            Log::info('Webhook de pergunta recebido', ['question_id' => $questionId]);
            
            // Buscar pergunta no ML
            $questionData = $this->makeRequest('GET', "/questions/{$questionId}", [], $this->accessToken, Auth::id());
            
            if (!$questionData) {
                return [
                    'success' => false,
                    'message' => 'Pergunta não encontrada',
                ];
            }
            
            // Aqui você pode:
            // - Enviar notificação ao usuário
            // - Salvar pergunta no banco
            // - Integrar com sistema de atendimento
            
            Log::info('Nova pergunta no ML', [
                'question_id' => $questionId,
                'item_id' => $questionData['item_id'] ?? null,
                'text' => $questionData['text'] ?? null,
                'status' => $questionData['status'] ?? null,
            ]);

            // Notificar o dono do anúncio
            $userId = Auth::id();
            if ($userId) {
                $this->notifications->notifyNewQuestion($userId, (string) $questionId, $questionData['text'] ?? null);
            }

            return [
                'success' => true,
                'message' => 'Pergunta registrada',
                'question_id' => $questionId,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de pergunta', [
                'question_id' => $questionId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao processar pergunta: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Processar webhook de reclamação
     * 
     * @param string $claimId ID da reclamação
     * @return array
     */
    public function handleClaimWebhook(string $claimId): array
    {
        try {
            Log::info('Webhook de reclamação recebido', ['claim_id' => $claimId]);
            
            // Buscar reclamação no ML
            $claimData = $this->makeRequest('GET', "/post-purchase/v1/claims/{$claimId}", [], $this->accessToken, Auth::id());
            
            if (!$claimData) {
                return [
                    'success' => false,
                    'message' => 'Reclamação não encontrada',
                ];
            }
            
            // Registrar log de reclamação
            Log::warning('Reclamação no ML', [
                'claim_id' => $claimId,
                'reason' => $claimData['reason_id'] ?? null,
                'status' => $claimData['status'] ?? null,
            ]);

            // Notificar (resolve pelo item relacionado, se houver)
            $userId = Auth::id();
            if ($userId) {
                $this->notifications->notifyClaim($userId, (string) $claimId, $claimData['reason_id'] ?? null);
            }

            return [
                'success' => true,
                'message' => 'Reclamação registrada',
                'claim_id' => $claimId,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de reclamação', [
                'claim_id' => $claimId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao processar reclamação: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Processar webhook de mensagem
     * 
     * @param string $messageId ID da mensagem
     * @return array
     */
    public function handleMessageWebhook(string $messageId): array
    {
        try {
            Log::info('Webhook de mensagem recebido', ['message_id' => $messageId]);
            
            // Buscar mensagem no ML
            $messageData = $this->makeRequest('GET', "/messages/{$messageId}?tag=post_sale", [], $this->accessToken, Auth::id());
            
            if (!$messageData) {
                return [
                    'success' => false,
                    'message' => 'Mensagem não encontrada',
                ];
            }
            
            // Registrar log de mensagem
            Log::info('Nova mensagem no ML', [
                'message_id' => $messageId,
                'from' => $messageData['from']['user_id'] ?? null,
                'subject' => $messageData['subject'] ?? null,
            ]);

            // Notificar o destinatário (vendedor) da mensagem
            $userId = Auth::id();
            if ($userId) {
                $this->notifications->notifyNewMessage($userId, (string) $messageId);
            }

            return [
                'success' => true,
                'message' => 'Mensagem registrada',
                'message_id' => $messageId,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de mensagem', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao processar mensagem: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Limpar webhooks antigos
     * 
     * @param int $days Dias para manter (padrão: 30)
     * @return int Quantidade de registros deletados
     */
    public function cleanupOldWebhooks(int $days = 30): int
    {
        try {
            $date = Carbon::now()->subDays($days);
            
            $deleted = MercadoLivreWebhook::where('received_at', '<', $date)
                ->where('processed', true)
                ->delete();
            
            Log::info("Webhooks antigos limpos: {$deleted} registros");
            
            return $deleted;
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar webhooks antigos', [
                'error' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }
}
