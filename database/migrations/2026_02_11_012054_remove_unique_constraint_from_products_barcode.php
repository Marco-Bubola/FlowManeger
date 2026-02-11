<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove a constraint UNIQUE da coluna barcode para permitir
     * que múltiplos produtos tenham o mesmo código de barras
     * (necessário para produtos com várias unidades)
     */
    public function up(): void
    {
        // Verifica se a constraint existe antes de tentar remover
        $indexExists = DB::select(
            "SHOW INDEXES FROM products WHERE Key_name = 'products_barcode_unique'"
        );

        if (!empty($indexExists)) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_barcode_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Reaplica a constraint UNIQUE caso seja necessário reverter
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique('barcode', 'products_barcode_unique');
        });
    }
};
