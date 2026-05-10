<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        // Evita SQL específico de MySQL e mantém compatibilidade com SQLite.
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_barcode_unique');
            });
        } catch (\Throwable $e) {
            // Índice já removido ou não existe neste banco.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'barcode')) {
            return;
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->unique('barcode', 'products_barcode_unique');
            });
        } catch (\Throwable $e) {
            // Índice já existe.
        }
    }
};
