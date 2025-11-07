<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->increments('id_invoice');
            $table->unsignedInteger('id_bank');
            $table->string('description');
            $table->string('installments');
            $table->decimal('value', 10, 2);
            $table->date('invoice_date');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unsignedInteger('client_id')->nullable();
            $table->boolean('dividida')->default(false);

            $table->index('id_bank');
            $table->index('category_id');
            $table->index('user_id', 'idx_invoice_user_id');
            $table->index('value', 'idx_invoice_value');
            $table->index('client_id', 'fk_invoice_clients');

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_bank')->references('id_bank')->on('banks');
            $table->foreign('category_id')->references('id_category')->on('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
