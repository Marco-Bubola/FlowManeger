<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Banner feito a partir de um modelo pronto: modelo, cores, selo e fotos (sem imagem pronta). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_banners') && !Schema::hasColumn('portal_banners', 'design')) {
            Schema::table('portal_banners', function (Blueprint $t) {
                $t->json('design')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('portal_banners', 'design')) {
            Schema::table('portal_banners', fn (Blueprint $t) => $t->dropColumn('design'));
        }
    }
};
