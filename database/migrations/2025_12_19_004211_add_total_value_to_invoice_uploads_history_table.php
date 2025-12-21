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
        Schema::table('invoice_uploads_history', function (Blueprint $table) {
            $table->decimal('total_value', 15, 2)->default(0)->after('transactions_skipped');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_uploads_history', function (Blueprint $table) {
            $table->dropColumn('total_value');
        });
    }
};
