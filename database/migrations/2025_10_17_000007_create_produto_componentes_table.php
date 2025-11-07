<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_componentes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('kit_produto_id');
            $table->integer('componente_produto_id');
            $table->integer('quantidade');
            $table->decimal('preco_custo_unitario', 10, 2)->comment('Preço de custo unitário do componente no momento da criação do kit');
            $table->decimal('preco_venda_unitario', 10, 2)->comment('Preço de venda unitário do componente no momento da criação do kit');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('kit_produto_id');
            $table->index('componente_produto_id');
            $table->foreign('kit_produto_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('componente_produto_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_componentes');
    }
};
