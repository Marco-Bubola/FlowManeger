<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adiciona controle de "estoque aplicado" às vendas.
 *
 * A partir de agora o estoque só é debitado quando a venda é CONFIRMADA
 * (paga na Sales Index, criada já quitada, ou vendida no Mercado Livre),
 * e não mais na criação de uma venda pendente.
 *
 * Não destrutiva e idempotente. Vendas EXISTENTES já tiveram o estoque
 * debitado no fluxo antigo (na criação), então são marcadas como
 * stock_applied = true no backfill para não debitar/duplicar de novo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'stock_applied')) {
            Schema::table('sales', function (Blueprint $t) {
                $t->boolean('stock_applied')->default(false)->after('status');
            });

            // Backfill: vendas antigas já tiveram o estoque aplicado no fluxo antigo.
            DB::table('sales')->update(['stock_applied' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'stock_applied')) {
            Schema::table('sales', function (Blueprint $t) {
                $t->dropColumn('stock_applied');
            });
        }
    }
};
