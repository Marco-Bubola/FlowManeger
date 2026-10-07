<?php

namespace App\Console\Commands;

use App\Services\Products\PromotionService;
use Illuminate\Console\Command;

/** Liga promoções agendadas e encerra as vencidas ou sem estoque. */
class RefreshPromotions extends Command
{
    protected $signature = 'promotions:refresh';

    protected $description = 'Atualiza o status das promoções (agendadas, vencidas, sem estoque)';

    public function handle(PromotionService $service): int
    {
        $result = $service->refreshStatuses();

        $this->info(sprintf(
            'Iniciadas: %d | Vencidas: %d | Sem estoque: %d',
            $result['started'],
            $result['expired'],
            $result['out_of_stock']
        ));

        return self::SUCCESS;
    }
}
