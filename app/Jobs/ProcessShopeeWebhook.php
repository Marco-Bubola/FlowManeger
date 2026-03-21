<?php

namespace App\Jobs;

use App\Services\Shopee\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job assíncrono para processar webhooks da Shopee.
 * Libera a resposta HTTP imediatamente (< 3s) e processa em background.
 */
class ProcessShopeeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public array $backoff = [30, 120, 300];
    public int $timeout = 60;

    public function __construct(
        public array $payload,
        public int   $userId
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $result = $webhookService->processWebhook($this->payload, $this->userId);

        Log::info('ProcessShopeeWebhook: processado', [
            'code'    => $this->payload['code'] ?? null,
            'shop_id' => $this->payload['shop_id'] ?? null,
            'result'  => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessShopeeWebhook: falhou', [
            'code'    => $this->payload['code'] ?? null,
            'shop_id' => $this->payload['shop_id'] ?? null,
            'error'   => $exception->getMessage(),
        ]);
    }
}
