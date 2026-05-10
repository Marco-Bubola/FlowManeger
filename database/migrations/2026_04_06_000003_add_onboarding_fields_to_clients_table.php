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
            if (! Schema::hasColumn('clients', 'portal_force_password_change')) {
                $table->boolean('portal_force_password_change')->default(false);
            }
            if (! Schema::hasColumn('clients', 'portal_profile_completed_at')) {
                $table->timestamp('portal_profile_completed_at')->nullable();
            }
            if (! Schema::hasColumn('clients', 'cep')) {
                $table->string('cep', 9)->nullable();
            }
            if (! Schema::hasColumn('clients', 'street')) {
                $table->string('street', 180)->nullable();
            }
            if (! Schema::hasColumn('clients', 'number')) {
                $table->string('number', 20)->nullable();
            }
            if (! Schema::hasColumn('clients', 'complement')) {
                $table->string('complement', 120)->nullable();
            }
            if (! Schema::hasColumn('clients', 'neighborhood')) {
                $table->string('neighborhood', 120)->nullable();
            }
            if (! Schema::hasColumn('clients', 'city')) {
                $table->string('city', 120)->nullable();
            }
            if (! Schema::hasColumn('clients', 'state')) {
                $table->string('state', 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        $columns = [
            'portal_force_password_change',
            'portal_profile_completed_at',
            'cep',
            'street',
            'number',
            'complement',
            'neighborhood',
            'city',
            'state',
        ];

        $toDrop = array_values(array_filter($columns, static fn (string $column): bool => Schema::hasColumn('clients', $column)));
        if ($toDrop === []) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }
};