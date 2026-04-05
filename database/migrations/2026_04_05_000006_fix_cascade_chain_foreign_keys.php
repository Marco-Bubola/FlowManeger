<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FKs que bloqueiam a exclusão em cascata ao deletar um usuário.
     *
     * Quando o usuário é deletado → category/clients/products/sales/segment
     * são deletados em cascata. Mas essas tabelas têm filhos sem regra de
     * exclusão, o que causa RESTRICT e bloqueia toda a cadeia.
     *
     * [tabela, coluna, nome_fk, tabela_ref, coluna_ref, ação]
     */
    private array $fks = [
        // Tabelas que referenciam `category` (deletada via users→category CASCADE)
        // NOT NULL → CASCADE (dados do mesmo usuário, serão deletados de qualquer forma)
        ['invoice',                 'category_id', 'invoice_ibfk_3',                 'category', 'id_category', 'cascade'],
        ['lancamentos_recorrentes', 'category_id', 'lancamentos_recorrentes_ibfk_3', 'category', 'id_category', 'cascade'],
        ['products',                'category_id', 'products_ibfk_1',                'category', 'id_category', 'cascade'],
        ['segment',                 'category_id', 'segment_ibfk_1',                 'category', 'id_category', 'cascade'],

        // Tabelas que referenciam `clients` (deletada via users→clients CASCADE)
        ['sales',       'client_id',  'sales_ibfk_1',       'clients', 'id', 'cascade'],

        // Tabelas que referenciam `sales` (deletada via users→sales CASCADE)
        ['sale_items',  'sale_id',    'sale_items_ibfk_1',  'sales',   'id', 'cascade'],

        // Tabelas que referenciam `products` (deletada via users→products CASCADE)
        ['sale_items',  'product_id', 'sale_items_ibfk_2',  'products','id', 'cascade'],

        // Tabelas que referenciam `segment` (deletada via users→segment CASCADE)
        // segment_id é nullable → SET NULL
        ['cashbook',    'segment_id', 'cashbook_ibfk_4',    'segment', 'id', 'set null'],
    ];

    public function up(): void
    {
        foreach ($this->fks as [$table, $column, $fkName, $refTable, $refColumn, $onDelete]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $fkName, $refTable, $refColumn, $onDelete) {
                $blueprint->dropForeign($fkName);
                $blueprint->foreign($column, $fkName)
                          ->references($refColumn)->on($refTable)
                          ->onDelete($onDelete);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->fks as [$table, $column, $fkName, $refTable, $refColumn]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $fkName, $refTable, $refColumn) {
                $blueprint->dropForeign($fkName);
                $blueprint->foreign($column, $fkName)
                          ->references($refColumn)->on($refTable);
            });
        }
    }
};
