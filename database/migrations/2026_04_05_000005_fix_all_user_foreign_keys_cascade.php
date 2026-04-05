<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas com FK user_id → users.id sem ON DELETE CASCADE.
     * Cada entrada: [tabela, nome_da_fk]
     */
    private array $fks = [
        ['cashbook',                 'cashbook_ibfk_1'],
        ['category',                 'category_ibfk_1'],
        ['clients',                  'clients_ibfk_1'],
        ['cofrinhos',                'cofrinhos_ibfk_1'],
        ['lancamentos_recorrentes',  'lancamentos_recorrentes_ibfk_1'],
        ['products',                 'products_ibfk_2'],
        ['sales',                    'sales_ibfk_2'],
        ['segment',                  'segment_ibfk_2'],
        ['targets',                  'targets_ibfk_1'],
    ];

    public function up(): void
    {
        foreach ($this->fks as [$table, $fkName]) {
            Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
                $blueprint->dropForeign($fkName);
                $blueprint->foreign('user_id', $fkName)
                          ->references('id')->on('users')
                          ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->fks as [$table, $fkName]) {
            Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
                $blueprint->dropForeign($fkName);
                $blueprint->foreign('user_id', $fkName)
                          ->references('id')->on('users');
            });
        }
    }
};
