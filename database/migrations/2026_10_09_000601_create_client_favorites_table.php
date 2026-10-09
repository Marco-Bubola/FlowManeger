<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lista de desejos do portal: produtos que o cliente marcou com o coração,
 * com o pedido de aviso (entrar em promoção / voltar ao estoque).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_favorites')) {
            Schema::create('client_favorites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');      // loja (dono do produto)
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('product_id');
                $table->boolean('notify_promo')->default(true);
                $table->boolean('notify_stock')->default(true);
                $table->timestamps();

                $table->unique(['client_id', 'product_id']);
                $table->index('product_id');
                $table->index('user_id');
            });
        }

        foreach (['notify_promo', 'notify_stock'] as $col) {
            if (! Schema::hasColumn('client_favorites', $col)) {
                Schema::table('client_favorites', fn (Blueprint $t) => $t->boolean($col)->default(true));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_favorites');
    }
};
