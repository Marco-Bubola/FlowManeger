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
        Schema::create('invoice_uploads_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('bank_id'); // Signed integer para corresponder a banks.id_bank
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->string('file_type', 10); // pdf, csv
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

            // Índices
            $table->index('user_id');
            $table->index('bank_id');
            $table->index('status');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('bank_id')->references('id_bank')->on('banks')->onDelete('cascade');
            // Comentado temporariamente - tabela banks precisa ser criada primeiro
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_uploads_history');
    }
};
