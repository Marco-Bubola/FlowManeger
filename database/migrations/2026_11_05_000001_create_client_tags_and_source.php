<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM, etapa 2: etiquetas do cliente (VIP, Atacado, Revendedora...) e a origem
 * (como conheceu a loja). O status Novo/Ativo/Sumido/Inativo é calculado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_tags')) {
            Schema::create('client_tags', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->string('name', 40);
                $t->string('color', 16)->default('indigo');
                $t->timestamps();
                $t->index(['user_id', 'name']);
            });
        }

        if (! Schema::hasTable('client_tag')) {
            Schema::create('client_tag', function (Blueprint $t) {
                $t->unsignedBigInteger('client_id');
                $t->unsignedBigInteger('client_tag_id');
                $t->primary(['client_id', 'client_tag_id']);
                $t->index('client_tag_id');
            });
        }

        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'source')) {
            Schema::table('clients', function (Blueprint $t) {
                $t->string('source', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tag');
        Schema::dropIfExists('client_tags');
        if (Schema::hasColumn('clients', 'source')) {
            Schema::table('clients', fn (Blueprint $t) => $t->dropColumn('source'));
        }
    }
};
