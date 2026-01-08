<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar ENUM para incluir 'payoff'
        DB::statement("ALTER TABLE consortium_contemplations MODIFY COLUMN contemplation_type ENUM('draw', 'bid', 'payoff') NOT NULL DEFAULT 'draw'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para ENUM original
        DB::statement("ALTER TABLE consortium_contemplations MODIFY COLUMN contemplation_type ENUM('draw', 'bid') NOT NULL DEFAULT 'draw'");
    }
};
