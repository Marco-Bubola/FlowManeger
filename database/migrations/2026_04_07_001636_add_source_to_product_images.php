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
        Schema::table('product_images', function (Blueprint $table) {
            if (!Schema::hasColumn('product_images', 'source')) {
                $table->string('source')->default('upload')->after('sort_order');
            }
            if (!Schema::hasColumn('product_images', 'source_url')) {
                $table->string('source_url')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumnIfExists('source');
            $table->dropColumnIfExists('source_url');
        });
    }
};
