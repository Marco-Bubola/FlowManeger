<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tabela de pedidos do ML nunca teve migration: cria se faltar e garante
 * a coluna que liga o pedido à venda importada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mercadolivre_orders')) {
            Schema::create('mercadolivre_orders', function (Blueprint $table) {
                $table->id();
                $table->string('ml_order_id', 50)->unique();
                $table->string('ml_item_id', 50)->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('buyer_id', 50)->nullable();
                $table->string('buyer_nickname')->nullable();
                $table->string('buyer_email')->nullable();
                $table->string('buyer_phone', 50)->nullable();
                $table->json('buyer_address')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->string('currency_id', 10)->default('BRL');
                $table->string('order_status', 50)->nullable();
                $table->string('payment_status', 50)->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_type', 50)->nullable();
                $table->string('shipping_id', 50)->nullable();
                $table->string('tracking_number', 100)->nullable();
                $table->string('shipping_method', 100)->nullable();
                $table->decimal('shipping_cost', 10, 2)->nullable();
                $table->timestamp('date_created')->nullable();
                $table->timestamp('date_closed')->nullable();
                $table->timestamp('date_last_updated')->nullable();
                $table->unsignedBigInteger('imported_to_sale_id')->nullable();
                $table->string('sync_status', 30)->nullable();
                $table->text('error_message')->nullable();
                $table->json('raw_data')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (!Schema::hasColumn('mercadolivre_orders', 'imported_to_sale_id')) {
            Schema::table('mercadolivre_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('imported_to_sale_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Tabela pode ter vindo de fora das migrations: não apaga.
    }
};
