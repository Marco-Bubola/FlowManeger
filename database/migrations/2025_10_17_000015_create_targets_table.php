<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->default('Default Title');
            $table->date('target_date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->boolean('is_completed')->default(false);
            $table->unsignedBigInteger('user_id');

            $table->index('user_id', 'idx_targets_user_id');
            $table->index('target_date', 'idx_targets_target_date');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
