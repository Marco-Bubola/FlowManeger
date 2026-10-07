<?php

namespace App\Console\Commands;

use App\Services\Cashbook\RecurringEntryService;
use Illuminate\Console\Command;

class GenerateRecurringEntries extends Command
{
    protected $signature = 'recorrentes:gerar';

    protected $description = 'Gera no livro-caixa os lançamentos recorrentes que já venceram';

    public function handle(RecurringEntryService $service): int
    {
        $created = $service->generateAll();
        $this->info("Lançamentos recorrentes gerados: {$created}");

        return self::SUCCESS;
    }
}
