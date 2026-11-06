<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_deals')) {
            return;
        }
        Schema::create('crm_deals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('client_id')->nullable()->index();
            // Lead: quem ainda não é cliente fica só com nome + telefone.
            $t->string('name', 120)->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('source', 30)->nullable();
            $t->string('title', 160);
            $t->decimal('value', 12, 2)->nullable();
            $t->string('stage', 20)->default('contato');
            $t->string('lost_reason', 30)->nullable();
            $t->string('lost_note', 255)->nullable();
            $t->date('expected_date')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('sale_id')->nullable();
            $t->integer('position')->default(0);
            $t->timestamp('stage_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
