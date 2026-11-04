<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM, etapa 1: anotações de contato do cliente (ligação, WhatsApp, visita...)
 * e tarefas/follow-ups com data, ligadas ou não a um cliente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_notes')) {
            Schema::create('client_notes', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('client_id');
                $t->string('kind', 16)->default('nota'); // nota | ligacao | whatsapp | visita | instagram | email
                $t->text('body');
                $t->dateTime('happened_at');
                $t->timestamps();
                $t->index(['user_id', 'client_id', 'happened_at']);
            });
        }

        if (! Schema::hasTable('client_tasks')) {
            Schema::create('client_tasks', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('client_id')->nullable();
                $t->string('title', 160);
                $t->text('notes')->nullable();
                $t->date('due_date');
                $t->time('due_time')->nullable();
                $t->dateTime('done_at')->nullable();
                $t->dateTime('notified_at')->nullable();
                $t->timestamps();
                $t->index(['user_id', 'done_at', 'due_date']);
                $t->index(['client_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tasks');
        Schema::dropIfExists('client_notes');
    }
};
