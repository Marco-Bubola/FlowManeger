<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Identidade da loja no catálogo: logo, nome, contatos do rodapé, fonte e as páginas Sobre / Trocas / Entrega.
    public function up(): void
    {
        if (Schema::hasTable('portal_settings') && ! Schema::hasColumn('portal_settings', 'identity')) {
            Schema::table('portal_settings', function (Blueprint $t) {
                $t->json('identity')->nullable();
                $t->json('pages')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('portal_settings', 'identity')) {
            Schema::table('portal_settings', function (Blueprint $t) {
                $t->dropColumn(['identity', 'pages']);
            });
        }
    }
};
