<?php

namespace Tests\Feature;

use App\Livewire\Accounts\AccountsIndex;
use App\Models\Account;
use App\Models\Cashbook;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AccountsTest extends TestCase
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
            $t->text('preferences')->nullable();
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
            $t->dateTime('inc_datetime')->nullable();
            $t->timestamps();
        });

        // A migration nova cria accounts e a coluna cashbook.account_id
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_07_000003_create_accounts_and_cashbook_account.php', '--force' => true]);

        $this->user = User::forceCreate(['name' => 'Ana', 'email' => 'a@a.test', 'password' => 'x']);
        $this->actingAs($this->user);
    }

    public function test_cria_contas_e_transfere(): void
    {
        Livewire::test(AccountsIndex::class)
            ->call('openCreate')->set('name', 'Nubank')->set('initial_balance', 1000)->call('save')
            ->call('openCreate')->set('name', 'Carteira')->set('type', 'carteira')->set('initial_balance', 50)->call('save')
            ->assertSee('R$ 1.050,00');

        [$nubank, $carteira] = [Account::where('name', 'Nubank')->first(), Account::where('name', 'Carteira')->first()];

        Livewire::test(AccountsIndex::class)
            ->call('openTransfer', $nubank->id)
            ->set('toId', $carteira->id)
            ->set('transferValue', 200)
            ->call('transfer')
            ->assertHasNoErrors();

        $this->assertSame(2, Cashbook::count());
        $this->assertSame(800.0, $nubank->balance());
        $this->assertSame(250.0, $carteira->balance());
    }

    public function test_nao_transfere_para_a_mesma_conta(): void
    {
        $a = Account::create(['user_id' => $this->user->id, 'name' => 'A']);

        Livewire::test(AccountsIndex::class)
            ->set('fromId', $a->id)->set('toId', $a->id)->set('transferValue', 10)->set('transferDate', '2026-10-07')
            ->call('transfer')
            ->assertHasErrors('fromId');
    }

    public function test_nao_mexe_em_conta_de_outro_usuario(): void
    {
        $other = Account::create(['user_id' => 999, 'name' => 'Alheia']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(AccountsIndex::class)->call('openEdit', $other->id);
    }
}
