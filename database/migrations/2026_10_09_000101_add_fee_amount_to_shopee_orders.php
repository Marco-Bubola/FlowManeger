<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedidos Shopee:
 * - fee_amount: tarifas da Shopee no pedido (comissão + taxa de serviço + taxa de transação),
 *   vindas do get_escrow_detail. Null quando a Shopee ainda não informou.
 * - stock_processed_at: quando o estoque deste pedido já foi baixado (ou o pedido chegou
 *   cancelado), para nunca baixar duas vezes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shopee_orders')) {
            return;
        }

        if (! Schema::hasColumn('shopee_orders', 'fee_amount')) {
            Schema::table('shopee_orders', function (Blueprint $table) {
                $table->decimal('fee_amount', 12, 2)->nullable()->after('total_amount');
            });
        }

        if (! Schema::hasColumn('shopee_orders', 'stock_processed_at')) {
            Schema::table('shopee_orders', function (Blueprint $table) {
                $table->timestamp('stock_processed_at')->nullable()->after('sync_status');
            });

            // Pedidos já gravados pela versão antiga tiveram o estoque tratado
            // na criação: marca para não baixar de novo num push futuro.
            \Illuminate\Support\Facades\DB::table('shopee_orders')
                ->whereNull('stock_processed_at')
                ->update(['stock_processed_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('shopee_orders')) {
            return;
        }
        foreach (['fee_amount', 'stock_processed_at'] as $col) {
            if (Schema::hasColumn('shopee_orders', $col)) {
                Schema::table('shopee_orders', fn (Blueprint $table) => $table->dropColumn($col));
            }
        }
    }
};
