<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Página especial de um produto no catálogo: blocos montados pelo dono da loja. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_product_pages')) {
            return;
        }
        Schema::create('portal_product_pages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('product_id');
            $t->boolean('active')->default(true);
            $t->json('settings')->nullable();
            $t->json('blocks')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_product_pages');
    }
};
