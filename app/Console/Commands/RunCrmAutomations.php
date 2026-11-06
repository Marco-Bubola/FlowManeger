<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Clients\CrmAutomations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/** Automações do CRM: cria as tarefas do dia (aniversário, pós-venda, sumido). */
class RunCrmAutomations extends Command
{
    protected $signature = 'crm:automacoes';

    protected $description = 'Cria as tarefas com mensagem pronta das automações de relacionamento ligadas';

    public function handle(CrmAutomations $auto): int
    {
        if (! Schema::hasTable('crm_automation_logs')) {
            return self::SUCCESS;
        }
        $total = 0;
        User::whereNotNull('preferences')->where('preferences', 'like', '%"auto"%')->chunkById(100, function ($users) use ($auto, &$total) {
            foreach ($users as $user) {
                $total += array_sum($auto->run($user));
            }
        });
        $this->info("Tarefas criadas: {$total}");

        return self::SUCCESS;
    }
}
