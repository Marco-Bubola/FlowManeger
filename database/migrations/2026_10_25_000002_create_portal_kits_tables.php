<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Monte seu kit: tipos de kit (cesta, caixa, banheira…), enfeites com preço e o kit montado no pedido. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portal_kit_types')) {
            Schema::create('portal_kit_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 80);
                $table->string('style', 20)->default('cesta');
                $table->string('description', 400)->nullable();
                $table->string('image')->nullable();
                $table->decimal('base_price', 10, 2)->default(0);
                $table->unsignedSmallInteger('min_items')->default(1);
                $table->unsignedSmallInteger('max_items')->default(6);
                $table->json('category_ids')->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('portal_kit_extras')) {
            Schema::create('portal_kit_extras', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 80);
                $table->string('group', 20)->default('outros');
                $table->string('description', 200)->nullable();
                $table->string('image')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedSmallInteger('max_qty')->default(5);
                $table->boolean('active')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }
        if (Schema::hasTable('client_quote_requests') && ! Schema::hasColumn('client_quote_requests', 'kit')) {
            Schema::table('client_quote_requests', function (Blueprint $table) {
                $table->json('kit')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_kit_types');
        Schema::dropIfExists('portal_kit_extras');
        if (Schema::hasColumn('client_quote_requests', 'kit')) {
            Schema::table('client_quote_requests', fn (Blueprint $table) => $table->dropColumn('kit'));
        }
    }
};
