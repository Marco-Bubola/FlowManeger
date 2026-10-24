<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Frete por CEP do portal: a loja cadastra regiões (faixa de CEP ou cidade) com
 * taxa, frete grátis acima de um valor e prazo. Fora delas vale a taxa padrão
 * (payment_settings.delivery_fee) ou a loja não entrega.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('portal_shipping_zones')) {
            Schema::create('portal_shipping_zones', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->index();
                $t->string('name', 80);
                $t->string('kind', 10)->default('cep'); // cep | city
                $t->char('cep_from', 8)->nullable();
                $t->char('cep_to', 8)->nullable();
                $t->string('city', 80)->nullable();
                $t->char('uf', 2)->nullable();
                $t->decimal('fee', 10, 2)->default(0);
                $t->decimal('free_above', 10, 2)->nullable();
                $t->string('eta', 60)->nullable(); // "no mesmo dia", "1 a 2 dias úteis"
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (Schema::hasTable('payment_settings') && !Schema::hasColumn('payment_settings', 'shipping_outside')) {
            Schema::table('payment_settings', function (Blueprint $t) {
                $t->string('shipping_outside', 10)->default('fee'); // fee = taxa padrão | none = não entrega
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_shipping_zones');
        if (Schema::hasColumn('payment_settings', 'shipping_outside')) {
            Schema::table('payment_settings', fn (Blueprint $t) => $t->dropColumn('shipping_outside'));
        }
    }
};
