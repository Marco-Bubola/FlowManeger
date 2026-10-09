<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avisos "Avise-me" pendentes: um favorito entrou em promoção (reason = promo)
 * ou voltou ao estoque (reason = stock). O cliente vê no portal (seen_at) e a
 * loja avisa pelo WhatsApp (contacted_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_product_alerts')) {
            return;
        }

        Schema::create('client_product_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('product_id');
            $table->string('reason', 10);                 // promo | stock
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'seen_at']);
            $table->index(['product_id', 'reason']);
            $table->index(['user_id', 'contacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_product_alerts');
    }
};
