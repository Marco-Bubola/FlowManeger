<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venda_parcelas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('sale_id');
            $table->integer('numero_parcela');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->enum('status', ['pendente','paga','vencida'])->default('pendente');
            $table->dateTime('pago_em')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_parcelas');
    }
};
