<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Variações de um anúncio do ML ligadas a produtos do estoque
 * (ex.: batom Nude / Rosa / Vermelho, cada um um produto local).
 *
 * - ml_publication_variations: uma linha por variação do item no ML, preenchida
 *   a partir do item (rótulo "Cor: Nude", foto, SKU, GTIN, estoque no ML) e com
 *   o produto escolhido para ela (product_id) e quantas unidades saem por venda.
 *   stock_confirmed: o dono já aceitou que o sistema mande o estoque dessa
 *   variação ao ML (antes disso nada é sobrescrito automaticamente).
 * - ml_stock_logs.ml_variation_id: a baixa de um pedido fica por variação, para
 *   que duas variações do mesmo anúncio no mesmo pedido baixem as duas uma vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ml_publications') && ! Schema::hasTable('ml_publication_variations')) {
            Schema::create('ml_publication_variations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ml_publication_id');
                $table->string('ml_variation_id', 40);
                $table->string('label')->nullable();
                $table->json('attribute_values')->nullable();
                $table->string('seller_sku', 120)->nullable();
                $table->string('gtin', 40)->nullable();
                $table->string('picture_url', 500)->nullable();
                $table->integer('ml_available_quantity')->default(0);
                // Sem FK: products.id varia de tipo entre instalações. A limpeza
                // ao apagar o produto é feita no ProductObserver.
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->boolean('stock_confirmed')->default(false);
                $table->string('link_source', 20)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('ml_publication_id')->references('id')->on('ml_publications')->onDelete('cascade');
                $table->unique(['ml_publication_id', 'ml_variation_id'], 'ml_pub_var_unique');
                $table->index('product_id');
            });
        }

        if (Schema::hasTable('ml_stock_logs') && ! Schema::hasColumn('ml_stock_logs', 'ml_variation_id')) {
            Schema::table('ml_stock_logs', function (Blueprint $table) {
                $table->string('ml_variation_id', 40)->nullable()->after('ml_order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_publication_variations');

        if (Schema::hasTable('ml_stock_logs') && Schema::hasColumn('ml_stock_logs', 'ml_variation_id')) {
            Schema::table('ml_stock_logs', function (Blueprint $table) {
                $table->dropColumn('ml_variation_id');
            });
        }
    }
};
