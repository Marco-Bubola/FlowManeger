<?php

namespace App\Http\Controllers\Shopee;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessShopeeWebhook;
use App\Services\Shopee\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller para receber Webhooks da Shopee Open Platform.
 *
 * IMPORTANTE: A Shopee espera uma resposta HTTP 200 em menos de 3 segundos.
 * Por isso, o processamento é delegado para um Job em background.
 *
 * Endpoint: POST /shopee/webhook
 */
class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    /**
     * Recebe e valida o webhook da Shopee, depois despacha para processamento assíncrono.
     */
    public function handle(Request $request): JsonResponse
    {
        // Log imediato para auditoria
        Log::info('ShopeeWebhook: recebido', [
            'code'    => $request->input('code'),
            'shop_id' => $request->input('shop_id'),
            'ip'      => $request->ip(),
        ]);

        // Validar assinatura HMAC-SHA256
        if (!$this->webhookService->validateWebhook($request)) {
            Log::warning('ShopeeWebhook: assinatura inválida rejeitada', [
                'ip' => $request->ip(),
            ]);
            // Retornar 200 mesmo assim — a Shopee reenveria com 4xx
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 200);
        }

        $payload = $request->all();
        $shopId  = (string) ($payload['shop_id'] ?? '');

        // Resolver o usuário interno pelo shop_id
        $userId = $this->webhookService->resolveUserByShopId($shopId);

        if (!$userId) {
            Log::warning('ShopeeWebhook: shop_id não encontrado no sistema', [
                'shop_id' => $shopId,
            ]);
            return response()->json(['success' => true, 'message' => 'Shop not registered'], 200);
        }

        // Despacha para fila — libera a resposta HTTP imediatamente
        ProcessShopeeWebhook::dispatch($payload, $userId)
            ->onQueue('marketplace');

        return response()->json(['success' => true, 'message' => 'Webhook received'], 200);
    }

    /**
     * Endpoint de verificação/healthcheck (GET).
     * A Shopee pode chamar este endpoint para confirmar que o servidor está ativo.
     */
    public function verify(Request $request): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'message'   => 'FlowManager Shopee Webhook endpoint is active.',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
