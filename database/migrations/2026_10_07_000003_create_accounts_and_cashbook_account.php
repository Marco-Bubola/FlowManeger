<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 100);
                // corrente | poupanca | carteira | investimento
                $table->string('type', 20)->default('corrente');
                $table->decimal('initial_balance', 12, 2)->default(0);
                $table->string('color', 20)->nullable();
                $table->boolean('archived')->default(false);
                $table->timestamps();
            });
        }

        // cashbook veio do dump original (sem migration de criação)
        if (Schema::hasTable('cashbook') && ! Schema::hasColumn('cashbook', 'account_id')) {
            Schema::table('cashbook', function (Blueprint $table) {
                $table->unsignedBigInteger('account_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cashbook') && Schema::hasColumn('cashbook', 'account_id')) {
            Schema::table('cashbook', function (Blueprint $table) {
                $table->dropIndex(['account_id']);
                $table->dropColumn('account_id');
            });
        }

        Schema::dropIfExists('accounts');
    }
};
