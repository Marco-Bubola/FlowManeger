<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carrinho abandonado (portal_carts), relatório do site (portal_page_views),
 * acompanhamento da entrega nos pedidos do portal e o aceite da política de
 * privacidade no cadastro do cliente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portal_carts')) {
            Schema::create('portal_carts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('client_id');
                $t->json('items')->nullable();
                $t->decimal('total', 12, 2)->default(0);
                $t->string('token', 40)->unique();
                $t->timestamp('last_activity_at')->nullable();
                $t->timestamp('reminded_at')->nullable();
                $t->string('reminder_via', 12)->nullable(); // whatsapp | manual | aviso
                $t->timestamp('recovered_at')->nullable();
                $t->unsignedBigInteger('quote_id')->nullable();
                $t->timestamp('dismissed_at')->nullable();
                $t->timestamps();
                $t->unique(['user_id', 'client_id']);
                $t->index(['user_id', 'last_activity_at']);
            });
        }

        if (! Schema::hasTable('portal_page_views')) {
            Schema::create('portal_page_views', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->date('day');
                $t->string('kind', 12); // inicio | catalogo | produto | kit | carrinho
                $t->unsignedBigInteger('product_id')->nullable();
                $t->char('visitor', 16);
                $t->unsignedBigInteger('client_id')->nullable();
                $t->string('source', 12)->nullable(); // whatsapp | instagram | facebook | google | outro | direto
                $t->timestamp('created_at')->nullable();
                $t->index(['user_id', 'day']);
                $t->index(['user_id', 'product_id']);
            });
        }

        if (Schema::hasTable('client_quote_requests')) {
            Schema::table('client_quote_requests', function (Blueprint $t) {
                if (! Schema::hasColumn('client_quote_requests', 'delivery_stage')) {
                    $t->string('delivery_stage', 20)->nullable();
                    $t->json('delivery_events')->nullable();
                    $t->string('tracking_code', 60)->nullable();
                    $t->string('tracking_carrier', 30)->nullable();
                    $t->date('delivery_eta')->nullable();
                }
            });
        }

        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'privacy_accepted_at')) {
            Schema::table('clients', fn (Blueprint $t) => $t->timestamp('privacy_accepted_at')->nullable());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_carts');
        Schema::dropIfExists('portal_page_views');
        if (Schema::hasColumn('client_quote_requests', 'delivery_stage')) {
            Schema::table('client_quote_requests', fn (Blueprint $t) => $t->dropColumn(['delivery_stage', 'delivery_events', 'tracking_code', 'tracking_carrier', 'delivery_eta']));
        }
        if (Schema::hasColumn('clients', 'privacy_accepted_at')) {
            Schema::table('clients', fn (Blueprint $t) => $t->dropColumn('privacy_accepted_at'));
        }
    }
};
