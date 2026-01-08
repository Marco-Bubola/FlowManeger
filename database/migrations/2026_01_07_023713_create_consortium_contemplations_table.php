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
        Schema::create('consortium_contemplations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consortium_participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('draw_id')->nullable()->constrained('consortium_draws')->onDelete('set null');
            $table->enum('contemplation_type', ['draw', 'bid'])->default('draw');
            $table->dateTime('contemplation_date');
            $table->enum('redemption_type', ['cash', 'products', 'pending'])->default('pending');
            $table->decimal('redemption_value', 10, 2)->nullable();
            $table->date('redemption_date')->nullable();
            $table->json('products')->nullable();
            $table->enum('status', ['pending', 'redeemed', 'cancelled'])->default('pending');
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
        Schema::dropIfExists('consortium_contemplations');
    }
};
