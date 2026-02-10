<?php

namespace App\Console\Commands\MercadoLivre;

use App\Models\MlPublication;
use App\Services\MercadoLivre\MlStockSyncService;
use App\Services\MercadoLivre\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AuditAndFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:audit-and-fix 
                            {--publication= : ID específico da publicação para auditar}
                            {--dry-run : Apenas reportar divergências sem corrigir}
                            {--fix-all : Corrigir todas as divergências encontradas automaticamente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita publicações comparando estoque local vs ML e corrige divergências';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Iniciando auditoria de publicações do Mercado Livre...');
        $this->newLine();

        $authService = app(AuthService::class);
        $syncService = new MlStockSyncService($authService);
        $isDryRun = $this->option('dry-run');
        $fixAll = $this->option('fix-all');

        // Determinar publicações para auditar
        if ($publicationId = $this->option('publication')) {
            $publications = MlPublication::where('id', $publicationId)
                ->where('status', 'active')
                ->with(['publicationProducts.product'])
                ->get();
                
            if ($publications->isEmpty()) {
                $this->error("❌ Publicação #{$publicationId} não encontrada ou não está ativa!");
                return 1;
            }
            
            $this->info("📦 Auditando publicação específica: #{$publicationId}");
        } else {
            $publications = MlPublication::where('status', 'active')
                ->with(['publicationProducts.product'])
                ->get();
                
            $this->info("🌍 Auditando " . $publications->count() . " publicações ativas...");
        }

        if ($isDryRun) {
            $this->warn('🚫 Modo DRY-RUN: Nenhuma correção será aplicada');
        }

        $this->newLine();

        $divergences = [];
        $bar = $this->output->createProgressBar($publications->count());
        $bar->start();

        foreach ($publications as $publication) {
            try {
                // Calcular estoque local
                $localQuantity = $publication->calculateAvailableQuantity();
                
                // Buscar estoque no ML via API
                $mlQuantity = $syncService->getMlItemQuantity($publication->ml_item_id);
                
                if ($mlQuantity === null) {
                    $divergences[] = [
                        'type' => 'error',
                        'publication' => $publication,
                        'local' => $localQuantity,
                        'ml' => 'N/A',
                        'issue' => 'Erro ao buscar estoque no ML',
                    ];
                } elseif ($localQuantity !== $mlQuantity) {
                    $divergences[] = [
                        'type' => 'divergence',
                        'publication' => $publication,
                        'local' => $localQuantity,
                        'ml' => $mlQuantity,
                        'diff' => $localQuantity - $mlQuantity,
                    ];
                }
                
            } catch (\Exception $e) {
                $divergences[] = [
                    'type' => 'exception',
                    'publication' => $publication,
                    'error' => $e->getMessage(),
                ];
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Relatório de divergências
        if (empty($divergences)) {
            $this->info('✅ Nenhuma divergência encontrada! Todos os estoques estão sincronizados.');
            return 0;
        }

        $this->warn("⚠️  Encontradas " . count($divergences) . " divergências:");
        $this->newLine();

        // Tabela de divergências
        $tableData = [];
        foreach ($divergences as $div) {
            $pub = $div['publication'];
            $tableData[] = [
                'ID' => $pub->id,
                'ML Item' => $pub->ml_item_id,
                'Tipo' => $pub->publication_type,
                'Local' => $div['local'] ?? 'N/A',
                'ML' => $div['ml'] ?? 'N/A',
                'Diferença' => $div['diff'] ?? $div['issue'] ?? $div['error'] ?? '-',
            ];
        }

        $this->table(
            ['ID', 'ML Item', 'Tipo', 'Local', 'ML', 'Diferença'],
            $tableData
        );

        $this->newLine();

        // Correção
        if ($isDryRun) {
            $this->comment('💡 Execute sem --dry-run para corrigir as divergências.');
            return 0;
        }

        if (!$fixAll && !$this->confirm('Deseja corrigir as divergências agora?', false)) {
            $this->comment('⏭️  Correções canceladas pelo usuário.');
            return 0;
        }

        $this->info('🔧 Aplicando correções...');
        $this->newLine();

        $fixedCount = 0;
        $failedCount = 0;

        foreach ($divergences as $div) {
            if ($div['type'] !== 'divergence') {
                continue; // Pular erros/exceções
            }

            $pub = $div['publication'];
            
            try {
                $result = $syncService->syncQuantityToMercadoLivre($pub);
                
                if ($result['success']) {
                    $this->line("  ✅ Pub #{$pub->id} ({$pub->ml_item_id}): {$div['ml']} → {$div['local']}");
                    $fixedCount++;
                } else {
                    $this->line("  ❌ Pub #{$pub->id}: {$result['message']}");
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $this->line("  ❌ Pub #{$pub->id}: {$e->getMessage()}");
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("✅ Corrigidas: {$fixedCount}");
        
        if ($failedCount > 0) {
            $this->error("❌ Falhas: {$failedCount}");
        }

        $this->newLine();
        $this->info('🎉 Auditoria e correção concluídas!');

        return $failedCount > 0 ? 1 : 0;
    }
}
