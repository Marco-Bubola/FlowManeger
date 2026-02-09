<?php

namespace App\Http\Controllers\MercadoLivre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLivre\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller para receber webhooks do Mercado Livre
 */
class WebhookController extends Controller
{
    protected WebhookService $webhookService;
    
    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }
    
    /**
     * Receber e processar webhook do Mercado Livre
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Log da requisição recebida
            Log::info('Webhook recebido do Mercado Livre', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);
            
            // Validar autenticidade do webhook
            if (!$this->webhookService->validateWebhook($request)) {
                Log::warning('Webhook rejeitado: assinatura inválida');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }
            
            // Extrair dados do webhook
            $topic = $request->input('topic');
            $resource = $request->input('resource');
            
            if (!$topic || !$resource) {
                Log::warning('Webhook inválido: topic ou resource ausente', [
                    'topic' => $topic,
                    'resource' => $resource,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing topic or resource',
                ], 400);
            }
            
            // Processar webhook de forma assíncrona (job)
            // Por enquanto, processar síncrono
            $result = $this->webhookService->processWebhook(
                $topic,
                $resource,
                $request->all()
            );
            
            // Mercado Livre espera resposta rápida (< 3 segundos)
            // Sempre retornar 200 OK se webhook foi recebido
            return response()->json([
                'success' => true,
                'message' => 'Webhook received',
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Mesmo com erro, retornar 200 para ML não reenviar
            return response()->json([
                'success' => true,
                'message' => 'Webhook received (error logged)',
            ], 200);
        }
    }
    
    /**
     * Endpoint de teste para webhooks
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function test(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Webhook endpoint is working',
                'timestamp' => now()->toDateTimeString(),
                'data' => $request->all(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
