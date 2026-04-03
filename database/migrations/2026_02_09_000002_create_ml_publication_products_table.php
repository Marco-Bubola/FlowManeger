<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ml_publication_products')) {
            return;
        }

        Schema::create('ml_publication_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ml_publication_id')->comment('Publicação ML');
            $table->unsignedBigInteger('product_id')->comment('Produto do sistema');
            $table->integer('quantity')->default(1)->comment('Quantidade por venda (para kits)');
            $table->decimal('unit_cost', 10, 2)->nullable()->comment('Snapshot do custo unitário');
            $table->integer('sort_order')->default(0)->comment('Ordem de apresentação');
            $table->timestamps();

            $table->foreign('ml_publication_id')->references('id')->on('ml_publications')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['ml_publication_id', 'product_id']);
            $table->index('product_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_publication_products');
    }
};
