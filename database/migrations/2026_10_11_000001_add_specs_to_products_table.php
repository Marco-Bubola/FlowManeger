<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha técnica do produto (lista de nome/valor), trazida do catálogo do ML
 * ou digitada à mão. Aparece na ficha do produto no portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'specs')) {
            Schema::table('products', function (Blueprint $t) {
                $t->json('specs')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'specs')) {
            Schema::table('products', function (Blueprint $t) {
                $t->dropColumn('specs');
            });
        }
    }
};
