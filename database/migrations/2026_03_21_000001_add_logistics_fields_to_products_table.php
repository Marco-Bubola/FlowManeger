<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos logísticos obrigatórios para integração com Shopee
 * (Peso, Dimensões do Pacote) — também úteis para cálculo de frete ML
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Peso em gramas (obrigatório Shopee)
            $table->unsignedInteger('weight_grams')->nullable()->after('condition')
                ->comment('Peso do produto em gramas (obrigatório Shopee)');

            // Dimensões do pacote em centímetros
            $table->decimal('length_cm', 8, 2)->nullable()->after('weight_grams')
                ->comment('Comprimento do pacote em cm');
            $table->decimal('width_cm', 8, 2)->nullable()->after('length_cm')
                ->comment('Largura do pacote em cm');
            $table->decimal('height_cm', 8, 2)->nullable()->after('width_cm')
                ->comment('Altura do pacote em cm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_grams', 'length_cm', 'width_cm', 'height_cm']);
        });
    }
};
