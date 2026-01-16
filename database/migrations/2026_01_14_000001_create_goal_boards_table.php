<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('tipo', ['pessoal', 'financeiro', 'profissional', 'saude', 'estudos', 'outro'])->default('pessoal');
            $table->string('background_color')->default('#0079BF');
            $table->string('background_image')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_boards');
    }
};
