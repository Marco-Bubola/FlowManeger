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
        Schema::table('ml_publications', function (Blueprint $table) {
            $table->string('warranty', 255)->nullable()->after('condition')->comment('Garantia do produto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ml_publications', function (Blueprint $table) {
            $table->dropColumn('warranty');
        });
    }
};
