<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('portal_force_password_change')->default(false)->after('portal_active');
            $table->timestamp('portal_profile_completed_at')->nullable()->after('portal_last_login_at');
            $table->string('cep', 9)->nullable()->after('cpf_cnpj');
            $table->string('street', 180)->nullable()->after('cep');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement', 120)->nullable()->after('number');
            $table->string('neighborhood', 120)->nullable()->after('complement');
            $table->string('city', 120)->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'portal_force_password_change',
                'portal_profile_completed_at',
                'cep',
                'street',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
            ]);
        });
    }
};