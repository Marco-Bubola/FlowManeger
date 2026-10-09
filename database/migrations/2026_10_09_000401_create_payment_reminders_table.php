<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cobranças: registro de cada lembrete de pagamento enviado ao cliente
 * (parcela de venda, saldo de venda ou parcela de consórcio).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_reminders')) {
            return;
        }

        Schema::create('payment_reminders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('remindable_type');
            $t->unsignedBigInteger('remindable_id');
            $t->unsignedBigInteger('client_id')->nullable()->index();
            $t->string('channel', 20)->default('whatsapp');
            $t->string('template', 20)->nullable(); // due | overdue
            $t->timestamp('sent_at');
            $t->timestamps();

            $t->index(['remindable_type', 'remindable_id']);
            $t->index(['user_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
