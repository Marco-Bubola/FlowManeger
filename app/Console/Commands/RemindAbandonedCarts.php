<?php

namespace App\Console\Commands;

use App\Services\Portal\CartRecovery;
use App\Services\Portal\SiteStats;
use Illuminate\Console\Command;

/** Carrinho abandonado: manda (ou avisa a loja para mandar) a lembrança no WhatsApp. */
class RemindAbandonedCarts extends Command
{
    protected $signature = 'portal:carrinhos';

    protected $description = 'Lembra no WhatsApp os clientes que deixaram produtos no carrinho do catálogo';

    public function handle(): int
    {
        $r = CartRecovery::run();
        $pruned = now()->minute < 15 && now()->hour === 4 ? SiteStats::prune() : 0;
        $this->info("Enviados pelo WhatsApp: {$r['sent']} | Avisos para a loja: {$r['noticed']}" . ($pruned ? " | Visitas antigas apagadas: {$pruned}" : ''));

        return self::SUCCESS;
    }
}
