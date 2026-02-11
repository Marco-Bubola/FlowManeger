<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\MlPublication;
use App\Models\MlStockLog;
use App\Services\MercadoLivre\MlStockSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     * Dispara sincronização quando o estoque muda
     */
    public function updated(Product $product): void
    {
        // Verifica se o estoque foi alterado
        if ($product->wasChanged('stock_quantity')) {
            $this->handleStockChange($product);
        }
    }

    /**
     * Handle the Product "deleted" event.
     * Remove vinculações com publicações ML
     */
    public function deleting(Product $product): void
    {
        // Remove produto de todas as publicações ML
        $publications = MlPublication::withProduct($product->id)->get();
        
        foreach ($publications as $publication) {
            $publication->removeProduct($product->id);
        }
    }

    /**
     * Processa mudança de estoque
     */
    protected function handleStockChange(Product $product): void
    {
        $oldQuantity = $product->getOriginal('stock_quantity');
        $newQuantity = $product->stock_quantity;
        $change = $newQuantity - $oldQuantity;

        // Registra log da mudança
        $transactionId = Str::uuid()->toString();
        
        MlStockLog::create([
            'product_id' => $product->id,
            'ml_publication_id' => null,
            'operation_type' => $this->detectOperationType(),
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'quantity_change' => $change,
            'source' => $this->getSource(),
            'notes' => "Alteração de estoque: {$oldQuantity} → {$newQuantity}",
            'transaction_id' => $transactionId,
            'user_id' => Auth::id(),
        ]);

        // Sincroniza com todas as publicações ML que contém este produto
        $this->syncPublications($product, $transactionId);
    }

    /**
     * Sincroniza quantidade em todas as publicações ML vinculadas
     */
    protected function syncPublications(Product $product, string $transactionId): void
    {
        // Busca publicações por ID do produto
        $publicationsById = MlPublication::withProduct($product->id)
            ->active()
            ->get();

        // Busca também por product_code (produtos com mesmo código)
        $publicationsByCode = collect();
        if ($product->product_code) {
            $publicationsByCode = MlPublication::withProductCode($product->product_code)
                ->active()
                ->get();
        }

        // Combina e remove duplicatas
        $publications = $publicationsById->merge($publicationsByCode)->unique('id');

        // Sincroniza cada publicação
        foreach ($publications as $publication) {
            try {
                // Recalcula quantidade disponível
                $newQuantity = $publication->syncQuantityToMl();

                // Registra log específico da publicação
                MlStockLog::create([
                    'product_id' => $product->id,
                    'ml_publication_id' => $publication->id,
                    'operation_type' => 'sync_to_ml',
                    'quantity_before' => $publication->getOriginal('available_quantity'),
                    'quantity_after' => $newQuantity,
                    'quantity_change' => $newQuantity - $publication->getOriginal('available_quantity'),
                    'source' => 'ProductObserver::syncPublications',
                    'notes' => "Sincronização automática após mudança no produto ID {$product->id}",
                    'transaction_id' => $transactionId,
                    'user_id' => Auth::id(),
                ]);

                // Dispara job para sincronizar com API do ML (assíncrono)
                \App\Jobs\SyncPublicationToMercadoLivre::dispatch($publication);

            } catch (\Exception $e) {
                \Log::error("Erro ao sincronizar publicação ML {$publication->id}: " . $e->getMessage());
                
                $publication->update([
                    'sync_status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Detecta tipo de operação baseado no contexto
     */
    protected function detectOperationType(): string
    {
        // Verifica se está rodando via CLI/Artisan (import de planilha)
        if (app()->runningInConsole()) {
            return 'import_excel';
        }

        // Verifica se está em um contexto de venda
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($backtrace as $trace) {
            if (isset($trace['class'])) {
                if (str_contains($trace['class'], 'SaleController') || 
                    str_contains($trace['class'], 'Sale')) {
                    return 'internal_sale';
                }
            }
        }

        // Padrão: atualização manual
        return 'manual_update';
    }

    /**
     * Identifica fonte da mudança
     */
    protected function getSource(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        foreach ($backtrace as $trace) {
            if (isset($trace['class']) && $trace['class'] !== self::class) {
                return $trace['class'] . '::' . ($trace['function'] ?? 'unknown');
            }
        }

        return 'ProductObserver';
    }
}
