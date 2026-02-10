<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Nova estrutura: Uma publicação ML pode ter múltiplos produtos (kits)
     * Substitui o relacionamento 1:1 de mercadolivre_products
     */
    public function up(): void
    {
        Schema::create('ml_publications', function (Blueprint $table) {
            $table->id();
            
            // Identificação no Mercado Livre
            $table->string('ml_item_id', 50)->unique()->comment('ID do anúncio no Mercado Livre');
            $table->string('ml_category_id', 20)->comment('Categoria MLB');
            $table->string('ml_permalink', 255)->nullable()->comment('Link público do anúncio');
            
            // Dados da publicação
            $table->string('title', 255)->comment('Título do anúncio');
            $table->text('description')->nullable()->comment('Descrição completa');
            $table->decimal('price', 10, 2)->comment('Preço de venda');
            $table->integer('available_quantity')->default(0)->comment('Quantidade disponível total');
            
            // Tipo e configurações
            $table->enum('publication_type', ['simple', 'kit'])->default('simple')
                ->comment('simple: 1 produto | kit: múltiplos produtos');
            $table->string('listing_type', 20)->default('gold_special')
                ->comment('Tipo de anúncio (gold_special, gold_pro, gold, free)');
            $table->enum('condition', ['new', 'used'])->default('new');
            $table->boolean('free_shipping')->default(false);
            $table->boolean('local_pickup')->default(false);
            
            // Status e sincronização
            $table->enum('status', ['active', 'paused', 'closed', 'under_review'])->default('active');
            $table->enum('sync_status', ['synced', 'pending', 'error'])->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('error_message')->nullable();
            
            // Metadados
            $table->json('ml_attributes')->nullable()->comment('Atributos da categoria MLB');
            $table->json('pictures')->nullable()->comment('URLs das imagens');
            
            // Controle
            $table->unsignedBigInteger('user_id')->comment('Usuário que criou');
            $table->timestamps();
            
            // Índices
            $table->index('ml_item_id');
            $table->index('status');
            $table->index('sync_status');
            $table->index('publication_type');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_publications');
    }
};
