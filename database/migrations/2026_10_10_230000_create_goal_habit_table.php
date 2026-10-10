<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ligação entre metas do tipo "hábito" e os hábitos diários (usada ao marcar um hábito).
    public function up(): void
    {
        if (Schema::hasTable('goal_habit')) {
            return;
        }

        Schema::create('goal_habit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goal_id');
            $table->unsignedBigInteger('daily_habit_id');
            $table->decimal('peso', 8, 2)->default(5);
            $table->timestamps();

            $table->unique(['goal_id', 'daily_habit_id']);
            $table->index('daily_habit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_habit');
    }
};
