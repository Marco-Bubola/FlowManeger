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
        // Esta migration garante que o campo status possa aceitar o valor 'pago'
        // Não precisa fazer nada se a migration posterior já alterou o campo para VARCHAR
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nada a fazer
    }
};
