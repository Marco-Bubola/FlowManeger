<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar o ENUM da coluna type para incluir novos tipos
        DB::statement("
            ALTER TABLE `consortium_notifications`
            MODIFY COLUMN `type` ENUM(
                'draw_available',
                'redemption_pending',
                'sale_pending',
                'sale_completed',
                'payment_overdue',
                'payment_received',
                'client_new',
                'client_birthday',
                'system_backup',
                'system_update',
                'system_error'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os tipos originais
        DB::statement("
            ALTER TABLE `consortium_notifications`
            MODIFY COLUMN `type` ENUM(
                'draw_available',
                'redemption_pending'
            ) NOT NULL
        ");
    }
};
