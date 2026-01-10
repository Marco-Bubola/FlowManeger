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
        Schema::create('consortium_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consortium_id')->constrained('consortiums')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Dono do consórcio
            $table->foreignId('related_participant_id')->nullable()->constrained('consortium_participants')->onDelete('cascade');
            
            // Tipos: draw_available, redemption_pending
            $table->enum('type', ['draw_available', 'redemption_pending']);
            
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Dados extras (ID do sorteio, contemplação, etc)
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('action_url')->nullable(); // URL para ação rápida
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para performance
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['consortium_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consortium_notifications');
    }
};
