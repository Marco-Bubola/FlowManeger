<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela pivot: relaciona publicações ML com produtos internos
     * Suporta múltiplos produtos por publicação (kits)
     */
    public function up(): void
    {
        Schema::create('ml_publication_products', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->unsignedBigInteger('ml_publication_id')->comment('ID da publicação ML');
            $table->unsignedInteger('product_id')->comment('ID do produto interno');
            
            // Configuração do produto na publicação
            $table->integer('quantity')->default(1)
                ->comment('Quantidade deste produto por venda (ex: kit com 2 shampoos)');
            $table->decimal('unit_cost', 10, 2)->nullable()
                ->comment('Custo unitário no momento da vinculação');
            $table->integer('sort_order')->default(0)
                ->comment('Ordem de exibição (0 = principal)');
            
            // Controle
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('ml_publication_id')
                ->references('id')
                ->on('ml_publications')
                ->onDelete('cascade');
            
            // product_id é INT UNSIGNED, não posso adicionar FK diretamente
            // mas crio índice para performance
            
            // Índices
            $table->index('product_id');
            $table->index('ml_publication_id');
            $table->unique(['ml_publication_id', 'product_id'], 'ml_pub_prod_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_publication_products');
    }
};
