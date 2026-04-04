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
        Schema::table('users', function (Blueprint $table) {
            $table->string('website', 255)->nullable()->after('about_me');
            $table->string('twitter', 100)->nullable()->after('website');
            $table->string('instagram', 100)->nullable()->after('twitter');
            $table->string('linkedin', 100)->nullable()->after('instagram');
            $table->string('birth_date', 20)->nullable()->after('linkedin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['website', 'twitter', 'instagram', 'linkedin', 'birth_date']);
        });
    }
};
