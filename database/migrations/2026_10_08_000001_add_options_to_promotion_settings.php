<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mais opções nas configurações de Promoções: desconto padrão, final do
 * preço (,90 / ,99 / inteiro), encerrar ao zerar o estoque e saudação
 * automática ao enviar para um cliente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotion_settings')) {
            return;
        }

        Schema::table('promotion_settings', function (Blueprint $t) {
            if (!Schema::hasColumn('promotion_settings', 'default_discount')) {
                $t->decimal('default_discount', 5, 2)->default(10);
            }
            if (!Schema::hasColumn('promotion_settings', 'price_ending')) {
                $t->string('price_ending', 8)->default('none');
            }
            if (!Schema::hasColumn('promotion_settings', 'auto_end_out_of_stock')) {
                $t->boolean('auto_end_out_of_stock')->default(true);
            }
            if (!Schema::hasColumn('promotion_settings', 'greet_client')) {
                $t->boolean('greet_client')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('promotion_settings')) {
            return;
        }

        Schema::table('promotion_settings', function (Blueprint $t) {
            foreach (['default_discount', 'price_ending', 'auto_end_out_of_stock', 'greet_client'] as $col) {
                if (Schema::hasColumn('promotion_settings', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
