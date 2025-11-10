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
        Schema::create('invoice_category_learning', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('description_pattern'); // Padrão da descrição (normalizado/limpo)
            $table->integer('category_id'); // Compatível com id_category (int signed)
            $table->integer('confidence')->default(1); // Quantas vezes foi confirmado
            $table->timestamps();

            // Índices para busca rápida
            $table->index(['user_id', 'description_pattern']);
            $table->index(['user_id', 'category_id']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id_category')->on('category')->onDelete('cascade');

            // Garantir que cada padrão seja único por usuário e categoria (nome curto para índice)
            $table->unique(['user_id', 'description_pattern', 'category_id'], 'icl_unique_pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_category_learning');
    }
};
