<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('descricao_custos_adicionais')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('price_sale', 10, 2);
            $table->decimal('custos_adicionais', 10, 2)->default('0.00');
            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('category_id');
            $table->unsignedBigInteger('user_id');
            $table->string('product_code', 100);
            $table->string('image')->nullable();
            $table->enum('status', ['ativo','inativo','descontinuado'])->default('ativo');
            $table->enum('tipo', ['simples','kit'])->default('simples');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('category_id', 'idx_products_category_id');
            $table->index('user_id', 'idx_products_user_id');
            $table->foreign('category_id')->references('id_category')->on('category');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
