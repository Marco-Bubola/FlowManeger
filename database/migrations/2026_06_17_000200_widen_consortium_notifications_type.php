<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Amplia consortium_notifications.type de ENUM (fixo, só valores de consórcio)
 * para VARCHAR, permitindo notificações de qualquer módulo (mercadolivre,
 * sale, payment, etc.) no sino unificado. Não destrutiva: os valores atuais
 * continuam válidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('consortium_notifications', 'type')) {
            DB::statement("ALTER TABLE `consortium_notifications` MODIFY `type` VARCHAR(60) NOT NULL");
        }
    }

    public function down(): void
    {
        // Reverter para o ENUM original (apenas se necessário).
        if (Schema::hasColumn('consortium_notifications', 'type')) {
            DB::statement("ALTER TABLE `consortium_notifications` MODIFY `type` ENUM('draw_available','redemption_pending','sale_pending','sale_completed','payment_overdue','payment_received','client_new','client_birthday','system_backup','system_update','system_error') NOT NULL");
        }
    }
};
