<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->integer('category_id');
            $table->decimal('valor', 10, 2);
            $table->integer('mes');
            $table->integer('ano');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['user_id','category_id','mes','ano'], 'usuario_categoria_periodo');
            $table->index('category_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id_category')->on('category')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};
