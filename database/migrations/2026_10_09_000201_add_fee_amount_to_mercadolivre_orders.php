<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarifa de venda cobrada pelo Mercado Livre em cada pedido
 * (soma de sale_fee × quantidade dos itens). Usada no relatório "Lucro real".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mercadolivre_orders') || Schema::hasColumn('mercadolivre_orders', 'fee_amount')) {
            return;
        }

        Schema::table('mercadolivre_orders', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('mercadolivre_orders') && Schema::hasColumn('mercadolivre_orders', 'fee_amount')) {
            Schema::table('mercadolivre_orders', function (Blueprint $table) {
                $table->dropColumn('fee_amount');
            });
        }
    }
};
