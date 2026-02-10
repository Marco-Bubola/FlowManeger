<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para armazenar pedidos vindos do Mercado Livre
     */
    public function up(): void
    {
        // Verificar se a tabela já existe
        if (Schema::hasTable('mercadolivre_orders')) {
            return;
        }

        Schema::create('mercadolivre_orders', function (Blueprint $table) {
            $table->id();
            
            // Identificadores do Mercado Livre
            $table->string('ml_order_id', 50)->unique()->comment('ID do pedido no ML');
            $table->string('ml_item_id', 50)->comment('ID do produto no ML');
            $table->unsignedInteger('product_id')->nullable()->comment('ID do produto no sistema (products.id é unsigned int)');
            
            // Dados do comprador
            $table->bigInteger('buyer_id')->comment('ID do comprador no ML');
            $table->string('buyer_nickname', 100)->nullable();
            $table->string('buyer_email', 255)->nullable();
            $table->string('buyer_phone', 20)->nullable();
            $table->text('buyer_address')->nullable()->comment('Endereço de entrega JSON');
            
            // Dados do pedido
            $table->integer('quantity')->comment('Quantidade comprada');
            $table->decimal('unit_price', 10, 2)->comment('Preço unitário');
            $table->decimal('total_amount', 10, 2)->comment('Valor total do pedido');
            $table->string('currency_id', 3)->default('BRL');
            
            // Status do pedido
            $table->enum('order_status', ['pending', 'paid', 'confirmed', 'ready_to_ship', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->comment('Status do pedido');
            
            // Status do pagamento
            $table->enum('payment_status', ['pending', 'approved', 'in_process', 'rejected', 'cancelled', 'refunded'])
                ->default('pending')
                ->comment('Status do pagamento');
            
            $table->string('payment_method', 50)->nullable()->comment('Método de pagamento');
            $table->string('payment_type', 50)->nullable()->comment('Tipo de pagamento (credit_card, pix, etc)');
            
            // Dados de envio
            $table->string('shipping_id', 50)->nullable()->comment('ID do envio no ML');
            $table->string('tracking_number', 100)->nullable()->comment('Código de rastreamento');
            $table->string('shipping_method', 100)->nullable()->comment('Método de envio');
            $table->decimal('shipping_cost', 10, 2)->nullable()->comment('Custo do frete');
            
            // Datas importantes
            $table->timestamp('date_created')->comment('Data de criação no ML');
            $table->timestamp('date_closed')->nullable()->comment('Data de fechamento no ML');
            $table->timestamp('date_last_updated')->nullable()->comment('Última atualização no ML');
            
            // Integração com o sistema interno
            $table->unsignedInteger('imported_to_sale_id')->nullable()
                ->comment('ID da venda importada no sistema');
            
            $table->enum('sync_status', ['pending', 'imported', 'error'])->default('pending');
            $table->text('error_message')->nullable();
            
            // Dados brutos da API (para referência)
            $table->json('raw_data')->nullable()->comment('Dados completos da API ML');
            
            $table->timestamps();
            
            // Foreign keys - Comentadas temporariamente por incompatibilidade de tipos
            // TODO: Adicionar foreign keys manualmente se necessário
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            // $table->foreign('imported_to_sale_id')->references('id')->on('sales')->onDelete('set null');
            
            // Índices
            $table->index('product_id');
            $table->index('imported_to_sale_id');
            $table->index('ml_order_id');
            $table->index('ml_item_id');
            $table->index('buyer_id');
            $table->index('order_status');
            $table->index('payment_status');
            $table->index('sync_status');
            $table->index('date_created');
            $table->index(['sync_status', 'order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_orders');
    }
};
