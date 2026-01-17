<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goal_habit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('daily_habit_id')->constrained('daily_habits')->onDelete('cascade');
            $table->decimal('peso', 5, 2)->default(1.00)->comment('Peso do hábito no cálculo de progresso da meta');
            $table->timestamps();

            $table->unique(['goal_id', 'daily_habit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_habit');
    }
};
