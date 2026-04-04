<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('portal_password')->nullable()->after('caminho_foto');
            $table->boolean('portal_active')->default(false)->after('portal_password');
            $table->string('portal_token', 64)->nullable()->unique()->after('portal_active');
            $table->timestamp('portal_token_expires_at')->nullable()->after('portal_token');
            $table->timestamp('portal_last_login_at')->nullable()->after('portal_token_expires_at');
            $table->rememberToken()->after('portal_last_login_at');
            // Campos adicionais para o cliente preencher no portal
            $table->string('cpf_cnpj', 20)->nullable()->after('address');
            $table->date('birth_date')->nullable()->after('cpf_cnpj');
            $table->string('company', 150)->nullable()->after('birth_date');
            $table->text('portal_notes')->nullable()->after('company');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'portal_password', 'portal_active', 'portal_token',
                'portal_token_expires_at', 'portal_last_login_at', 'remember_token',
                'cpf_cnpj', 'birth_date', 'company', 'portal_notes',
            ]);
        });
    }
};
