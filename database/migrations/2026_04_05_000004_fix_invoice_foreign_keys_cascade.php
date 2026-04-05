<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            // Drop existing FKs without ON DELETE CASCADE
            $table->dropForeign('invoice_ibfk_1');
            $table->dropForeign('invoice_ibfk_2');

            // Re-add with ON DELETE CASCADE
            $table->foreign('user_id', 'invoice_ibfk_1')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('id_bank', 'invoice_ibfk_2')
                  ->references('id_bank')->on('banks')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropForeign('invoice_ibfk_1');
            $table->dropForeign('invoice_ibfk_2');

            $table->foreign('user_id', 'invoice_ibfk_1')
                  ->references('id')->on('users');

            $table->foreign('id_bank', 'invoice_ibfk_2')
                  ->references('id_bank')->on('banks');
        });
    }
};
