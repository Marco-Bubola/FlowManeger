<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Último alerta de estoque baixo enviado por produto (evita notificar
 * repetidamente o mesmo produto). Apagado quando o estoque volta acima do mínimo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('low_stock_alerts')) {
            return;
        }

        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamp('alerted_at');
            $table->integer('stock_at_alert')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('low_stock_alerts');
    }
};
