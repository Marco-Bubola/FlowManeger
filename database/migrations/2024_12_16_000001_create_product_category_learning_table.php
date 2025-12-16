<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_category_learning', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_name_pattern', 255); // Padrão do nome do produto (normalizado)
            $table->string('product_code', 100)->nullable(); // Código do produto (opcional)
            $table->integer('category_id'); // ID da categoria (compatível com id_category)
            $table->integer('confidence')->default(1); // Quantas vezes foi confirmado (peso)
            $table->timestamp('last_used_at')->nullable(); // Última vez que foi usado
            $table->timestamps();

            // Índices para busca rápida
            $table->index(['user_id', 'product_name_pattern']);
            $table->index(['user_id', 'product_code']);
            $table->index(['user_id', 'category_id']);
            $table->index('last_used_at');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id_category')->on('category')->onDelete('cascade');

            // Garantir que cada padrão seja único por usuário
            $table->unique(['user_id', 'product_name_pattern', 'category_id'], 'unique_user_pattern_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_learning');
    }
};
