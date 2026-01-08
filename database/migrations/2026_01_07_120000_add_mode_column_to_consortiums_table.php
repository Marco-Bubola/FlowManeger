<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('consortiums')) {
            return;
        }

        if (Schema::hasColumn('consortiums', 'mode')) {
            return;
        }

        Schema::table('consortiums', function (Blueprint $table) {
            $table->enum('mode', ['draw', 'payoff'])->default('draw')->after('draw_frequency');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('consortiums')) {
            return;
        }

        if (!Schema::hasColumn('consortiums', 'mode')) {
            return;
        }

        Schema::table('consortiums', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
