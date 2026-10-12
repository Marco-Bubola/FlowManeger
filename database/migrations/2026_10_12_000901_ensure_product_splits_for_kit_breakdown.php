<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Desmembrar kit: o histórico usa product_splits / product_split_items.
 * Cria as tabelas se a migração antiga não as criou e guarda quantas
 * unidades do kit foram desmembradas em cada vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_splits')) {
            Schema::create('product_splits', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedBigInteger('source_product_id')->index();
                $t->string('source_action', 20)->default('kept');
                $t->boolean('distributed_stock')->default(false);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('product_split_items')) {
            Schema::create('product_split_items', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('split_id')->index();
                $t->unsignedBigInteger('result_product_id')->index();
                $t->unsignedBigInteger('variation_parent_id')->nullable();
                $t->string('mode', 20)->default('new');
                $t->integer('quantity')->default(1);
                $t->integer('stock_assigned')->default(0);
                $t->timestamps();
            });
        }

        if (!Schema::hasColumn('product_splits', 'units')) {
            Schema::table('product_splits', function (Blueprint $t) {
                $t->integer('units')->default(0)->after('source_product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_splits', 'units')) {
            Schema::table('product_splits', function (Blueprint $t) {
                $t->dropColumn('units');
            });
        }
    }
};
