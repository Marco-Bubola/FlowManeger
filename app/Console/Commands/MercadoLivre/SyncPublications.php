<?php

namespace App\Console\Commands\MercadoLivre;

use App\Models\MlPublication;
use App\Services\MercadoLivre\MlStockSyncService;
use App\Services\MercadoLivre\AuthService;
use Illuminate\Console\Command;

class SyncPublications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:sync-publications 
                            {--publication= : ID específico da publicação para sincronizar}
                            {--pending : Sincronizar apenas publicações com sync pendente}
                            {--all : Sincronizar todas as publicações ativas}
                            {--force : Forçar sincronização mesmo se estoque não mudou}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza estoque de publicações do ML com produtos internos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando sincronização de publicações com Mercado Livre...');
        $this->newLine();

        $authService = app(AuthService::class);
        $syncService = new MlStockSyncService($authService);
        $publicationsToSync = [];

        // Determinar quais publicações sincronizar
        if ($publicationId = $this->option('publication')) {
            // Sincronizar publicação específica
            $publication = MlPublication::find($publicationId);
            
            if (!$publication) {
                $this->error("❌ Publicação #{$publicationId} não encontrada!");
                return 1;
            }
            
            $publicationsToSync = collect([$publication]);
            $this->info("📦 Sincronizando publicação específica: #{$publicationId}");
            
        } elseif ($this->option('pending')) {
            // Sincronizar apenas pending
            $publicationsToSync = MlPublication::where('needs_sync', true)
                ->where('status', 'active')
                ->with(['publicationProducts.product'])
                ->get();
                
            $this->info("⏳ Sincronizando " . $publicationsToSync->count() . " publicações pendentes...");
            
        } elseif ($this->option('all')) {
            // Sincronizar todas ativas
            $publicationsToSync = MlPublication::where('status', 'active')
                ->with(['publicationProducts.product'])
                ->get();
                
            $this->info("🌍 Sincronizando TODAS as " . $publicationsToSync->count() . " publicações ativas...");
            
        } else {
            // Padrão: pending
            $publicationsToSync = MlPublication::where('needs_sync', true)
                ->where('status', 'active')
                ->with(['publicationProducts.product'])
                ->get();
                
            $this->info("⏳ Sincronizando " . $publicationsToSync->count() . " publicações pendentes (padrão)...");
            $this->comment("💡 Dica: Use --all para sincronizar todas ou --publication=ID para uma específica");
        }

        if ($publicationsToSync->isEmpty()) {
            $this->warn('⚠️  Nenhuma publicação para sincronizar.');
            return 0;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($publicationsToSync->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($publicationsToSync as $publication) {
            try {
                // Verificar se precisa sincronizar (exceto se --force)
                if (!$this->option('force') && !$publication->needs_sync) {
                    $skippedCount++;
                    $bar->advance();
                    continue;
                }

                // Sincronizar
                $result = $syncService->syncQuantityToMercadoLivre($publication);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = [
                        'id' => $publication->id,
                        'ml_item_id' => $publication->ml_item_id,
                        'error' => $result['message'] ?? 'Erro desconhecido'
                    ];
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'id' => $publication->id,
                    'ml_item_id' => $publication->ml_item_id,
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resultados
        $this->info("✅ Sincronizadas com sucesso: {$successCount}");
        
        if ($skippedCount > 0) {
            $this->comment("⏭️  Puladas (sem mudanças): {$skippedCount}");
        }
        
        if ($errorCount > 0) {
            $this->error("❌ Erros: {$errorCount}");
            $this->newLine();
            
            if ($this->option('verbose') || $errorCount <= 5) {
                $this->warn('Detalhes dos erros:');
                foreach ($errors as $error) {
                    $this->line("  • Pub #{$error['id']} ({$error['ml_item_id']}): {$error['error']}");
                }
            } else {
                $this->comment("Use -v para ver detalhes dos erros.");
            }
        }

        $this->newLine();
        $this->info('🎉 Sincronização concluída!');

        return $errorCount > 0 ? 1 : 0;
    }
}
