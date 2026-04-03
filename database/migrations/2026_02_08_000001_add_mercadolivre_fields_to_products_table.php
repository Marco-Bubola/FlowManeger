<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode', 15)->unique()->nullable()->after('product_code')
                    ->comment('Código de barras EAN/GTIN para integração Mercado Livre');
            }

            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand', 100)->nullable()->after('barcode')
                    ->comment('Marca do produto');
            }

            if (! Schema::hasColumn('products', 'model')) {
                $table->string('model', 100)->nullable()->after('brand')
                    ->comment('Modelo específico do produto');
            }

            if (! Schema::hasColumn('products', 'warranty_months')) {
                $table->integer('warranty_months')->nullable()->default(3)->after('model')
                    ->comment('Meses de garantia do produto');
            }

            if (! Schema::hasColumn('products', 'condition')) {
                $table->enum('condition', ['new', 'used'])->default('new')->after('warranty_months')
                    ->comment('Condição do produto: new (novo) ou used (usado)');
            }
        });

        $indexExists = \DB::select("SHOW INDEX FROM products WHERE Key_name = 'idx_products_barcode'");
        if (empty($indexExists)) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('barcode', 'idx_products_barcode');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_barcode');
            $table->dropColumn(['barcode', 'brand', 'model', 'warranty_months', 'condition']);
        });
    }
};
