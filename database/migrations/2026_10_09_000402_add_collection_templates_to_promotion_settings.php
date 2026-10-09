<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelos de mensagem de Cobrança (parcela vencendo / vencida), guardados
 * junto dos modelos de mensagem das Promoções.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotion_settings')) {
            return;
        }

        Schema::table('promotion_settings', function (Blueprint $t) {
            if (!Schema::hasColumn('promotion_settings', 'collection_due_template')) {
                $t->text('collection_due_template')->nullable();
            }
            if (!Schema::hasColumn('promotion_settings', 'collection_overdue_template')) {
                $t->text('collection_overdue_template')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('promotion_settings')) {
            return;
        }

        Schema::table('promotion_settings', function (Blueprint $t) {
            foreach (['collection_due_template', 'collection_overdue_template'] as $col) {
                if (Schema::hasColumn('promotion_settings', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
