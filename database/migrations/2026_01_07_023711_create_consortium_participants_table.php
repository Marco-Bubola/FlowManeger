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
        Schema::create('consortium_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consortium_id')->constrained('consortiums')->onDelete('cascade');
            $table->unsignedBigInteger('client_id');
            // TODO: Fix foreign key constraint incompatibility with clients table
            // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->integer('participation_number');
            $table->date('entry_date');
            $table->enum('status', ['active', 'contemplated', 'quit', 'defaulter'])->default('active');
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->boolean('is_contemplated')->default(false);
            $table->date('contemplation_date')->nullable();
            $table->enum('contemplation_type', ['draw', 'bid'])->nullable();
            $table->unique(['consortium_id', 'participation_number'], 'consortium_part_unique');
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
        Schema::dropIfExists('consortium_participants');
    }
};
