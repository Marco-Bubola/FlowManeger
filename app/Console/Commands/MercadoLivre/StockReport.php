<?php

namespace App\Console\Commands\MercadoLivre;

use App\Models\MlPublication;
use App\Models\MlStockLog;
use App\Models\Product;
use Illuminate\Console\Command;
use Carbon\Carbon;

class StockReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:stock-report 
                            {--product= : ID do produto para relatório específico}
                            {--publication= : ID da publicação para relatório específico}
                            {--conflicts : Mostrar apenas conflitos e divergências}
                            {--days=7 : Número de dias para histórico (padrão: 7)}
                            {--limit=50 : Limite de registros a exibir}
                            {--export= : Exportar relatório para arquivo CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera relatório de movimentações de estoque e sincronizações ML';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Gerando Relatório de Estoque - Mercado Livre');
        $this->newLine();

        $days = (int) $this->option('days');
        $limit = (int) $this->option('limit');
        $dateFrom = Carbon::now()->subDays($days);

        // Filtros
        $query = MlStockLog::with(['publication', 'product'])
            ->where('created_at', '>=', $dateFrom)
            ->orderBy('created_at', 'desc');

        if ($productId = $this->option('product')) {
            $product = Product::find($productId);
            if (!$product) {
                $this->error("❌ Produto #{$productId} não encontrado!");
                return 1;
            }
            $query->where('product_id', $productId);
            $this->info("📦 Relatório do Produto: {$product->name} (#{$productId})");
        }

        if ($publicationId = $this->option('publication')) {
            $publication = MlPublication::find($publicationId);
            if (!$publication) {
                $this->error("❌ Publicação #{$publicationId} não encontrada!");
                return 1;
            }
            $query->where('ml_publication_id', $publicationId);
            $this->info("📋 Relatório da Publicação: {$publication->ml_item_id} (#{$publicationId})");
        }

        if ($this->option('conflicts')) {
            $query->where('is_conflict', true);
            $this->warn("⚠️  Exibindo apenas CONFLITOS");
        }

        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            $this->warn('⚠️  Nenhum registro encontrado com os filtros aplicados.');
            return 0;
        }

        $this->info("📋 Encontrados {$logs->count()} registros (últimos {$days} dias)");
        $this->newLine();

        // Tabela de logs
        $tableData = [];
        foreach ($logs as $log) {
            $product = $log->product;
            $publication = $log->publication;
            
            $conflictIcon = $log->is_conflict ? '⚠️' : '';
            $operationIcon = $this->getOperationIcon($log->operation);
            
            $tableData[] = [
                'Data/Hora' => $log->created_at->format('d/m H:i'),
                'Operação' => $operationIcon . ' ' . $this->getOperationLabel($log->operation),
                'Produto' => $product ? "#{$product->id} {$product->product_code}" : 'N/A',
                'Publicação' => $publication ? substr($publication->ml_item_id, 0, 15) : 'N/A',
                'Antes' => $log->quantity_before,
                'Depois' => $log->quantity_after,
                'Δ' => ($log->quantity_after - $log->quantity_before),
                'Status' => $conflictIcon . ' ' . ($log->sync_success ? '✓' : '✗'),
            ];
        }

        $this->table(
            ['Data/Hora', 'Operação', 'Produto', 'Publicação', 'Antes', 'Depois', 'Δ', 'Status'],
            $tableData
        );

        // Estatísticas
        $this->newLine();
        $this->info('📈 Estatísticas:');
        $this->line('  • Total de registros: ' . $logs->count());
        $this->line('  • Sincronizações bem-sucedidas: ' . $logs->where('sync_success', true)->count());
        $this->line('  • Falhas: ' . $logs->where('sync_success', false)->count());
        $this->line('  • Conflitos detectados: ' . $logs->where('is_conflict', true)->count());

        // Operações por tipo
        $this->newLine();
        $this->info('📊 Operações por tipo:');
        $operations = $logs->groupBy('operation');
        foreach ($operations as $operation => $items) {
            $icon = $this->getOperationIcon($operation);
            $label = $this->getOperationLabel($operation);
            $this->line("  {$icon} {$label}: {$items->count()}");
        }

        // Exportar CSV se solicitado
        if ($exportPath = $this->option('export')) {
            $this->newLine();
            $this->info("💾 Exportando para CSV: {$exportPath}");
            
            try {
                $this->exportToCsv($logs, $exportPath);
                $this->info("✅ Relatório exportado com sucesso!");
            } catch (\Exception $e) {
                $this->error("❌ Erro ao exportar: {$e->getMessage()}");
                return 1;
            }
        }

        // Conflitos recentes
        if ($this->option('conflicts') === false) {
            $conflicts = $logs->where('is_conflict', true);
            if ($conflicts->count() > 0) {
                $this->newLine();
                $this->warn("⚠️  {$conflicts->count()} conflitos detectados!");
                $this->comment("💡 Use --conflicts para ver apenas conflitos");
            }
        }

        $this->newLine();
        $this->info('🎉 Relatório gerado com sucesso!');

        return 0;
    }

    /**
     * Exporta logs para CSV
     */
    protected function exportToCsv($logs, string $path): void
    {
        $handle = fopen($path, 'w');
        
        // Cabeçalhos
        fputcsv($handle, [
            'ID',
            'Data/Hora',
            'Operação',
            'Produto ID',
            'Produto Nome',
            'Publicação ID',
            'ML Item ID',
            'Quantidade Antes',
            'Quantidade Depois',
            'Diferença',
            'Sucesso',
            'Conflito',
            'Mensagem',
        ]);
        
        // Dados
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->operation,
                $log->product_id,
                $log->product?->name ?? 'N/A',
                $log->ml_publication_id,
                $log->publication?->ml_item_id ?? 'N/A',
                $log->quantity_before,
                $log->quantity_after,
                ($log->quantity_after - $log->quantity_before),
                $log->sync_success ? 'Sim' : 'Não',
                $log->is_conflict ? 'Sim' : 'Não',
                $log->error_message ?? '-',
            ]);
        }
        
        fclose($handle);
    }

    /**
     * Retorna ícone para operação
     */
    protected function getOperationIcon(string $operation): string
    {
        return match($operation) {
            'stock_updated' => '📦',
            'publication_created' => '✨',
            'manual_sync' => '🔄',
            'auto_sync' => '⚡',
            'audit_fix' => '🔧',
            default => '•',
        };
    }

    /**
     * Retorna label para operação
     */
    protected function getOperationLabel(string $operation): string
    {
        return match($operation) {
            'stock_updated' => 'Estoque Atualizado',
            'publication_created' => 'Publicação Criada',
            'manual_sync' => 'Sincronização Manual',
            'auto_sync' => 'Sincronização Automática',
            'audit_fix' => 'Correção de Auditoria',
            default => ucfirst(str_replace('_', ' ', $operation)),
        };
    }
}
