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
        Schema::create('product_uploads_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('filename'); // Nome do arquivo enviado
            $table->string('file_type', 10); // pdf, csv
            $table->integer('total_products')->default(0); // Total de produtos no arquivo
            $table->integer('products_created')->default(0); // Produtos criados
            $table->integer('products_updated')->default(0); // Produtos atualizados (estoque somado)
            $table->integer('products_skipped')->default(0); // Produtos pulados (duplicatas)
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error_message')->nullable(); // Mensagem de erro se falhar
            $table->json('summary')->nullable(); // Resumo detalhado do upload
            $table->timestamp('started_at')->nullable(); // Quando começou
            $table->timestamp('completed_at')->nullable(); // Quando terminou
            $table->timestamps();

            // Índices para busca rápida
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_uploads_history');
    }
};
