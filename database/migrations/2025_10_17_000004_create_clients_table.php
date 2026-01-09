<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')) {
            return;
        }

        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email')->nullable();
            $table->string('phone', 16)->nullable();
            $table->text('address')->nullable();
            $table->string('caminho_foto')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id', 'idx_clients_user_id');
            $table->index('name', 'idx_clients_name');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
