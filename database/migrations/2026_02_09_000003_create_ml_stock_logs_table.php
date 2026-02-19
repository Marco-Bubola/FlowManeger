<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Log de movimentações de estoque para auditoria e prevenção de concorrência
     */
    public function up(): void
    {
        Schema::create('ml_stock_logs', function (Blueprint $table) {
            $table->id();
            
            // Identificação
            $table->unsignedInteger('product_id')->comment('Produto afetado');
            $table->unsignedBigInteger('ml_publication_id')->nullable()
                ->comment('Publicação ML relacionada (se aplicável)');
            
            // Tipo de operação
            $table->enum('operation_type', [
                'ml_sale',           // Venda no ML
                'manual_update',     // Atualização manual
                'import_excel',      // Import de planilha
                'internal_sale',     // Venda interna
                'sync_to_ml',        // Sincronização para ML
                'adjustment',        // Ajuste manual
                'return',            // Devolução
            ])->comment('Tipo de operação');
            
            // Dados da mudança
            $table->integer('quantity_before')->comment('Quantidade antes');
            $table->integer('quantity_after')->comment('Quantidade depois');
            $table->integer('quantity_change')->comment('Delta (+/-)');
            
            // Contexto
            $table->string('source', 100)->nullable()
                ->comment('Fonte da operação (ex: MercadoLivreWebhook, ProductController)');
            $table->string('ml_order_id', 50)->nullable()
                ->comment('ID do pedido ML (se aplicável)');
            $table->text('notes')->nullable()
                ->comment('Observações adicionais');
            
            // Controle de concorrência
            $table->string('transaction_id', 36)->nullable()
                ->comment('UUID da transação (agrupa operações atômicas)');
            $table->boolean('rolled_back')->default(false)
                ->comment('Se a operação foi revertida');
            
            // Auditoria
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('Usuário responsável (se manual)');
            $table->timestamp('created_at')->useCurrent();
            
            // Índices para consultas rápidas
            $table->index('product_id');
            $table->index('ml_publication_id');
            $table->index('operation_type');
            $table->index('ml_order_id');
            $table->index('transaction_id');
            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_stock_logs');
    }
};
