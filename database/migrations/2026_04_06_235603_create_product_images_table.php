<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A tabela já existe — se der erro na criação é porque já foi migrada antes.
        // Veja a migration 2026_04_07_..._add_source_to_product_images para as colunas extras.
        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('filename');
                $table->string('alt_text')->nullable();
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['product_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
