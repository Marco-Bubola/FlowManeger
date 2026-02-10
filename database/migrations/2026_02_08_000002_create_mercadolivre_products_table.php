<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para relacionar produtos internos com anúncios no Mercado Livre
     */
    public function up(): void
    {
        // Verificar se a tabela já existe
        if (Schema::hasTable('mercadolivre_products')) {
            return;
        }

        Schema::create('mercadolivre_products', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com produto interno (products.id é unsigned int)
            $table->unsignedInteger('product_id');
            
            // Dados do anúncio no Mercado Livre
            $table->string('ml_item_id', 50)->unique()->comment('ID do anúncio no Mercado Livre');
            $table->string('ml_category_id', 20)->comment('ID da categoria MLB');
            $table->string('listing_type', 20)->default('gold_special')->comment('Tipo de anúncio: free, bronze, silver, gold');
            
            // Status do anúncio
            $table->enum('status', ['active', 'paused', 'closed', 'under_review', 'inactive'])
                ->default('active')
                ->comment('Status do anúncio no ML');
            
            // Link direto para o anúncio
            $table->string('ml_permalink', 255)->nullable()->comment('Link permanente do anúncio no ML');
            
            // Controle de sincronização
            $table->enum('sync_status', ['synced', 'pending', 'error'])->default('pending');
            $table->timestamp('last_sync_at')->nullable()->comment('Última sincronização bem-sucedida');
            $table->text('error_message')->nullable()->comment('Mensagem de erro na última tentativa');
            
            // Dados adicionais (opcional)
            $table->json('ml_attributes')->nullable()->comment('Atributos específicos da categoria MLB');
            $table->decimal('ml_price', 10, 2)->nullable()->comment('Preço publicado no ML');
            $table->integer('ml_quantity')->nullable()->comment('Quantidade publicada no ML');
            
            $table->timestamps();
            
            // Foreign key - Comentada temporariamente por incompatibilidade de tipos
            // TODO: Adicionar foreign key manualmente se necessário
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Índices para performance
            $table->index('product_id');
            $table->index('ml_item_id');
            $table->index('sync_status');
            $table->index('status');
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_products');
    }
};
