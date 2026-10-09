<?php

namespace App\Console\Commands;

use App\Services\Products\PromotionNotificationService;
use App\Services\Products\PromotionService;
use Illuminate\Console\Command;

/**
 * Liga promoções agendadas e encerra as vencidas ou sem estoque. Depois avisa
 * no sino as promoções que acabam em até 48 h e as que encerraram sozinhas.
 */
class RefreshPromotions extends Command
{
    protected $signature = 'promotions:refresh';

    protected $description = 'Atualiza o status das promoções (agendadas, vencidas, sem estoque)';

    public function handle(PromotionService $service, PromotionNotificationService $notifications): int
    {
        $result = $service->refreshStatuses();
        $notified = $notifications->check();

        $this->info(sprintf(
            'Iniciadas: %d | Vencidas: %d | Sem estoque: %d',
            $result['started'],
            $result['expired'],
            $result['out_of_stock']
        ));
        $this->info(sprintf('Avisos: %d acabando | %d encerradas', $notified['ending'], $notified['ended']));

        return self::SUCCESS;
    }
}
