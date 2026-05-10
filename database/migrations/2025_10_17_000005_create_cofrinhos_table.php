<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cofrinhos')) {
            return;
        }

        Schema::create('cofrinhos', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->decimal('meta_valor', 10, 2)->nullable();
            $table->enum('status', ['ativo', 'arquivado'])->default('ativo');
            $table->timestamps();
            $table->string('icone', 50)->default('fa-piggy-bank');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cofrinhos')) {
            return;
        }

        Schema::dropIfExists('cofrinhos');
    }
};
