<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_habit_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habit_id');
            $table->unsignedBigInteger('user_id');
            $table->date('completion_date'); // Data da conclusão
            $table->integer('times_completed')->default(1); // Quantas vezes completou no dia
            $table->text('notes')->nullable(); // Notas do usuário
            $table->timestamps();

            $table->foreign('habit_id')->references('id')->on('daily_habits')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Um hábito só pode ser completado uma vez por dia
            $table->unique(['habit_id', 'completion_date']);
            $table->index(['user_id', 'completion_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_habit_completions');
    }
};
