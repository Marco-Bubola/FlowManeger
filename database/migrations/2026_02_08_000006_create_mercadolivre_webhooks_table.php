<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para registrar webhooks recebidos do Mercado Livre
     */
    public function up(): void
    {
        // Verificar se a tabela já existe
        if (Schema::hasTable('mercadolivre_webhooks')) {
            return;
        }

        Schema::create('mercadolivre_webhooks', function (Blueprint $table) {
            $table->id();
            
            // Dados do webhook
            $table->string('topic', 100)->comment('orders, items, questions, claims, shipments');
            $table->string('resource', 255)->comment('URL do recurso afetado');
            $table->bigInteger('ml_user_id')->comment('ID do usuário ML');
            $table->bigInteger('application_id')->comment('ID da aplicação');
            
            // Controle de processamento
            $table->integer('attempts')->default(0)->comment('Tentativas de processamento');
            $table->timestamp('sent')->comment('Quando o ML enviou o webhook');
            $table->timestamp('received_at')->useCurrent()->comment('Quando recebemos o webhook');
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            
            // Dados e erros
            $table->json('raw_data')->comment('Payload completo do webhook');
            $table->text('error_message')->nullable();
            $table->integer('http_status')->nullable()->comment('Status HTTP ao buscar resource');
            
            // Resultado do processamento
            $table->json('processing_result')->nullable()->comment('Resultado do processamento');
            
            // Índices
            $table->index('topic');
            $table->index('ml_user_id');
            $table->index('processed');
            $table->index('received_at');
            $table->index(['topic', 'processed']);
            $table->index(['received_at', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_webhooks');
    }
};
