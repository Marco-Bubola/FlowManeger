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
        if (! Schema::hasTable('client_quote_requests')) {
            return;
        }

        Schema::table('client_quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('client_quote_requests', 'payment_preference')) {
                $table->string('payment_preference')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('client_quote_requests') || ! Schema::hasColumn('client_quote_requests', 'payment_preference')) {
            return;
        }

        Schema::table('client_quote_requests', function (Blueprint $table) {
            $table->dropColumn('payment_preference');
        });
    }
};
