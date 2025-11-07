<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('value', 10, 2);
            $table->string('description')->nullable();
            $table->date('date');
            $table->boolean('is_pending')->default(false);
            $table->string('attachment')->nullable();
            $table->dateTime('inc_datetime')->nullable();
            $table->dateTime('edit_datetime')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('type_id');
            $table->string('note')->nullable();
            $table->integer('segment_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unsignedInteger('id_bank')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->unsignedInteger('cofrinho_id')->nullable();

            $table->index('category_id');
            $table->index('type_id');
            $table->index('segment_id');
            $table->index('date');
            $table->index('user_id', 'idx_cashbook_user_id');
            $table->index('id_bank', 'cashbook_fk_id_bank');
            $table->index('client_id', 'fk_cashbook_clients');
            $table->index('cofrinho_id', 'cashbook_cofrinho_id_foreign');

            $table->foreign('cofrinho_id')->references('id')->on('cofrinhos')->onDelete('set null');
            $table->foreign('id_bank')->references('id_bank')->on('banks')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id_category')->on('category');
            $table->foreign('type_id')->references('id_type')->on('type');
            $table->foreign('segment_id')->references('id')->on('segment');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook');
    }
};
