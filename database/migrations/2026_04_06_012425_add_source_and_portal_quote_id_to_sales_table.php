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
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'source')) {
                $table->string('source')->nullable(); // e.g. 'portal'
            }
            if (! Schema::hasColumn('sales', 'portal_quote_id')) {
                $table->unsignedBigInteger('portal_quote_id')->nullable();
            }
        });

        if (Schema::hasTable('client_quote_requests')) {
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->foreign('portal_quote_id')->references('id')->on('client_quote_requests')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // FK já existe ou não pode ser criada neste ambiente.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['portal_quote_id']);
            });
        } catch (\Throwable $e) {
            // FK não existe neste banco.
        }

        $toDrop = [];
        if (Schema::hasColumn('sales', 'source')) {
            $toDrop[] = 'source';
        }
        if (Schema::hasColumn('sales', 'portal_quote_id')) {
            $toDrop[] = 'portal_quote_id';
        }

        if ($toDrop === []) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }
};
