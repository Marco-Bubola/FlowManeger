<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orçamentos de produto sob medida e de reforma (app + portal) e a tabela de
 * materiais e horas usada para montar o custo (a mesma do robô Móveis Stylo).
 * Não confundir com a tabela `orcamentos`, que é o orçamento mensal das finanças.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quote_materials')) {
            Schema::create('quote_materials', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('category', 80);
                $t->string('name', 160);
                $t->string('unit', 20)->default('un');
                $t->decimal('price', 10, 2)->default(0);
                $t->string('note', 200)->nullable();
                $t->boolean('estimated')->default(true); // preço que veio pronto, ainda não conferido
                $t->unsignedInteger('position')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedInteger('number');                 // nº do orçamento da loja (1, 2, 3...)
                $t->string('token', 40)->unique();             // link que o cliente abre sem login
                $t->unsignedBigInteger('client_id')->nullable()->index();
                $t->string('contact_name', 120)->nullable();   // quem pediu e ainda não é cliente
                $t->string('contact_phone', 30)->nullable();
                $t->string('kind', 12)->default('produto');    // produto | reforma
                $t->string('status', 12)->default('rascunho'); // pedido | rascunho | enviado | aprovado | recusado | vencido | convertido
                $t->string('source', 12)->default('app');      // app | portal | portal_cor
                $t->string('title', 160)->nullable();
                $t->text('request_text')->nullable();          // o que o cliente escreveu ao pedir
                $t->json('request_photos')->nullable();        // fotos que o cliente mandou
                $t->json('request_data')->nullable();          // cores escolhidas, medidas, produto de origem
                $t->unsignedBigInteger('product_id')->nullable(); // produto de origem (pedido de outra cor)
                $t->json('items')->nullable();                 // itens com ficha de custo (custo NUNCA vai ao cliente)
                $t->json('services')->nullable();              // reforma: o que fazer
                $t->string('pickup', 12)->nullable();          // reforma: busco | cliente_leva
                $t->json('after_photos')->nullable();          // reforma: fotos de depois
                $t->decimal('discount', 10, 2)->default(0);
                $t->decimal('delivery_fee', 10, 2)->default(0);
                $t->decimal('total', 10, 2)->default(0);       // o que o cliente paga
                $t->decimal('cost_total', 10, 2)->default(0);  // só para a loja
                $t->unsignedSmallInteger('production_days')->nullable();
                $t->date('valid_until')->nullable();
                $t->string('payment_terms', 255)->nullable();
                $t->text('client_notes')->nullable();          // aparece para o cliente
                $t->text('admin_notes')->nullable();           // só a loja vê
                $t->text('change_request')->nullable();        // "pedir mudança" do cliente
                $t->json('events')->nullable();                // linha do tempo
                $t->json('reminders')->nullable();             // automações já feitas
                $t->timestamp('sent_at')->nullable();
                $t->timestamp('viewed_at')->nullable();
                $t->timestamp('approved_at')->nullable();
                $t->timestamp('rejected_at')->nullable();
                $t->timestamp('edited_after_sent_at')->nullable();
                $t->unsignedBigInteger('sale_id')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->unique(['user_id', 'number']);
                $t->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('quote_materials');
    }
};
