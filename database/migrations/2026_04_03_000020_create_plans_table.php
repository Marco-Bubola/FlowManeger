<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // Grátis, Pro, Business
            $table->string('slug')->unique();      // free, pro, business
            $table->text('description')->nullable();

            // Preços
            $table->decimal('price_monthly', 8, 2)->default(0);
            $table->decimal('price_annual', 8, 2)->default(0);  // valor/mês no plano anual

            // Configurações
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);      // atribuído a novos usuários
            $table->integer('sort_order')->default(0);

            // Limites (-1 = ilimitado)
            $table->integer('max_products')->default(50);
            $table->integer('max_orders_per_month')->default(100);
            $table->integer('max_clients')->default(200);
            $table->integer('max_users')->default(1);           // membros da equipe

            // Feature flags
            $table->boolean('has_ml_integration')->default(false);
            $table->boolean('has_shopee_integration')->default(false);
            $table->boolean('has_ai_features')->default(false);
            $table->boolean('has_advanced_reports')->default(false);
            $table->boolean('has_financial_control')->default(true);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('has_export_pdf_excel')->default(false);

            // Features extras como JSON (para extensibilidade futura)
            $table->json('features')->nullable();

            // Cor/estilo para exibição na UI
            $table->string('color')->nullable();       // ex: #a855f7
            $table->string('badge_label')->nullable(); // ex: "Mais popular"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
