<?php

namespace App\Console\Commands;

use App\Services\ConsortiumNotificationService;
use Illuminate\Console\Command;

class CheckConsortiumNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consortium:check-notifications
                            {--clean : Limpar notificações antigas}
                            {--consortium= : ID do consórcio específico para verificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica consórcios e cria notificações para sorteios disponíveis e resgates pendentes';

    /**
     * Execute the console command.
     */
    public function handle(ConsortiumNotificationService $service): int
    {
        $this->info('🔔 Verificando notificações de consórcios...');
        $this->newLine();

        try {
            // Limpar notificações antigas se solicitado
            if ($this->option('clean')) {
                $this->info('🧹 Limpando notificações antigas...');
                $cleanStats = $service->cleanOldNotifications();

                $this->info("   ✓ Lidas deletadas: {$cleanStats['read']}");
                $this->info("   ✓ Não lidas deletadas: {$cleanStats['unread']}");
                $this->info("   ✓ Total deletado: {$cleanStats['total']}");
                $this->newLine();
            }

            // Verificar consórcio específico
            if ($consortiumId = $this->option('consortium')) {
                $consortium = \App\Models\Consortium::find($consortiumId);

                if (!$consortium) {
                    $this->error("❌ Consórcio #{$consortiumId} não encontrado");
                    return self::FAILURE;
                }

                $this->info("🎯 Verificando consórcio: {$consortium->name}");
                $count = $service->triggerForConsortium($consortium);

                $this->info("   ✓ {$count} notificação(ões) criada(s)");
                $this->newLine();

                return self::SUCCESS;
            }

            // Verificar todos os consórcios
            $stats = $service->checkAndCreateNotifications();

            $this->newLine();
            $this->info('📊 Resultados:');
            $this->table(
                ['Tipo', 'Quantidade'],
                [
                    ['🎯 Sorteios Disponíveis', $stats['draw_available']],
                    ['⏰ Resgates Pendentes', $stats['redemption_pending']],
                    ['📋 Total', $stats['total']],
                ]
            );

            if ($stats['total'] > 0) {
                $this->newLine();
                $this->info("✅ {$stats['total']} notificação(ões) criada(s) com sucesso!");
            } else {
                $this->newLine();
                $this->comment('ℹ️  Nenhuma nova notificação criada.');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao verificar notificações: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
