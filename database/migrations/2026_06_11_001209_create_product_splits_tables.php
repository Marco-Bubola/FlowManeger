<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Auditoria das divisões de combo/kit em produtos separados.
 * As colunas que referenciam products.id são criadas com o MESMO tipo de
 * products.id (detectado em runtime) para casar a FK em qualquer ambiente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idType = $this->referencedIntType('products', 'id');

        if (!Schema::hasTable('product_splits')) {
            Schema::create('product_splits', function (Blueprint $t) use ($idType) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $this->productRefColumn($t, 'source_product_id', $idType); // combo/kit dividido
                $t->enum('source_action', ['kept', 'archived', 'converted'])->default('kept');
                $t->boolean('distributed_stock')->default(false);
                $t->timestamps();

                $t->index(['user_id', 'created_at']);
            });

            $this->tryForeign('product_splits', 'source_product_id', 'cascade');
        }

        if (!Schema::hasTable('product_split_items')) {
            Schema::create('product_split_items', function (Blueprint $t) use ($idType) {
                $t->id();
                $t->foreignId('split_id')->constrained('product_splits')->cascadeOnDelete();
                $this->productRefColumn($t, 'result_product_id', $idType);            // produto gerado/vinculado
                $this->productRefColumn($t, 'variation_parent_id', $idType, true);    // pai (variação)
                $t->enum('mode', ['new', 'linked', 'variation']);
                $t->integer('quantity')->default(1);
                $t->integer('stock_assigned')->default(0);
                $t->timestamps();
            });

            $this->tryForeign('product_split_items', 'result_product_id', 'cascade');
            $this->tryForeign('product_split_items', 'variation_parent_id', 'null');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_split_items');
        Schema::dropIfExists('product_splits');
    }

    /** Cria uma coluna que referencia products.id com o tipo correto. */
    private function productRefColumn(Blueprint $t, string $name, string $idType, bool $nullable = false): void
    {
        $col = match ($idType) {
            'unsignedBigInteger' => $t->unsignedBigInteger($name),
            'bigInteger'         => $t->bigInteger($name),
            'unsignedInteger'    => $t->unsignedInteger($name),
            default              => $t->integer($name),
        };
        if ($nullable) {
            $col->nullable();
        }
    }

    /** Adiciona a FK para products.id sem derrubar a migração se falhar. */
    private function tryForeign(string $table, string $column, string $onDelete): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($column, $onDelete) {
                $fk = $t->foreign($column)->references('id')->on('products');
                $onDelete === 'null' ? $fk->nullOnDelete() : $fk->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // FK opcional: o app não depende da constraint no banco.
        }
    }

    private function referencedIntType(string $table, string $column): string
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );
        $type = strtolower($row->t ?? 'int');
        $unsigned = str_contains($type, 'unsigned');
        if (str_contains($type, 'bigint')) {
            return $unsigned ? 'unsignedBigInteger' : 'bigInteger';
        }
        return $unsigned ? 'unsignedInteger' : 'integer';
    }
};
