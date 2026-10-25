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

class ImportFichaUpdateTest extends TestCase
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
        $this->user = User::forceCreate(['name' => 'Marco', 'email' => 'm@m.test', 'password' => 'x']);
        $this->actingAs($this->user);
    }

    private function ficha(array $over = []): array
    {
        return array_replace([
            'nome' => 'Mesa lateral de ferro',
            'codigo' => 'MLF',
            'categoria' => 'Mesas',
            'descricao' => 'Mesa de ferro e madeira.',
            'material' => 'Ferro e madeira',
            'variacao_por' => 'Tamanho',
            'sob_encomenda' => true,
            'prazo_dias' => 15,
            'variacoes' => [
                ['valor' => '40 cm', 'custo' => 80, 'venda' => 199, 'estoque' => 3, 'embalagem' => ['peso_kg' => 4, 'comprimento_cm' => 45, 'largura_cm' => 45, 'altura_cm' => 10], 'fotos' => ['a.jpg']],
                ['valor' => '50 cm', 'custo' => 95, 'venda' => 239, 'estoque' => 2, 'fotos' => ['b.jpg']],
            ],
        ], $over);
    }

    private function importa(array $f, array $photos, bool $update): array
    {
        $svc = app(ProductFichaImporter::class);
        $plan = $svc->plan($this->user->id, $f, $photos, $update);
        $files = collect($photos)->mapWithKeys(fn ($n) => [$n => UploadedFile::fake()->image($n)])->all();

        return [$plan, $svc->import($this->user->id, $plan, $files, app(VariationService::class))];
    }

    public function test_sem_marcar_atualizar_pula_os_cadastrados(): void
    {
        $this->importa($this->ficha(), ['a.jpg', 'b.jpg'], false);
        [$plan, $res] = $this->importa($this->ficha(), [], false);

        $this->assertSame(['skip', 'skip'], array_column($plan['rows'], 'status'));
        $this->assertSame([], $res);
        $this->assertTrue(collect($plan['warnings'])->contains(fn ($w) => str_contains($w, 'Atualizar os que já estão cadastrados')));
    }

    public function test_atualiza_precos_textos_e_fotos_sem_mexer_no_estoque(): void
    {
        $this->importa($this->ficha(), ['a.jpg', 'b.jpg'], false);
        $parent = Product::where('product_code', 'MLF-40-CM')->firstOrFail();
        $child = Product::where('product_code', 'MLF-50-CM')->firstOrFail();
        $this->assertTrue((bool) $parent->is_variation_parent);
        $this->assertSame($parent->id, (int) $child->parent_id);
        $oldImage = $child->image;

        $f = $this->ficha(['descricao' => 'Mesa nova, com pé reforçado.', 'prazo_dias' => 20]);
        $f['variacoes'][0]['venda'] = 219;
        $f['variacoes'][0]['estoque'] = 99;
        $f['variacoes'][1]['venda'] = 259;
        $f['variacoes'][1]['fotos'] = ['c.jpg', 'd.jpg'];
        [$plan, $res] = $this->importa($f, ['c.jpg', 'd.jpg'], true);

        $this->assertSame(['update', 'update'], array_column($plan['rows'], 'status'));
        $this->assertSame(['atualizado', 'atualizado'], array_column($res, 'action'));

        $parent->refresh();
        $child->refresh();
        $this->assertEquals(219, $parent->price_sale);
        $this->assertEquals(259, $child->price_sale);
        $this->assertSame(3, (int) $parent->stock_quantity);
        $this->assertSame(2, (int) $child->stock_quantity);
        $this->assertSame('Mesa lateral de ferro', $parent->name);
        $this->assertStringContainsString('pé reforçado', $parent->description);
        $this->assertSame(20, (int) $child->production_days);
        $this->assertNotSame($oldImage, $child->image);
        $this->assertSame(1, ProductImage::where('product_id', $child->id)->count());
        $this->assertSame(2, Product::count());
    }

    public function test_variacao_nova_na_ficha_entra_no_mesmo_grupo(): void
    {
        $this->importa($this->ficha(), ['a.jpg', 'b.jpg'], false);
        $f = $this->ficha();
        $f['variacoes'][] = ['valor' => '60 cm', 'custo' => 110, 'venda' => 279, 'estoque' => 1];
        [$plan, $res] = $this->importa($f, [], true);

        $this->assertSame(['update', 'update', 'ok'], array_column($plan['rows'], 'status'));
        $this->assertSame('variação', $res[2]['action']);
        $parent = Product::where('product_code', 'MLF-40-CM')->firstOrFail();
        $new = Product::where('product_code', 'MLF-60-CM')->firstOrFail();
        $this->assertSame($parent->id, (int) $new->parent_id);
        $this->assertSame(1, (int) $new->stock_quantity);
        // Sem fotos enviadas, as fotos dos cadastrados ficam como estão
        $this->assertNotSame('product-placeholder.png', Product::where('product_code', 'MLF-50-CM')->value('image'));
    }
}
