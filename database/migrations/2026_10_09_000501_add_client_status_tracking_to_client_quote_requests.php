<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acompanhamento do pedido pelo cliente no portal:
 * - responded_at: quando a loja confirmou/recusou/cotou o pedido;
 * - client_seen_status: último status que o cliente já viu (aviso "novidade");
 * - client_notified_at: quando o dono abriu o aviso no WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_quote_requests')) {
            return;
        }

        Schema::table('client_quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('client_quote_requests', 'responded_at')) {
                $table->timestamp('responded_at')->nullable();
            }
            if (! Schema::hasColumn('client_quote_requests', 'client_seen_status')) {
                $table->string('client_seen_status', 20)->nullable();
            }
            if (! Schema::hasColumn('client_quote_requests', 'client_notified_at')) {
                $table->timestamp('client_notified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_quote_requests')) {
            return;
        }

        foreach (['responded_at', 'client_seen_status', 'client_notified_at'] as $column) {
            if (Schema::hasColumn('client_quote_requests', $column)) {
                Schema::table('client_quote_requests', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
