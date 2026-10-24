<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vitrine do catálogo (portal): banners (topo, laterais e faixas), coleções com
 * banner e produtos escolhidos, e as opções da loja (destaques e aviso no topo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('portal_banners')) {
            Schema::create('portal_banners', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->index();
                $t->string('placement', 10)->default('hero'); // hero | side | strip
                $t->string('image')->nullable();         // storage/app/public/portal-banners
                $t->string('image_mobile')->nullable();  // versão para celular (opcional)
                $t->string('color', 20)->default('blue'); // fundo quando não há imagem
                $t->string('title', 120)->nullable();
                $t->string('subtitle', 200)->nullable();
                $t->string('button_text', 40)->nullable();
                $t->string('link_type', 12)->default('none'); // none | product | category | collection | offers | url
                $t->string('link_value')->nullable();
                $t->unsignedInteger('sort')->default(0);
                $t->boolean('active')->default(true);
                $t->date('starts_at')->nullable();
                $t->date('ends_at')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('portal_collections')) {
            Schema::create('portal_collections', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->index();
                $t->string('name', 80);
                $t->string('slug', 90);
                $t->string('description', 200)->nullable();
                $t->string('image')->nullable();
                $t->string('color', 20)->default('violet');
                $t->unsignedInteger('sort')->default(0);
                $t->boolean('active')->default(true);
                $t->boolean('show_on_home')->default(true);
                $t->timestamps();
                $t->unique(['user_id', 'slug']);
            });
        }

        if (!Schema::hasTable('portal_collection_product')) {
            Schema::create('portal_collection_product', function (Blueprint $t) {
                $t->id();
                $t->foreignId('portal_collection_id')->index();
                $t->unsignedBigInteger('product_id'); // produto principal da família
                $t->unsignedInteger('sort')->default(0);
                $t->unique(['portal_collection_id', 'product_id']);
            });
        }

        if (!Schema::hasTable('portal_settings')) {
            Schema::create('portal_settings', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->unique();
                $t->string('featured_title', 60)->nullable();
                $t->json('featured_ids')->nullable();
                $t->string('notice_text', 160)->nullable(); // aviso fino no topo do catálogo
                $t->string('notice_link')->nullable();
                $t->boolean('notice_active')->default(false);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_settings');
        Schema::dropIfExists('portal_collection_product');
        Schema::dropIfExists('portal_collections');
        Schema::dropIfExists('portal_banners');
    }
};
