<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fichas do Robô Móveis Stylo guardadas no site (sem tela: o robô lê e grava pela /robo-api). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('robo_tokens')) {
            Schema::create('robo_tokens', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('token_hash', 64)->unique();
                $t->string('nome', 120)->nullable();
                $t->timestamp('last_used_at')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('robo_fichas')) {
            Schema::create('robo_fichas', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('pasta', 191);
                $t->string('situacao', 12)->default('aberta');
                $t->string('codigo', 60)->nullable();
                $t->string('nome', 255)->nullable();
                $t->longText('conteudo')->nullable();
                $t->json('arquivos')->nullable();
                $t->json('historico')->nullable();
                $t->unsignedInteger('versao')->default(0);
                $t->boolean('completa')->default(true);
                $t->timestamp('editada_em')->nullable();
                $t->softDeletes();
                $t->timestamps();
                $t->unique(['user_id', 'pasta']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('robo_fichas');
        Schema::dropIfExists('robo_tokens');
    }
};
