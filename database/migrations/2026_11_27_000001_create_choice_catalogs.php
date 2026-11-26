<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de escolhas (estofados, cores de ferragem...): listas da loja com
 * tecido, cor, amostra e valor a mais. O produto liga as listas que usa e o
 * portal mostra uma lista suspensa para cada uma. O robô Móveis Stylo usa o
 * mesmo catálogo. As escolhas são sob encomenda (sem estoque por combinação).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('choice_catalogs')) {
            Schema::create('choice_catalogs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('name', 80);                    // Estofados, Cores de ferragem
                $t->string('kind', 12)->default('outro');  // estofado | ferragem | outro
                $t->unsignedInteger('position')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('choice_items')) {
            Schema::create('choice_items', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedBigInteger('choice_catalog_id')->index();
                $t->string('material', 80)->nullable();    // tecido: Corino, Corvin, Suede
                $t->string('name', 80);                    // cor: Preto, Caramelo
                $t->string('code', 30)->nullable();        // código do fornecedor (ex.: 2792)
                $t->string('hex', 7)->nullable();          // amostra da cor
                $t->string('image', 255)->nullable();      // foto do tecido
                $t->decimal('extra_price', 10, 2)->default(0); // valor a mais no produto
                $t->boolean('active')->default(true);      // em falta = some do portal
                $t->unsignedInteger('position')->default(0);
                $t->timestamps();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'choice_lists')) {
            Schema::table('products', function (Blueprint $t) {
                $t->json('choice_lists')->nullable(); // [{c: catálogo, l: nome da lista, i: itens ou null = todos}]
            });
        }

        // O que o cliente escolheu ("Estofado: Corino · Preto | Cor da ferragem: Preto") vai junto na venda.
        if (Schema::hasTable('sale_items') && ! Schema::hasColumn('sale_items', 'choices')) {
            Schema::table('sale_items', function (Blueprint $t) {
                $t->string('choices', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_items', 'choices')) {
            Schema::table('sale_items', fn (Blueprint $t) => $t->dropColumn('choices'));
        }
        if (Schema::hasColumn('products', 'choice_lists')) {
            Schema::table('products', fn (Blueprint $t) => $t->dropColumn('choice_lists'));
        }
        Schema::dropIfExists('choice_items');
        Schema::dropIfExists('choice_catalogs');
    }
};
