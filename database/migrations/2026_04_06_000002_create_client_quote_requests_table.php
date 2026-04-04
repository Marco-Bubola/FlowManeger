<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // owner
            $table->enum('status', ['pending', 'reviewing', 'quoted', 'approved', 'rejected'])->default('pending');
            $table->json('items')->nullable(); // [{product_id, name, quantity, notes}]
            $table->json('extra_items')->nullable(); // [{description, quantity}]
            $table->text('client_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_quote_requests');
    }
};
