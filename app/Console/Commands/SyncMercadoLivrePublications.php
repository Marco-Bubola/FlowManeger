<?php

namespace App\Console\Commands;

use App\Models\MlPublication;
use App\Services\MercadoLivre\MlStockSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMercadoLivrePublications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:sync-publications 
                            {--user-id= : Sincronizar apenas publicações de um usuário específico}
                            {--limit=50 : Número máximo de publicações para sincronizar por execução}
                            {--force : Forçar sincronização mesmo se já sincronizado recentemente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza automaticamente todas as publicações do Mercado Livre';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando sincronização automática das publicações do Mercado Livre...');
        $this->newLine();

        $query = MlPublication::whereNotNull('ml_item_id')
            ->where('ml_item_id', 'NOT LIKE', 'TEMP_%');

        // Filtrar por usuário se especificado
        if ($userId = $this->option('user-id')) {
            $query->where('user_id', $userId);
            $this->info("👤 Filtrando por usuário ID: {$userId}");
        }

        // Se não for forçado, sincronizar apenas publicações desatualizadas (mais de 1 hora)
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('last_sync_at')
                    ->orWhere('last_sync_at', '<', now()->subHour());
            });
        }

        // Limitar quantidade
        $limit = (int) $this->option('limit');
        $publications = $query->limit($limit)->get();

        if ($publications->isEmpty()) {
            $this->info('✅ Nenhuma publicação precisa ser sincronizada no momento.');
            return Command::SUCCESS;
        }

        $this->info("📦 {$publications->count()} publicações serão sincronizadas");
        $this->newLine();

        $syncService = app(MlStockSyncService::class);
        $bar = $this->output->createProgressBar($publications->count());
        $bar->start();

        $syncedCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($publications as $publication) {
            try {
                $result = $syncService->fetchPublicationFromMercadoLivre($publication);

                if ($result['success']) {
                    $syncedCount++;
                } else {
                    $errorCount++;
                    $errors[] = [
                        'id' => $publication->id,
                        'ml_item_id' => $publication->ml_item_id,
                        'title' => $publication->title,
                        'error' => $result['message'],
                    ];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'id' => $publication->id,
                    'ml_item_id' => $publication->ml_item_id,
                    'title' => $publication->title,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Erro ao sincronizar publicação', [
                    'publication_id' => $publication->id,
                    'ml_item_id' => $publication->ml_item_id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumo
        $this->info('═══════════════════════════════════════════════');
        $this->info('📊 RESUMO DA SINCRONIZAÇÃO');
        $this->info('═══════════════════════════════════════════════');
        $this->info("✅ Sincronizadas com sucesso: {$syncedCount}");
        
        if ($errorCount > 0) {
            $this->warn("❌ Com erro: {$errorCount}");
            $this->newLine();
            
            if ($this->option('verbose') && !empty($errors)) {
                $this->error('Detalhes dos erros:');
                foreach ($errors as $error) {
                    $this->line("  • {$error['title']} ({$error['ml_item_id']})");
                    $this->line("    └─ {$error['error']}");
                }
            }
        }

        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        Log::info('Sincronização automática de publicações ML concluída', [
            'total' => $publications->count(),
            'synced' => $syncedCount,
            'errors' => $errorCount,
        ]);

        return $errorCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
