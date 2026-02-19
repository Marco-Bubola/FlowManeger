<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cria pivô N:N entre publicações e produtos
     */
    public function up(): void
    {
        if (Schema::hasTable('ml_publication_products')) {
            return; // Tabela já existe
        }

        Schema::create('ml_publication_products', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->unsignedBigInteger('ml_publication_id')
                ->comment('Publicação ML');
            $table->unsignedBigInteger('product_id')
                ->comment('Produto do sistema');
            
            // Dados da relação
            $table->integer('quantity')->default(1)
                ->comment('Quantidade por venda (para kits)');
            $table->decimal('unit_cost', 10, 2)->nullable()
                ->comment('Snapshot do custo unitário');
            $table->integer('sort_order')->default(0)
                ->comment('Ordem de apresentação');
            
            // Timestamps
            $table->timestamps();
            
            // Constraints
            $table->foreign('ml_publication_id')
                ->references('id')
                ->on('ml_publications')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
            
            // Índices
            $table->unique(['ml_publication_id', 'product_id']);
            $table->index('product_id');
            $table->index('sort_order');
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
