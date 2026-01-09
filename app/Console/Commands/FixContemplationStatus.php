<?php

namespace App\Console\Commands;

use App\Models\ConsortiumContemplation;
use Illuminate\Console\Command;

class FixContemplationStatus extends Command
{
    protected $signature = 'consortium:fix-contemplation-status';
    protected $description = 'Corrige o status das contemplações que estão com status incorreto';

    public function handle()
    {
        $this->info('Verificando contemplações...');

        // Buscar contemplações que estão com status 'redeemed' mas redemption_type 'products' ou 'pending' e sem produtos registrados
        $contemplations = ConsortiumContemplation::where('status', 'redeemed')
            ->whereIn('redemption_type', ['products', 'pending'])
            ->whereNull('products')
            ->get();

        if ($contemplations->isEmpty()) {
            $this->info('Nenhuma contemplação precisa ser corrigida.');
            return 0;
        }

        $this->info("Encontradas {$contemplations->count()} contemplações para corrigir:");

        foreach ($contemplations as $contemplation) {
            $this->line("ID: {$contemplation->id} - Tipo: {$contemplation->redemption_type} - Status atual: {$contemplation->status}");

            $contemplation->update(['status' => 'pending']);

            $this->info("✓ Status corrigido para 'pending'");
        }

        $this->info("\n✓ Processo concluído!");
        return 0;
    }
}
