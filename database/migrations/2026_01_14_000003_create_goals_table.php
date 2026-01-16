<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('goal_lists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('periodo', ['diario', 'semanal', 'mensal', 'trimestral', 'semestral', 'anual', 'custom'])->default('custom');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->date('data_inicio')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->decimal('progresso', 5, 2)->default(0);
            $table->decimal('valor_meta', 10, 2)->nullable();
            $table->decimal('valor_atual', 10, 2)->default(0);
            $table->integer('cofrinho_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->json('labels')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys separadas
            $table->foreign('cofrinho_id')->references('id')->on('cofrinhos')->onDelete('set null');
            $table->foreign('category_id')->references('id_category')->on('category')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
