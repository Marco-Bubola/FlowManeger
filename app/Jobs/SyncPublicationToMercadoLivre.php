<?php

namespace App\Jobs;

use App\Models\MlPublication;
use App\Services\MercadoLivre\MlStockSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class SyncPublicationToMercadoLivre implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Por quantos segundos o job é considerado único (evita fila duplicada para a mesma publicação).
     */
    public int $uniqueFor = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MlPublication $publication
    ) {}

    /**
     * Identificador único: uma publicação só tem um job de sync na fila por vez.
     */
    public function uniqueId(): string
    {
        return 'ml_sync_' . $this->publication->id;
    }

    /**
     * Execute the job.
     */
    public function handle(MlStockSyncService $syncService): void
    {
        $publication = $this->publication->fresh();
        if ($publication) {
            $syncService->syncQuantityToMercadoLivre($publication);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->publication->update([
            'sync_status' => 'error',
            'error_message' => 'Job falhou após ' . $this->tries . ' tentativas: ' . $exception->getMessage(),
        ]);

        Log::error('Job de sincronização ML falhou', [
            'publication_id' => $this->publication->id,
            'ml_item_id' => $this->publication->ml_item_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
