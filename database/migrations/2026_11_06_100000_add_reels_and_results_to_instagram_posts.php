<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Instagram: vídeo dos Reels e os resultados de cada post (curtidas, alcance...). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_posts', function (Blueprint $t) {
            if (! Schema::hasColumn('instagram_posts', 'video')) {
                $t->string('video', 255)->nullable()->after('images');
                $t->boolean('share_to_feed')->default(true)->after('video');
                $t->string('container_id', 60)->nullable()->after('media_id');
                $t->json('metrics')->nullable()->after('permalink');
                $t->timestamp('metrics_at')->nullable()->after('metrics');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instagram_posts', function (Blueprint $t) {
            $t->dropColumn(['video', 'share_to_feed', 'container_id', 'metrics', 'metrics_at']);
        });
    }
};
