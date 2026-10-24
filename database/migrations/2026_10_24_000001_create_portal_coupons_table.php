<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cupons de desconto do catálogo (portal): a loja cria o código e o cliente
 * digita no carrinho. O pedido guarda o código e o desconto dado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('portal_coupons')) {
            Schema::create('portal_coupons', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->index();
                $t->string('code', 40);
                $t->string('kind', 10)->default('percent'); // percent | fixed
                $t->decimal('value', 10, 2);
                $t->decimal('min_total', 10, 2)->nullable();
                $t->date('starts_at')->nullable();
                $t->date('ends_at')->nullable();
                $t->unsignedInteger('max_uses')->nullable();
                $t->boolean('once_per_client')->default(true);
                $t->boolean('active')->default(true);
                $t->timestamps();
                $t->unique(['user_id', 'code']);
            });
        }

        Schema::table('client_quote_requests', function (Blueprint $t) {
            if (!Schema::hasColumn('client_quote_requests', 'coupon_code')) {
                $t->string('coupon_code', 40)->nullable();
            }
            if (!Schema::hasColumn('client_quote_requests', 'discount')) {
                $t->decimal('discount', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_coupons');
        Schema::table('client_quote_requests', function (Blueprint $t) {
            foreach (['coupon_code', 'discount'] as $col) {
                if (Schema::hasColumn('client_quote_requests', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
