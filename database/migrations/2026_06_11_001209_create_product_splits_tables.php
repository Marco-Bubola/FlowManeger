<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoria das divisões de combo/kit em produtos separados.
 * products.id é UNSIGNED INT → referências usam unsignedInteger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_splits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->integer('source_product_id'); // combo/kit dividido (products.id é int signed)
            $t->enum('source_action', ['kept', 'archived', 'converted'])->default('kept');
            $t->boolean('distributed_stock')->default(false);
            $t->timestamps();

            $t->foreign('source_product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->index(['user_id', 'created_at']);
        });

        Schema::create('product_split_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('split_id')->constrained('product_splits')->cascadeOnDelete();
            $t->integer('result_product_id');          // produto gerado/vinculado
            $t->integer('variation_parent_id')->nullable();
            $t->enum('mode', ['new', 'linked', 'variation']);
            $t->integer('quantity')->default(1);
            $t->integer('stock_assigned')->default(0);
            $t->timestamps();

            $t->foreign('result_product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->foreign('variation_parent_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_split_items');
        Schema::dropIfExists('product_splits');
    }
};
