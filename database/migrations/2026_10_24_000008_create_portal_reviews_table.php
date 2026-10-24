<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avaliações dos produtos no catálogo: nota de 1 a 5 e comentário de um cliente do portal,
 * guardadas no produto principal da família. verified = o cliente já comprou o produto.
 * A loja pode responder ou esconder (hidden) em Promoções › Avaliações.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_reviews')) {
            return;
        }
        Schema::create('portal_reviews', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('product_id')->index();
            $t->unsignedBigInteger('client_id')->nullable()->index();
            $t->string('name', 80);
            $t->unsignedTinyInteger('rating');
            $t->text('comment')->nullable();
            $t->text('reply')->nullable();
            $t->timestamp('replied_at')->nullable();
            $t->boolean('verified')->default(false);
            $t->boolean('hidden')->default(false);
            $t->timestamps();
            $t->unique(['product_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_reviews');
    }
};
