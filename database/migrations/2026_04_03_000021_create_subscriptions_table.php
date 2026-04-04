<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();

            // Status: active | trialing | canceled | past_due | paused | expired | pending
            $table->string('status')->default('active');

            // Ciclo de cobrança
            $table->string('billing_cycle')->default('monthly'); // monthly | annual

            // Valor efetivamente cobrado
            $table->decimal('price_paid', 8, 2)->default(0);

            // Gateway de pagamento
            $table->string('gateway')->nullable();                    // stripe | pagseguro | mercadopago | manual
            $table->string('gateway_subscription_id')->nullable();    // ID da assinatura no gateway
            $table->string('gateway_customer_id')->nullable();        // ID do cliente no gateway
            $table->string('gateway_payment_id')->nullable();         // ID do último pagamento

            // Datas de vigência
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('paused_at')->nullable();

            // Metadados
            $table->text('notes')->nullable();                        // Notas internas do admin
            $table->json('metadata')->nullable();                     // Dados extras do gateway

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'current_period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
