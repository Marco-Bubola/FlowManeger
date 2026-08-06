<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O model InvoiceUploadHistory e a tela de upload sempre gravaram `total_value`,
 * mas a coluna nunca existiu: o INSERT falhava e o botão "Processar Arquivo"
 * não fazia nada (o erro era engolido pelo try/catch).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_uploads_history')) {
            return;
        }

        if (Schema::hasColumn('invoice_uploads_history', 'total_value')) {
            return;
        }

        Schema::table('invoice_uploads_history', function (Blueprint $table) {
            $table->decimal('total_value', 12, 2)->default(0)->after('transactions_skipped');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('invoice_uploads_history', 'total_value')) {
            return;
        }

        Schema::table('invoice_uploads_history', function (Blueprint $table) {
            $table->dropColumn('total_value');
        });
    }
};
