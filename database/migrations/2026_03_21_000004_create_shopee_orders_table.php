<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedidos recebidos via Shopee Webhook (Order Status notifications)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shopee_orders')) {
            return;
        }

        Schema::create('shopee_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shop_id')->comment('Shop ID onde o pedido foi gerado');

            // IDs Shopee
            $table->string('shopee_order_sn')->unique()->comment('Order SN da Shopee (identificador único)');
            $table->string('shopee_item_id')->nullable();
            $table->string('shopee_model_id')->nullable();

            // Dados do comprador
            $table->string('buyer_username')->nullable();
            $table->string('buyer_phone', 30)->nullable();
            $table->json('shipping_address')->nullable();

            // Itens do pedido
            $table->json('order_items')->nullable()->comment('Array de itens do pedido');

            // Valores
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('BRL');

            // Status
            $table->string('order_status', 50)->comment('UNPAID, READY_TO_SHIP, SHIPPED, COMPLETED, CANCELLED');
            $table->string('payment_method', 50)->nullable();

            // Logística
            $table->string('tracking_number', 100)->nullable();
            $table->string('shipping_carrier', 100)->nullable();
            $table->unsignedTinyInteger('days_to_ship')->nullable();
            $table->timestamp('ship_by_date')->nullable();

            // Integração interna
            $table->integer('imported_to_sale_id')->nullable()
                ->comment('Venda interna gerada a partir deste pedido');
            $table->foreign('imported_to_sale_id')->references('id')->on('sales')->nullOnDelete();
            $table->enum('sync_status', ['pending', 'synced', 'error'])->default('pending');
            $table->text('error_message')->nullable();

            // Dados brutos para auditoria
            $table->json('raw_data')->nullable();

            $table->timestamp('shopee_created_at')->nullable();
            $table->timestamp('shopee_updated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'order_status']);
            $table->index(['shopee_item_id']);
            $table->index(['sync_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_orders');
    }
};
