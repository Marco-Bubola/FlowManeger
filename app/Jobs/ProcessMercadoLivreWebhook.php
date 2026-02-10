<?php

namespace App\Jobs;

use App\Models\MercadoLivreWebhook;
use App\Http\Controllers\MercadoLivre\WebhookController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMercadoLivreWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 600]; // 30s, 2min, 10min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MercadoLivreWebhook $webhook
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WebhookController $controller): void
    {
        $this->webhook->incrementAttempts();
        $controller->process($this->webhook);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->webhook->markAsError($exception->getMessage());

        \Log::error('Job de processamento de webhook ML falhou', [
            'webhook_id' => $this->webhook->id,
            'topic' => $this->webhook->topic,
            'error' => $exception->getMessage(),
        ]);
    }
}
