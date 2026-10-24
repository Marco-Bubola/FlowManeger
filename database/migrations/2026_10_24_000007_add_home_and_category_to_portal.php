<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * portal_collections.category_id = coleção criada a partir de uma categoria (não cria de novo
 * mesmo se a loja trocar o nome). portal_settings.home = montagem da página inicial do
 * catálogo (ordem e quais blocos aparecem, tema de evento com cores e estilo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_collections') && ! Schema::hasColumn('portal_collections', 'category_id')) {
            Schema::table('portal_collections', fn (Blueprint $t) => $t->unsignedBigInteger('category_id')->nullable()->index());
        }
        if (Schema::hasTable('portal_settings') && ! Schema::hasColumn('portal_settings', 'home')) {
            Schema::table('portal_settings', fn (Blueprint $t) => $t->json('home')->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('portal_collections', 'category_id')) {
            Schema::table('portal_collections', fn (Blueprint $t) => $t->dropColumn('category_id'));
        }
        if (Schema::hasColumn('portal_settings', 'home')) {
            Schema::table('portal_settings', fn (Blueprint $t) => $t->dropColumn('home'));
        }
    }
};
