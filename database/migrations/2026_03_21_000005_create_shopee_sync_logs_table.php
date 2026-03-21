<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logs de sincronização de estoque entre todos os canais (Shopee + ML)
 * Registra cada movimentação para rastreabilidade e diagnóstico de erros.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_sync_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform', 20)->comment('shopee | mercadolivre | internal');
            $table->string('sync_type', 50)->comment('stock_update | publish | order_import | auth');
            $table->string('entity_type', 50)->nullable()->comment('product | publication | order');
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->string('action', 50)->nullable()->comment('create | update | delete | sync');
            $table->enum('status', ['success', 'error', 'warning', 'info'])->default('info');
            $table->text('message')->nullable();

            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('execution_time_ms')->nullable();

            $table->string('reference_id')->nullable()
                ->comment('ID externo de referência (shopee_order_sn, shopee_item_id, etc.)');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'platform', 'status']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_sync_logs');
    }
};
