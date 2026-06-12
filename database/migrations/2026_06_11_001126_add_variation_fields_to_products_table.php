<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Variações (produto-pai + variantes) — NÃO destrutiva.
 * Só adiciona colunas nuláveis. Todo produto existente fica parent_id = NULL
 * e is_variation_parent = false → continua se comportando como hoje (standalone).
 *
 * A coluna parent_id é criada com o MESMO tipo de products.id (detectado em
 * runtime), para casar a foreign key em qualquer ambiente (int signed/unsigned,
 * bigint, etc.). FK e índices são idempotentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idType = $this->referencedIntType('products', 'id');

        Schema::table('products', function (Blueprint $t) use ($idType) {
            if (!Schema::hasColumn('products', 'parent_id')) {
                $col = match ($idType) {
                    'unsignedBigInteger' => $t->unsignedBigInteger('parent_id'),
                    'bigInteger'         => $t->bigInteger('parent_id'),
                    'unsignedInteger'    => $t->unsignedInteger('parent_id'),
                    default              => $t->integer('parent_id'),
                };
                $col->nullable()->after('id');
            }
            if (!Schema::hasColumn('products', 'is_variation_parent')) {
                $t->boolean('is_variation_parent')->default(false)->after('parent_id');
            }
            if (!Schema::hasColumn('products', 'variation_attribute')) {
                $t->string('variation_attribute', 60)->nullable()->after('is_variation_parent');
            }
            if (!Schema::hasColumn('products', 'variation_value')) {
                $t->string('variation_value', 120)->nullable()->after('variation_attribute');
            }
            if (!Schema::hasColumn('products', 'variation_sort')) {
                $t->integer('variation_sort')->default(0)->after('variation_value');
            }
        });

        // FK opcional (não é essencial para a lógica do app). Idempotente.
        if (!$this->foreignKeyExists('products', 'products_parent_id_foreign')) {
            try {
                Schema::table('products', function (Blueprint $t) {
                    $t->foreign('parent_id', 'products_parent_id_foreign')
                        ->references('id')->on('products')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Se a FK não puder ser criada (diferença de tipo/engine), seguimos
                // só com o índice — o app não depende da constraint no banco.
            }
        }

        if (!$this->indexExists('products', 'products_parent_id_index')) {
            Schema::table('products', fn (Blueprint $t) => $t->index(['parent_id'], 'products_parent_id_index'));
        }
        if (!$this->indexExists('products', 'products_user_variation_parent_index')) {
            Schema::table('products', fn (Blueprint $t) => $t->index(['user_id', 'is_variation_parent'], 'products_user_variation_parent_index'));
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if ($this->foreignKeyExists('products', 'products_parent_id_foreign')) {
                $t->dropForeign('products_parent_id_foreign');
            }
            if ($this->indexExists('products', 'products_parent_id_index')) {
                $t->dropIndex('products_parent_id_index');
            }
            if ($this->indexExists('products', 'products_user_variation_parent_index')) {
                $t->dropIndex('products_user_variation_parent_index');
            }
        });

        Schema::table('products', function (Blueprint $t) {
            foreach (['parent_id', 'is_variation_parent', 'variation_attribute', 'variation_value', 'variation_sort'] as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }

    /** Retorna o método Blueprint que casa com o tipo da coluna referenciada. */
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

    private function indexExists(string $table, string $index): bool
    {
        return DB::selectOne("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]) !== null;
    }

    private function foreignKeyExists(string $table, string $fk): bool
    {
        $row = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, $fk]
        );
        return $row !== null;
    }
};
