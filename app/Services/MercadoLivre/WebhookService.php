<?php

namespace App\Services\MercadoLivre;

use App\Models\MercadoLivre\MercadoLivreWebhook;
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
    
    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
        $this->productService = new ProductService();
    }
    
    /**
     * Validar autenticidade do webhook
     * 
     * @param Request $request
     * @return bool
     */
    public function validateWebhook(Request $request): bool
    {
        try {
            // ML envia assinatura no header X-Hub-Signature
            $signature = $request->header('X-Hub-Signature');
            
            if (!$signature) {
                Log::warning('Webhook sem assinatura recebido');
                return false;
            }
            
            // Verificar com secret do .env (se configurado)
            $webhookSecret = config('mercadolivre.webhook_secret');
            
            if (!$webhookSecret) {
                // Se não tiver secret configurado, aceitar (modo desenvolvimento)
                Log::info('Webhook aceito sem validação (secret não configurado)');
                return true;
            }
            
            // Calcular assinatura esperada
            $payload = $request->getContent();
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Assinatura de webhook inválida', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]);
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Erro ao validar webhook', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
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
        try {
            // Registrar webhook recebido
            $webhook = $this->logWebhook($topic, $resource, $rawData);
            
            // Processar baseado no tópico
            $result = match($topic) {
                'orders' => $this->handleOrderWebhook($resource),
                'items' => $this->handleItemWebhook($resource),
                'questions' => $this->handleQuestionWebhook($resource),
                'claims' => $this->handleClaimWebhook($resource),
                'messages' => $this->handleMessageWebhook($resource),
                default => [
                    'success' => false,
                    'message' => "Tópico desconhecido: {$topic}",
                ],
            };
            
            // Atualizar status do webhook
            $webhook->update([
                'status' => $result['success'] ? 'processed' : 'failed',
                'processed_at' => Carbon::now(),
                'response' => json_encode($result),
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook', [
                'topic' => $topic,
                'resource' => $resource,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Registrar webhook no banco de dados
     * 
     * @param string $topic
     * @param string $resource
     * @param array $rawData
     * @return MercadoLivreWebhook
     */
    protected function logWebhook(string $topic, string $resource, array $rawData): MercadoLivreWebhook
    {
        return MercadoLivreWebhook::create([
            'topic' => $topic,
            'resource' => $resource,
            'user_id' => $rawData['user_id'] ?? null,
            'application_id' => $rawData['application_id'] ?? null,
            'attempts' => $rawData['attempts'] ?? 1,
            'sent' => isset($rawData['sent']) ? Carbon::parse($rawData['sent']) : Carbon::now(),
            'received' => Carbon::now(),
            'status' => 'pending',
            'raw_data' => json_encode($rawData),
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
            
            // Buscar pedido no ML
            $orderData = $this->orderService->getOrderDetails($orderId);
            
            if (!$orderData) {
                return [
                    'success' => false,
                    'message' => 'Pedido não encontrado no ML',
                ];
            }
            
            // Verificar se já existe no sistema
            $existingOrder = \App\Models\MercadoLivre\MercadoLivreOrder::where('ml_order_id', $orderId)->first();
            
            if ($existingOrder) {
                // Atualizar pedido existente
                $existingOrder->update([
                    'status' => $orderData['status'],
                    'status_detail' => $orderData['status_detail'] ?? null,
                    'shipping' => json_encode($orderData['shipping'] ?? []),
                    'payments' => json_encode($orderData['payments'] ?? []),
                    'last_updated' => Carbon::parse($orderData['last_updated'] ?? Carbon::now()),
                    'raw_data' => json_encode($orderData),
                ]);
                
                // Atualizar venda se necessário
                if ($existingOrder->sale && $orderData['status'] === 'cancelled') {
                    $existingOrder->sale->update(['status' => 'cancelled']);
                    
                    // Devolver estoque
                    foreach ($existingOrder->sale->items as $item) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Pedido atualizado com sucesso',
                    'action' => 'updated',
                    'order_id' => $orderId,
                ];
                
            } else {
                // Importar novo pedido
                $result = $this->orderService->importOrder($orderId);
                
                return [
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'action' => 'imported',
                    'order_id' => $orderId,
                ];
            }
            
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
            Log::info('Processando webhook de item', ['item_id' => $itemId]);
            
            // Buscar item no ML
            $itemData = $this->makeRequest('GET', "/items/{$itemId}");
            
            if (!$itemData) {
                return [
                    'success' => false,
                    'message' => 'Item não encontrado no ML',
                ];
            }
            
            // Verificar se item pertence ao usuário
            $mlProduct = \App\Models\MercadoLivre\MercadoLivreProduct::where('ml_item_id', $itemId)->first();
            
            if (!$mlProduct) {
                return [
                    'success' => false,
                    'message' => 'Item não está vinculado a nenhum produto no sistema',
                ];
            }
            
            // Atualizar informações do produto
            $mlProduct->update([
                'status' => $itemData['status'],
                'price' => $itemData['price'],
                'available_quantity' => $itemData['available_quantity'],
                'sold_quantity' => $itemData['sold_quantity'],
                'permalink' => $itemData['permalink'],
                'last_updated' => Carbon::now(),
            ]);
            
            // Sincronizar com produto local se necessário
            if ($mlProduct->product && $itemData['status'] === 'paused') {
                // Produto pausado no ML, marcar como inativo localmente?
                // Ou apenas registrar log
                Log::info('Produto pausado no ML', [
                    'ml_item_id' => $itemId,
                    'product_id' => $mlProduct->product_id,
                ]);
            }
            
            return [
                'success' => true,
                'message' => 'Item atualizado com sucesso',
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
            $questionData = $this->makeRequest('GET', "/questions/{$questionId}");
            
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
            
            // Por enquanto, apenas registrar log
            Log::info('Nova pergunta no ML', [
                'question_id' => $questionId,
                'item_id' => $questionData['item_id'] ?? null,
                'text' => $questionData['text'] ?? null,
                'status' => $questionData['status'] ?? null,
            ]);
            
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
            $claimData = $this->makeRequest('GET', "/claims/{$claimId}");
            
            if (!$claimData) {
                return [
                    'success' => false,
                    'message' => 'Reclamação não encontrada',
                ];
            }
            
            // Registrar log de reclamação
            Log::warning('Reclamação no ML', [
                'claim_id' => $claimId,
                'reason' => $claimData['reason'] ?? null,
                'status' => $claimData['status'] ?? null,
            ]);
            
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
            $messageData = $this->makeRequest('GET', "/messages/{$messageId}");
            
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
            
            $deleted = MercadoLivreWebhook::where('received', '<', $date)
                ->where('status', 'processed')
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
