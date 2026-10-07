<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A tabela banks veio do dump original (não tem migration de criação)
        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                if (! Schema::hasColumn('banks', 'credit_limit')) {
                    $table->decimal('credit_limit', 12, 2)->nullable()->after('end_date');
                }
                if (! Schema::hasColumn('banks', 'due_day')) {
                    $table->unsignedTinyInteger('due_day')->nullable()->after('credit_limit');
                }
            });
        }

        if (! Schema::hasTable('card_bill_payments')) {
            Schema::create('card_bill_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('id_bank')->index();
                // Primeiro dia do ciclo da fatura (identifica a fatura do mês)
                $table->date('cycle_start');
                $table->date('cycle_end');
                $table->decimal('amount', 12, 2)->default(0);
                $table->date('paid_at');
                $table->timestamps();

                $table->unique(['id_bank', 'cycle_start']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('card_bill_payments');

        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                foreach (['credit_limit', 'due_day'] as $column) {
                    if (Schema::hasColumn('banks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
