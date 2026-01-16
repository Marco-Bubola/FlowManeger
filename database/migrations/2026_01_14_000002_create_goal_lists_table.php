<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('goal_boards')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#344563');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_lists');
    }
};
