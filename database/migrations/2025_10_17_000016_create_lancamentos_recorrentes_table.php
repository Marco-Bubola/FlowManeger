<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lancamentos_recorrentes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->integer('type_id');
            $table->integer('category_id');
            $table->enum('frequencia', ['diaria','semanal','mensal','anual']);
            $table->date('data_inicio');
            $table->date('proximo_vencimento');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id');
            $table->index('type_id');
            $table->index('category_id');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('type_id')->references('id_type')->on('type');
            $table->foreign('category_id')->references('id_category')->on('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos_recorrentes');
    }
};
