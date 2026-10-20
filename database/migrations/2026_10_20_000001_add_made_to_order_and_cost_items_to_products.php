<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Móveis e peças feitas à mão: "sob encomenda" com prazo de produção e a
 * ficha de custo da peça (materiais e horas que somam o custo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (!Schema::hasColumn('products', 'made_to_order')) {
                $t->boolean('made_to_order')->default(false);
            }
            if (!Schema::hasColumn('products', 'production_days')) {
                $t->unsignedSmallInteger('production_days')->nullable();
            }
            if (!Schema::hasColumn('products', 'cost_items')) {
                $t->json('cost_items')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            foreach (['made_to_order', 'production_days', 'cost_items'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
