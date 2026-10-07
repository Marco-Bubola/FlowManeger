<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\User;
use App\Services\Cashbook\SalePaymentCashbookSync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * As tabelas de produção vieram de um dump (sem migrations), então o teste
 * cria só as colunas que a sincronização usa.
 */
class SalePaymentCashbookSyncTest extends TestCase
{
    private User $user;
    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('password');
            $t->text('preferences')->nullable();
            $t->timestamps();
        });
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('client_id')->nullable();
            $t->decimal('total_price', 10, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('sale_payments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sale_id');
            $t->decimal('amount_paid', 10, 2);
            $t->string('payment_method')->nullable();
            $t->date('payment_date')->nullable();
            $t->timestamps();
        });
        Schema::create('category', function (Blueprint $t) {
            $t->increments('id_category');
            $t->string('name');
            $t->unsignedBigInteger('user_id');
            $t->string('type')->nullable();
            $t->string('tipo')->nullable();
            $t->string('hexcolor_category')->nullable();
            $t->string('icone')->nullable();
            $t->integer('is_active')->default(1);
            $t->timestamps();
        });
        Schema::create('cashbook', function (Blueprint $t) {
            $t->increments('id');
            $t->decimal('value', 10, 2);
            $t->string('description')->nullable();
            $t->date('date');
            $t->boolean('is_pending')->default(false);
            $t->unsignedBigInteger('user_id');
            $t->integer('category_id');
            $t->integer('type_id');
            $t->string('note')->nullable();
            $t->integer('client_id')->nullable();
            $t->dateTime('inc_datetime')->nullable();
            $t->dateTime('edit_datetime')->nullable();
            $t->unsignedBigInteger('sale_payment_id')->nullable();
            $t->timestamps();
        });

        $this->user = User::forceCreate(['name' => 'Ana', 'email' => 'a@a.test', 'password' => 'x']);
        $this->sale = Sale::withoutGlobalScopes()->forceCreate(['user_id' => $this->user->id, 'total_price' => 100]);
    }

    public function test_desligado_nao_lanca_nada(): void
    {
        SalePayment::create(['sale_id' => $this->sale->id, 'amount_paid' => 50, 'payment_date' => '2026-10-07']);

        $this->assertSame(0, Cashbook::count());
    }

    public function test_ligado_cria_edita_e_remove_a_receita(): void
    {
        SalePaymentCashbookSync::setEnabled($this->user, true);

        $payment = SalePayment::create(['sale_id' => $this->sale->id, 'amount_paid' => 50, 'payment_date' => '2026-10-07']);

        $entry = Cashbook::firstOrFail();
        $this->assertSame('50.00', number_format($entry->value, 2, '.', ''));
        $this->assertSame(1, (int) $entry->type_id);
        $this->assertSame('Venda #'.$this->sale->id, $entry->description);

        $payment->update(['amount_paid' => 70]);
        $this->assertSame('70.00', number_format(Cashbook::firstOrFail()->value, 2, '.', ''));

        $payment->delete();
        $this->assertSame(0, Cashbook::count());
    }

    public function test_reaproveita_a_categoria_vendas(): void
    {
        SalePaymentCashbookSync::setEnabled($this->user, true);

        SalePayment::create(['sale_id' => $this->sale->id, 'amount_paid' => 10]);
        SalePayment::create(['sale_id' => $this->sale->id, 'amount_paid' => 20]);

        $this->assertSame(1, \App\Models\Category::where('name', 'Vendas')->count());
        $this->assertSame(2, Cashbook::count());
    }
}
