<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_habits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name'); // Nome do hábito
            $table->text('description')->nullable();
            $table->string('icon')->default('bi-check-circle'); // Ícone Bootstrap
            $table->string('color')->default('#3B82F6'); // Cor do card
            $table->integer('goal_frequency')->default(1); // Quantas vezes por dia
            $table->time('reminder_time')->nullable(); // Horário de lembrete
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Ordem de exibição
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_habits');
    }
};
