<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Publicações na Shopee
 *
 * A Shopee usa uma hierarquia de IDs:
 *   item_id → identificador do produto pai (listagem)
 *   model_id → identificador da variação (SKU/modelo: cor, tamanho, etc.)
 *
 * Esta tabela armazena a listagem (item). Variações ficam em shopee_publication_models.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_publications', function (Blueprint $table) {
            $table->id();

            // Relação com usuário e loja
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shop_id')->comment('Shop ID da Shopee onde foi publicado');

            // IDs Shopee
            $table->string('shopee_item_id')->nullable()->comment('Item ID na Shopee (após publicação)');
            $table->string('shopee_category_id')->nullable()->comment('Categoria na Shopee');
            $table->string('shopee_permalink')->nullable()->comment('URL do anúncio na Shopee');

            // Dados do anúncio
            $table->string('title')->comment('Título do anúncio');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->comment('Preço base na Shopee');
            $table->unsignedInteger('available_quantity')->default(0);
            $table->string('condition', 20)->default('NEW')->comment('NEW ou USED');

            // Logística (obrigatório Shopee)
            $table->unsignedInteger('weight_grams')->comment('Peso em gramas');
            $table->decimal('length_cm', 8, 2)->nullable()->comment('Comprimento pacote (cm)');
            $table->decimal('width_cm', 8, 2)->nullable()->comment('Largura pacote (cm)');
            $table->decimal('height_cm', 8, 2)->nullable()->comment('Altura pacote (cm)');
            $table->unsignedTinyInteger('days_to_ship')->default(3)->comment('Prazo de envio em dias (DTS)');

            // Controle de variações
            $table->boolean('has_variations')->default(false);

            // Imagens (array de URLs)
            $table->json('pictures')->nullable();

            // Atributos extras da categoria
            $table->json('shopee_attributes')->nullable();

            // Status de sincronização
            $table->enum('status', ['draft', 'published', 'inactive', 'deleted'])->default('draft');
            $table->enum('sync_status', ['pending', 'synced', 'error', 'updating'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('last_sync_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'shop_id']);
            $table->index(['shopee_item_id']);
            $table->index(['sync_status']);
        });

        // Tabela pivot: publicação Shopee ↔ produtos internos
        Schema::create('shopee_publication_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_publication_id');
            $table->foreign('shopee_publication_id')->references('id')->on('shopee_publications')->onDelete('cascade');
            $table->integer('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // ID do modelo/variação Shopee para este produto
            $table->string('shopee_model_id')->nullable()
                ->comment('Model ID da variação na Shopee (hierarquia item → model)');
            $table->string('shopee_model_sku')->nullable()
                ->comment('SKU do modelo na Shopee');

            // Mapeamento de variação (cor, tamanho, etc.)
            $table->json('variation_attributes')->nullable()
                ->comment('Ex: {"name":"Cor","value_name":"Azul"}');

            $table->unsignedInteger('quantity')->default(1)
                ->comment('Quantidade deste produto na publicação');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['shopee_publication_id', 'product_id', 'shopee_model_id'],
                'shopee_pub_product_model_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_publication_products');
        Schema::dropIfExists('shopee_publications');
    }
};
