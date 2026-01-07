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
        // 1. Corrigir tipo de client_id para corresponder ao tipo de clients.id (int - signed)
        Schema::table('consortium_participants', function (Blueprint $table) {
            // Alterar tipo de bigint unsigned para int (signed, para corresponder a clients.id)
            DB::statement('ALTER TABLE consortium_participants MODIFY client_id INT NOT NULL');

            // Agora podemos adicionar a foreign key
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        // 2. Corrigir draw_date para datetime (atualmente é date)
        Schema::table('consortium_draws', function (Blueprint $table) {
            $table->dateTime('draw_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consortium_participants', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('consortium_draws', function (Blueprint $table) {
            $table->date('draw_date')->change();
        });
    }
};
