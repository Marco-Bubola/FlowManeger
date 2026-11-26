<?php

namespace App\Console\Commands;

use App\Services\Quotes\QuoteAutomations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/** Lembretes e vencimento dos orçamentos (sob medida e reforma). */
class RunQuoteAutomations extends Command
{
    protected $signature = 'orcamentos:automacoes';

    protected $description = 'Lembretes de pedido sem resposta, orçamento não visto, perto de vencer e muda os vencidos';

    public function handle(QuoteAutomations $auto): int
    {
        if (!Schema::hasTable('quotes')) {
            return self::SUCCESS;
        }
        $r = $auto->run();
        $this->info('Pedidos: ' . $r['pedido'] . ' · não vistos: ' . $r['nao_visto'] . ' · vencendo: ' . $r['vence'] . ' · vencidos: ' . $r['vencido']);

        return self::SUCCESS;
    }
}
