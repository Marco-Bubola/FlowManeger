<?php

namespace App\Console\Commands;

use App\Models\BankConnection;
use App\Services\BankSync\BankSyncService;
use Illuminate\Console\Command;

class SyncBankConnections extends Command
{
    protected $signature = 'bancos:sincronizar {--user= : Só um usuário}';

    protected $description = 'Bancos conectados (Open Finance): lança as transações novas no livro caixa e nas faturas';

    public function handle(BankSyncService $sync): int
    {
        $connections = BankConnection::where('enabled', true)
            ->when($this->option('user'), fn ($q, $u) => $q->where('user_id', $u))->get()
            ->filter->ready();

        foreach ($connections as $c) {
            try {
                $r = $sync->sync($c);
                $this->info("Usuário {$c->user_id}: {$r['created']} novos, {$r['linked']} já existiam" . ($r['errors'] ? ', erros: ' . implode(' | ', $r['errors']) : ''));
            } catch (\Throwable $e) {
                $c->forceFill(['last_error' => mb_substr($e->getMessage(), 0, 250)])->save();
                $this->error("Usuário {$c->user_id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
