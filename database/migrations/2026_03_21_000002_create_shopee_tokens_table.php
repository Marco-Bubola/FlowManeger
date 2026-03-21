<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens OAuth da Shopee por usuário/loja
 * A Shopee usa autenticação via Partner ID + Shop ID + HMAC-SHA256
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Credenciais da loja Shopee
            $table->string('shop_id')->comment('ID da loja no Shopee (Shop ID)');
            $table->string('shop_name')->nullable()->comment('Nome da loja no Shopee');
            $table->string('partner_id')->comment('Partner ID da aplicação Shopee');

            // Tokens OAuth 2.0
            $table->text('access_token')->comment('Token de acesso');
            $table->text('refresh_token')->nullable()->comment('Token de renovação');
            $table->timestamp('expires_at')->nullable()->comment('Expiração do access_token');
            $table->timestamp('refresh_expires_at')->nullable()->comment('Expiração do refresh_token');

            // Metadados da loja
            $table->string('region', 10)->default('BR')->comment('Região: BR, MX, etc.');
            $table->json('shop_info')->nullable()->comment('Informações completas da loja');

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_refreshed_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'shop_id']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_tokens');
    }
};
