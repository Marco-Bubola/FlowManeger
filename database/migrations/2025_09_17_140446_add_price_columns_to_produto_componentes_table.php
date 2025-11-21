<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('produto_componentes')) {
            return;
        }

        if (Schema::hasColumns('produto_componentes', ['preco_custo_unitario', 'preco_venda_unitario'])) {
            return;
        }

        Schema::table('produto_componentes', function (Blueprint $table) {
            $table->decimal('preco_custo_unitario', 10, 2)->after('quantidade')->comment('Preço de custo unitário do componente no momento da criação do kit');
            $table->decimal('preco_venda_unitario', 10, 2)->after('preco_custo_unitario')->comment('Preço de venda unitário do componente no momento da criação do kit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('produto_componentes') || ! Schema::hasColumn('produto_componentes', 'preco_custo_unitario')) {
            return;
        }

        Schema::table('produto_componentes', function (Blueprint $table) {
            $table->dropColumn(['preco_custo_unitario', 'preco_venda_unitario']);
        });
    }
};
