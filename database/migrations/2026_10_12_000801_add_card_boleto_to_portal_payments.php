<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Cartão (crédito/débito), boleto e estorno no pagamento online do portal. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'card_enabled')) {
                $table->boolean('card_enabled')->default(false)->after('pix_minutes');
                $table->unsignedTinyInteger('max_installments')->default(12)->after('card_enabled');
                $table->boolean('boleto_enabled')->default(false)->after('max_installments');
                $table->unsignedTinyInteger('boleto_days')->default(3)->after('boleto_enabled');
            }
        });

        Schema::table('portal_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_payments', 'installments')) {
                $table->unsignedTinyInteger('installments')->nullable()->after('amount');
                $table->string('card_brand', 30)->nullable()->after('installments');
                $table->string('card_last4', 4)->nullable()->after('card_brand');
                $table->string('barcode', 80)->nullable()->after('ticket_url');
                $table->unsignedBigInteger('sale_payment_id')->nullable()->after('sale_id');
                $table->timestamp('refunded_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portal_payments', function (Blueprint $table) {
            foreach (['installments', 'card_brand', 'card_last4', 'barcode', 'sale_payment_id', 'refunded_at'] as $c) {
                if (Schema::hasColumn('portal_payments', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['card_enabled', 'max_installments', 'boleto_enabled', 'boleto_days'] as $c) {
                if (Schema::hasColumn('payment_settings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
