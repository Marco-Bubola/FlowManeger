<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_habit_streaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habit_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('current_streak')->default(0); // Sequência atual
            $table->integer('longest_streak')->default(0); // Maior sequência
            $table->date('last_completion_date')->nullable(); // Última conclusão
            $table->integer('total_completions')->default(0); // Total de conclusões
            $table->timestamps();

            $table->foreign('habit_id')->references('id')->on('daily_habits')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['habit_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_habit_streaks');
    }
};
