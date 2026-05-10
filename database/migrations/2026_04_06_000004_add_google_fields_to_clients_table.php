<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'google_id')) {
                $table->string('google_id')->nullable()->unique();
            }
            if (! Schema::hasColumn('clients', 'google_avatar')) {
                $table->string('google_avatar')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        $toDrop = [];
        if (Schema::hasColumn('clients', 'google_id')) {
            $toDrop[] = 'google_id';
        }
        if (Schema::hasColumn('clients', 'google_avatar')) {
            $toDrop[] = 'google_avatar';
        }

        if ($toDrop === []) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }
};