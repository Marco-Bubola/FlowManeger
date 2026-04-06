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
        Schema::table('client_quote_requests', function (Blueprint $table) {
            $table->string('payment_preference')->nullable()->after('client_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_quote_requests', function (Blueprint $table) {
            $table->dropColumn('payment_preference');
        });
    }
};
