<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // cashbook veio do dump original (sem migration de criação)
        if (Schema::hasTable('cashbook') && ! Schema::hasColumn('cashbook', 'sale_payment_id')) {
            Schema::table('cashbook', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_payment_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cashbook') && Schema::hasColumn('cashbook', 'sale_payment_id')) {
            Schema::table('cashbook', function (Blueprint $table) {
                $table->dropIndex(['sale_payment_id']);
                $table->dropColumn('sale_payment_id');
            });
        }
    }
};
