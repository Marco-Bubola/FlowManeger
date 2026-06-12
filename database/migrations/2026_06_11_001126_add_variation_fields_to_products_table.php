<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Variações (produto-pai + variantes) — NÃO destrutiva.
 * Só adiciona colunas nuláveis. Todo produto existente fica parent_id = NULL
 * e is_variation_parent = false → continua se comportando como hoje (standalone).
 *
 * Obs: a PK de `products` é INT SIGNED neste banco, por isso parent_id usa
 * integer() (signed) para casar com o tipo de products.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (!Schema::hasColumn('products', 'parent_id')) {
                $t->integer('parent_id')->nullable()->after('id'); // signed: casa com products.id
            }
            if (!Schema::hasColumn('products', 'is_variation_parent')) {
                $t->boolean('is_variation_parent')->default(false)->after('parent_id');
            }
            if (!Schema::hasColumn('products', 'variation_attribute')) {
                $t->string('variation_attribute', 60)->nullable()->after('is_variation_parent');
            }
            if (!Schema::hasColumn('products', 'variation_value')) {
                $t->string('variation_value', 120)->nullable()->after('variation_attribute');
            }
            if (!Schema::hasColumn('products', 'variation_sort')) {
                $t->integer('variation_sort')->default(0)->after('variation_value');
            }
        });

        // Índices e FK em passo separado (evita erro caso já existam)
        Schema::table('products', function (Blueprint $t) {
            $t->foreign('parent_id')->references('id')->on('products')->nullOnDelete();
            $t->index(['parent_id'], 'products_parent_id_index');
            $t->index(['user_id', 'is_variation_parent'], 'products_user_variation_parent_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->dropForeign(['parent_id']);
            $t->dropIndex('products_parent_id_index');
            $t->dropIndex('products_user_variation_parent_index');
            $t->dropColumn(['parent_id', 'is_variation_parent', 'variation_attribute', 'variation_value', 'variation_sort']);
        });
    }
};
