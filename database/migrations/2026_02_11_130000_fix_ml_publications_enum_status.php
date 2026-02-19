<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix ml_publications table - adiciona 'pending' ao ENUM status e corrige schema
     */
    public function up(): void
    {
        // Drop das tabelas dependentes primeiro (ordem de dependencies)
        if (Schema::hasTable('ml_publication_products')) {
            Schema::dropIfExists('ml_publication_products');
        }
        if (Schema::hasTable('ml_publications')) {
            Schema::dropIfExists('ml_publications');
        }

        // Recriar ml_publications com schema correto
        Schema::create('ml_publications', function (Blueprint $table) {
            $table->id();
            
            // Identificação no ML
            $table->string('ml_item_id', 50)->unique()->comment('ID único da publicação no ML');
            $table->string('ml_category_id', 50)->nullable()->comment('Categoria ML (MLB1051, etc)');
            $table->string('ml_permalink', 500)->nullable()->comment('URL da publicação no ML');
            
            // Conteúdo
            $table->string('title', 255)->comment('Título do anúncio');
            $table->longText('description')->nullable()->comment('Descrição completa');
            $table->decimal('price', 10, 2)->default(0)->comment('Preço de venda');
            $table->integer('available_quantity')->default(0)->comment('Quantidade disponível');
            
            // Tipo de publicação
            $table->enum('publication_type', ['simple', 'kit'])->default('simple')
                ->comment('Simples ou Kit');
            $table->string('listing_type', 50)->default('gold_special')
                ->comment('Tipo de anúncio (gold_special, gold_pro, gold, free)');
            
            // Condições
            $table->enum('condition', ['new', 'used'])->default('new')
                ->comment('Novo ou usado');
            $table->string('warranty', 100)->nullable()
                ->comment('Garantia do produto');
            
            // Opções de envio
            $table->boolean('free_shipping')->default(true)
                ->comment('Frete grátis?');
            $table->boolean('local_pickup')->default(false)
                ->comment('Retirada local?');
            
            // Status e sincronização - IMPORTANTE: 'pending' deve estar aqui!
            $table->enum('status', ['pending', 'active', 'paused', 'closed', 'under_review'])->default('pending')
                ->comment('Status da publicação no ML');
            $table->enum('sync_status', ['pending', 'synced', 'error'])->default('pending')
                ->comment('Status de sincronização com ML');
            $table->timestamp('last_sync_at')->nullable()
                ->comment('Última sincronização com ML');
            $table->longText('error_message')->nullable()
                ->comment('Mensagem de erro se houver');
            
            // Dados adicionais
            $table->json('ml_attributes')->nullable()
                ->comment('Atributos específicos da categoria');
            $table->json('pictures')->nullable()
                ->comment('URLs das imagens');
            
            // Relacionamentos
            $table->unsignedBigInteger('user_id')
                ->comment('Usuário proprietário');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('ml_item_id');
            $table->index('status');
            $table->index('sync_status');
            $table->index('publication_type');
            $table->index(['user_id', 'status']);
            $table->index('ml_category_id');
        });

        // Recriar ml_publication_products
        Schema::create('ml_publication_products', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->unsignedBigInteger('ml_publication_id')
                ->comment('Publicação ML');
            $table->unsignedInteger('product_id')
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
            
            // Constraints - sem FK por agora, vai ser adicionada manually via foreign() se necessário
            $table->foreign('ml_publication_id')
                ->references('id')
                ->on('ml_publications')
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
        Schema::dropIfExists('ml_publications');
    }
};
