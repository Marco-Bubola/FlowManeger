<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Área de Promoções.
 *
 * - promotions: uma promoção por produto, com preço "de" e "por", datas,
 *   mensagem de WhatsApp própria e histórico (encerradas ficam guardadas).
 * - promotion_sends: registro de cada envio por WhatsApp (para quem e quando).
 * - promotion_settings: limite de lucro mínimo, desconto mínimo para sugerir,
 *   modelo e rodapé da mensagem, por usuário.
 * - sale_items.original_price / promotion_id: item vendido em promoção.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedInteger('product_id')->index();
                $t->decimal('original_price', 10, 2);
                $t->decimal('promo_price', 10, 2);
                $t->dateTime('starts_at')->nullable();
                $t->dateTime('ends_at')->nullable();
                // ativa | agendada | encerrada
                $t->string('status', 20)->default('ativa')->index();
                // manual | vencida | sem_estoque | substituida
                $t->string('ended_reason', 20)->nullable();
                $t->dateTime('ended_at')->nullable();
                $t->text('message')->nullable();
                $t->string('source', 20)->default('manual'); // manual | upload
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('promotion_sends')) {
            Schema::create('promotion_sends', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedBigInteger('promotion_id')->nullable()->index();
                $t->unsignedInteger('client_id')->nullable()->index();
                // produto | ofertas (várias promoções numa mensagem)
                $t->string('kind', 20)->default('produto');
                // whatsapp | compartilhar | copiar
                $t->string('channel', 20)->default('whatsapp');
                $t->json('promotion_ids')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('promotion_settings')) {
            Schema::create('promotion_settings', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->unique();
                $t->decimal('min_margin_percent', 5, 2)->default(5);
                $t->decimal('suggest_min_discount', 5, 2)->default(10);
                $t->unsignedSmallInteger('default_days')->nullable()->default(7);
                $t->text('message_template')->nullable();
                $t->text('footer')->nullable();
                $t->boolean('footer_catalog_link')->default(true);
                $t->timestamps();
            });
        }

        Schema::table('sale_items', function (Blueprint $t) {
            if (!Schema::hasColumn('sale_items', 'original_price')) {
                $t->decimal('original_price', 10, 2)->nullable()->after('price_sale');
            }
            if (!Schema::hasColumn('sale_items', 'promotion_id')) {
                $t->unsignedBigInteger('promotion_id')->nullable()->after('original_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $t) {
            foreach (['promotion_id', 'original_price'] as $col) {
                if (Schema::hasColumn('sale_items', $col)) {
                    $t->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('promotion_settings');
        Schema::dropIfExists('promotion_sends');
        Schema::dropIfExists('promotions');
    }
};
