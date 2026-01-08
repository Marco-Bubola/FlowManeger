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
        Schema::create('consortium_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consortium_participant_id')->constrained()->onDelete('cascade');
            $table->integer('reference_month');
            $table->integer('reference_year');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'late', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
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
        Schema::dropIfExists('consortium_payments');
    }
};
