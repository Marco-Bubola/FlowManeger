<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pagamento online no portal (Mercado Pago): configuração do dono, campos de
 * pagamento e entrega no pedido e uma linha por tentativa de pagamento.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_settings')) {
            Schema::create('payment_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('provider', 30)->default('mercadopago');
                $table->boolean('enabled')->default(false);
                $table->text('access_token')->nullable();      // criptografado
                $table->string('public_key')->nullable();
                $table->text('webhook_secret')->nullable();    // criptografado
                $table->boolean('pix_enabled')->default(true);
                $table->boolean('allow_manual')->default(true); // "Combinar com a loja" continua
                $table->boolean('pickup_enabled')->default(true);
                $table->boolean('delivery_enabled')->default(false);
                $table->decimal('delivery_fee', 10, 2)->default(0);
                $table->string('delivery_note')->nullable();   // ex.: "Entregamos em Sorocaba"
                $table->unsignedSmallInteger('pix_minutes')->default(30);
                $table->timestamps();
            });
        }

        Schema::table('client_quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('client_quote_requests', 'payment_status')) {
                $table->string('payment_status', 20)->nullable()->after('payment_preference');
                $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_status');
                $table->timestamp('paid_at')->nullable()->after('payment_amount');
                $table->string('delivery_method', 20)->nullable()->after('paid_at');
                $table->decimal('delivery_fee', 10, 2)->nullable()->after('delivery_method');
                $table->json('shipping_address')->nullable()->after('delivery_fee');
            }
        });

        if (! Schema::hasTable('portal_payments')) {
            Schema::create('portal_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quote_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('provider', 30)->default('mercadopago');
                $table->string('provider_id', 60)->nullable()->index();
                $table->string('method', 20)->default('pix');
                $table->string('status', 30)->default('pending');
                $table->string('status_detail', 80)->nullable();
                $table->decimal('amount', 10, 2);
                $table->text('qr_code')->nullable();
                $table->longText('qr_code_base64')->nullable();
                $table->string('ticket_url', 500)->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->string('idempotency_key', 64)->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_payments');
        Schema::table('client_quote_requests', function (Blueprint $table) {
            foreach (['payment_status', 'payment_amount', 'paid_at', 'delivery_method', 'delivery_fee', 'shipping_address'] as $c) {
                if (Schema::hasColumn('client_quote_requests', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
        Schema::dropIfExists('payment_settings');
    }
};
