<?php

namespace App\Jobs;

use App\Models\ShopeePublication;
use App\Services\Shopee\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de sincronização de estoque com a Shopee.
 * Disparado sempre que o estoque de um produto vinculado for alterado.
 *
 * Usa ShouldBeUnique para garantir que haja no máximo um job
 * pendente por publicação na fila (evita sobrecarga desnecessária).
 */
class SyncPublicationToShopee implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /** Janela de unicidade em segundos */
    public int $uniqueFor = 60;

    public function __construct(
        public ShopeePublication $publication
    ) {}

    /**
     * Chave única: uma publicação Shopee só tem um job de sync na fila por vez.
     */
    public function uniqueId(): string
    {
        return 'shopee_sync_' . $this->publication->id;
    }

    /**
     * Executa a sincronização de estoque na Shopee.
     */
    public function handle(ProductService $productService): void
    {
        $publication = $this->publication->fresh();

        if (!$publication || !$publication->shopee_item_id) {
            Log::info('SyncToShopee: publicação ignorada (sem item_id ou não encontrada)', [
                'publication_id' => $this->publication->id,
            ]);
            return;
        }

        $result = $productService->syncStock(
            publication:   $publication,
            userId:        $publication->user_id,
            forceQuantity: null  // Calcula automaticamente pelo estoque dos produtos
        );

        if (!$result['success']) {
            Log::warning('SyncToShopee: falha na sincronização', [
                'publication_id' => $publication->id,
                'message'        => $result['message'],
            ]);
            $this->fail(new \RuntimeException($result['message']));
        }
    }

    /**
     * Callback em caso de falha definitiva (após todas as tentativas).
     */
    public function failed(\Throwable $exception): void
    {
        $this->publication->update([
            'sync_status'   => 'error',
            'error_message' => 'Job falhou após ' . $this->tries . ' tentativas: ' . $exception->getMessage(),
        ]);

        Log::error('SyncToShopee: job falhou definitivamente', [
            'publication_id' => $this->publication->id,
            'shopee_item_id' => $this->publication->shopee_item_id,
            'error'          => $exception->getMessage(),
        ]);
    }
}
