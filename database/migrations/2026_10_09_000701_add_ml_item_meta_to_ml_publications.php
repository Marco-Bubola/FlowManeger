<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dados do anúncio no ML que mudam o que pode ser enviado na edição:
 * - ml_family_name: anúncio no modelo "User Products"/catálogo; o ML recusa
 *   mudança de título (erro 400, cause 374) quando o item tem family_name.
 * - ml_user_product_id: id do user product (informativo).
 * - ml_variations: resumo das variações [{id, available_quantity, label}];
 *   com variações o estoque vai por variação, não no topo do item.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ml_publications')) {
            return;
        }

        Schema::table('ml_publications', function (Blueprint $table) {
            if (! Schema::hasColumn('ml_publications', 'ml_family_name')) {
                $table->string('ml_family_name')->nullable()->after('ml_permalink');
            }
            if (! Schema::hasColumn('ml_publications', 'ml_user_product_id')) {
                $table->string('ml_user_product_id', 64)->nullable()->after('ml_permalink');
            }
            if (! Schema::hasColumn('ml_publications', 'ml_variations')) {
                $table->json('ml_variations')->nullable()->after('pictures');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ml_publications')) {
            return;
        }

        Schema::table('ml_publications', function (Blueprint $table) {
            foreach (['ml_family_name', 'ml_user_product_id', 'ml_variations'] as $col) {
                if (Schema::hasColumn('ml_publications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
