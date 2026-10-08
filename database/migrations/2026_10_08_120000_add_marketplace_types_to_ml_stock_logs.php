<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A venda da Shopee gravava operation_type 'sale', que não existia no enum:
 * o MySQL recusava o log e o pedido inteiro voltava (estoque nunca baixava).
 * Também entra o tipo do cancelamento de pedido em marketplace.
 */
return new class extends Migration
{
    private array $base = ['ml_sale', 'manual_update', 'import_excel', 'internal_sale', 'sync_to_ml', 'adjustment', 'return'];

    public function up(): void
    {
        $this->setEnum([...$this->base, 'shopee_sale', 'marketplace_cancel']);
    }

    public function down(): void
    {
        $this->setEnum($this->base);
    }

    private function setEnum(array $values): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $list = implode(',', array_map(fn ($v) => "'{$v}'", $values));
        DB::statement("ALTER TABLE ml_stock_logs MODIFY operation_type ENUM({$list}) NOT NULL COMMENT 'Tipo de operação'");
    }
};
