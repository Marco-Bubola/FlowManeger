<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preço original (R$ TABELA do extrato), mostrado riscado nas promoções.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'price_original')) {
            Schema::table('products', function (Blueprint $t) {
                $t->decimal('price_original', 10, 2)->nullable()->after('price_sale');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'price_original')) {
            Schema::table('products', function (Blueprint $t) {
                $t->dropColumn('price_original');
            });
        }
    }
};
