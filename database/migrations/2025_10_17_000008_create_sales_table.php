<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('total_price', 10, 2);
            $table->string('status', 20)->default('pendente');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->string('payment_method', 100)->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->enum('tipo_pagamento', ['a_vista','parcelado'])->default('a_vista');
            $table->integer('parcelas')->default(1);

            $table->index('client_id', 'idx_sales_client_id');
            $table->index('user_id', 'idx_sales_user_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
