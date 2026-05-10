<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_uploads_history')) {
            return;
        }

        Schema::create('invoice_uploads_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('bank_id');
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->string('file_type', 100);
            $table->integer('total_transactions')->default(0);
            $table->integer('transactions_created')->default(0);
            $table->integer('transactions_updated')->default(0);
            $table->integer('transactions_skipped')->default(0);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            $table->json('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_uploads_history')) {
            return;
        }

        Schema::dropIfExists('invoice_uploads_history');
    }
};
