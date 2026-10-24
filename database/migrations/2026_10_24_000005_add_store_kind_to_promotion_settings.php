<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo da loja (beleza, móveis ou outra): escolhe os modelos de banner,
 * as datas e as mensagens de promoção que combinam com o que a loja vende.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotion_settings') && ! Schema::hasColumn('promotion_settings', 'store_kind')) {
            Schema::table('promotion_settings', function (Blueprint $t) {
                $t->string('store_kind', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('promotion_settings', 'store_kind')) {
            Schema::table('promotion_settings', fn (Blueprint $t) => $t->dropColumn('store_kind'));
        }
    }
};
