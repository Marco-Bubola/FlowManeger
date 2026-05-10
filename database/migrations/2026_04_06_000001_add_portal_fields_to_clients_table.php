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
            if (! Schema::hasColumn('clients', 'portal_password')) {
                $table->string('portal_password')->nullable();
            }
            if (! Schema::hasColumn('clients', 'portal_active')) {
                $table->boolean('portal_active')->default(false);
            }
            if (! Schema::hasColumn('clients', 'portal_token')) {
                $table->string('portal_token', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('clients', 'portal_token_expires_at')) {
                $table->timestamp('portal_token_expires_at')->nullable();
            }
            if (! Schema::hasColumn('clients', 'portal_last_login_at')) {
                $table->timestamp('portal_last_login_at')->nullable();
            }
            if (! Schema::hasColumn('clients', 'remember_token')) {
                $table->rememberToken();
            }
            // Campos adicionais para o cliente preencher no portal
            if (! Schema::hasColumn('clients', 'cpf_cnpj')) {
                $table->string('cpf_cnpj', 20)->nullable();
            }
            if (! Schema::hasColumn('clients', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            if (! Schema::hasColumn('clients', 'company')) {
                $table->string('company', 150)->nullable();
            }
            if (! Schema::hasColumn('clients', 'portal_notes')) {
                $table->text('portal_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        $columns = [
            'portal_password', 'portal_active', 'portal_token',
            'portal_token_expires_at', 'portal_last_login_at', 'remember_token',
            'cpf_cnpj', 'birth_date', 'company', 'portal_notes',
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
