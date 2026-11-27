<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\Products\ProductFichaImporter;
use App\Services\Products\VariationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportFichaKitTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->text('preferences')->nullable(); $t->timestamps();
        });
        Schema::create('category', function (Blueprint $t) {
            $t->increments('id_category'); $t->string('name'); $t->unsignedBigInteger('user_id'); $t->string('type')->nullable(); $t->string('tipo')->nullable(); $t->integer('is_active')->default(1); $t->string('icone')->nullable(); $t->string('hexcolor_category')->nullable(); $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->text('description')->nullable(); $t->text('specs')->nullable();
            $t->decimal('price', 10, 2)->default(0); $t->decimal('price_sale', 10, 2)->default(0); $t->decimal('price_original', 10, 2)->nullable();
            $t->integer('stock_quantity')->default(0); $t->integer('category_id')->nullable(); $t->unsignedBigInteger('user_id');
            $t->string('product_code')->nullable(); $t->string('image')->nullable(); $t->string('status')->nullable(); $t->string('tipo')->nullable();
            $t->decimal('custos_adicionais', 10, 2)->default(0); $t->string('descricao_custos_adicionais')->nullable();
            $t->string('barcode')->nullable(); $t->string('brand')->nullable(); $t->string('model')->nullable(); $t->integer('warranty_months')->nullable(); $t->string('condition')->nullable();
            $t->integer('weight_grams')->nullable(); $t->decimal('length_cm', 8, 2)->nullable(); $t->decimal('width_cm', 8, 2)->nullable(); $t->decimal('height_cm', 8, 2)->nullable();
            $t->unsignedBigInteger('parent_id')->nullable(); $t->boolean('is_variation_parent')->default(false); $t->string('variation_attribute')->nullable(); $t->string('variation_value')->nullable(); $t->integer('variation_sort')->nullable();
            $t->boolean('made_to_order')->default(false); $t->integer('production_days')->nullable(); $t->text('cost_items')->nullable();
            $t->timestamps();
        });
        Schema::create('product_images', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('product_id'); $t->string('filename'); $t->string('alt_text')->nullable(); $t->string('source')->nullable(); $t->integer('sort_order')->default(0); $t->timestamps();
        });
        Schema::create('produto_componentes', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('kit_produto_id'); $t->unsignedBigInteger('componente_produto_id'); $t->integer('quantidade')->default(1);
            $t->decimal('preco_custo_unitario', 10, 2)->nullable(); $t->decimal('preco_venda_unitario', 10, 2)->nullable(); $t->timestamps();
        });
        // Sem o observer do ML (tabelas do ML não existem aqui)
        $this->app->instance(\App\Observers\ProductObserver::class, new class { public function __call($m, $a) {} });
        $this->user = User::forceCreate(['name' => 'Marco', 'email' => 'm@m.test', 'password' => 'x']);
        $this->actingAs($this->user);
    }


    private function importa(array $f, bool $update = false): array
    {
        $svc = app(ProductFichaImporter::class);
        $plan = $svc->plan($this->user->id, $f, [], $update);

        return [$plan, $svc->import($this->user->id, $plan, [], app(VariationService::class))];
    }

    private function p(string $code): Product
    {
        return Product::where('product_code', $code)->firstOrFail();
    }

    private function pecas(): void
    {
        $this->importa(['nome' => 'Mesinha bistrô', 'codigo' => 'MBR', 'variacao_por' => 'Cor', 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'MBR-PT', 'custo' => 100, 'venda' => 320, 'estoque' => 3],
            ['valor' => 'Branco', 'codigo' => 'MBR-BR', 'custo' => 100, 'venda' => 320, 'estoque' => 1],
        ]]);
        $this->importa(['nome' => 'Banqueta', 'codigo' => 'BQQ', 'variacao_por' => 'Tamanho e cor', 'variacoes' => [
            ['valor' => '75 cm · Preto', 'codigo' => 'BQQ75-PT', 'custo' => 50, 'venda' => 199, 'estoque' => 4],
            ['valor' => '54 cm · Preto', 'codigo' => 'BQQ54-PT', 'custo' => 40, 'venda' => 179, 'estoque' => 5],
            ['valor' => '75 cm · Branco', 'codigo' => 'BQQ75-BR', 'custo' => 50, 'venda' => 199, 'estoque' => 1],
            ['valor' => '54 cm · Branco', 'codigo' => 'BQQ54-BR', 'custo' => 40, 'venda' => 179, 'estoque' => 2],
        ]]);
    }

    public function test_conjunto_vira_kit_ligado_as_variacoes(): void
    {
        $this->pecas();
        [$plan, $res] = $this->importa(['nome' => 'Conjunto bistrô', 'codigo' => 'CBR', 'variacao_por' => 'Cor', 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'CBR-PT', 'venda' => 678, 'componentes' => [['codigo' => 'MBR-PT', 'quantidade' => 1], ['codigo' => 'BQQ54-PT', 'quantidade' => 2]]],
            ['valor' => 'Branco', 'codigo' => 'CBR-BR', 'venda' => 678, 'componentes' => [['codigo' => 'MBR-BR'], ['codigo' => 'BQQ54-BR', 'quantidade' => 2]]],
        ]]);
        $this->assertCount(2, $res);
        $kit = $this->p('CBR-PT');
        $this->assertSame('kit', $kit->tipo);
        $this->assertEquals(180, (float) $kit->price); // 100 + 2×40
        $this->assertSame(2, (int) $kit->stock_quantity); // banqueta 5/2 = 2, mesa 3
        $this->assertSame(1, (int) $this->p('CBR-BR')->stock_quantity);
        $this->assertSame(2, $kit->componentes()->count());

        // vende uma banqueta preta 54: o kit acompanha
        $this->p('BQQ54-PT')->adjustStock(-2);
        $this->assertSame(1, (int) $kit->fresh()->stock_quantity);
        // vende o kit: baixa as peças
        $kit->fresh()->adjustStock(-1);
        $this->assertSame(2, (int) $this->p('MBR-PT')->stock_quantity);
        $this->assertSame(1, (int) $this->p('BQQ54-PT')->stock_quantity);
        $this->assertSame(0, (int) $kit->fresh()->stock_quantity);
    }

    public function test_kit_com_peca_nao_cadastrada_da_erro(): void
    {
        [$plan] = $this->importa(['nome' => 'Conjunto', 'codigo' => 'CXX', 'variacoes' => [
            ['valor' => 'Preto', 'venda' => 500, 'componentes' => [['codigo' => 'NAO-EXISTE']]],
        ]]);
        $this->assertSame('error', $plan['rows'][0]['status']);
        $this->assertStringContainsString('NAO-EXISTE', $plan['rows'][0]['note']);
    }

    public function test_atualizar_kit_troca_as_pecas(): void
    {
        $this->pecas();
        $f = ['nome' => 'Conjunto bistrô', 'codigo' => 'CBR', 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'CBR-PT', 'venda' => 678, 'componentes' => [['codigo' => 'MBR-PT']]],
        ]];
        $this->importa($f);
        $f['variacoes'][0]['componentes'] = [['codigo' => 'MBR-PT'], ['codigo' => 'BQQ54-PT', 'quantidade' => 2]];
        $this->importa($f, true);
        $kit = $this->p('CBR-PT');
        $this->assertSame(2, $kit->componentes()->count());
        $this->assertSame(2, (int) $kit->stock_quantity);
    }

    public function test_separar_por_tamanho_move_as_variacoes_sem_perder_estoque(): void
    {
        $this->pecas();
        $root = $this->p('BQQ75-PT');
        $this->assertTrue((bool) $root->is_variation_parent);

        $this->importa(['nome' => 'Banqueta 54 cm', 'codigo' => 'BQQ54', 'variacao_por' => 'Cor', 'separar_do_grupo' => true, 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'BQQ54-PT', 'venda' => 179],
            ['valor' => 'Branco', 'codigo' => 'BQQ54-BR', 'venda' => 179],
        ]], true);
        $this->importa(['nome' => 'Banqueta 75 cm', 'codigo' => 'BQQ', 'variacao_por' => 'Cor', 'separar_do_grupo' => true, 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'BQQ75-PT', 'venda' => 199],
            ['valor' => 'Branco', 'codigo' => 'BQQ75-BR', 'venda' => 199],
        ]], true);

        $p54 = $this->p('BQQ54-PT');
        $this->assertTrue((bool) $p54->is_variation_parent);
        $this->assertNull($p54->parent_id);
        $this->assertSame('Banqueta 54 cm', $p54->name);
        $this->assertSame($p54->id, (int) $this->p('BQQ54-BR')->parent_id);
        $this->assertSame('Branco', $this->p('BQQ54-BR')->variation_value);
        $this->assertSame(5, (int) $p54->stock_quantity);

        $p75 = $this->p('BQQ75-PT');
        $this->assertTrue((bool) $p75->is_variation_parent);
        $this->assertSame($p75->id, (int) $this->p('BQQ75-BR')->parent_id);
        $this->assertSame(1, $p75->variants()->count());
        $this->assertSame('Banqueta 75 cm', $p75->name);
    }

    public function test_separar_quando_o_principal_vai_para_a_outra_ficha(): void
    {
        $this->pecas();
        // a 75 cm branca é separada sozinha (sem o principal antigo), depois a 54
        $this->importa(['nome' => 'Banqueta 75 cm', 'codigo' => 'BQQ75', 'variacao_por' => 'Cor', 'separar_do_grupo' => true, 'variacoes' => [
            ['valor' => 'Branco', 'codigo' => 'BQQ75-BR', 'venda' => 199],
        ]], true);
        $this->importa(['nome' => 'Banqueta 54 cm', 'codigo' => 'BQQ54', 'variacao_por' => 'Cor', 'separar_do_grupo' => true, 'variacoes' => [
            ['valor' => 'Preto', 'codigo' => 'BQQ54-PT', 'venda' => 179],
            ['valor' => 'Branco', 'codigo' => 'BQQ54-BR', 'venda' => 179],
        ]], true);
        $br75 = $this->p('BQQ75-BR');
        $this->assertNull($br75->parent_id);
        $this->assertTrue((bool) $br75->is_variation_parent);
        $p54 = $this->p('BQQ54-PT');
        $this->assertSame($p54->id, (int) $this->p('BQQ54-BR')->parent_id);
        // o principal antigo ficou sozinho
        $this->assertSame(0, $this->p('BQQ75-PT')->variants()->count());
    }

    public function test_atualizar_so_o_que_mudou_e_retirar_variacao(): void
    {
        $this->pecas();
        $antes = $this->p('BQQ54-BR')->image;
        // robô manda só a variação que mudou e a que saiu
        [$plan] = $this->importa(['nome' => 'Banqueta', 'codigo' => 'BQQ', 'variacao_por' => 'Tamanho e cor', 'remover_codigos' => ['BQQ75-BR'], 'variacoes' => [
            ['valor' => '54 cm · Preto', 'codigo' => 'BQQ54-PT', 'venda' => 189],
        ]], true);
        $this->assertEquals(189, (float) $this->p('BQQ54-PT')->price_sale);
        $this->assertSame('inativo', $this->p('BQQ75-BR')->status);
        $this->assertSame('ativo', $this->p('BQQ54-BR')->status);
        $this->assertSame($antes, $this->p('BQQ54-BR')->image);
        $this->assertSame(5, (int) $this->p('BQQ54-PT')->stock_quantity);
        // voltou para a ficha: ativa de novo
        $this->importa(['nome' => 'Banqueta', 'codigo' => 'BQQ', 'variacao_por' => 'Tamanho e cor', 'variacoes' => [
            ['valor' => '75 cm · Branco', 'codigo' => 'BQQ75-BR', 'venda' => 199],
        ]], true);
        $this->assertSame('ativo', $this->p('BQQ75-BR')->status);
    }
}
