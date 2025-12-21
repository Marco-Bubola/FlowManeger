<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a tabela já existe antes de criar
        if (!Schema::hasTable('type')) {
            Schema::create('type', function (Blueprint $table) {
                $table->increments('id_type');
                $table->string('desc_type', 45);
                $table->string('hexcolor_type', 45)->nullable();
                $table->string('icon_type', 45)->nullable();
                $table->index('desc_type', 'idx_type_desc_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('type');
    }
};
