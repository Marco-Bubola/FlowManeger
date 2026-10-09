<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estoque mínimo por produto (opcional). Quando preenchido, substitui o
 * mínimo global do usuário (preferences.stock.minimum) nos alertas e na tela "Repor".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || Schema::hasColumn('products', 'min_stock')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->integer('min_stock')->nullable()->after('stock_quantity');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'min_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }
    }
};
