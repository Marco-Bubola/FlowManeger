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
        // Adicionar coluna module à tabela existente
        Schema::table('consortium_notifications', function (Blueprint $table) {
            $table->string('module', 50)->default('consortium')->after('id')
                ->comment('Módulo da notificação: consortium, sale, payment, client, etc');

            // Tornar consortium_id nullable para suportar outros módulos
            $table->foreignId('consortium_id')->nullable()->change();

            // Adicionar colunas genéricas para suportar outros módulos
            $table->string('entity_type', 100)->nullable()->after('consortium_id')
                ->comment('Tipo da entidade relacionada (Consortium, Sale, Payment, Client, etc)');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type')
                ->comment('ID da entidade relacionada');

            // Índice para melhor performance nas consultas
            $table->index(['module', 'is_read', 'created_at'], 'idx_module_read_created');
            $table->index(['entity_type', 'entity_id'], 'idx_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consortium_notifications', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex('idx_module_read_created');
            $table->dropIndex('idx_entity');

            // Remover colunas
            $table->dropColumn(['module', 'entity_type', 'entity_id']);

            // Restaurar consortium_id como NOT NULL
            $table->foreignId('consortium_id')->nullable(false)->change();
        });
    }
};
