<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para gerenciar tokens OAuth 2.0 do Mercado Livre
     */
    public function up(): void
    {
        // Verificar se a tabela já existe
        if (Schema::hasTable('mercadolivre_tokens')) {
            return;
        }

        Schema::create('mercadolivre_tokens', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com usuário
            $table->unsignedBigInteger('user_id');
            
            // ID do usuário no Mercado Livre
            $table->bigInteger('ml_user_id')->nullable()->comment('ID do usuário no Mercado Livre');
            
            // Tokens OAuth 2.0
            $table->text('access_token')->comment('Token de acesso');
            $table->text('refresh_token')->comment('Token de renovação');
            $table->string('token_type', 20)->default('Bearer');
            
            // Controle de expiração
            $table->timestamp('expires_at')->comment('Data/hora de expiração do token');
            $table->text('scope')->nullable()->comment('Permissões do token');
            
            // Status
            $table->boolean('is_active')->default(true)->comment('Token está ativo');
            
            // Metadados
            $table->string('ml_nickname', 100)->nullable()->comment('Nome do usuário no ML');
            $table->json('user_info')->nullable()->comment('Informações do usuário ML');
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Índices
            $table->index('user_id');
            $table->index('ml_user_id');
            $table->index('expires_at');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_tokens');
    }
};
