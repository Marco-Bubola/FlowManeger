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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('source')->nullable()->after('status'); // e.g. 'portal'
            $table->unsignedBigInteger('portal_quote_id')->nullable()->after('source');
            $table->foreign('portal_quote_id')->references('id')->on('client_quote_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['portal_quote_id']);
            $table->dropColumn(['source', 'portal_quote_id']);
        });
    }
};
