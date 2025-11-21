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
        if (! Schema::hasTable('category') || Schema::hasColumn('category', 'sort_order')) {
            return;
        }

        Schema::table('category', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->after('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('category') || ! Schema::hasColumn('category', 'sort_order')) {
            return;
        }

        Schema::table('category', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
