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
        Schema::create('consortium_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consortium_id')->constrained('consortiums')->onDelete('cascade');
            $table->date('draw_date');
            $table->integer('draw_number');
            $table->foreignId('winner_participant_id')->nullable()->constrained('consortium_participants')->onDelete('set null');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consortium_draws');
    }
};
