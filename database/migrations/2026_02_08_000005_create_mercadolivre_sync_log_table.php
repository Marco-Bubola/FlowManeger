<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para log de sincronizações com o Mercado Livre
     */
    public function up(): void
    {
        // Verificar se a tabela já existe
        if (Schema::hasTable('mercadolivre_sync_log')) {
            return;
        }

        Schema::create('mercadolivre_sync_log', function (Blueprint $table) {
            $table->id();
            
            // Usuário que executou/disparou a sincronização
            $table->unsignedBigInteger('user_id');
            
            // Tipo de sincronização
            $table->enum('sync_type', ['stock', 'price', 'product', 'order', 'status', 'full'])
                ->comment('Tipo de sincronização realizada');
            
            // Entidade afetada
            $table->string('entity_type', 50)->nullable()->comment('Product, Order, etc');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID da entidade');
            
            // Ação realizada
            $table->string('action', 50)->nullable()->comment('create, update, delete, sync, import');
            
            // Resultado
            $table->enum('status', ['success', 'error', 'warning'])->comment('Resultado da operação');
            $table->text('message')->nullable()->comment('Mensagem descritiva');
            
            // Dados técnicos (para debug)
            $table->json('request_data')->nullable()->comment('Dados enviados para API');
            $table->json('response_data')->nullable()->comment('Resposta da API');
            $table->integer('http_status')->nullable()->comment('Status HTTP da resposta');
            
            // Performance
            $table->integer('execution_time')->nullable()->comment('Tempo de execução em milissegundos');
            
            // API Rate Limiting info
            $table->integer('api_calls_remaining')->nullable()->comment('Chamadas restantes da API');
            
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Índices para consultas rápidas
            $table->index(['user_id', 'sync_type', 'created_at'], 'idx_user_sync_date');
            $table->index('status');
            $table->index(['entity_type', 'entity_id'], 'idx_entity');
            $table->index('created_at');
            $table->index(['sync_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_sync_log');
    }
};
