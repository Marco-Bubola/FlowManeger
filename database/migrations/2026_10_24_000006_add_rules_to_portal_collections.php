<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coleções automáticas e capa de modelo: rule = manual (produtos escolhidos) ou uma
 * regra que se atualiza sozinha (categoria, em promoção, novos, mais vendidos, faixa
 * de preço, palavra, pronta entrega, sob encomenda); rule_data guarda os valores.
 * design = capa montada com os modelos de banner (tpl, cores, selo, animação).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portal_collections')) {
            return;
        }
        Schema::table('portal_collections', function (Blueprint $t) {
            if (! Schema::hasColumn('portal_collections', 'rule')) {
                $t->string('rule', 20)->default('manual');
            }
            if (! Schema::hasColumn('portal_collections', 'rule_data')) {
                $t->json('rule_data')->nullable();
            }
            if (! Schema::hasColumn('portal_collections', 'design')) {
                $t->json('design')->nullable();
            }
        });
    }

    public function down(): void
    {
        foreach (['rule', 'rule_data', 'design'] as $c) {
            if (Schema::hasColumn('portal_collections', $c)) {
                Schema::table('portal_collections', fn (Blueprint $t) => $t->dropColumn($c));
            }
        }
    }
};
