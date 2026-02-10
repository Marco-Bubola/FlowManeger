<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos necessários para integração com Mercado Livre:
     * - barcode: Código de barras EAN/GTIN
     * - brand: Marca do produto
     * - model: Modelo do produto
     * - warranty_months: Meses de garantia
     * - condition: Condição (novo/usado)
     */
    public function up(): void
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            // Código de barras EAN/GTIN (único, para integração ML)
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode', 15)->unique()->nullable()->after('product_code')
                    ->comment('Código de barras EAN/GTIN para integração Mercado Livre');
            }
            
            // Marca do produto (obrigatório em muitas categorias ML)
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand', 100)->nullable()->after('barcode')
                    ->comment('Marca do produto');
            }
            
            // Modelo do produto
            if (!Schema::hasColumn('products', 'model')) {
                $table->string('model', 100)->nullable()->after('brand')
                    ->comment('Modelo específico do produto');
            }
            
            // Garantia em meses
            if (!Schema::hasColumn('products', 'warranty_months')) {
                $table->integer('warranty_months')->nullable()->default(3)->after('model')
                    ->comment('Meses de garantia do produto');
            }
            
            // Condição do produto (novo ou usado)
            if (!Schema::hasColumn('products', 'condition')) {
                $table->enum('condition', ['new', 'used'])->default('new')->after('warranty_months')
                    ->comment('Condição do produto: new (novo) ou used (usado)');
            }
        });

        // Adicionar índice se não existir
        if (!$this->indexExists('products', 'idx_products_barcode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('barcode', 'idx_products_barcode');
            });
        }
    }

    /**
     * Verifica se um índice existe
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'");
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_barcode');
            $table->dropColumn([
                'barcode',
                'brand',
                'model',
                'warranty_months',
                'condition'
            ]);
        });
    }
};
