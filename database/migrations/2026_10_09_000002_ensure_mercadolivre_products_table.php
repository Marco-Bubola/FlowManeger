<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A tabela mercadolivre_products (model MercadoLivreProduct) nunca teve
     * migration. Cria só se ainda não existir (em produção pode ter sido
     * criada à mão).
     */
    public function up(): void
    {
        if (Schema::hasTable('mercadolivre_products')) {
            return;
        }

        Schema::create('mercadolivre_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('ml_item_id', 50)->nullable()->index();
            $table->string('ml_category_id', 50)->nullable();
            $table->string('listing_type', 50)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('ml_permalink', 500)->nullable();
            $table->string('sync_status', 30)->nullable()->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('ml_attributes')->nullable();
            $table->decimal('ml_price', 10, 2)->nullable();
            $table->integer('ml_quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Não remove: a tabela pode ter existido antes desta migration.
     */
    public function down(): void
    {
        //
    }
};
