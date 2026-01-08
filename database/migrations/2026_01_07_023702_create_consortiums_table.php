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
        Schema::create('consortiums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_value', 10, 2);
            $table->integer('duration_months');
            $table->decimal('total_value', 10, 2);
            $table->integer('max_participants')->default(100);
            $table->date('start_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('draw_frequency', ['monthly', 'bimonthly', 'weekly'])->default('monthly');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consortiums');
    }
};
