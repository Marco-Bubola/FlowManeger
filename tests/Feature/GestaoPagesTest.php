<?php

namespace Tests\Feature;

use App\Livewire\Gestao\Receivables;
use App\Livewire\Gestao\Restock;
use App\Livewire\Gestao\SalesProfit;
use App\Livewire\Gestao\StockMovements;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Telas de Gestão com dados mínimos. As tabelas de produção vieram de um
 * dump (sem migrations), então o teste cria só as colunas usadas.
 */
class GestaoPagesTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('password');
            $t->boolean('is_admin')->default(false);
            $t->text('preferences')->nullable();
            $t->timestamps();
        });
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('client_id')->nullable();
            $t->decimal('total_price', 10, 2)->default(0);
            $t->decimal('amount_paid', 10, 2)->default(0);
            $t->string('status')->nullable();
            $t->string('tipo_pagamento')->nullable();
            $t->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sale_id');
            $t->unsignedBigInteger('product_id');
            $t->integer('quantity');
            $t->decimal('price', 10, 2);
            $t->decimal('price_sale', 10, 2);
            $t->timestamps();
        });
        Schema::create('venda_parcelas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sale_id');
            $t->integer('numero_parcela');
            $t->decimal('valor', 10, 2);
            $t->date('data_vencimento')->nullable();
            $t->string('status');
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('name');
            $t->string('product_code')->nullable();
            $t->integer('stock_quantity')->default(0);
            $t->decimal('price', 10, 2)->default(0);
            $t->string('image')->nullable();
            $t->string('tipo')->default('simples');
            $t->string('status')->nullable();
            $t->timestamps();
        });
        Schema::create('ml_stock_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('ml_publication_id')->nullable();
            $t->string('operation_type');
            $t->integer('quantity_before');
            $t->integer('quantity_after');
            $t->integer('quantity_change');
            $t->timestamps();
        });

        $this->user = User::forceCreate(['name' => 'Ana', 'email' => 'a@a.test', 'password' => 'x']);
        $clientId = DB::table('clients')->insertGetId(['name' => 'Maria Silva', 'phone' => '(11) 98888-7777']);
        $productId = DB::table('products')->insertGetId(['user_id' => $this->user->id, 'name' => 'Perfume', 'product_code' => 'P1', 'stock_quantity' => 1, 'price' => 40]);

        $now = now()->toDateTimeString();
        $parcelada = DB::table('sales')->insertGetId(['user_id' => $this->user->id, 'client_id' => $clientId, 'total_price' => 200, 'amount_paid' => 100, 'status' => 'confirmada', 'tipo_pagamento' => 'parcelado', 'created_at' => $now]);
        DB::table('venda_parcelas')->insert([
            ['sale_id' => $parcelada, 'numero_parcela' => 1, 'valor' => 100, 'data_vencimento' => now()->subDays(3)->toDateString(), 'status' => 'pendente'],
            ['sale_id' => $parcelada, 'numero_parcela' => 2, 'valor' => 100, 'data_vencimento' => now()->addDays(25)->toDateString(), 'status' => 'paga'],
        ]);
        $avista = DB::table('sales')->insertGetId(['user_id' => $this->user->id, 'client_id' => $clientId, 'total_price' => 80, 'amount_paid' => 30, 'status' => 'confirmada', 'tipo_pagamento' => 'a_vista', 'created_at' => $now]);
        DB::table('sale_items')->insert(['sale_id' => $avista, 'product_id' => $productId, 'quantity' => 2, 'price' => 25, 'price_sale' => 40, 'created_at' => $now]);
        DB::table('ml_stock_logs')->insert(['product_id' => $productId, 'operation_type' => 'internal_sale', 'quantity_before' => 3, 'quantity_after' => 1, 'quantity_change' => -2, 'created_at' => $now]);

        $this->actingAs($this->user);
    }

    public function test_contas_a_receber_soma_parcelas_pendentes_e_saldos(): void
    {
        Livewire::test(Receivables::class)
            ->assertSee('Maria Silva')
            ->assertSee('R$ 150,00')   // 100 da parcela + 50 de saldo
            ->assertSee('R$ 100,00')   // vencido
            ->assertSee('wa.me/5511988887777', false)
            ->set('filter', 'vencidas')
            ->assertSee('Parcela 1')
            ->assertDontSee('Saldo da venda');
    }

    public function test_lucro_desconta_o_custo(): void
    {
        Livewire::test(SalesProfit::class)
            ->assertSee('R$ 280,00')   // faturamento 200 + 80
            ->assertSee('R$ 50,00')    // custo 2 x 25
            ->assertSee('R$ 30,00');   // lucro da venda à vista: 80 - 50
    }

    public function test_repor_lista_produto_abaixo_do_minimo(): void
    {
        Livewire::test(Restock::class)
            ->assertSee('Perfume')
            ->assertSee('vendidos em 30 dias')
            ->set('minimum', 0)
            ->assertDontSee('Perfume');

        $this->assertSame(0, $this->user->fresh()->preferences['stock']['minimum']);
    }

    public function test_movimentacoes_mostram_saida(): void
    {
        Livewire::test(StockMovements::class)
            ->assertSee('Perfume')
            ->assertSee('Venda Interna')
            ->set('direction', 'entrada')
            ->assertDontSee('Venda Interna');
    }
}
