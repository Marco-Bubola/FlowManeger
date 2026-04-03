<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mercadolivre_tokens')) {
            return;
        }

        Schema::create('mercadolivre_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('ml_user_id')->nullable()->comment('ID do usuário no Mercado Livre');
            $table->text('access_token')->comment('Token de acesso');
            $table->text('refresh_token')->comment('Token de renovação');
            $table->string('token_type', 20)->default('Bearer');
            $table->timestamp('expires_at')->comment('Data/hora de expiração do token');
            $table->text('scope')->nullable()->comment('Permissões do token');
            $table->boolean('is_active')->default(true)->comment('Token está ativo');
            $table->string('ml_nickname', 100)->nullable()->comment('Nome do usuário no ML');
            $table->json('user_info')->nullable()->comment('Informações do usuário ML');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('ml_user_id');
            $table->index('expires_at');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mercadolivre_tokens');
    }
};
