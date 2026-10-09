<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Stock\LowStockService;
use Illuminate\Console\Command;

/** Notifica produtos com estoque no mínimo ou esgotados (sem repetir alertas). */
class CheckLowStock extends Command
{
    protected $signature = 'stock:check-low {--user= : Verificar só este usuário}';

    protected $description = 'Cria notificações de estoque baixo/esgotado com sugestão de reposição';

    public function handle(LowStockService $service): int
    {
        $userIds = $this->option('user')
            ? [(int) $this->option('user')]
            : Product::withoutGlobalScopes()->whereNotNull('user_id')->distinct()->pluck('user_id')->all();

        $alerted = $cleared = 0;
        foreach ($userIds as $userId) {
            try {
                $r = $service->check((int) $userId);
                $alerted += $r['alerted'];
                $cleared += $r['cleared'];
            } catch (\Throwable $e) {
                $this->warn("Usuário {$userId}: {$e->getMessage()}");
            }
        }

        $this->info(sprintf('Usuários: %d | Alertas: %d | Normalizados: %d', count($userIds), $alerted, $cleared));

        return self::SUCCESS;
    }
}
