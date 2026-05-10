<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        if (! Schema::hasColumn('clients', 'portal_login')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('portal_login')->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('clients', 'id') || ! Schema::hasColumn('clients', 'portal_login')) {
            return;
        }

        DB::table('clients')
            ->select('id', 'portal_login')
            ->orderBy('id')
            ->get()
            ->each(function ($client): void {
                if (! blank($client->portal_login)) {
                    return;
                }

                DB::table('clients')
                    ->where('id', $client->id)
                    ->update([
                        'portal_login' => 'cli-' . str_pad((string) $client->id, 6, '0', STR_PAD_LEFT),
                    ]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients') || ! Schema::hasColumn('clients', 'portal_login')) {
            return;
        }

        try {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropUnique(['portal_login']);
            });
        } catch (\Throwable $e) {
            // Índice já removido ou não existe neste banco.
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('portal_login');
        });
    }
};