<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = DB::select(
            "SHOW INDEXES FROM products WHERE Key_name = 'products_barcode_unique'"
        );

        if (! empty($indexExists)) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_barcode_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique('barcode', 'products_barcode_unique');
        });
    }
};
